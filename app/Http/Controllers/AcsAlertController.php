<?php

namespace App\Http\Controllers;

use App\Models\AcsAlert;
use App\Models\AcsAlertRule;
use App\Models\ONT;
use Illuminate\Http\Request;

class AcsAlertController extends Controller
{
    /**
     * Display alerts list
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $severity = $request->input('severity');
        $type = $request->input('alert_type');

        $query = AcsAlert::with(['ont', 'rule']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($type) {
            $query->where('alert_type', $type);
        }

        $alerts = $query->latest('triggered_at')->paginate(50)->withQueryString();

        // Statistics
        $stats = [
            'total' => AcsAlert::count(),
            'new' => AcsAlert::where('status', 'new')->count(),
            'acknowledged' => AcsAlert::where('status', 'acknowledged')->count(),
            'resolved' => AcsAlert::where('status', 'resolved')->count(),
            'critical' => AcsAlert::where('severity', 'critical')->whereIn('status', ['new', 'acknowledged'])->count(),
            'warning' => AcsAlert::where('severity', 'warning')->whereIn('status', ['new', 'acknowledged'])->count(),
        ];

        return view('acs.alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Display alert details
     */
    public function show(AcsAlert $alert)
    {
        $alert->load(['ont', 'rule', 'acknowledgedBy']);

        return view('acs.alerts.show', compact('alert'));
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge(AcsAlert $alert)
    {
        try {
            $alert->acknowledge(auth()->user());

            return back()->with('success', 'Alert acknowledged!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to acknowledge alert: ' . $e->getMessage());
        }
    }

    /**
     * Resolve alert
     */
    public function resolve(AcsAlert $alert)
    {
        try {
            $alert->resolve();

            return back()->with('success', 'Alert resolved!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resolve alert: ' . $e->getMessage());
        }
    }

    /**
     * Delete alert
     */
    public function destroy(AcsAlert $alert)
    {
        $alert->delete();

        return back()->with('success', 'Alert deleted!');
    }

    /**
     * Bulk acknowledge alerts
     */
    public function bulkAcknowledge(Request $request)
    {
        $validated = $request->validate([
            'alert_ids' => 'required|array|min:1',
            'alert_ids.*' => 'exists:acs_alerts,id',
        ]);

        try {
            $updated = AcsAlert::whereIn('id', $validated['alert_ids'])
                ->where('status', 'new')
                ->update([
                    'status' => 'acknowledged',
                    'acknowledged_at' => now(),
                    'acknowledged_by' => auth()->id(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} alert(s) acknowledged!",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to acknowledge alerts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk resolve alerts
     */
    public function bulkResolve(Request $request)
    {
        $validated = $request->validate([
            'alert_ids' => 'required|array|min:1',
            'alert_ids.*' => 'exists:acs_alerts,id',
        ]);

        try {
            $updated = AcsAlert::whereIn('id', $validated['alert_ids'])
                ->whereIn('status', ['new', 'acknowledged'])
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} alert(s) resolved!",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve alerts: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==================== ALERT RULES ====================

    /**
     * Display alert rules
     */
    public function rules(Request $request)
    {
        $rules = AcsAlertRule::latest()->paginate(20);

        $stats = [
            'total' => AcsAlertRule::count(),
            'active' => AcsAlertRule::where('is_active', true)->count(),
            'inactive' => AcsAlertRule::where('is_active', false)->count(),
        ];

        return view('acs.alert-rules.index', compact('rules', 'stats'));
    }

    /**
     * Show form to create alert rule
     */
    public function createRule()
    {
        return view('acs.alert-rules.create');
    }

    /**
     * Store alert rule
     */
    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'condition_type' => 'required|in:offline,signal_low,los,no_inform',
            'condition_parameters' => 'required|array',
            'notification_channels' => 'required|array',
            'recipients' => 'required|array',
            'check_interval' => 'nullable|integer|min:60',
            'cooldown_period' => 'nullable|integer|min:300',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['check_interval'] = $validated['check_interval'] ?? 300;
        $validated['cooldown_period'] = $validated['cooldown_period'] ?? 3600;

        AcsAlertRule::create($validated);

        return redirect()->route('acs.alert-rules.index')
            ->with('success', 'Alert rule created!');
    }

    /**
     * Show alert rule
     */
    public function showRule(AcsAlertRule $rule)
    {
        $rule->load('alerts');

        $stats = [
            'total_alerts' => $rule->alerts()->count(),
            'today' => $rule->alerts()->whereDate('triggered_at', today())->count(),
            'this_week' => $rule->alerts()->where('triggered_at', '>=', now()->startOfWeek())->count(),
            'active' => $rule->alerts()->whereIn('status', ['new', 'acknowledged'])->count(),
        ];

        return view('acs.alert-rules.show', compact('rule', 'stats'));
    }

    /**
     * Edit alert rule
     */
    public function editRule(AcsAlertRule $rule)
    {
        return view('acs.alert-rules.edit', compact('rule'));
    }

    /**
     * Update alert rule
     */
    public function updateRule(Request $request, AcsAlertRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'condition_type' => 'required|in:offline,signal_low,los,no_inform',
            'condition_parameters' => 'required|array',
            'notification_channels' => 'required|array',
            'recipients' => 'required|array',
            'check_interval' => 'nullable|integer|min:60',
            'cooldown_period' => 'nullable|integer|min:300',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rule->update($validated);

        return redirect()->route('acs.alert-rules.index')
            ->with('success', 'Alert rule updated!');
    }

    /**
     * Delete alert rule
     */
    public function destroyRule(AcsAlertRule $rule)
    {
        // Check if rule has active alerts
        $activeAlerts = $rule->alerts()->whereIn('status', ['new', 'acknowledged'])->count();

        if ($activeAlerts > 0) {
            return back()->with('error', "Cannot delete rule with {$activeAlerts} active alert(s)!");
        }

        $rule->delete();

        return redirect()->route('acs.alert-rules.index')
            ->with('success', 'Alert rule deleted!');
    }

    /**
     * Toggle alert rule active status
     */
    public function toggleRule(AcsAlertRule $rule)
    {
        $rule->update([
            'is_active' => !$rule->is_active
        ]);

        $status = $rule->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "Alert rule {$status}!");
    }

    /**
     * API: Get alerts count
     */
    public function alertsCount()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'new' => AcsAlert::where('status', 'new')->count(),
                'critical' => AcsAlert::where('severity', 'critical')
                    ->whereIn('status', ['new', 'acknowledged'])
                    ->count(),
                'total_active' => AcsAlert::whereIn('status', ['new', 'acknowledged'])->count(),
            ]
        ]);
    }
}
