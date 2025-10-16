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

        $query = ODP::with('onts');

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

        $stats = [
            'total' => ODP::count(),
            'active' => ODP::where('is_active', true)->count(),
            'inactive' => ODP::where('is_active', false)->count(),
        ];

        return view('odps.index', compact('odps', 'stats'));
    }

    public function create()
    {
        $olts = OLT::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('odps.create', compact('olts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:odps,code',
            'olt_id' => 'nullable|exists:olts,id',
            'total_ports' => 'required|integer|min:1|max:48',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['used_ports'] = 0;

        ODP::create($validated);

        return redirect()
            ->route('odps.index')
            ->with('success', 'ODP created successfully!');
    }

    public function show(ODP $odp)
    {
        $odp->load(['olt', 'onts']);

        $incomingCables = \App\Models\FiberCableSegment::where('end_point_type', 'odp')
            ->where('end_point_id', $odp->id)
            ->with('startPoint')
            ->get();

        $outgoingCables = \App\Models\FiberCableSegment::where('start_point_type', 'odp')
            ->where('start_point_id', $odp->id)
            ->with('endPoint')
            ->get();

        return view('odps.show', compact('odp', 'incomingCables', 'outgoingCables'));
    }

    public function edit(ODP $odp)
    {
        return view('odps.edit', compact('odp'));
    }

    public function update(Request $request, ODP $odp)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:odps,code,' . $odp->id,
            'total_ports' => 'required|integer|min:1|max:48',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',

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
            ->route('odps.show', $odp)
            ->with('success', 'ODP updated successfully!');
    }

    public function destroy(ODP $odp)
    {
        if ($odp->onts()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete ODP that has connected ONTs!');
        }

        $odp->delete();

        return redirect()
            ->route('odps.index')
            ->with('success', 'ODP deleted successfully!');
    }
}
