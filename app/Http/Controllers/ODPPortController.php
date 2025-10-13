<?php

namespace App\Http\Controllers;

use App\Models\ODPPort;
use App\Models\ODP;
use App\Models\FiberCore;
use App\Models\Splitter;
use App\Models\ONT;
use Illuminate\Http\Request;

class ODPPortController extends Controller
{
    /**
     * Display a listing of ports for specific ODP.
     */
    public function index(ODP $odp)
    {
        $odp->load(['onts']);

        // Get all ports for this ODP
        $ports = ODPPort::where('odp_id', $odp->id)
            ->with(['fiberCore', 'splitter', 'ont.customer'])
            ->orderBy('port_number')
            ->get();

        // If no ports exist, auto-generate based on ODP total_ports
        if ($ports->isEmpty() && $odp->total_ports > 0) {
            for ($i = 1; $i <= $odp->total_ports; $i++) {
                ODPPort::create([
                    'odp_id' => $odp->id,
                    'port_number' => $i,
                    'status' => 'available',
                ]);
            }

            // Reload ports
            $ports = ODPPort::where('odp_id', $odp->id)
                ->orderBy('port_number')
                ->get();
        }

        // Port statistics
        $stats = [
            'total' => $ports->count(),
            'available' => $ports->where('status', 'available')->count(),
            'used' => $ports->where('status', 'used')->count(),
            'reserved' => $ports->where('status', 'reserved')->count(),
            'damaged' => $ports->where('status', 'damaged')->count(),
        ];

        return view('odp-ports.index', compact('odp', 'ports', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $odpId = $request->input('odp_id');

        if (!$odpId) {
            return redirect()->route('odps.index')
                ->with('error', 'ODP ID required!');
        }

        $odp = ODP::findOrFail($odpId);
        $fiberCores = FiberCore::where('status', 'available')->get();
        $splitters = Splitter::where('is_active', true)->get();

        return view('odp-ports.create', compact('odp', 'fiberCores', 'splitters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'odp_id' => 'required|exists:odps,id',
            'port_number' => 'required|integer|min:1',
            'status' => 'required|in:available,used,reserved,damaged',
            'fiber_core_id' => 'nullable|exists:fiber_cores,id',
            'splitter_id' => 'nullable|exists:splitters,id',
            'splitter_port' => 'nullable|integer|min:1',
            'ont_id' => 'nullable|exists:onts,id',
            'notes' => 'nullable|string',
        ]);

        // Check if port number already exists for this ODP
        $existingPort = ODPPort::where('odp_id', $validated['odp_id'])
            ->where('port_number', $validated['port_number'])
            ->first();

        if ($existingPort) {
            return back()
                ->with('error', "Port number {$validated['port_number']} already exists!")
                ->withInput();
        }

        // Validate port number not exceeding ODP total_ports
        $odp = ODP::find($validated['odp_id']);
        if ($validated['port_number'] > $odp->total_ports) {
            return back()
                ->with('error', "Port number cannot exceed ODP total ports ({$odp->total_ports})!")
                ->withInput();
        }

        $port = ODPPort::create($validated);

        // Update ODP used_ports if status is used
        if ($validated['status'] === 'used' && $odp) {
            $odp->incrementUsedPorts();
        }

        return redirect()
            ->route('odp-ports.index', ['odp' => $validated['odp_id']])
            ->with('success', 'ODP Port created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ODPPort $odpPort)
    {
        $odpPort->load([
            'odp',
            'fiberCore.cableSegment',
            'splitter',
            'ont.customer',
        ]);

        return view('odp-ports.show', compact('odpPort'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ODPPort $odpPort)
    {
        $odpPort->load('odp');
        $fiberCores = FiberCore::where('status', 'available')
            ->orWhere('id', $odpPort->fiber_core_id)
            ->get();
        $splitters = Splitter::get();
        $onts = ONT::where('odp_id', $odpPort->odp_id)
            ->orWhere('id', $odpPort->ont_id)
            ->get();

        return view('odp-ports.edit', compact('odpPort', 'fiberCores', 'splitters', 'onts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ODPPort $odpPort)
    {
        $validated = $request->validate([
            'port_number' => 'required|integer|min:1',
            'status' => 'required|in:available,used,reserved,damaged',
            'fiber_core_id' => 'nullable|exists:fiber_cores,id',
            'splitter_id' => 'nullable|exists:splitters,id',
            'splitter_port' => 'nullable|integer|min:1',
            'ont_id' => 'nullable|exists:onts,id',
            'notes' => 'nullable|string',
        ]);

        // Check if port number changed and already exists
        if ($validated['port_number'] != $odpPort->port_number) {
            $existingPort = ODPPort::where('odp_id', $odpPort->odp_id)
                ->where('port_number', $validated['port_number'])
                ->where('id', '!=', $odpPort->id)
                ->first();

            if ($existingPort) {
                return back()
                    ->with('error', "Port number {$validated['port_number']} already exists!")
                    ->withInput();
            }
        }

        // Track status change for ODP used_ports update
        $oldStatus = $odpPort->status;
        $newStatus = $validated['status'];

        $odpPort->update($validated);

        // Update ODP used_ports based on status change
        $odp = $odpPort->odp;
        if ($odp) {
            if ($oldStatus !== 'used' && $newStatus === 'used') {
                $odp->incrementUsedPorts();
            } elseif ($oldStatus === 'used' && $newStatus !== 'used') {
                $odp->decrementUsedPorts();
            }
        }

        return redirect()
            ->route('odp-ports.show', $odpPort)
            ->with('success', 'ODP Port updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ODPPort $odpPort)
    {
        $odpId = $odpPort->odp_id;
        $odp = $odpPort->odp;

        // Decrement used_ports if port was in use
        if ($odpPort->status === 'used' && $odp) {
            $odp->decrementUsedPorts();
        }

        $odpPort->delete();

        return redirect()
            ->route('odp-ports.index', ['odp' => $odpId])
            ->with('success', 'ODP Port deleted successfully!');
    }

    /**
     * Assign equipment to port
     */
    public function assign(Request $request, ODPPort $odpPort)
    {
        $validated = $request->validate([
            'assignment_type' => 'required|in:fiber_core,splitter,ont',
            'fiber_core_id' => 'required_if:assignment_type,fiber_core|exists:fiber_cores,id',
            'splitter_id' => 'required_if:assignment_type,splitter|exists:splitters,id',
            'splitter_port' => 'required_if:assignment_type,splitter|integer|min:1',
            'ont_id' => 'required_if:assignment_type,ont|exists:onts,id',
            'notes' => 'nullable|string',
        ]);

        // Check if port is available
        if ($odpPort->status === 'used') {
            return back()->with('error', 'Port is already in use!');
        }

        $updateData = [
            'status' => 'used',
            'notes' => $validated['notes'] ?? $odpPort->notes,
        ];

        switch ($validated['assignment_type']) {
            case 'fiber_core':
                $updateData['fiber_core_id'] = $validated['fiber_core_id'];

                // Update fiber core status
                $fiberCore = FiberCore::find($validated['fiber_core_id']);
                if ($fiberCore) {
                    $fiberCore->update([
                        'status' => 'used',
                        'connected_to_type' => ODPPort::class,
                        'connected_to_id' => $odpPort->id,
                    ]);
                }
                break;

            case 'splitter':
                $updateData['splitter_id'] = $validated['splitter_id'];
                $updateData['splitter_port'] = $validated['splitter_port'];
                break;

            case 'ont':
                $updateData['ont_id'] = $validated['ont_id'];

                // Update ONT odp_port if not set
                $ont = ONT::find($validated['ont_id']);
                if ($ont && !$ont->odp_port) {
                    $ont->update(['odp_port' => $odpPort->port_number]);
                }
                break;
        }

        $odpPort->update($updateData);

        // Update ODP used_ports
        $odp = $odpPort->odp;
        if ($odp) {
            $odp->incrementUsedPorts();
        }

        return back()->with('success', 'Equipment assigned to port successfully!');
    }

    /**
     * Release equipment from port
     */
    public function release(Request $request, ODPPort $odpPort)
    {
        if ($odpPort->status === 'available') {
            return back()->with('info', 'Port is already available!');
        }

        // Release fiber core if connected
        if ($odpPort->fiber_core_id) {
            $fiberCore = FiberCore::find($odpPort->fiber_core_id);
            if ($fiberCore) {
                $fiberCore->update([
                    'status' => 'available',
                    'connected_to_type' => null,
                    'connected_to_id' => null,
                ]);
            }
        }

        // Update port
        $odpPort->update([
            'status' => 'available',
            'fiber_core_id' => null,
            'splitter_id' => null,
            'splitter_port' => null,
            'ont_id' => null,
        ]);

        // Update ODP used_ports
        $odp = $odpPort->odp;
        if ($odp) {
            $odp->decrementUsedPorts();
        }

        return back()->with('success', 'Port released successfully!');
    }

    /**
     * Bulk create ports for ODP
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'odp_id' => 'required|exists:odps,id',
            'start_port' => 'required|integer|min:1',
            'end_port' => 'required|integer|min:1|gte:start_port',
        ]);

        $odp = ODP::find($validated['odp_id']);

        // Validate end_port not exceeding total_ports
        if ($validated['end_port'] > $odp->total_ports) {
            return back()
                ->with('error', "End port cannot exceed ODP total ports ({$odp->total_ports})!");
        }

        $created = 0;
        $skipped = 0;

        for ($i = $validated['start_port']; $i <= $validated['end_port']; $i++) {
            // Check if port already exists
            $exists = ODPPort::where('odp_id', $validated['odp_id'])
                ->where('port_number', $i)
                ->exists();

            if (!$exists) {
                ODPPort::create([
                    'odp_id' => $validated['odp_id'],
                    'port_number' => $i,
                    'status' => 'available',
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $message = "Created {$created} port(s)";
        if ($skipped > 0) {
            $message .= ", skipped {$skipped} existing port(s)";
        }

        return redirect()
            ->route('odp-ports.index', ['odp' => $validated['odp_id']])
            ->with('success', $message);
    }

    /**
     * Get port details (AJAX)
     */
    public function getPortDetails(Request $request)
    {
        $portId = $request->input('port_id');

        if (!$portId) {
            return response()->json(['error' => 'Port ID required'], 400);
        }

        $port = ODPPort::with([
            'odp',
            'fiberCore',
            'splitter',
            'ont.customer'
        ])->find($portId);

        if (!$port) {
            return response()->json(['error' => 'Port not found'], 404);
        }

        return response()->json([
            'success' => true,
            'port' => [
                'id' => $port->id,
                'port_number' => $port->port_number,
                'status' => $port->status,
                'odp' => $port->odp ? [
                    'id' => $port->odp->id,
                    'name' => $port->odp->name,
                ] : null,
                'fiber_core' => $port->fiberCore ? [
                    'id' => $port->fiberCore->id,
                    'core_number' => $port->fiberCore->core_number,
                    'core_color' => $port->fiberCore->core_color,
                ] : null,
                'splitter' => $port->splitter ? [
                    'id' => $port->splitter->id,
                    'name' => $port->splitter->name,
                    'port' => $port->splitter_port,
                ] : null,
                'ont' => $port->ont ? [
                    'id' => $port->ont->id,
                    'name' => $port->ont->name,
                    'customer' => $port->ont->customer ? $port->ont->customer->name : null,
                ] : null,
                'notes' => $port->notes,
            ]
        ]);
    }
}
