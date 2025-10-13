<?php

// app/Http/Controllers/FiberCoreController.php

namespace App\Http\Controllers;

use App\Models\FiberCore;
use App\Models\FiberCableSegment;
use App\Models\Splitter;
use App\Models\ONT;
use App\Models\ODP;
use Illuminate\Http\Request;

class FiberCoreController extends Controller
{
    public function index(Request $request)
    {
        $query = FiberCore::with([
            'cableSegment.startPoint',
            'cableSegment.endPoint',
            'connectedTo'
        ]);

        // Filter by cable segment
        if ($request->has('cable_segment_id') && $request->cable_segment_id) {
            $query->where('cable_segment_id', $request->cable_segment_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by connection
        if ($request->has('connected')) {
            if ($request->connected === 'yes') {
                $query->whereNotNull('connected_to_id');
            } elseif ($request->connected === 'no') {
                $query->whereNull('connected_to_id');
            }
        }

        $perPage = $request->get('per_page', 50);
        $cores = $query->orderBy('cable_segment_id')
            ->orderBy('core_number')
            ->paginate($perPage);

        $cableSegments = FiberCableSegment::orderBy('name')->get();

        return view('fiber.cores.index', compact('cores', 'cableSegments'));
    }

    public function create(Request $request)
    {
        $cableSegments = FiberCableSegment::all();
        $selectedSegment = null;

        if ($request->has('cable_segment_id')) {
            $selectedSegment = FiberCableSegment::find($request->cable_segment_id);
        }

        return view('fiber.cores.create', compact('cableSegments', 'selectedSegment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cable_segment_id' => 'required|exists:fiber_cable_segments,id',
            'core_number' => 'required|integer|min:1',
            'core_color' => 'nullable|string',
            'tube_number' => 'nullable|integer',
            'status' => 'required|string',
            'loss_db' => 'nullable|numeric',
            'length_km' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        // Check duplicate core number in same segment
        $exists = FiberCore::where('cable_segment_id', $validated['cable_segment_id'])
            ->where('core_number', $validated['core_number'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Core number already exists in this cable segment!');
        }

        FiberCore::create($validated);

        return redirect()->route('cores.index', ['cable_segment_id' => $validated['cable_segment_id']])
            ->with('success', 'Fiber core created!');
    }

    public function show(FiberCore $core)
    {
        $core->load([
            'cableSegment.startPoint',
            'cableSegment.endPoint',
            'connectedTo',
            'testResults',
            'inputSplices.jointBox',
            'outputSplices.jointBox'
        ]);

        return view('fiber.cores.show', compact('core'));
    }

    public function edit(FiberCore $core)
    {
        $cableSegments = FiberCableSegment::all();
        $splitters = Splitter::all();
        $onts = ONT::all();
        $odps = ODP::all();

        return view('fiber.cores.edit', compact('core', 'cableSegments', 'splitters', 'onts', 'odps'));
    }

    public function update(Request $request, FiberCore $core)
    {
        $validated = $request->validate([
            'cable_segment_id' => 'required|exists:fiber_cable_segments,id',
            'core_number' => 'required|integer|min:1',
            'core_color' => 'nullable|string',
            'tube_number' => 'nullable|integer',
            'status' => 'required|string',
            'connected_to_type' => 'nullable|string',
            'connected_to_id' => 'nullable|integer',
            'loss_db' => 'nullable|numeric',
            'length_km' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        // Check duplicate if core number changed
        if ($core->core_number != $validated['core_number'] || $core->cable_segment_id != $validated['cable_segment_id']) {
            $exists = FiberCore::where('cable_segment_id', $validated['cable_segment_id'])
                ->where('core_number', $validated['core_number'])
                ->where('id', '!=', $core->id)
                ->exists();

            if ($exists) {
                return back()->withInput()->with('error', 'Core number already exists in this cable segment!');
            }
        }

        $core->update($validated);

        return redirect()->route('cores.show', $core)->with('success', 'Fiber core updated!');
    }

    public function destroy(FiberCore $core)
    {
        if ($core->status === 'used') {
            return back()->with('error', 'Cannot delete fiber core in use!');
        }

        $core->delete();
        return redirect()->route('cores.index')->with('success', 'Fiber core deleted!');
    }

    /**
     * Assign fiber core to device
     */
    public function assign(Request $request, FiberCore $core)
    {
        $validated = $request->validate([
            'connected_to_type' => 'required|string',
            'connected_to_id' => 'required|integer',
        ]);

        $core->update([
            'status' => 'used',
            'connected_to_type' => $validated['connected_to_type'],
            'connected_to_id' => $validated['connected_to_id'],
        ]);

        return back()->with('success', 'Fiber core assigned successfully!');
    }

    /**
     * Release fiber core
     */
    public function release(FiberCore $core)
    {
        $core->update([
            'status' => 'available',
            'connected_to_type' => null,
            'connected_to_id' => null,
        ]);

        return back()->with('success', 'Fiber core released!');
    }

    /**
     * Bulk create cores for cable segment
     */
    /**
 * Bulk create cores for cable segment
 */
public function bulkCreate(Request $request)
{
    $validated = $request->validate([
        'cable_segment_id' => 'required|exists:fiber_cable_segments,id',
        'start_core' => 'required|integer|min:1',
        'end_core' => 'required|integer|min:1',
        'auto_color' => 'nullable|boolean',
        'status' => 'nullable|in:available,reserved',
    ]);

    // Validate end_core >= start_core
    if ($validated['end_core'] < $validated['start_core']) {
        return back()
            ->with('error', 'End core number must be greater than or equal to start core number!')
            ->withInput();
    }

    $segment = FiberCableSegment::findOrFail($validated['cable_segment_id']);

    // Validate not exceeding cable capacity
    if ($validated['end_core'] > $segment->core_count) {
        return back()
            ->with('error', "End core number ({$validated['end_core']}) exceeds cable capacity ({$segment->core_count})!")
            ->withInput();
    }

    $colors = FiberCore::getCoreColors();
    $autoColor = $validated['auto_color'] ?? true;
    $status = $validated['status'] ?? 'available';
    $created = 0;
    $skipped = 0;

    for ($i = $validated['start_core']; $i <= $validated['end_core']; $i++) {
        // Check if exists
        $exists = FiberCore::where('cable_segment_id', $segment->id)
            ->where('core_number', $i)
            ->exists();

        if ($exists) {
            $skipped++;
            continue;
        }

        $tubeNumber = ceil($i / 12);
        $colorIndex = (($i - 1) % 12);  // 0-11

        FiberCore::create([
            'cable_segment_id' => $segment->id,
            'core_number' => $i,
            'core_color' => $autoColor ? ($colors[$colorIndex] ?? null) : null,
            'tube_number' => $tubeNumber,
            'status' => $status,
        ]);

        $created++;
    }

    $message = "Created {$created} fiber cores!";
    if ($skipped > 0) {
        $message .= " ({$skipped} already existed and were skipped)";
    }

    return redirect()
        ->route('cable-segments.cores', $segment)
        ->with('success', $message);
}

    /**
     * Get single core data for API/AJAX (for edit modal)
     */
    public function showApi(FiberCore $core)
    {
        $core->load('cableSegment');
        return response()->json($core);
    }
}
