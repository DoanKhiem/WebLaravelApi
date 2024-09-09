<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Mail\LoanMail;
use App\Models\Customer;
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
    public function index()
    {
        $query = Loan::with(['customer', 'package', 'paymentPeriod']);

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
                'customers' => Customer::all(),
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

        if ($request->hasFile('nid_driver_license_file')) {
            $validated['nid_driver_license_file'] = $request->file('nid_driver_license_file')->store('loans', 'public');
        }

        if ($request->hasFile('work_id_file')) {
            $validated['work_id_file'] = $request->file('work_id_file')->store('loans', 'public');
        }

        if ($request->hasFile('selfie')) {
            $validated['selfie'] = $request->file('selfie')->store('loans', 'public');
        }

        if ($request->hasFile('pay_slip_1')) {
            $validated['pay_slip_1'] = $request->file('pay_slip_1')->store('loans', 'public');
        }
        if ($request->hasFile('pay_slip_2')) {
            $validated['pay_slip_2'] = $request->file('pay_slip_2')->store('loans', 'public');
        }

        if ($request->hasFile('pay_slip_3')) {
            $validated['pay_slip_3'] = $request->file('pay_slip_3')->store('loans', 'public');
        }

        $loan = Loan::create($validated);

        if ($loan) {
            $mailData = [
                'title' => 'Mail from Payday',
                'body' => 'Create loan successfully'
            ];
            Mail::to($loan->customer->email)->send(new LoanMail($mailData));

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
        $loan = Loan::find($id);

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

        if ($request->hasFile('nid_driver_license_file')) {
            if ($loan->nid_driver_license_file) {
                Storage::disk('public')->delete($loan->nid_driver_license_file);
            }
            $validated['nid_driver_license_file'] = $request->file('nid_driver_license_file')->store('loans', 'public');
        } else {
            unset($validated['nid_driver_license_file']);
        }

        if ($request->hasFile('work_id_file')) {
            if ($loan->work_id_file) {
                Storage::disk('public')->delete($loan->work_id_file);
            }
            $validated['work_id_file'] = $request->file('work_id_file')->store('loans', 'public');
        } else {
            unset($validated['work_id_file']);
        }

        if ($request->hasFile('selfie')) {
            if ($loan->selfie) {
                Storage::disk('public')->delete($loan->selfie);
            }
            $validated['selfie'] = $request->file('selfie')->store('loans', 'public');
        } else {
            unset($validated['selfie']);
        }

        if ($request->hasFile('pay_slip_1')) {
            if ($loan->pay_slip_1) {
                Storage::disk('public')->delete($loan->pay_slip_1);
            }
            $validated['pay_slip_1'] = $request->file('pay_slip_1')->store('loans', 'public');
        } else {
            unset($validated['pay_slip_1']);
        }
        if ($request->hasFile('pay_slip_2')) {
            if ($loan->pay_slip_2) {
                Storage::disk('public')->delete($loan->pay_slip_2);
            }
            $validated['pay_slip_2'] = $request->file('pay_slip_2')->store('loans', 'public');
        } else {
            unset($validated['pay_slip_2']);
        }

        if ($request->hasFile('pay_slip_3')) {
            if ($loan->pay_slip_3) {
                Storage::disk('public')->delete($loan->pay_slip_3);
            }
            $validated['pay_slip_3'] = $request->file('pay_slip_3')->store('loans', 'public');
        } else {
            unset($validated['pay_slip_3']);
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
}
