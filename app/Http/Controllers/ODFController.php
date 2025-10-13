<?php

namespace App\Http\Controllers;

use App\Models\ODF;
use App\Models\OLT;
use App\Models\FiberCableSegment;
use Illuminate\Http\Request;

class ODFController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $oltId = $request->input('olt_id');
        $status = $request->input('status');

        $query = ODF::with(['olt', 'odcs']);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // OLT filter
        if ($oltId) {
            $query->where('olt_id', $oltId);
        }

        // Status filter
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'full') {
            $query->whereRaw('used_ports >= total_ports');
        } elseif ($status === 'available') {
            $query->whereRaw('used_ports < total_ports');
        }

        $odfs = $query->latest()->paginate(20)->withQueryString();

        // Get OLTs for filter
        $olts = OLT::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => ODF::count(),
            'active' => ODF::where('is_active', true)->count(),
            'total_ports' => ODF::sum('total_ports'),
            'used_ports' => ODF::sum('used_ports'),
        ];

        return view('odfs.index', compact('odfs', 'olts', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $olts = OLT::where('is_active', true)->orderBy('name')->get();
        return view('odfs.create', compact('olts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:odfs,code',
            'olt_id' => 'required|exists:olts,id',
            'location' => 'nullable|string|in:indoor,outdoor',
            'total_ports' => 'required|integer|min:1|max:288',
            'rack_number' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['used_ports'] = 0; // Initialize
        $validated['location'] = $validated['location'] ?? 'indoor';

        $odf = ODF::create($validated);

        return redirect()
            ->route('odfs.index')
            ->with('success', "ODF {$odf->name} created successfully!");
    }

    /**
     * Display the specified resource.
     */
    public function show(ODF $odf)
    {
        $odf->load([
            'olt',
            'odcs.splitters',
            'incomingCables.startPoint',
            'outgoingCables.endPoint'
        ]);

        // Get port usage details
        $portUsage = [
            'total' => $odf->total_ports,
            'used' => $odf->used_ports,
            'available' => $odf->getAvailablePorts(),
            'percentage' => $odf->getUsagePercentage(),
        ];

        // Get connected cables
        $incomingCables = $odf->incomingCables;
        $outgoingCables = $odf->outgoingCables;

        return view('odfs.show', compact('odf', 'portUsage', 'incomingCables', 'outgoingCables'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ODF $odf)
    {
        $olts = OLT::where('is_active', true)->orderBy('name')->get();
        return view('odfs.edit', compact('odf', 'olts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ODF $odf)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:odfs,code,' . $odf->id,
            'olt_id' => 'required|exists:olts,id',
            'location' => 'nullable|string|in:indoor,outdoor',
            'total_ports' => 'required|integer|min:1|max:288',
            'rack_number' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Validate total_ports tidak boleh kurang dari used_ports
        if ($validated['total_ports'] < $odf->used_ports) {
            return back()
                ->with('error', "Cannot reduce total ports below currently used ports ({$odf->used_ports})!")
                ->withInput();
        }

        $odf->update($validated);

        return redirect()
            ->route('odfs.index')
            ->with('success', "ODF {$odf->name} updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ODF $odf)
    {
        // Check if ODF has connected ODCs
        if ($odf->odcs()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete ODF that has connected ODCs! Please remove ODCs first.');
        }

        // Check if ODF has connected cables
        if ($odf->outgoingCables()->count() > 0 || $odf->incomingCables()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete ODF that has connected fiber cables! Please remove cables first.');
        }

        $name = $odf->name;
        $odf->delete();

        return redirect()
            ->route('odfs.index')
            ->with('success', "ODF {$name} deleted successfully!");
    }

    /**
 * Show ODF port map
 */
public function ports(ODF $odf)
{
    $odf->load(['olt', 'odcs']);

    // Get all ports with cable connections
    $portsData = [];

    for ($i = 1; $i <= $odf->total_ports; $i++) {
        // Check if port is connected to cable (start from ODF)
        $cable = \App\Models\FiberCableSegment::where(function($q) use ($odf, $i) {
                $q->where('start_point_type', 'odf')
                  ->where('start_point_id', $odf->id)
                  ->where('start_port', $i);
            })
            ->orWhere(function($q) use ($odf, $i) {
                $q->where('end_point_type', 'odf')
                  ->where('end_point_id', $odf->id)
                  ->where('end_port', $i);
            })
            ->with(['startPoint', 'endPoint'])
            ->first();

        $status = 'available';
        if ($cable) {
            $status = 'used';
        }

        $portsData[] = [
            'number' => $i,
            'label' => 'Port ' . $i,
            'status' => $status,
            'cable' => $cable ? [
                'id' => $cable->id,
                'name' => $cable->name,
                'code' => $cable->code,
                'cable_type' => $cable->cable_type,
                'start_connector_type' => $cable->start_connector_type,
                'end_point' => $cable->endPoint ? [
                    'type' => $cable->end_point_type,
                    'name' => $cable->endPoint->name,
                ] : null,
            ] : null,
        ];
    }

    // Convert to Collection for view
    $ports = collect($portsData);

    return view('odfs.ports', compact('odf', 'ports'));
}

    /**
     * Get port map visualization
     */
    public function portMap(ODF $odf)
    {
        $portData = [];

        $usedPorts = $odf->outgoingCables()
            ->whereNotNull('start_port')
            ->with('endPoint')
            ->get()
            ->keyBy('start_port');

        for ($i = 1; $i <= $odf->total_ports; $i++) {
            $portLabel = "Port-{$i}";
            $cable = $usedPorts->get($portLabel);

            $portData[] = [
                'port' => $i,
                'label' => $portLabel,
                'status' => $cable ? 'used' : 'available',
                'cable_id' => $cable ? $cable->id : null,
                'cable_name' => $cable ? $cable->name : null,
                'destination' => $cable && $cable->endPoint ? $cable->endPoint->name : null,
            ];
        }

        return response()->json([
            'success' => true,
            'odf' => [
                'id' => $odf->id,
                'name' => $odf->name,
                'total_ports' => $odf->total_ports,
                'used_ports' => $odf->used_ports,
                'available_ports' => $odf->getAvailablePorts(),
            ],
            'ports' => $portData,
        ]);
    }

    /**
     * Get available ports (AJAX)
     */
    public function getAvailablePorts(ODF $odf)
    {
        $usedPorts = $odf->outgoingCables()
            ->whereNotNull('start_port')
            ->pluck('start_port')
            ->toArray();

        $availablePorts = [];
        for ($i = 1; $i <= $odf->total_ports; $i++) {
            $portLabel = "Port-{$i}";
            if (!in_array($portLabel, $usedPorts)) {
                $availablePorts[] = $portLabel;
            }
        }

        return response()->json([
            'success' => true,
            'total_ports' => $odf->total_ports,
            'used_ports' => count($usedPorts),
            'available_ports' => $availablePorts,
            'next_available' => $odf->getNextAvailablePort(),
        ]);
    }
}
