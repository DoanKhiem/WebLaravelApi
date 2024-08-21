<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Http\Requests\PackageUpdateRequest;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $packages
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
    public function store(PackageRequest $request)
    {
        dd($request->all());
        $validated = $request->validated();

        $package = Package::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $package,
            'message' => 'Package created successfully'
        ], 201); // Trả về mã trạng thái 201 (Created)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $package
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PackageRequest $request, Package $package)
    {
        dd($request->all());
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $validated = $request->validated();

        $package->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $package,
            'message' => 'Package updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    }
}
