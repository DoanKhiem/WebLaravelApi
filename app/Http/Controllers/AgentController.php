<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = User::query();

        if ($request->get('id')) {
            $query->where('id', 'like', '%' . $request->get('id') . '%');
        }
        if ($request->get('full_name')) {
            $query->where('full_name', 'like', '%' . $request->get('full_name') . '%');
        }
        if ($request->get('dob')) {
            $query->where('dob', $request->get('dob'));
        }
        if ($request->get('doj')) {
            $query->where('doj', $request->get('doj'));
        }
        if ($request->get('email')) {
            $query->where('email', 'like', '%' . $request->get('email') . '%');
        }
        if ($request->get('contact_number')) {
            $query->where('contact_number', 'like', '%' . $request->get('contact_number') . '%');
        }
        if ($request->get('role')) {
            $query->where('role', $request->get('role'));
        }
        if ($request->get('level')) {
            $query->where('level', $request->get('level'));
        }

        if ($request->get('sortID')) {
            $query->orderBy('id', $request->get('sortID'));
        }
        if ($request->get('sortEmail')) {
            $query->orderBy('email', $request->get('sortEmail'));
        }
        if ($request->get('sortFullName')) {
            $query->orderBy('full_name', $request->get('sortFullName'));
        }
        if ($request->get('sortContactNumber')) {
            $query->orderBy('contact_number', $request->get('sortContactNumber'));
        }
        if ($request->get('sortDob')) {
            $query->orderBy('dob', $request->get('sortDob'));
        }
        if ($request->get('sortDoj')) {
            $query->orderBy('doj', $request->get('sortDoj'));
        }
        if ($request->get('sortRole')) {
            $query->orderBy('role', $request->get('sortRole'));
        }
        if ($request->get('sortLevel')) {
            $query->orderBy('level', $request->get('sortLevel'));
        }

        $agents = $query->orderBy('updated_at','DESC')->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $agents
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
    public function store(AgentRequest $request)
    {
        $validatedData = $request->validated();

        $member = User::create([
            'full_name' => $validatedData['full_name'],
            'email' => $validatedData['email'],
            'contact_number' => $validatedData['contact_number'],
            'dob' => $validatedData['dob'],
            'doj' => $validatedData['doj'],
            'role' => $validatedData['role'],
            'level' => $validatedData['level'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json([
            'success' => true,
            'data' => $member,
            'message' => 'Agent created successfully'
        ], 201); // Trả về mã trạng thái 201 (Created)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $agents = User::find($id);

        if (!$agents) {
            return response()->json([
                'success' => false,
                'message' => 'Agent not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $agents
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
    public function update(AgentRequest $request, string $id)
    {
        $agents = User::find($id);

        if (!$agents) {
            return response()->json([
                'success' => false,
                'message' => 'Agent not found'
            ], 404);
        }

        $validated = $request->validated();

        // If password is present in the request and it matches the password_confirmation, update the password
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $agents->update($validated);



        return response()->json([
            'success' => true,
            'data' => $agents,
            'message' => 'Agent updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $agents = User::find($id);

        if (!$agents) {
            return response()->json([
                'success' => false,
                'message' => 'Agent not found'
            ], 404);
        }

        $agents->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agent deleted successfully'
        ]);
    }
}
