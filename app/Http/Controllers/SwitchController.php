<?php

namespace App\Http\Controllers;

use App\Models\SwitchModel;  // ✅ Use alias to avoid conflict with PHP keyword
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SwitchController extends Controller
{
    /**
     * Display a listing of switches
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $brand = $request->input('brand');

        $query = SwitchModel::query();

        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status === 'online') {
            $query->where('status', 'online');
        } elseif ($status === 'offline') {
            $query->where('status', 'offline');
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // Filter by brand
        if ($brand) {
            $query->where('brand', 'like', "%{$brand}%");
        }

        // Get switches
        $switches = $query->latest()->paginate(20)->withQueryString();

        // Auto-check status for managed switches
        foreach ($switches as $switch) {
            if ($switch->isManaged() && (!$switch->last_seen || $switch->last_seen->lt(now()->subMinutes(5)))) {
                $this->quickCheckSwitch($switch);
            }
        }

        // Statistics
        $stats = [
            'total' => SwitchModel::count(),
            'active' => SwitchModel::where('is_active', true)->count(),
            'inactive' => SwitchModel::where('is_active', false)->count(),
            'managed' => SwitchModel::managed()->count(),
            'unmanaged' => SwitchModel::unmanaged()->count(),
        ];

        if (Schema::hasColumn('switches', 'status')) {
            $stats['online'] = SwitchModel::where('status', 'online')->count();
            $stats['offline'] = SwitchModel::where('status', 'offline')->count();
        }

        // Get unique brands for filter
        $brands = SwitchModel::select('brand')
            ->whereNotNull('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return view('switches.index', compact('switches', 'stats', 'brands'));
    }

    /**
     * Quick status check via ping
     */
    private function quickCheckSwitch($switch)
    {
        if (!$switch->isManaged()) {
            return;
        }

        $host = $switch->ip_address;
        $startTime = microtime(true);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "ping -n 1 -w 1000 {$host}";
        } else {
            $command = "ping -c 1 -W 1 {$host} 2>&1";
        }

        exec($command, $output, $result);

        $isOnline = ($result === 0);
        $latency = $isOnline ? round((microtime(true) - $startTime) * 1000) : null;

        $switch->update([
            'status' => $isOnline ? 'online' : 'offline',
            'last_seen' => $isOnline ? now() : $switch->last_seen,
            'ping_latency' => $latency,
        ]);
    }

    /**
     * Show the form for creating a new switch
     */
    public function create()
    {
        return view('switches.create');
    }

    /**
     * Store a newly created switch
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'ip_address' => 'nullable|string|max:255',
        'mac_address' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:255',
        'password' => 'nullable|string|max:255',
        'ssh_port' => 'nullable|integer',
        'port_count' => 'nullable|integer',
        'location' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'notes' => 'nullable|string',
    ]);

    // Handle NULL values - set empty string if null
    $validated['ip_address'] = $validated['ip_address'] ?? '';
    $validated['mac_address'] = $validated['mac_address'] ?? '';
    $validated['username'] = $validated['username'] ?? '';
    $validated['password'] = $validated['password'] ?? '';
    $validated['location'] = $validated['location'] ?? '';
    $validated['notes'] = $validated['notes'] ?? '';

    // Set defaults
    $validated['status'] = 'offline';
    $validated['is_active'] = $request->has('is_active') ? 1 : 0;
    $validated['ping_latency'] = null;
    $validated['last_seen'] = null;

    // Create switch
    $switch = SwitchModel::create($validated);

    // Check status if managed (has IP)
    if (!empty($switch->ip_address) && !empty($switch->username)) {
        $this->quickCheckSwitch($switch);
    }

    return redirect()->route('switches.index')
        ->with('success', 'Switch created successfully!');
}

    /**
     * Display the specified switch
     */
    public function show(SwitchModel $switch)
    {
        // Check status if managed
        if ($switch->isManaged()) {
            $this->quickCheckSwitch($switch);
            $switch->refresh();
        }

        return view('switches.show', compact('switch'));
    }

    /**
     * Show the form for editing the specified switch
     */
    public function edit(SwitchModel $switch)
    {
        return view('switches.edit', compact('switch'));
    }

    /**
     * Update the specified switch
     */
    public function update(Request $request, SwitchModel $switch)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'ip_address' => 'nullable|string|max:255',
        'mac_address' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:255',
        'password' => 'nullable|string|max:255',
        'ssh_port' => 'nullable|integer',
        'port_count' => 'nullable|integer',
        'location' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'notes' => 'nullable|string',
    ]);

    // Handle NULL values - set empty string if null
    $validated['ip_address'] = $validated['ip_address'] ?? '';
    $validated['mac_address'] = $validated['mac_address'] ?? '';
    $validated['username'] = $validated['username'] ?? '';
    $validated['location'] = $validated['location'] ?? '';
    $validated['notes'] = $validated['notes'] ?? '';

    $validated['is_active'] = $request->has('is_active') ? 1 : 0;

    // Don't update password if empty
    if (empty($validated['password'])) {
        unset($validated['password']);
    }

    $switch->update($validated);

    // Check status if managed
    if (!empty($switch->ip_address) && !empty($switch->username)) {
        $this->quickCheckSwitch($switch);
    }

    return redirect()->route('switches.index')
        ->with('success', 'Switch updated successfully!');
}


    /**
     * Remove the specified switch
     */
    public function destroy(SwitchModel $switch)
    {
        $switchName = $switch->name;
        $switchIp = $switch->ip_address;

        $switch->delete();

        if (class_exists(ActivityLog::class)) {
            ActivityLog::log(
                'deleted',
                'Switch',
                $switch->id,
                "Deleted switch: {$switchName}",
                ['ip_address' => $switchIp]
            );
        }

        return redirect()->route('switches.index')
            ->with('success', 'Switch deleted successfully!');
    }

    /**
     * Check status manually
     */
    public function checkStatus(SwitchModel $switch)
    {
        if (!$switch->isManaged()) {
            return back()->with('error', 'This is an unmanaged switch. Cannot ping.');
        }

        $this->quickCheckSwitch($switch);
        $switch->refresh();

        $statusText = $switch->status;
        $latencyText = $switch->ping_latency ? " ({$switch->ping_latency}ms)" : '';

        return back()->with('success', "Switch is {$statusText}{$latencyText}");
    }

    /**
     * Check all switches status
     */
    public function checkAllStatus()
    {
        $switches = SwitchModel::managed()->where('is_active', true)->get();

        $onlineCount = 0;
        $offlineCount = 0;

        foreach ($switches as $switch) {
            $this->quickCheckSwitch($switch);

            if ($switch->status === 'online') {
                $onlineCount++;
            } else {
                $offlineCount++;
            }
        }

        return back()->with('success', "Status checked: {$onlineCount} online, {$offlineCount} offline");
    }
}
