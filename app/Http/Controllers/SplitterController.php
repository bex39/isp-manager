<?php

namespace App\Http\Controllers;

use App\Models\Splitter;
use App\Models\ODP;
use App\Models\ODC;
use Illuminate\Http\Request;

class SplitterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $odpId = $request->input('odp_id');
        $odcId = $request->input('odc_id');
        $ratio = $request->input('ratio');

        $query = Splitter::with(['odp', 'odc']);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // ODP filter
        if ($odpId) {
            $query->where('odp_id', $odpId);
        }

        // ODC filter (NEW)
        if ($odcId) {
            $query->where('odc_id', $odcId);
        }

        // Ratio filter
        if ($ratio) {
            $query->where('ratio', $ratio);
        }

        $splitters = $query->latest()->paginate(20)->withQueryString();

        // Get ODPs and ODCs for filter
        $odps = ODP::where('is_active', true)->orderBy('name')->get();
        $odcs = ODC::where('is_active', true)->orderBy('name')->get();

        // Get unique ratios
        $ratios = Splitter::distinct()->pluck('ratio')->sort();

        // Statistics
        $stats = [
            'total' => Splitter::count(),
            'with_odc' => Splitter::whereNotNull('odc_id')->count(),
            'with_odp' => Splitter::whereNotNull('odp_id')->count(),
            'total_outputs' => Splitter::sum('output_ports'),
            'used_outputs' => Splitter::sum('used_outputs'),
        ];

        return view('splitters.index', compact('splitters', 'odps', 'odcs', 'ratios', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $odps = ODP::where('is_active', true)->orderBy('name')->get();
        $odcs = ODC::where('is_active', true)->orderBy('name')->get();

        return view('splitters.create', compact('odps', 'odcs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'odp_id' => 'nullable|exists:odps,id',
            'odc_id' => 'nullable|exists:odcs,id',
            'odc_port' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:100',
            'ratio' => 'required|string|max:50',
            'input_ports' => 'required|integer|min:1',
            'output_ports' => 'required|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string',
        ]);

        $validated['used_outputs'] = 0;
        $validated['total_ports'] = $validated['output_ports'];
        $validated['used_ports'] = 0;

        // Validate ODC port if ODC selected
        if ($validated['odc_id'] && $validated['odc_port']) {
            $odc = ODC::find($validated['odc_id']);

            // Check if port already used
            $portUsed = Splitter::where('odc_id', $validated['odc_id'])
                ->where('odc_port', $validated['odc_port'])
                ->exists();

            if ($portUsed) {
                return back()
                    ->with('error', "ODC port {$validated['odc_port']} is already in use!")
                    ->withInput();
            }

            // Increment ODC used_ports
            $odc->incrementUsedPorts();
        }

        $splitter = Splitter::create($validated);

        return redirect()
            ->route('splitters.index')
            ->with('success', "Splitter {$splitter->name} created successfully!");
    }

    /**
     * Display the specified resource.
     */
    public function show(Splitter $splitter)
    {
        $splitter->load(['odp.onts', 'odc.odf', 'incomingCables', 'outgoingCables']);

        // Get port usage
        $portUsage = [
            'total' => $splitter->output_ports,
            'used' => $splitter->used_outputs,
            'available' => $splitter->getAvailableOutputs(),
            'percentage' => $splitter->getUsagePercentage(),
        ];

        return view('splitters.show', compact('splitter', 'portUsage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Splitter $splitter)
    {
        $odps = ODP::where('is_active', true)->orderBy('name')->get();
        $odcs = ODC::where('is_active', true)->orderBy('name')->get();

        return view('splitters.edit', compact('splitter', 'odps', 'odcs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Splitter $splitter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'odp_id' => 'nullable|exists:odps,id',
            'odc_id' => 'nullable|exists:odcs,id',
            'odc_port' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:100',
            'ratio' => 'required|string|max:50',
            'input_ports' => 'required|integer|min:1',
            'output_ports' => 'required|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string',
        ]);

        // Handle ODC change
        $oldOdcId = $splitter->odc_id;
        $oldOdcPort = $splitter->odc_port;
        $newOdcId = $validated['odc_id'] ?? null;
        $newOdcPort = $validated['odc_port'] ?? null;

        if ($oldOdcId != $newOdcId || $oldOdcPort != $newOdcPort) {
            // Decrement old ODC
            if ($oldOdcId) {
                $oldOdc = ODC::find($oldOdcId);
                if ($oldOdc) {
                    $oldOdc->decrementUsedPorts();
                }
            }

            // Validate and increment new ODC
            if ($newOdcId && $newOdcPort) {
                $newOdc = ODC::find($newOdcId);

                // Check if new port available
                $portUsed = Splitter::where('odc_id', $newOdcId)
                    ->where('odc_port', $newOdcPort)
                    ->where('id', '!=', $splitter->id)
                    ->exists();

                if ($portUsed) {
                    return back()
                        ->with('error', "ODC port {$newOdcPort} is already in use!")
                        ->withInput();
                }

                if ($newOdc) {
                    $newOdc->incrementUsedPorts();
                }
            }
        }

        // Validate output_ports not less than used_outputs
        if ($validated['output_ports'] < $splitter->used_outputs) {
            return back()
                ->with('error', "Cannot reduce output ports below currently used outputs ({$splitter->used_outputs})!")
                ->withInput();
        }

        $validated['total_ports'] = $validated['output_ports'];

        $splitter->update($validated);

        return redirect()
            ->route('splitters.show', $splitter)
            ->with('success', "Splitter {$splitter->name} updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Splitter $splitter)
    {
        // Check if splitter has outputs in use
        if ($splitter->used_outputs > 0) {
            return back()
                ->with('error', 'Cannot delete splitter that has outputs in use!');
        }

        // Decrement ODC used_ports if connected
        if ($splitter->odc_id) {
            $odc = ODC::find($splitter->odc_id);
            if ($odc) {
                $odc->decrementUsedPorts();
            }
        }

        $name = $splitter->name;
        $splitter->delete();

        return redirect()
            ->route('splitters.index')
            ->with('success', "Splitter {$name} deleted successfully!");
    }

    /**
     * Show splitter ports
     */
    public function ports(Splitter $splitter)
    {
        $splitter->load(['odp', 'odc']);

        // Get port assignments (placeholder - implement based on your needs)
        $ports = [];
        for ($i = 1; $i <= $splitter->output_ports; $i++) {
            $ports[] = [
                'number' => $i,
                'status' => $i <= $splitter->used_outputs ? 'used' : 'available',
            ];
        }

        return view('splitters.ports', compact('splitter', 'ports'));
    }
}
