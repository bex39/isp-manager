<?php

namespace App\Http\Controllers;

use App\Models\FiberTestResult;
use App\Models\FiberCore;
use App\Models\FiberCableSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FiberTestResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $coreId = $request->input('core_id');
        $testType = $request->input('test_type');
        $status = $request->input('status');
        $technician = $request->input('technician');

        $query = FiberTestResult::with([
            'fiberCore.cableSegment'
        ]);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('technician', 'like', "%{$search}%")
                  ->orWhereHas('fiberCore.cableSegment', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Fiber Core filter
        if ($coreId) {
            $query->where('fiber_core_id', $coreId);
        }

        // Test Type filter
        if ($testType) {
            $query->where('test_type', $testType);
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Technician filter
        if ($technician) {
            $query->where('technician', 'like', "%{$technician}%");
        }

        $testResults = $query->latest('test_date')->paginate(20)->withQueryString();

        // Get unique technicians
        $technicians = FiberTestResult::distinct()
            ->whereNotNull('technician')
            ->pluck('technician')
            ->sort();

        // Statistics
        $stats = [
            'total_tests' => FiberTestResult::count(),
            'passed' => FiberTestResult::where('status', 'pass')->count(),
            'failed' => FiberTestResult::where('status', 'fail')->count(),
            'warnings' => FiberTestResult::where('status', 'warning')->count(),
            'avg_loss' => round(FiberTestResult::avg('total_loss'), 2),
            'otdr_tests' => FiberTestResult::where('test_type', 'OTDR')->count(),
        ];

        return view('fiber-test-results.index', compact('testResults', 'technicians', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $coreId = $request->input('core_id');

        // Get cable segments for selection
        $cableSegments = FiberCableSegment::where('status', 'active')
            ->orderBy('name')
            ->get();

        // If core_id provided, get that specific core
        $selectedCore = null;
        if ($coreId) {
            $selectedCore = FiberCore::with('cableSegment')->find($coreId);
        }

        return view('fiber-test-results.create', compact('cableSegments', 'selectedCore'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fiber_core_id' => 'required|exists:fiber_cores,id',
            'test_date' => 'required|date',
            'test_type' => 'required|in:OTDR,Power Meter,Light Source',
            'total_loss' => 'nullable|numeric|min:0|max:50',
            'total_length' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:pass,fail,warning',
            'technician' => 'nullable|string|max:255',
            'sor_file' => 'nullable|file|mimes:sor,pdf,jpg,jpeg,png|max:10240',
            'test_data' => 'nullable|json',
            'notes' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('sor_file')) {
            $file = $request->file('sor_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('fiber-tests', $fileName, 'public');
            $validated['sor_file'] = $filePath;
        }

        // Parse test_data if provided as JSON string
        if ($request->has('test_data') && is_string($request->test_data)) {
            $validated['test_data'] = json_decode($request->test_data, true);
        }

        $testResult = FiberTestResult::create($validated);

        // Update fiber core loss if provided
        if ($validated['total_loss']) {
            $fiberCore = FiberCore::find($validated['fiber_core_id']);
            if ($fiberCore) {
                $fiberCore->update([
                    'loss_db' => $validated['total_loss'],
                    'length_km' => $validated['total_length'] ?? $fiberCore->length_km,
                ]);
            }
        }

        return redirect()
            ->route('fiber-test-results.show', $testResult)
            ->with('success', 'Test result saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(FiberTestResult $fiberTestResult)
    {
        $fiberTestResult->load([
            'fiberCore.cableSegment.startPoint',
            'fiberCore.cableSegment.endPoint',
        ]);

        // Get test history for this core
        $testHistory = FiberTestResult::where('fiber_core_id', $fiberTestResult->fiber_core_id)
            ->where('id', '!=', $fiberTestResult->id)
            ->latest('test_date')
            ->take(5)
            ->get();

        return view('fiber-test-results.show', compact('fiberTestResult', 'testHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FiberTestResult $fiberTestResult)
    {
        $fiberTestResult->load('fiberCore.cableSegment');

        $cableSegments = FiberCableSegment::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('fiber-test-results.edit', compact('fiberTestResult', 'cableSegments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FiberTestResult $fiberTestResult)
    {
        $validated = $request->validate([
            'fiber_core_id' => 'required|exists:fiber_cores,id',
            'test_date' => 'required|date',
            'test_type' => 'required|in:OTDR,Power Meter,Light Source',
            'total_loss' => 'nullable|numeric|min:0|max:50',
            'total_length' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:pass,fail,warning',
            'technician' => 'nullable|string|max:255',
            'sor_file' => 'nullable|file|mimes:sor,pdf,jpg,jpeg,png|max:10240',
            'test_data' => 'nullable|json',
            'notes' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('sor_file')) {
            // Delete old file if exists
            if ($fiberTestResult->sor_file) {
                Storage::disk('public')->delete($fiberTestResult->sor_file);
            }

            $file = $request->file('sor_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('fiber-tests', $fileName, 'public');
            $validated['sor_file'] = $filePath;
        }

        // Parse test_data if provided as JSON string
        if ($request->has('test_data') && is_string($request->test_data)) {
            $validated['test_data'] = json_decode($request->test_data, true);
        }

        $fiberTestResult->update($validated);

        // Update fiber core loss if changed
        if (isset($validated['total_loss'])) {
            $fiberCore = FiberCore::find($validated['fiber_core_id']);
            if ($fiberCore) {
                $fiberCore->update([
                    'loss_db' => $validated['total_loss'],
                    'length_km' => $validated['total_length'] ?? $fiberCore->length_km,
                ]);
            }
        }

        return redirect()
            ->route('fiber-test-results.show', $fiberTestResult)
            ->with('success', 'Test result updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FiberTestResult $fiberTestResult)
    {
        // Delete file if exists
        if ($fiberTestResult->sor_file) {
            Storage::disk('public')->delete($fiberTestResult->sor_file);
        }

        $fiberTestResult->delete();

        return redirect()
            ->route('fiber-test-results.index')
            ->with('success', 'Test result deleted successfully!');
    }

    /**
     * Download SOR file
     */
    public function download(FiberTestResult $fiberTestResult)
    {
        if (!$fiberTestResult->sor_file) {
            return back()->with('error', 'No file attached to this test result!');
        }

        $filePath = storage_path('app/public/' . $fiberTestResult->sor_file);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found!');
        }

        return response()->download($filePath);
    }

    /**
     * Get cores for cable segment (AJAX)
     */
    public function getCoresForSegment(Request $request)
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
                    'loss_db' => $core->loss_db,
                    'label' => "Core #{$core->core_number}" . ($core->core_color ? " ({$core->core_color})" : ''),
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
     * Get test history for core (AJAX)
     */
    public function getTestHistory(Request $request)
    {
        $coreId = $request->input('core_id');

        if (!$coreId) {
            return response()->json(['error' => 'Core ID required'], 400);
        }

        $core = FiberCore::with('cableSegment')->find($coreId);

        if (!$core) {
            return response()->json(['error' => 'Core not found'], 404);
        }

        $history = FiberTestResult::where('fiber_core_id', $coreId)
            ->orderBy('test_date', 'desc')
            ->get()
            ->map(function($test) {
                return [
                    'id' => $test->id,
                    'test_date' => $test->test_date->format('Y-m-d'),
                    'test_type' => $test->test_type,
                    'status' => $test->status,
                    'total_loss' => $test->total_loss,
                    'total_length' => $test->total_length,
                    'technician' => $test->technician,
                ];
            });

        return response()->json([
            'success' => true,
            'core' => [
                'id' => $core->id,
                'core_number' => $core->core_number,
                'segment_name' => $core->cableSegment->name,
            ],
            'history' => $history,
            'test_count' => $history->count(),
        ]);
    }

    /**
     * Bulk upload test results
     */
    public function bulkUpload(Request $request)
    {
        $validated = $request->validate([
            'cable_segment_id' => 'required|exists:fiber_cable_segments,id',
            'test_date' => 'required|date',
            'test_type' => 'required|in:OTDR,Power Meter,Light Source',
            'technician' => 'nullable|string|max:255',
            'test_results' => 'required|array|min:1',
            'test_results.*.core_number' => 'required|integer|min:1',
            'test_results.*.total_loss' => 'nullable|numeric|min:0',
            'test_results.*.total_length' => 'nullable|numeric|min:0',
            'test_results.*.status' => 'required|in:pass,fail,warning',
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['test_results'] as $index => $testData) {
            try {
                // Find core
                $core = FiberCore::where('cable_segment_id', $validated['cable_segment_id'])
                    ->where('core_number', $testData['core_number'])
                    ->first();

                if (!$core) {
                    $errors[] = "Core #{$testData['core_number']} not found";
                    continue;
                }

                // Create test result
                $test = FiberTestResult::create([
                    'fiber_core_id' => $core->id,
                    'test_date' => $validated['test_date'],
                    'test_type' => $validated['test_type'],
                    'total_loss' => $testData['total_loss'] ?? null,
                    'total_length' => $testData['total_length'] ?? null,
                    'status' => $testData['status'],
                    'technician' => $validated['technician'],
                ]);

                // Update core loss
                if (isset($testData['total_loss'])) {
                    $core->update([
                        'loss_db' => $testData['total_loss'],
                        'length_km' => $testData['total_length'] ?? $core->length_km,
                    ]);
                }

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Core #{$testData['core_number']}: " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return back()
                ->with('warning', "Created {$created} test results with some errors: " . implode(', ', $errors));
        }

        return redirect()
            ->route('fiber-test-results.index')
            ->with('success', "Successfully created {$created} test results!");
    }

    /**
     * Export test results
     */
    public function export(Request $request)
    {
        $coreId = $request->input('core_id');

        $query = FiberTestResult::with('fiberCore.cableSegment');

        if ($coreId) {
            $query->where('fiber_core_id', $coreId);
        }

        $results = $query->latest('test_date')->get();

        $csv = "Test Date,Cable Segment,Core Number,Test Type,Loss (dB),Length (km),Status,Technician\n";

        foreach ($results as $result) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $result->test_date->format('Y-m-d'),
                $result->fiberCore->cableSegment->name,
                $result->fiberCore->core_number,
                $result->test_type,
                $result->total_loss ?? 'N/A',
                $result->total_length ?? 'N/A',
                $result->status,
                $result->technician ?? 'N/A'
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="fiber-test-results-' . date('Y-m-d') . '.csv"');
    }
}
