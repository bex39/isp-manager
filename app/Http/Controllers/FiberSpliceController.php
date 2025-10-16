<?php

// content of app/Http/Controllers/FiberSpliceController.php

namespace App\Http\Controllers;

use App\Models\FiberSplice;
use App\Models\JointBox;
use App\Models\FiberCableSegment;
use App\Models\FiberCore;
use Illuminate\Http\Request;

class FiberSpliceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $jointBoxId = $request->input('joint_box_id');
        $spliceType = $request->input('splice_type');
        $technician = $request->input('technician');

        $query = FiberSplice::with([
            'jointBox',
            'inputSegment',
            'outputSegment',
        ]);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('technician', 'like', "%{$search}%")
                  ->orWhereHas('jointBox', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Joint Box filter
        if ($jointBoxId) {
            $query->where('joint_box_id', $jointBoxId);
        }

        // Splice Type filter
        if ($spliceType) {
            $query->where('splice_type', $spliceType);
        }

        // Technician filter
        if ($technician) {
            $query->where('technician', 'like', "%{$technician}%");
        }

        $splices = $query->latest()->paginate(20)->withQueryString();

        // Get Joint Boxes for filter
        $jointBoxes = JointBox::where('is_active', true)->orderBy('name')->get();

        // Get unique technicians
        $technicians = FiberSplice::distinct()
            ->whereNotNull('technician')
            ->pluck('technician')
            ->sort();

        // Statistics
        $stats = [
            'total' => FiberSplice::count(),
            'fusion' => FiberSplice::where('splice_type', 'fusion')->count(),
            'mechanical' => FiberSplice::where('splice_type', 'mechanical')->count(),
            'avg_loss' => round(FiberSplice::avg('splice_loss') ?? 0, 2),
        ];

        return view('fiber-splices.index', compact('splices', 'jointBoxes', 'technicians', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jointBoxes = JointBox::where('is_active', true)->orderBy('name')->get();
        $cableSegments = FiberCableSegment::where('status', 'active')->orderBy('name')->get();

        return view('fiber-splices.create', compact('jointBoxes', 'cableSegments'));
    }

    /**
     * Show the form for creating splice for specific joint box.
     */
    public function createForJointBox(JointBox $jointBox)
    {
        $cableSegments = FiberCableSegment::where('status', 'active')->orderBy('name')->get();

        return view('fiber-splices.create', compact('jointBox', 'cableSegments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'joint_box_id' => 'required|exists:joint_boxes,id',
            'input_segment_id' => 'required|exists:fiber_cable_segments,id',
            'input_core_number' => 'required|integer|min:1',
            'output_segment_id' => 'required|exists:fiber_cable_segments,id',
            'output_core_number' => 'required|integer|min:1',
            'splice_type' => 'required|in:fusion,mechanical',
            'splice_loss' => 'nullable|numeric|min:0|max:5',
            'splice_date' => 'nullable|date',
            'technician' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate input and output segments are different
        if ($validated['input_segment_id'] == $validated['output_segment_id']) {
            return back()
                ->with('error', 'Input and output cable segments must be different!')
                ->withInput();
        }

        // Validate input core exists and available
        $inputCore = FiberCore::where('cable_segment_id', $validated['input_segment_id'])
            ->where('core_number', $validated['input_core_number'])
            ->first();

        if (!$inputCore) {
            return back()
                ->with('error', "Input core #{$validated['input_core_number']} not found in selected cable segment!")
                ->withInput();
        }

        // Validate output core exists and available
        $outputCore = FiberCore::where('cable_segment_id', $validated['output_segment_id'])
            ->where('core_number', $validated['output_core_number'])
            ->first();

        if (!$outputCore) {
            return back()
                ->with('error', "Output core #{$validated['output_core_number']} not found in selected cable segment!")
                ->withInput();
        }

        // Check if cores are already used in another splice
        $existingSplice = FiberSplice::where(function($q) use ($validated) {
            $q->where('input_segment_id', $validated['input_segment_id'])
            ->where('input_core_number', $validated['input_core_number']);
        })->orWhere(function($q) use ($validated) {
            $q->where('output_segment_id', $validated['output_segment_id'])
            ->where('output_core_number', $validated['output_core_number']);
        })->first();

        if ($existingSplice) {
            return back()
                ->with('error', 'One or both cores are already used in another splice!')
                ->withInput();
        }

        // Set default values
        $validated['splice_loss'] = $validated['splice_loss'] ?? ($validated['splice_type'] === 'fusion' ? 0.1 : 0.3);
        $validated['splice_date'] = $validated['splice_date'] ?? now();

        // Create splice
        $splice = FiberSplice::create($validated);

        // Update fiber cores status
        $inputCore->update(['status' => 'used']);
        $outputCore->update(['status' => 'used']);

        return redirect()
            ->route('joint-boxes.splices', $splice->joint_box_id)
            ->with('success', 'Fiber splice created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(FiberSplice $fiberSplice)
    {
        $fiberSplice->load([
            'jointBox',
            'inputSegment.startPoint',
            'inputSegment.endPoint',
            'outputSegment.startPoint',
            'outputSegment.endPoint',
        ]);

        // Get input and output cores
        $inputCore = FiberCore::where('cable_segment_id', $fiberSplice->input_segment_id)
            ->where('core_number', $fiberSplice->input_core_number)
            ->first();

        $outputCore = FiberCore::where('cable_segment_id', $fiberSplice->output_segment_id)
            ->where('core_number', $fiberSplice->output_core_number)
            ->first();

        return view('fiber-splices.show', compact('fiberSplice', 'inputCore', 'outputCore'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FiberSplice $fiberSplice)
        {
            $fiberSplice->load([
                'jointBox',
                'inputSegment',
                'outputSegment',
            ]);

            // Get core details
            $inputCore = FiberCore::where('cable_segment_id', $fiberSplice->input_segment_id)
                ->where('core_number', $fiberSplice->input_core_number)
                ->first();

            $outputCore = FiberCore::where('cable_segment_id', $fiberSplice->output_segment_id)
                ->where('core_number', $fiberSplice->output_core_number)
                ->first();

            return view('fiber-splices.edit', compact('fiberSplice', 'inputCore', 'outputCore'));
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FiberSplice $fiberSplice)
    {
        $validated = $request->validate([
            'splice_type' => 'required|in:fusion,mechanical',
            'splice_loss' => 'nullable|numeric|min:0|max:5',
            'splice_date' => 'nullable|date',
            'technician' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $fiberSplice->update($validated);

        return redirect()
            ->route('fiber-splices.show', $fiberSplice)
            ->with('success', 'Fiber splice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FiberSplice $fiberSplice)
    {
        $jointBoxId = $fiberSplice->joint_box_id;

        // Release fiber cores
        $inputCore = FiberCore::where('cable_segment_id', $fiberSplice->input_segment_id)
            ->where('core_number', $fiberSplice->input_core_number)
            ->first();

        $outputCore = FiberCore::where('cable_segment_id', $fiberSplice->output_segment_id)
            ->where('core_number', $fiberSplice->output_core_number)
            ->first();

        if ($inputCore) {
            $inputCore->update(['status' => 'available']);
        }

        if ($outputCore) {
            $outputCore->update(['status' => 'available']);
        }

        $fiberSplice->delete();

        return redirect()
            ->route('joint-boxes.splices', $jointBoxId)
            ->with('success', 'Fiber splice deleted successfully!');
    }

    /**
     * Get available cores for cable segment (AJAX)
     */
    public function getAvailableCores(Request $request)
    {
        $segmentId = $request->input('segment_id');

        if (!$segmentId) {
            return response()->json(['error' => 'Segment ID required'], 400);
        }

        $segment = FiberCableSegment::find($segmentId);

        if (!$segment) {
            return response()->json(['error' => 'Segment not found'], 404);
        }

        $cores = FiberCore::where('cable_segment_id', $segmentId)
            ->orderBy('core_number')
            ->get()
            ->map(function($core) {
                return [
                    'id' => $core->id,
                    'core_number' => $core->core_number,
                    'core_color' => $core->core_color,
                    'status' => $core->status,
                    'available' => $core->status === 'available',
                ];
            });

        return response()->json([
            'success' => true,
            'segment' => [
                'id' => $segment->id,
                'name' => $segment->name,
                'core_count' => $segment->core_count,
            ],
            'cores' => $cores,
        ]);
    }

    /**
     * Bulk create splices (for mass splicing)
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'joint_box_id' => 'required|exists:joint_boxes,id',
            'input_segment_id' => 'required|exists:fiber_cable_segments,id',
            'output_segment_id' => 'required|exists:fiber_cable_segments,id',
            'core_pairs' => 'required|array|min:1',
            'core_pairs.*.input' => 'required|integer|min:1',
            'core_pairs.*.output' => 'required|integer|min:1',
            'splice_type' => 'required|in:fusion,mechanical',
            'splice_loss' => 'nullable|numeric|min:0|max:5',
            'technician' => 'nullable|string|max:255',
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['core_pairs'] as $index => $pair) {
            try {
                $splice = FiberSplice::create([
                    'joint_box_id' => $validated['joint_box_id'],
                    'input_segment_id' => $validated['input_segment_id'],
                    'input_core_number' => $pair['input'],
                    'output_segment_id' => $validated['output_segment_id'],
                    'output_core_number' => $pair['output'],
                    'splice_type' => $validated['splice_type'],
                    'splice_loss' => $validated['splice_loss'] ?? ($validated['splice_type'] === 'fusion' ? 0.1 : 0.3),
                    'splice_date' => now(),
                    'technician' => $validated['technician'],
                ]);

                // Update core status
                FiberCore::where('cable_segment_id', $validated['input_segment_id'])
                    ->where('core_number', $pair['input'])
                    ->update(['status' => 'used']);

                FiberCore::where('cable_segment_id', $validated['output_segment_id'])
                    ->where('core_number', $pair['output'])
                    ->update(['status' => 'used']);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Pair " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        // Update joint box capacity
        /*$jointBox = JointBox::find($validated['joint_box_id']);
        if ($jointBox) {
            $jointBox->increment('used_capacity', $created);
        }*/

        if (count($errors) > 0) {
            return back()
                ->with('warning', "Created {$created} splices with some errors: " . implode(', ', $errors));
        }

        return redirect()
            ->route('joint-boxes.splices', $validated['joint_box_id'])
            ->with('success', "Successfully created {$created} fiber splices!");
    }
}
