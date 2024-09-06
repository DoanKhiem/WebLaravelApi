<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentPeriodRequest;
use App\Models\PaymentPeriod;
use Illuminate\Http\Request;

class PaymentPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $name = $request->get('name');

        $query = PaymentPeriod::query();

        if ($name) {
            $query->where('title', 'like', '%' . $name . '%');
        }

        $paymentPeriod = $query->orderBy('updated_at','DESC')->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $paymentPeriod
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentPeriodRequest $request)
    {
        $validated = $request->validated();

        $paymentPeriod = PaymentPeriod::create($validated);

        return response()->json([
            'success' => true,
            'data' => $paymentPeriod,
            'message' => 'Payment Period created successfully'
        ], 201); // Trả về mã trạng thái 201 (Created)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paymentPeriod = PaymentPeriod::find($id);

        if (!$paymentPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment Period not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $paymentPeriod
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
    public function update(PaymentPeriodRequest $request, string $id)
    {
        $paymentPeriod = PaymentPeriod::find($id);

        if (!$paymentPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment Period not found'
            ], 404);
        }

        $validated = $request->validated();

        $paymentPeriod->update($validated);

        return response()->json([
            'success' => true,
            'data' => $paymentPeriod,
            'message' => 'Payment Period updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentPeriod = PaymentPeriod::find($id);

        if (!$paymentPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment Period not found'
            ], 404);
        }

        $paymentPeriod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment Period deleted successfully'
        ]);
    }
}
