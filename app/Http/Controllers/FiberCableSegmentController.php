<?php

namespace App\Http\Controllers;

use App\Models\FiberCableSegment;
use App\Models\OLT;
use App\Models\ODF;
use App\Models\ODC;
use App\Models\JointBox;
use App\Models\Splitter;
use App\Models\ODP;
use App\Models\ONT;
use Illuminate\Http\Request;

class FiberCableSegmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $cableType = $request->input('cable_type');
        $status = $request->input('status');
        $installationType = $request->input('installation_type');

        $query = FiberCableSegment::query();

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Cable type filter
        if ($cableType) {
            $query->where('cable_type', $cableType);
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Installation type filter
        if ($installationType) {
            $query->where('installation_type', $installationType);
        }

        $segments = $query->latest()->paginate(20)->withQueryString();

        // Load relationships after pagination
        $segments->load(['cores']);

        // Statistics
        $stats = [
            'total' => FiberCableSegment::count(),
            'active' => FiberCableSegment::where('status', 'active')->count(),
            'total_cores' => FiberCableSegment::sum('core_count'),
            'total_distance' => round(FiberCableSegment::sum('distance') / 1000, 2),
        ];

        return view('cable-segments.index', compact('segments', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipment = [
            'olts' => OLT::where('is_active', true)->orderBy('name')->get(),
            'odfs' => ODF::where('is_active', true)->orderBy('name')->get(),
            'odcs' => ODC::where('is_active', true)->orderBy('name')->get(),
            'joint_boxes' => JointBox::where('is_active', true)->orderBy('name')->get(),
            'splitters' => Splitter::orderBy('name')->get(),
            'odps' => ODP::where('is_active', true)->orderBy('name')->get(),
            'onts' => ONT::where('is_active', true)->orderBy('name')->get(),
        ];

        return view('cable-segments.create', compact('equipment'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:fiber_cable_segments,code',
            'cable_type' => 'required|in:backbone,distribution,drop',
            'core_count' => 'required|integer|min:1|max:288',
            'cable_brand' => 'nullable|string|max:100',
            'cable_model' => 'nullable|string|max:100',

            'start_point_type' => 'required|in:olt,odf,odc,joint_box,splitter,odp',
            'start_point_id' => 'required|integer',
            'start_latitude' => 'nullable|numeric|between:-90,90',
            'start_longitude' => 'nullable|numeric|between:-180,180',
            'start_connector_type' => 'nullable|in:SC,LC,FC,ST,E2000,MPO',
            'start_port' => 'nullable|string|max:50',

            'end_point_type' => 'required|in:odf,odc,joint_box,splitter,odp,ont',
            'end_point_id' => 'required|integer',
            'end_latitude' => 'nullable|numeric|between:-90,90',
            'end_longitude' => 'nullable|numeric|between:-180,180',
            'end_connector_type' => 'nullable|in:SC,LC,FC,ST,E2000,MPO',
            'end_port' => 'nullable|string|max:50',

            'distance' => 'nullable|numeric|min:0',
            'installation_type' => 'nullable|in:aerial,underground,duct',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'active';

        $segment = FiberCableSegment::create($validated);

        return redirect()
            ->route('cable-segments.show', $segment)
            ->with('success', "Fiber cable segment {$segment->name} created successfully!");
    }

    /**
     * Display the specified resource.
     */
    public function show(FiberCableSegment $cableSegment)
    {
        $cableSegment->load(['startPoint', 'endPoint', 'cores']);

        // Get core statistics
        $coreStats = [
            'total' => $cableSegment->core_count,
            'created' => $cableSegment->cores()->count(),
            'available' => $cableSegment->cores()->where('status', 'available')->count(),
            'used' => $cableSegment->cores()->where('status', 'used')->count(),
            'reserved' => $cableSegment->cores()->where('status', 'reserved')->count(),
            'damaged' => $cableSegment->cores()->where('status', 'damaged')->count(),
        ];

        return view('cable-segments.show', compact('cableSegment', 'coreStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FiberCableSegment $cableSegment)
{
    $cableSegment->load(['startPoint', 'endPoint', 'cores']);

    return view('cable-segments.edit', compact('cableSegment'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FiberCableSegment $cableSegment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:fiber_cable_segments,code,' . $cableSegment->id,
            'cable_type' => 'required|in:backbone,distribution,drop',
            'core_count' => 'required|integer|min:2|max:288',
            'start_point_type' => 'required|string|in:olt,odf,odc,joint_box,splitter,odp',
            'start_point_id' => 'required|integer',
            'end_point_type' => 'required|string|in:olt,odf,odc,joint_box,splitter,odp,ont',
            'end_point_id' => 'required|integer',
            'distance' => 'nullable|numeric|min:0',
            'installation_type' => 'nullable|in:aerial,underground,buried',
            'installation_date' => 'nullable|date',
            'status' => 'nullable|in:active,maintenance,damaged,reserved',
            'notes' => 'nullable|string',
        ]);

        // Validate start and end points are different
        if ($validated['start_point_type'] === $validated['end_point_type'] &&
            $validated['start_point_id'] === $validated['end_point_id']) {
            return back()
                ->withInput()
                ->with('error', 'Start point and end point must be different!');
        }

        // Validate start point exists
        $startPointModel = $this->getEquipmentModel($validated['start_point_type']);
        if (!$startPointModel::find($validated['start_point_id'])) {
            return back()
                ->withInput()
                ->with('error', 'Start point equipment not found!');
        }

        // Validate end point exists
        $endPointModel = $this->getEquipmentModel($validated['end_point_type']);
        if (!$endPointModel::find($validated['end_point_id'])) {
            return back()
                ->withInput()
                ->with('error', 'End point equipment not found!');
        }

        // Check if core count is being reduced
        if ($validated['core_count'] < $cableSegment->cores()->count()) {
            return back()
                ->withInput()
                ->with('error', "Cannot reduce core count below existing cores count ({$cableSegment->cores()->count()})!");
        }

        // Set default status
        $validated['status'] = $validated['status'] ?? 'active';

        // Update cable segment
        $cableSegment->update($validated);

        return redirect()
            ->route('cable-segments.show', $cableSegment)
            ->with('success', "Cable segment {$cableSegment->name} updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FiberCableSegment $cableSegment)
    {
        if ($cableSegment->cores()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete cable segment that has fiber cores! Please remove cores first.');
        }

        $name = $cableSegment->name;
        $cableSegment->delete();

        return redirect()
            ->route('cable-segments.index')
            ->with('success', "Cable segment {$name} deleted successfully!");
    }

    /**
     * Show fiber cores for this cable segment
     */
    public function cores(Request $request, FiberCableSegment $cableSegment)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $color = $request->input('color');

        $query = $cableSegment->cores()->orderBy('core_number');

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('core_number', 'like', "%{$search}%")
                  ->orWhere('core_color', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Color filter
        if ($color) {
            $query->where('core_color', $color);
        }

        $cores = $query->paginate(50)->withQueryString();

        // Statistics
        $stats = [
            'available' => $cableSegment->cores()->where('status', 'available')->count(),
            'used' => $cableSegment->cores()->where('status', 'used')->count(),
            'reserved' => $cableSegment->cores()->where('status', 'reserved')->count(),
            'damaged' => $cableSegment->cores()->where('status', 'damaged')->count(),
        ];

        // Get unique colors for filter
        $colors = $cableSegment->cores()
            ->whereNotNull('core_color')
            ->distinct()
            ->pluck('core_color')
            ->sort();

        return view('cable-segments.cores', compact('cableSegment', 'cores', 'stats', 'colors'));
    }

    private function getEquipmentModel($type)
{
    return match($type) {
        'olt' => \App\Models\OLT::class,
        'odf' => \App\Models\ODF::class,
        'odc' => \App\Models\ODC::class,
        'joint_box' => \App\Models\JointBox::class,
        'splitter' => \App\Models\Splitter::class,
        'odp' => \App\Models\ODP::class,
        'ont' => \App\Models\ONT::class,
        default => null
    };
}
}
