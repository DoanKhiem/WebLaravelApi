<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Mail\LoanCreate;
use App\Mail\LoanMail;
use App\Models\Client;
use App\Models\GeneralConfig;
use App\Models\Loan;
use App\Models\LoanHistory;
use App\Models\Package;
use App\Models\PaymentPeriod;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class LoanControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dashboard = new DashboardController();
        $dashboard->cronJobLoan('all');

        $query = Loan::with(['client', 'package', 'paymentPeriod']);

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->get('key') == 1) {
            $query->whereIn('status', ['Approved', 'Late']);
        }

        if ($request->get('name_or_id')) {
            $query->whereHas('client', function ($query) use ($request) {
                $query->where('full_name', 'like', '%' . $request->get('name_or_id') . '%');
            })->orWhere('id', 'like', '%' . $request->get('name_or_id') . '%');
        }
        if ($request->get('period')) {
            $period = $request->get('period');
            $startDate = Carbon::parse($period[0])->startOfDay();
            $endDate = Carbon::parse($period[1])->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $loans = $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'clients' => Client::all(),
                'packages' => Package::all(),
                'paymentPeriods' => PaymentPeriod::all(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LoanRequest $request)
    {
        $validated = $request->validated();

        $fields = ['nid_driver_license_file', 'work_id_file', 'selfie', 'pay_slip_1', 'pay_slip_2', 'pay_slip_3'];
        $tempFiles = [];

        DB::beginTransaction(); // Bắt đầu giao dịch

        try {
            // Store files temporarily
            foreach ($fields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = $file->getClientOriginalName();
                    $tempPath = $file->storeAs('temp/'.$field, $filename, 'public');
                    $tempFiles[$field] = $tempPath;
                    $validated[$field] = $filename;
                }
            }

            // Send email
            $mailData = [
                'title' => 'Mail from Payday',
                'body' => 'Create loan successfully'
            ];
            $client = Client::find($request->client_id);
            Mail::to($client->email)->send(new LoanCreate($mailData));
            // Save loan to database
            $loan = Loan::create($validated);



            // Move files to final location
            foreach ($tempFiles as $field => $tempPath) {
                $finalPath = 'loans/'.$loan->id.'/'.$field.'/'.$validated[$field];
                Storage::disk('public')->move($tempPath, $finalPath);
            }

            DB::commit(); // Lưu tất cả các thay đổi vào cơ sở dữ liệu nếu không có lỗi
            return response()->json([
                'success' => true,
                'data' => $loan,
                'message' => 'Loan created successfully'
            ], 201); // Trả về mã trạng thái 201 (Created)
        } catch (\Exception $e) {
            // Delete temporary files if any step fails
            foreach ($tempFiles as $tempPath) {
                Storage::disk('public')->delete($tempPath);
            }
            DB::rollBack(); // Hủy tất cả các thay đổi nếu có lỗi xảy ra
            return response()->json([
                'success' => false,
                'message' => 'Loan could not be created'
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
//        $loan = Loan::with(['client', 'package', 'paymentPeriod', 'loanHistories'])
//            ->leftJoin('loan_history', 'loans.id', '=', 'loan_history.loan_id')
//            ->select('loans.*', DB::raw('SUM(loan_history.amount) as total_paid_amount'))
//            ->groupBy('loans.id')->find($id);

        $loan = Loan::with(['client', 'package', 'paymentPeriod', 'loanHistories'])->find($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $loan
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LoanRequest $request, string $id)
    {
        $validated = $request->validated();
        $loan = Loan::find($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }

        $fields = ['nid_driver_license_file', 'work_id_file', 'selfie', 'pay_slip_1', 'pay_slip_2', 'pay_slip_3'];
        $tempFiles = [];


        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $file->getClientOriginalName();
                $tempPath = $file->storeAs('temp/'.$field, $filename, 'public');
                $tempFiles[$field] = $tempPath;
                $validated[$field] = $filename;
                if ($loan->$field) {
                    Storage::disk('public')->delete('loans/'.$loan->id.'/'.$field.'/'.$loan->$field);
                }
            }
        }

        // Move files to final location
        foreach ($tempFiles as $field => $tempPath) {
            $finalPath = 'loans/'.$loan->id.'/'.$field.'/'.$validated[$field];
            Storage::disk('public')->move($tempPath, $finalPath);
        }

//        foreach ($fields as $field) {
//            if ($request->hasFile($field)) {
//                if ($loan->$field) {
//                    Storage::disk('public')->delete('loans/'.$loan->id.'/'.$field.'/'.$loan->$field);
//                }
//                $file = $request->file($field);
//                $filename = $file->getClientOriginalName();
//                $file->storeAs('loans/'.$loan->id.'/'.$field, $filename, 'public');
//                $validated[$field] = $filename;
//            }
//        }

        $loan->update($validated);

        return response()->json([
            'success' => true,
            'data' => $loan,
            'message' => 'Loan updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $loan = Loan::find($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }

        $loan->delete();
        $fields = ['nid_driver_license_file', 'work_id_file', 'selfie', 'pay_slip_1', 'pay_slip_2', 'pay_slip_3'];

        foreach ($fields as $field) {
            if ($loan->$field) {
                Storage::disk('public')->delete('loans/'.$loan->id.'/'.$field.'/'.$loan->$field);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Loan deleted successfully'
        ]);
    }

    public function exportPdf()
    {
        $loan = User::find(1);
        $this->sendLoanMailWithPdf($loan);
//        $loans = Loan::all(); // dữ liệu bạn muốn xuất ra PDF
//        $data = [
//            'title' => 'Welcome to Payday',
//            'date' => date('m/d/Y'),
//            'loans' => $loans
//        ];
//
//        $pdf = Pdf::loadView('loan_export', $data); // 'pdf_view' là tên view bạn muốn render ra PDF
//
//        $filename = 'export.pdf';
//        $path = public_path('pdf/' . $filename);
//        $pdf->save($path);
//
//        return response()->json(['url' => asset('pdf/' . $filename)]);
    }



    public function updateStatus(Request $request, string $id)
    {
        $loan = Loan::find($id);


        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }

        if($request->status == 'Approved') {
            $request->validate([
                'period_date' => 'required|date|after_or_equal:today',
                'start_date' => 'required|date|after_or_equal:today',
                'next_pay_date' => 'required|date|after_or_equal:today',
            ]);

            $loan->update($request->only('status', 'start_date', 'period_date', 'next_pay_date'));

            $pdfPath = $this->generatePdf($loan);

            $mailData = [
                'title' => 'Loan Application',
                'body' => 'Have new loan approved.
                 Attached is the loan agreement for loan number ' . $loan->id . '.'
            ];
            $email = GeneralConfig::find(1)->email_loan_approved;

            Mail::to($email)->send(new LoanMail($mailData, $pdfPath));

        }

        $loan->update($request->only('status'));

        return response()->json([
            'success' => true,
            'data' => $loan,
            'message' => 'Loan status updated successfully'
        ]);
    }

    public function updatePaidAmount(Request $request, string $id)
    {
        $loan = Loan::find($id);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }

        $loan_history = LoanHistory::create([
            'loan_id' => $loan->id,
            'paid_date' => now(),
            'amount' => $request->amount,
            'fn' => $request->fn,
        ]);
        if ($request->status) {
            $loan->update($request->only('status', 'next_pay_amount', 'paid_amount'));
        } else {
            $loan->update($request->only('next_pay_amount', 'paid_amount'));
        }

        if ($loan_history) {
            return response()->json([
                'success' => true,
                'data' => $loan,
                'message' => 'Loan paid amount updated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Loan paid amount could not be updated'
            ]);
        }
    }

    public function generatePdf($loan)
    {
        $data = [
            'title' => 'Loan Details',
            'loan' => $loan,
            'image_path' => public_path('images/logo.png'),
        ];
//        dd(public_path('images/logo.png'));
//        $pdf = Pdf::loadView('loan_export', $data);
//        $pdf->setPaper('A3', 'portrait'); // Thiết lập kích thước trang A4
//        $pdf->setPaper([0, 0, 1024, 1448], 'portrait');
//        $filename = 'loan_' . $loan->id . '.pdf';
//        $path = storage_path('app/public/pdf/' . $filename);
//        $pdf->save($path);

        $html = view('loan_detail_export', $data)->render();

        $filename = 'loan_' . $loan->id . '.pdf';
        $path = storage_path('app/public/pdf/' . $filename);

        Browsershot::html($html)
            ->margins(20, 20, 20, 20, 'px')
//            ->format('A4')
//            ->setOption('landscape', true) // Optional: set landscape mode
            ->save($path);

        return $path;
    }

    public function printLoan(string $id)
    {
        $loan = Loan::find($id);

        $pdfPath = $this->generatePdf($loan);

        $mailData = [
            'title' => 'Loan Details',
            'body' => 'Please find the attached PDF for loan details.'
        ];
        $client = Client::find($loan->client_id);

        Mail::to($client->email)->send(new LoanMail($mailData, $pdfPath));

        $filename = 'loan_' . $loan->id . '.pdf';
        return response()->json([
            'success' => true,
            'data' => $filename
        ]);
    }

    public function sendLoanMailWithPdf($loan)
    {
        $pdfPath = $this->generatePdf($loan);

        $mailData = [
            'title' => 'Loan Details',
            'body' => 'Please find the attached PDF for loan details.'
        ];
        $client = Client::find(1);

        Mail::to($client->email)->send(new LoanMail($mailData, $pdfPath));
    }

    public function exportLoan (Request $request)
    {
        $query = Loan::with(['client', 'package', 'paymentPeriod']);

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->get('name_or_id')) {
            $query->whereHas('client', function ($query) use ($request) {
                $query->where('full_name', 'like', '%' . $request->get('name_or_id') . '%');
            })->orWhere('id', 'like', '%' . $request->get('name_or_id') . '%');
        }
        if ($request->get('period')) {
            $period = $request->get('period');
            $startDate = Carbon::parse($period[0])->startOfDay();
            $endDate = Carbon::parse($period[1])->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $loans = $query->get();
        $clients = Client::get();

        $data = [
            'title' => 'Loans',
            'loans' => $loans,
            'clients' => $clients,
            'image_path' => public_path('images/logo.png'),
        ];

        $html = view('loan_export', $data)->render();

        $filename = 'loan_export_' . now()->format('d_m_Y') . '.pdf';
        $path = storage_path('app/public/pdf/' . $filename);

        Browsershot::html($html)
            ->margins(20, 20, 20, 20, 'px')
//            ->format('A4')
//            ->setOption('landscape', true) // Optional: set landscape mode
            ->save($path);

        return response()->json([
            'success' => true,
            'data' => $filename
        ]);
    }
}
