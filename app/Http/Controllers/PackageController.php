<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Http\Requests\PackageUpdateRequest;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $name = $request->get('name');

        $query = Package::query();

        if ($name) {
            $query->where('title', 'like', '%' . $name . '%');
        }

        $packages = $query->paginate(10);
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

        $validated = $request->validated();

        // Handle the file upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $validated['image'] = $image->storeAs('package/images', $imageName, 'public');
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $validated['file'] = $file->storeAs('package/file', $filename, 'public');
        }

        $package = Package::create($validated);

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
    public function update(PackageRequest $request, string $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $validated = $request->validated();

        // Handle the file upload
        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $validated['image'] = $image->storeAs('package/images', $imageName, 'public');
        }

        if ($request->hasFile('file')) {
            if ($package->file) {
                Storage::disk('public')->delete($package->file);
            }
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $validated['file'] = $file->storeAs('package/file', $filename, 'public');
        }

        $package->update($validated);

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
