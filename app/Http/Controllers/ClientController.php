<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $full_name_and_email = $request->get('full_name_and_email');

        $query = Client::query();

        if ($full_name_and_email) {
            $query->where('full_name', 'like', '%' . $full_name_and_email . '%')
                ->orWhere('email', 'like', '%' . $full_name_and_email . '%');
        }
        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
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
        if ($request->get('sortStatus')) {
            $query->orderBy('status', $request->get('sortStatus'));
        }
        if ($request->get('sortDob')) {
            $query->orderBy('dob', $request->get('sortDob'));
        }

        $clients = $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $clients
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
    public function store(ClientRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = $avatar->getClientOriginalName();
            $validated['avatar'] = $filename;
        }

//        if ($request->has('dob')) {
//            $date = \DateTime::createFromFormat('d-m-Y', $request->dob);
//            $validated['dob'] = $date->format('Y-m-d');
//        }

        $client = Client::create($validated);

        if ($client && $request->hasFile('avatar')) {
            $avatar->storeAs('avatars/'.$client->id, $filename, 'public');
        }

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Client created successfully'
        ], 201); // Trả về mã trạng thái 201 (Created)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::with('loans')->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $client
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
    public function update(ClientRequest $request, string $id)
    {
        $validated = $request->validated();


        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        if ($request->hasFile('avatar')) {
            if ($client->avatar) {
                Storage::disk('public')->delete('avatars/'.$client->id.'/'.$client->avatar);
            }
            $avatar = $request->file('avatar');

            $filename = $avatar->getClientOriginalName();
            $avatar->storeAs('avatars/'.$client->id, $filename, 'public');
            $validated['avatar'] = $filename;
        } else {
            unset($validated['avatar']);
        }

//        if ($request->has('dob')) {
//            $date = \DateTime::createFromFormat('d-m-Y', $request->dob);
//            $validated['dob'] = $date->format('Y-m-d');
//        }

        $client->update($validated);

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Client updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        $client->delete();

        if ($client->avatar) {
            Storage::disk('public')->delete('avatars/'.$client->id.'/'.$client->avatar);
        }

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully'
        ]);
    }
}
