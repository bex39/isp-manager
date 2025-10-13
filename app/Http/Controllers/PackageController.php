<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PackageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_packages', only: ['index', 'show']),
            new Middleware('can:create_package', only: ['create', 'store']),
            new Middleware('can:edit_package', only: ['edit', 'update']),
            new Middleware('can:delete_package', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Package::withCount('customers');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by billing cycle
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $packages = $query->latest()->paginate(10);

        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'download_speed' => 'required|integer|min:1',
            'upload_speed' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'has_fup' => 'boolean',
            'fup_quota' => 'nullable|integer|min:1',
            'fup_speed' => 'nullable|integer|min:1',
            'billing_cycle' => 'required|in:daily,weekly,monthly,yearly',
            'grace_period' => 'required|integer|min:0|max:30',
            'burst_limit' => 'nullable|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'connection_limit' => 'nullable|integer|min:1',
            'available_for' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Set defaults
        $validated['has_fup'] = $request->has('has_fup');
        $validated['is_active'] = $request->has('is_active');

        Package::create($validated);

        return redirect()->route('packages.index')->with('success', 'Paket berhasil ditambahkan!');
    }

    public function show(Package $package)
    {
        $package->loadCount('customers');
        return view('packages.show', compact('package'));
    }

    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'download_speed' => 'required|integer|min:1',
            'upload_speed' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'has_fup' => 'boolean',
            'fup_quota' => 'nullable|integer|min:1',
            'fup_speed' => 'nullable|integer|min:1',
            'billing_cycle' => 'required|in:daily,weekly,monthly,yearly',
            'grace_period' => 'required|integer|min:0|max:30',
            'burst_limit' => 'nullable|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'connection_limit' => 'nullable|integer|min:1',
            'available_for' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['has_fup'] = $request->has('has_fup');
        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return redirect()->route('packages.index')->with('success', 'Paket berhasil diupdate!');
    }

    public function destroy(Package $package)
    {
        // Check if package has customers
        if ($package->customers()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus paket yang masih digunakan customer!');
        }

        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dihapus!');
    }
}
