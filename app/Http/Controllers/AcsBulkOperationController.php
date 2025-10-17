<?php

namespace App\Http\Controllers;

use App\Models\AcsBulkOperation;
use App\Models\AcsBulkOperationDetail;
use App\Models\ONT;
use App\Models\AcsConfigTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcsBulkOperationController extends Controller
{
    /**
     * Display bulk operations list
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $type = $request->input('type');

        $query = AcsBulkOperation::with('creator');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('operation_type', $type);
        }

        $operations = $query->latest()->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => AcsBulkOperation::count(),
            'pending' => AcsBulkOperation::where('status', 'pending')->count(),
            'processing' => AcsBulkOperation::where('status', 'processing')->count(),
            'completed' => AcsBulkOperation::where('status', 'completed')->count(),
            'failed' => AcsBulkOperation::where('status', 'failed')->count(),
        ];

        return view('acs.bulk.index', compact('operations', 'stats'));
    }

    /**
     * Display bulk operation details
     */
    public function show(AcsBulkOperation $bulkOperation)
    {
        $bulkOperation->load(['creator', 'details.ont']);

        return view('acs.bulk.show', compact('bulkOperation'));
    }

    /**
     * Bulk reboot devices
     */
    public function reboot(Request $request)
    {
        $validated = $request->validate([
            'ont_ids' => 'required|array|min:1',
            'ont_ids.*' => 'exists:onts,id',
        ]);

        try {
            $ontIds = $validated['ont_ids'];

            // Create bulk operation
            $bulkOperation = AcsBulkOperation::create([
                'operation_name' => 'Bulk Reboot',
                'operation_type' => 'reboot',
                'target_filter' => ['ont_ids' => $ontIds],
                'total_devices' => count($ontIds),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create detail records
            foreach ($ontIds as $ontId) {
                $bulkOperation->details()->create([
                    'ont_id' => $ontId,
                    'status' => 'pending',
                ]);
            }

            // Dispatch job to process (will be created later)
            // ProcessBulkOperationJob::dispatch($bulkOperation);

            return response()->json([
                'success' => true,
                'message' => "Bulk reboot started for {$bulkOperation->total_devices} device(s)!",
                'operation_id' => $bulkOperation->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start bulk reboot: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk provision devices
     */
    public function provision(Request $request)
    {
        $validated = $request->validate([
            'ont_ids' => 'required|array|min:1',
            'ont_ids.*' => 'exists:onts,id',
            'provision_type' => 'nullable|in:provision,re_provision',
        ]);

        try {
            $ontIds = $validated['ont_ids'];
            $provisionType = $validated['provision_type'] ?? 'provision';

            // Create bulk operation
            $bulkOperation = AcsBulkOperation::create([
                'operation_name' => 'Bulk ' . ucfirst($provisionType),
                'operation_type' => $provisionType,
                'target_filter' => ['ont_ids' => $ontIds],
                'total_devices' => count($ontIds),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create detail records and queue provisioning
            foreach ($ontIds as $ontId) {
                $bulkOperation->details()->create([
                    'ont_id' => $ontId,
                    'status' => 'pending',
                ]);

                $ont = ONT::find($ontId);
                $ont->queueForProvisioning($provisionType, 'normal', [
                    'bulk_operation_id' => $bulkOperation->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk provisioning started for {$bulkOperation->total_devices} device(s)!",
                'operation_id' => $bulkOperation->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start bulk provision: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk WiFi update
     */
    public function wifiUpdate(Request $request)
    {
        $validated = $request->validate([
            'ont_ids' => 'required|array|min:1',
            'ont_ids.*' => 'exists:onts,id',
            'wifi_ssid' => 'required|string|max:32',
            'wifi_password' => 'required|string|min:8|max:63',
        ]);

        try {
            $ontIds = $validated['ont_ids'];

            // Create bulk operation
            $bulkOperation = AcsBulkOperation::create([
                'operation_name' => 'Bulk WiFi Update',
                'operation_type' => 'wifi_update',
                'target_filter' => ['ont_ids' => $ontIds],
                'parameters' => [
                    'wifi_ssid' => $validated['wifi_ssid'],
                    'wifi_password' => $validated['wifi_password'],
                ],
                'total_devices' => count($ontIds),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create detail records
            foreach ($ontIds as $ontId) {
                $bulkOperation->details()->create([
                    'ont_id' => $ontId,
                    'status' => 'pending',
                ]);

                $ont = ONT::find($ontId);
                $ont->queueForProvisioning('wifi_update', 'normal', [
                    'wifi_ssid' => $validated['wifi_ssid'],
                    'wifi_password' => $validated['wifi_password'],
                    'bulk_operation_id' => $bulkOperation->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "WiFi update queued for {$bulkOperation->total_devices} device(s)!",
                'operation_id' => $bulkOperation->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue WiFi update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk configuration
     */
    public function configure(Request $request)
    {
        $validated = $request->validate([
            'ont_ids' => 'required|array|min:1',
            'ont_ids.*' => 'exists:onts,id',
            'config_type' => 'required|in:wifi,vlan,port,custom',
            'parameters' => 'required|array',
        ]);

        try {
            $ontIds = $validated['ont_ids'];

            // Create bulk operation
            $bulkOperation = AcsBulkOperation::create([
                'operation_name' => 'Bulk Configuration: ' . $validated['config_type'],
                'operation_type' => 'configure',
                'target_filter' => ['ont_ids' => $ontIds],
                'parameters' => [
                    'config_type' => $validated['config_type'],
                    'parameters' => $validated['parameters'],
                ],
                'total_devices' => count($ontIds),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create detail records
            foreach ($ontIds as $ontId) {
                $bulkOperation->details()->create([
                    'ont_id' => $ontId,
                    'status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Configuration queued for {$bulkOperation->total_devices} device(s)!",
                'operation_id' => $bulkOperation->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply template to multiple devices
     */
    public function applyTemplate(Request $request)
    {
        $validated = $request->validate([
            'ont_ids' => 'required|array|min:1',
            'ont_ids.*' => 'exists:onts,id',
            'template_id' => 'required|exists:acs_config_templates,id',
        ]);

        try {
            $template = AcsConfigTemplate::find($validated['template_id']);
            $ontIds = $validated['ont_ids'];

            // Create bulk operation
            $bulkOperation = AcsBulkOperation::create([
                'operation_name' => "Apply Template: {$template->name}",
                'operation_type' => 'apply_template',
                'target_filter' => ['ont_ids' => $ontIds],
                'parameters' => [
                    'template_id' => $template->id,
                    'template_parameters' => $template->parameters,
                ],
                'total_devices' => count($ontIds),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create detail records
            foreach ($ontIds as $ontId) {
                $bulkOperation->details()->create([
                    'ont_id' => $ontId,
                    'status' => 'pending',
                ]);

                $ont = ONT::find($ontId);
                $ont->queueForProvisioning('apply_template', 'normal', [
                    'template_id' => $template->id,
                    'parameters' => $template->parameters,
                    'bulk_operation_id' => $bulkOperation->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Template will be applied to {$bulkOperation->total_devices} device(s)!",
                'operation_id' => $bulkOperation->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply template: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry failed bulk operation
     */
    public function retry(AcsBulkOperation $bulkOperation)
    {
        try {
            // Get failed details
            $failedDetails = $bulkOperation->details()->where('status', 'failed')->get();

            if ($failedDetails->isEmpty()) {
                return back()->with('info', 'No failed operations to retry.');
            }

            // Reset failed details to pending
            $bulkOperation->details()->where('status', 'failed')->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            // Reset operation counters
            $bulkOperation->update([
                'status' => 'pending',
                'processed_devices' => $bulkOperation->processed_devices - $failedDetails->count(),
                'failed_count' => 0,
            ]);

            // Re-queue failed devices
            foreach ($failedDetails as $detail) {
                $ont = $detail->ont;
                $ont->queueForProvisioning(
                    $bulkOperation->operation_type,
                    'high',
                    $bulkOperation->parameters ?? []
                );
            }

            return back()->with('success', "Retrying {$failedDetails->count()} failed operation(s)!");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry operation: ' . $e->getMessage());
        }
    }

    /**
     * Cancel bulk operation
     */
    public function cancel(AcsBulkOperation $bulkOperation)
    {
        if ($bulkOperation->status === 'completed') {
            return back()->with('error', 'Cannot cancel completed operation.');
        }

        try {
            $bulkOperation->update(['status' => 'cancelled']);

            // Cancel pending details
            $bulkOperation->details()
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            return back()->with('success', 'Bulk operation cancelled!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel operation: ' . $e->getMessage());
        }
    }

    /**
     * API: Get operation progress
     */
    public function progress(AcsBulkOperation $bulkOperation)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $bulkOperation->id,
                'status' => $bulkOperation->status,
                'total_devices' => $bulkOperation->total_devices,
                'processed_devices' => $bulkOperation->processed_devices,
                'success_count' => $bulkOperation->success_count,
                'failed_count' => $bulkOperation->failed_count,
                'progress_percentage' => $bulkOperation->getProgressPercentage(),
                'started_at' => $bulkOperation->started_at?->toIso8601String(),
                'completed_at' => $bulkOperation->completed_at?->toIso8601String(),
                'duration' => $bulkOperation->getDuration(),
            ]
        ]);
    }
}
