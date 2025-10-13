<?php

namespace App\Http\Controllers;

use App\Models\ODP;
use App\Models\OLT;
use Illuminate\Http\Request;

class ODPController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = ODP::with('onts'); // Load ONTs instead of OLT

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by capacity status
        if ($status === 'full') {
            $query->whereRaw('used_ports >= total_ports');
        } elseif ($status === 'available') {
            $query->whereRaw('used_ports < total_ports');
        }

        $odps = $query->latest()->paginate(20);

        return view('odps.index', compact('odps'));
    }

    public function create()
    {
        return view('odps.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:odps,code',
            'type' => 'nullable|string|max:50',
            'total_ports' => 'required|integer|min:1|max:48',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['used_ports'] = 0; // Initialize used_ports

        ODP::create($validated);

        return redirect()
            ->route('odps.index')
            ->with('success', 'ODP created successfully!');
    }

    public function show(ODP $odp)
    {
        // Load ONTs connected to this ODP
        $odp->load(['onts.customer', 'onts.olt']);

        return view('odps.show', compact('odp'));
    }

    public function edit(ODP $odp)
    {
        return view('odps.edit', compact('odp'));
    }

    public function update(Request $request, ODP $odp)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:odps,code,' . $odp->id,
            'type' => 'nullable|string|max:50',
            'total_ports' => 'required|integer|min:1|max:48',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Check if reducing total_ports below used_ports
        if ($validated['total_ports'] < $odp->used_ports) {
            return back()
                ->with('error', "Cannot reduce total ports below currently used ports ({$odp->used_ports})!")
                ->withInput();
        }

        $odp->update($validated);

        return redirect()
            ->route('odps.index')
            ->with('success', 'ODP updated successfully!');
    }

    public function destroy(ODP $odp)
    {
        // Check if ODP has connected ONTs
        if ($odp->onts()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete ODP that has connected ONTs! Please remove ONTs first.');
        }

        $odp->delete();

        return redirect()
            ->route('odps.index')
            ->with('success', 'ODP deleted successfully!');
    }

    /**
     * Get available ports for AJAX
     */
    public function getAvailablePorts(ODP $odp)
    {
        $occupiedPorts = $odp->onts()->pluck('odp_port')->toArray();
        $availablePorts = [];

        for ($i = 1; $i <= $odp->total_ports; $i++) {
            if (!in_array($i, $occupiedPorts)) {
                $availablePorts[] = $i;
            }
        }

        return response()->json([
            'success' => true,
            'available_ports' => $availablePorts,
            'used_ports' => $odp->used_ports,
            'total_ports' => $odp->total_ports,
            'occupied_ports' => $occupiedPorts,
        ]);
    }

    /**
     * Get ODP port details
     */
    public function getPortDetails(ODP $odp, $port)
    {
        $ont = $odp->onts()->where('odp_port', $port)->first();

        if ($ont) {
            return response()->json([
                'success' => true,
                'occupied' => true,
                'ont' => [
                    'id' => $ont->id,
                    'name' => $ont->name,
                    'sn' => $ont->sn,
                    'customer' => $ont->customer ? $ont->customer->name : null,
                    'status' => $ont->status,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'occupied' => false,
        ]);
    }
}
