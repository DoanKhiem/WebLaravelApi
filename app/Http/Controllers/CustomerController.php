<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $name = $request->get('name');

        $query = Customer::query();

        if ($name) {
            $query->where('full_name', 'like', '%' . $name . '%')
                ->orWhere('email', 'like', '%' . $name . '%');
        }

        $customers = $query->orderBy('updated_at','DESC')->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $customers
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
    public function store(CustomerRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = $avatar->getClientOriginalName();
            $validated['avatar'] = $filename;
        }

        if ($request->has('dob')) {
            $date = \DateTime::createFromFormat('d-m-Y', $request->dob);
            $validated['dob'] = $date->format('Y-m-d');
        }

        $customer = Customer::create($validated);

        if ($customer && $request->hasFile('avatar')) {
            $avatar->storeAs('avatars/'.$customer->id, $filename, 'public');
        }

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer created successfully'
        ], 201); // Trả về mã trạng thái 201 (Created)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $customer
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
    public function update(CustomerRequest $request, string $id)
    {
        $validated = $request->validated();


        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        if ($request->hasFile('avatar')) {
            if ($customer->avatar) {
                Storage::disk('public')->delete('avatars/'.$customer->id.'/'.$customer->avatar);
            }
            $avatar = $request->file('avatar');

            $filename = $avatar->getClientOriginalName();
            $avatar->storeAs('avatars/'.$customer->id, $filename, 'public');
            $validated['avatar'] = $filename;
        } else {
            unset($validated['avatar']);
        }

        if ($request->has('dob')) {
            $date = \DateTime::createFromFormat('d-m-Y', $request->dob);
            $validated['dob'] = $date->format('Y-m-d');
        }

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer->delete();

        if ($customer->avatar) {
            Storage::disk('public')->delete('avatars/'.$customer->id.'/'.$customer->avatar);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }
}
