<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Mail\LoanCreate;
use App\Mail\LoanMail;
use App\Models\AmountLoan;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Package;
use App\Models\PaymentPeriod;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LoanControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Loan::with(['client', 'package', 'paymentPeriod', 'amountLoan']);

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->get('name_or_id')) {
            $query->whereHas('client', function ($query) use ($request) {
                $query->where('full_name', 'like', '%' . $request->get('name_or_id') . '%');
            })->orWhere('id', 'like', '%' . $request->get('name_or_id') . '%');
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

            AmountLoan::create([
                'loan_id' => $loan->id,
                'fn1_amount' => $validated['fn1_amount'],
                'fn2_amount' => $validated['fn2_amount'],
                'fn3_amount' => $validated['fn3_amount'],
                'fn4_amount' => $validated['fn4_amount'],
                'fn5_amount' => $validated['fn5_amount'],
                'fn6_amount' => $validated['fn6_amount'],
                'outstanding_amount' => $validated['outstanding_amount'],
            ]);

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
        $loan = Loan::with(['client', 'package', 'paymentPeriod', 'amountLoan'])->find($id);

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

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                if ($loan->$field) {
                    Storage::disk('public')->delete('loans/'.$loan->id.'/'.$field.'/'.$loan->$field);
                }
                $file = $request->file($field);
                $filename = $file->getClientOriginalName();
                $file->storeAs('loans/'.$loan->id.'/'.$field, $filename, 'public');
                $validated[$field] = $filename;
            }
        }

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

        if($request->status == 'Approved'){
            $request->validate([
                'fn1_date' => 'required|date|after_or_equal:today',
                'start_date' => 'required|date|after_or_equal:today',
            ]);

            $loan->update($request->only('status', 'start_date'));

            $amountLoan = AmountLoan::where('loan_id', $loan->id)->whereNull('deleted_at')->first();

            $amountLoan->update([
                'fn1_date' => $request->fn1_date,
                'fn2_date' => date('Y-m-d', strtotime($request->fn1_date . ' + 14 days')),
                'fn3_date' => date('Y-m-d', strtotime($request->fn1_date . ' + 28 days')),
                'fn4_date' => date('Y-m-d', strtotime($request->fn1_date . ' + 42 days')),
                'fn5_date' => date('Y-m-d', strtotime($request->fn1_date . ' + 56 days')),
                'fn6_date' => date('Y-m-d', strtotime($request->fn1_date . ' + 70 days')),
            ]);


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

        $amountLoan = AmountLoan::where('loan_id', $loan->id)->whereNull('deleted_at')->first();
        if ($request->status) {
            $loan->update($request->only('status'));
        }

        $amountLoan->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $amountLoan,
            'message' => 'Loan paid amount updated successfully'
        ]);
    }
    public function generatePdf($loan)
    {
        $data = [
            'title' => 'Loan Details',
            'loan' => $loan
        ];

        $pdf = Pdf::loadView('loan_export', $data);
        $filename = 'loan_' . $loan->id . '.pdf';
        $path = storage_path('app/public/pdf/' . $filename);
        $pdf->save($path);

        return $path;
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
}
