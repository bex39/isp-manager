<?php

namespace App\Http\Controllers;

use App\Models\ODC;
use App\Models\ODF;
use App\Models\FiberCableSegment;
use App\Models\Splitter;
use Illuminate\Http\Request;

class ODCController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $odfId = $request->input('odf_id');
        $status = $request->input('status');

        $query = ODC::with(['odf.olt', 'splitters']);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // ODF filter
        if ($odfId) {
            $query->where('odf_id', $odfId);
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

        $odcs = $query->latest()->paginate(20)->withQueryString();

        // Get ODFs for filter
        $odfs = ODF::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => ODC::count(),
            'active' => ODC::where('is_active', true)->count(),
            'total_ports' => ODC::sum('total_ports'),
            'used_ports' => ODC::sum('used_ports'),
            'splitters' => Splitter::whereNotNull('odc_id')->count(),

        ];

        return view('odcs.index', compact('odcs', 'odfs', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $odfs = ODF::where('is_active', true)->orderBy('name')->get();
        return view('odcs.create', compact('odfs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:odcs,code',
            'odf_id' => 'nullable|exists:odfs,id',
            'type' => 'nullable|string|in:outdoor_cabinet,indoor_cabinet',
            'total_ports' => 'required|integer|min:1|max:576',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['used_ports'] = 0; // Initialize
        $validated['type'] = $validated['type'] ?? 'outdoor_cabinet';

        $odc = ODC::create($validated);

        return redirect()
            ->route('odcs.index')
            ->with('success', "ODC {$odc->name} created successfully!");
    }

    /**
     * Display the specified resource.
     */
    public function show(ODC $odc)
    {
        $odc->load([
            'odf.olt',
            'splitters.odp',
            'incomingCables.startPoint',
            'outgoingCables.endPoint'
        ]);

        // Get port usage details
        $portUsage = [
            'total' => $odc->total_ports,
            'used' => $odc->used_ports,
            'available' => $odc->getAvailablePorts(),
            'percentage' => $odc->getUsagePercentage(),
        ];

        // Get connected cables
        $incomingCables = $odc->incomingCables;
        $outgoingCables = $odc->outgoingCables;

        // Get connected splitters
        $splitters = $odc->splitters;

        return view('odcs.show', compact('odc', 'portUsage', 'incomingCables', 'outgoingCables', 'splitters'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ODC $odc)
    {
        $odfs = ODF::where('is_active', true)->orderBy('name')->get();
        return view('odcs.edit', compact('odc', 'odfs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ODC $odc)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:odcs,code,' . $odc->id,
            'odf_id' => 'nullable|exists:odfs,id',
            'type' => 'nullable|string|in:outdoor_cabinet,indoor_cabinet',
            'total_ports' => 'required|integer|min:1|max:576',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Validate total_ports tidak boleh kurang dari used_ports
        if ($validated['total_ports'] < $odc->used_ports) {
            return back()
                ->with('error', "Cannot reduce total ports below currently used ports ({$odc->used_ports})!")
                ->withInput();
        }

        $odc->update($validated);

        return redirect()
            ->route('odcs.index')
            ->with('success', "ODC {$odc->name} updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ODC $odc)
    {
        // Check if ODC has connected Splitters
        if ($odc->splitters()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete ODC that has connected Splitters! Please remove Splitters first.');
        }

        // Check if ODC has connected cables
        if ($odc->outgoingCables()->count() > 0 || $odc->incomingCables()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete ODC that has connected fiber cables! Please remove cables first.');
        }

        $name = $odc->name;
        $odc->delete();

        return redirect()
            ->route('odcs.index')
            ->with('success', "ODC {$name} deleted successfully!");
    }

    /**
     * Get port details
     */
    public function ports(ODC $odc)
    {
        $ports = [];

        // Get all outgoing cables and their ports
        $usedPorts = $odc->outgoingCables()
            ->whereNotNull('start_port')
            ->pluck('start_port')
            ->toArray();

        // Get splitters and their ports
        $splitterPorts = $odc->splitters()
            ->whereNotNull('odc_port')
            ->get()
            ->keyBy('odc_port');

        // Generate port map
        for ($i = 1; $i <= $odc->total_ports; $i++) {
            $portLabel = "Port-{$i}";
            $isUsedByCable = in_array($portLabel, $usedPorts);
            $isUsedBySplitter = $splitterPorts->has($i);

            $cable = null;
            $splitter = null;

            if ($isUsedByCable) {
                $cable = $odc->outgoingCables()
                    ->where('start_port', $portLabel)
                    ->with('endPoint')
                    ->first();
            }

            if ($isUsedBySplitter) {
                $splitter = $splitterPorts->get($i);
            }

            $ports[] = [
                'number' => $i,
                'label' => $portLabel,
                'status' => ($isUsedByCable || $isUsedBySplitter) ? 'used' : 'available',
                'cable' => $cable ? [
                    'id' => $cable->id,
                    'name' => $cable->name,
                    'end_point' => $cable->endPoint ? [
                        'type' => class_basename($cable->endPoint),
                        'name' => $cable->endPoint->name,
                    ] : null,
                ] : null,
                'splitter' => $splitter ? [
                    'id' => $splitter->id,
                    'name' => $splitter->name,
                    'ratio' => $splitter->ratio,
                ] : null,
            ];
        }

        return view('odcs.ports', compact('odc', 'ports'));
    }

    /**
     * Get port map visualization
     */
    public function portMap(ODC $odc)
    {
        $portData = [];

        $usedPorts = $odc->outgoingCables()
            ->whereNotNull('start_port')
            ->with('endPoint')
            ->get()
            ->keyBy('start_port');

        $splitterPorts = $odc->splitters()
            ->whereNotNull('odc_port')
            ->get()
            ->keyBy('odc_port');

        for ($i = 1; $i <= $odc->total_ports; $i++) {
            $portLabel = "Port-{$i}";
            $cable = $usedPorts->get($portLabel);
            $splitter = $splitterPorts->get($i);

            $portData[] = [
                'port' => $i,
                'label' => $portLabel,
                'status' => ($cable || $splitter) ? 'used' : 'available',
                'cable_id' => $cable ? $cable->id : null,
                'cable_name' => $cable ? $cable->name : null,
                'destination' => $cable && $cable->endPoint ? $cable->endPoint->name : null,
                'splitter_id' => $splitter ? $splitter->id : null,
                'splitter_name' => $splitter ? $splitter->name : null,
            ];
        }

        return response()->json([
            'success' => true,
            'odc' => [
                'id' => $odc->id,
                'name' => $odc->name,
                'total_ports' => $odc->total_ports,
                'used_ports' => $odc->used_ports,
                'available_ports' => $odc->getAvailablePorts(),
            ],
            'ports' => $portData,
        ]);
    }

    /**
     * Get available ports (AJAX)
     */
    public function getAvailablePorts(ODC $odc)
    {
        $usedPorts = $odc->outgoingCables()
            ->whereNotNull('start_port')
            ->pluck('start_port')
            ->toArray();

        $splitterPorts = $odc->splitters()
            ->whereNotNull('odc_port')
            ->pluck('odc_port')
            ->map(fn($port) => "Port-{$port}")
            ->toArray();

        $allUsedPorts = array_unique(array_merge($usedPorts, $splitterPorts));

        $availablePorts = [];
        for ($i = 1; $i <= $odc->total_ports; $i++) {
            $portLabel = "Port-{$i}";
            if (!in_array($portLabel, $allUsedPorts)) {
                $availablePorts[] = $portLabel;
            }
        }

        return response()->json([
            'success' => true,
            'total_ports' => $odc->total_ports,
            'used_ports' => count($allUsedPorts),
            'available_ports' => $availablePorts,
            'next_available' => $odc->getNextAvailablePort(),
        ]);
    }
}
