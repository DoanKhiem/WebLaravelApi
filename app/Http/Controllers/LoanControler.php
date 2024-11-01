<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Mail\LoanMail;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Package;
use App\Models\PaymentPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LoanControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        $loans = $query->orderBy('updated_at','DESC')->paginate(10);
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

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $file->getClientOriginalName();
                $validated[$field] = $filename;
            }
        }
        if ($request->has('next_fn_pay')) {
            $date = \DateTime::createFromFormat('d-m-Y', $request->next_fn_pay);
//            $formattedDate = $date->format('Y-m-d');
//            $validated['next_fn_pay'] = $formattedDate;
            $validated['next_fn_pay'] = $date->format('Y-m-d');
        }

        $loan = Loan::create($validated);

        if ($loan) {
            foreach ($fields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = $file->getClientOriginalName();
                    $file->storeAs('loans/'.$loan->id.'/'.$field, $filename, 'public');
                }
            }

            $mailData = [
                'title' => 'Mail from Payday',
                'body' => 'Create loan successfully'
            ];
            Mail::to($loan->client->email)->send(new LoanMail($mailData));

            return response()->json([
                'success' => true,
                'data' => $loan,
                'message' => 'Loan created successfully'
            ], 201); // Trả về mã trạng thái 201 (Created)
        } else {
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
        $loan = Loan::with(['client', 'package', 'paymentPeriod'])->find($id);

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

        if ($request->has('next_fn_pay')) {
            $date = \DateTime::createFromFormat('d-m-Y', $request->next_fn_pay);
            $validated['next_fn_pay'] = $date->format('Y-m-d');
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
        $loans = Loan::all(); // dữ liệu bạn muốn xuất ra PDF
        $data = [
            'title' => 'Welcome to Payday',
            'date' => date('m/d/Y'),
            'loans' => $loans
        ];

        $pdf = Pdf::loadView('loan_export', $data); // 'pdf_view' là tên view bạn muốn render ra PDF

        $filename = 'export.pdf';
        $path = public_path('pdf/' . $filename);
        $pdf->save($path);

        return response()->json(['url' => asset('pdf/' . $filename)]);
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
        $loan->update($request->only('status'));

        return response()->json([
            'success' => true,
            'data' => $loan,
            'message' => 'Loan status updated successfully'
        ]);
    }
}
