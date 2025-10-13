<?php

namespace App\Http\Controllers;

use App\Models\JointBox;
use App\Models\FiberSplice;
use Illuminate\Http\Request;

class JointBoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->input('search');
    $type = $request->input('type');
    $status = $request->input('status');
    $installationType = $request->input('installation_type');

    $query = JointBox::withCount('splices');

    // Search filter
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    // Type filter
    if ($type) {
        $query->where('type', $type);
    }

    // Status filter
    if ($status) {
        if ($status === 'active') {
            $query->where('is_active', true);
        } else {
            $query->where('status', $status);
        }
    }

    // Installation type filter
    if ($installationType) {
        $query->where('installation_type', $installationType);
    }

    $jointBoxes = $query->latest()->paginate(20)->withQueryString();

    // Statistics
    $stats = [
        'total' => JointBox::count(),
        'active' => JointBox::where('is_active', true)->count(),
        'total_splices' => \App\Models\FiberSplice::count(),
        'available_capacity' => JointBox::sum('capacity') - \App\Models\FiberSplice::count(),
    ];

    return view('joint-boxes.index', compact('jointBoxes', 'stats'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('joint-boxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:joint_boxes,code',
            'type' => 'required|in:inline,branch,terminal',
            'capacity' => 'required|integer|min:1|max:288',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'installation_type' => 'nullable|in:aerial,underground,buried',
            'installation_date' => 'nullable|date',
            'status' => 'nullable|in:active,maintenance,damaged',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['status'] = $validated['status'] ?? 'active';

        $jointBox = JointBox::create($validated);

        return redirect()
            ->route('joint-boxes.show', $jointBox)
            ->with('success', "Joint box {$jointBox->name} created successfully!");
    }

    /**
     * Display the specified resource.
     */
    public function show(JointBox $jointBox)
    {
        $jointBox->load([
            'splices.inputSegment',
            'splices.outputSegment',
            'incomingCables.startPoint',
            'outgoingCables.endPoint',
        ]);

        // Get capacity info
        $capacity = [
            'total' => $jointBox->capacity,
            'used' => $jointBox->used_capacity,
            'available' => $jointBox->getAvailableCapacity(),
            'percentage' => $jointBox->getUsagePercentage(),
        ];

        // Get splices
        $splices = $jointBox->splices()
            ->with(['inputSegment', 'outputSegment'])
            ->latest()
            ->get();

        return view('joint-boxes.show', compact('jointBox', 'capacity', 'splices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JointBox $jointBox)
    {
        return view('joint-boxes.edit', compact('jointBox'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JointBox $jointBox)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:100|unique:joint_boxes,code,' . $jointBox->id,
        'type' => 'required|in:inline,branch,terminal',
        'capacity' => 'required|integer|min:1|max:288',
        'location' => 'nullable|string',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'installation_type' => 'nullable|in:aerial,underground,buried',
        'installation_date' => 'nullable|date',
        'status' => 'nullable|in:active,maintenance,damaged',
        'notes' => 'nullable|string',
    ]);

    // Check if reducing capacity below current splice count
    if ($validated['capacity'] < $jointBox->splices()->count()) {
        return back()
            ->withInput()
            ->with('error', "Cannot reduce capacity below current splice count ({$jointBox->splices()->count()})!");
    }

    $validated['is_active'] = $request->has('is_active');
    $validated['status'] = $validated['status'] ?? 'active';

    $jointBox->update($validated);

    return redirect()
        ->route('joint-boxes.show', $jointBox)
        ->with('success', "Joint box {$jointBox->name} updated successfully!");
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JointBox $jointBox)
    {
        // Check if has splices
        if ($jointBox->splices()->count() > 0) {
            return back()
                ->with('error', 'Cannot delete joint box that has splices! Please remove splices first.');
        }

        $name = $jointBox->name;
        $jointBox->delete();

        return redirect()
            ->route('joint-boxes.index')
            ->with('success', "Joint Box {$name} deleted successfully!");
    }

    /**
     * Show splices for joint box (NEW)
     */
    /*public function splices(JointBox $jointBox)
    {
        $jointBox->load(['splices.inputSegment', 'splices.outputSegment']);

        $splices = $jointBox->splices()
            ->with([
                'inputSegment.startPoint',
                'inputSegment.endPoint',
                'outputSegment.startPoint',
                'outputSegment.endPoint',
            ])
            ->latest('splice_date')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_splices' => $jointBox->splices()->count(),
            'fusion_splices' => $jointBox->splices()->where('splice_type', 'fusion')->count(),
            'mechanical_splices' => $jointBox->splices()->where('splice_type', 'mechanical')->count(),
            'avg_loss' => round($jointBox->splices()->avg('splice_loss'), 2),
        ];

        return view('joint-boxes.splices', compact('jointBox', 'splices', 'stats'));
    }*/

    public function splices(Request $request, JointBox $jointBox)
    {
        $search = $request->input('search');
        $spliceType = $request->input('splice_type');
        $sortBy = $request->input('sort_by', 'date');

        $query = $jointBox->splices()
            ->with(['inputSegment', 'outputSegment']);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('inputSegment', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhereHas('outputSegment', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
                });
            });
        }

        // Splice type filter
        if ($spliceType) {
            $query->where('splice_type', $spliceType);
        }

        // Sorting
        switch ($sortBy) {
            case 'loss':
                $query->orderBy('splice_loss', 'desc');
                break;
            case 'input':
                $query->orderBy('input_segment_id');
                break;
            default:
                $query->latest('splice_date');
        }

        $splices = $query->paginate(20)->withQueryString();

        // Get cable segments for dropdown
        $cableSegments = \App\Models\FiberCableSegment::orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => $jointBox->splices()->count(),
            'avg_loss' => number_format($jointBox->splices()->avg('splice_loss') ?? 0, 2),
            'utilization' => $jointBox->capacity > 0
                ? round(($jointBox->splices()->count() / $jointBox->capacity) * 100, 1)
                : 0,
        ];

        return view('joint-boxes.splices', compact('jointBox', 'splices', 'cableSegments', 'stats'));
    }
}
