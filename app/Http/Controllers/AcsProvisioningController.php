<?php

namespace App\Http\Controllers;

use App\Models\AcsProvisioningQueue;
use App\Models\ONT;
use App\Models\OLT;
use Illuminate\Http\Request;

class AcsProvisioningController extends Controller
{
    /**
     * Display provisioning queue
     */
    public function queue(Request $request)
    {
        $status = $request->input('status');
        $priority = $request->input('priority');
        $oltId = $request->input('olt_id');

        $query = AcsProvisioningQueue::with(['ont', 'olt']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($oltId) {
            $query->where('olt_id', $oltId);
        }

        $queue = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(50)
            ->withQueryString();

        // Statistics
        $stats = [
            'total' => AcsProvisioningQueue::count(),
            'pending' => AcsProvisioningQueue::where('status', 'pending')->count(),
            'processing' => AcsProvisioningQueue::where('status', 'processing')->count(),
            'completed' => AcsProvisioningQueue::where('status', 'completed')->count(),
            'failed' => AcsProvisioningQueue::where('status', 'failed')->count(),
            'high_priority' => AcsProvisioningQueue::where('priority', 'high')
                ->where('status', 'pending')
                ->count(),
        ];

        // Get OLTs for filter
        $olts = OLT::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('acs.provisioning.queue', compact('queue', 'stats', 'olts'));
    }

    /**
     * Process provisioning queue
     */
    public function process()
    {
        try {
            // Trigger processing command
            \Artisan::call('acs:process-queue');

            return back()->with('success', 'Provisioning queue processing started!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process queue: ' . $e->getMessage());
        }
    }

    /**
     * Retry failed provisioning job
     */
    public function retry(AcsProvisioningQueue $queue)
    {
        if ($queue->status !== 'failed') {
            return back()->with('error', 'Only failed jobs can be retried!');
        }

        try {
            $queue->update([
                'status' => 'pending',
                'error_message' => null,
                'retry_count' => 0,
            ]);

            return back()->with('success', 'Job queued for retry!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    /**
     * Cancel provisioning job
     */
    public function cancel(AcsProvisioningQueue $queue)
    {
        if ($queue->status === 'completed') {
            return back()->with('error', 'Cannot cancel completed job!');
        }

        if ($queue->status === 'processing') {
            return back()->with('error', 'Cannot cancel job in progress!');
        }

        try {
            $queue->delete();

            return back()->with('success', 'Job cancelled!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel job: ' . $e->getMessage());
        }
    }

    /**
     * Clear all failed jobs
     */
    public function clearFailed()
    {
        try {
            $count = AcsProvisioningQueue::where('status', 'failed')->delete();

            return back()->with('success', "Cleared {$count} failed job(s)!");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear jobs: ' . $e->getMessage());
        }
    }

    /**
     * API: Get queue status
     */
    public function queueStatus()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending' => AcsProvisioningQueue::where('status', 'pending')->count(),
                'processing' => AcsProvisioningQueue::where('status', 'processing')->count(),
                'completed_today' => AcsProvisioningQueue::where('status', 'completed')
                    ->whereDate('processed_at', today())
                    ->count(),
                'failed' => AcsProvisioningQueue::where('status', 'failed')->count(),
                'high_priority_pending' => AcsProvisioningQueue::where('status', 'pending')
                    ->where('priority', 'high')
                    ->count(),
            ]
        ]);
    }
}
