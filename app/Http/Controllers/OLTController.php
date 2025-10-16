<?php

namespace App\Http\Controllers;

use App\Models\OLT;
use App\Models\ActivityLog;
use App\Services\OLTService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OLTController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_olts', only: ['index', 'show']),
            new Middleware('can:create_olt', only: ['create', 'store']),
            new Middleware('can:edit_olt', only: ['edit', 'update']),
            new Middleware('can:delete_olt', only: ['destroy']),
        ];
    }

    public function index(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status');

    $query = OLT::query();

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }

    // Filter by status if provided
    if ($status === 'active') {
        $query->where('is_active', true);
    } elseif ($status === 'inactive') {
        $query->where('is_active', false);
    }

    $olts = $query->latest()->paginate(20)->withQueryString();

    // Quick auto-check status
    if (\Schema::hasColumn('olts', 'status')) {
        foreach ($olts as $olt) {
            if (!$olt->last_seen || $olt->last_seen->lt(now()->subMinutes(5))) {
                $this->quickCheckOLT($olt);
            }
        }
    }

    // Build stats array
    $stats = [
        'total' => OLT::count(),
        'active' => OLT::where('is_active', true)->count(),
        'inactive' => OLT::where('is_active', false)->count(),
    ];

    // Add online/offline stats if status column exists
    if (\Schema::hasColumn('olts', 'status')) {
        $stats['online'] = OLT::where('status', 'online')->count();
        $stats['offline'] = OLT::where('status', 'offline')->count();
    }

    return view('olts.index', compact('olts', 'stats'));
}

/**
 * Quick status check
 */
private function quickCheckOLT($olt)
{
    $host = $olt->ip_address;

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = "ping -n 1 -w 1000 {$host}";
    } else {
        $command = "ping -c 1 -W 1 {$host} 2>&1";
    }

    exec($command, $output, $result);

    $isOnline = ($result === 0);

    $olt->update([
        'status' => $isOnline ? 'online' : 'offline',
        'last_seen' => $isOnline ? now() : $olt->last_seen,
    ]);
}

/**
 * Check OLT status manually
 */
public function checkStatus(OLT $olt)
{
    $host = $olt->ip_address;

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $ping = "ping -n 1 -w 1000 {$host}";
    } else {
        $ping = "ping -c 1 -W 1 {$host} 2>&1";
    }

    exec($ping, $output, $result);

    $isOnline = ($result === 0);

    $updateData = [
        'last_seen' => $isOnline ? now() : $olt->last_seen,
    ];

    if (\Schema::hasColumn('olts', 'status')) {
        $updateData['status'] = $isOnline ? 'online' : 'offline';
    }

    $olt->update($updateData);

    $statusText = $isOnline ? 'online' : 'offline';

    return back()->with('success', "OLT {$olt->name} is {$statusText}");
}

/**
 * Check all OLTs status
 */
public function checkAllStatus()
{
    $olts = OLT::where('is_active', true)->get();

    $onlineCount = 0;
    $offlineCount = 0;

    foreach ($olts as $olt) {
        $host = $olt->ip_address;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $ping = "ping -n 1 -w 1000 {$host}";
        } else {
            $ping = "ping -c 1 -W 1 {$host} 2>&1";
        }

        exec($ping, $output, $result);

        $isOnline = ($result === 0);

        if ($isOnline) {
            $onlineCount++;
        } else {
            $offlineCount++;
        }

        $updateData = [
            'last_seen' => $isOnline ? now() : $olt->last_seen,
        ];

        if (\Schema::hasColumn('olts', 'status')) {
            $updateData['status'] = $isOnline ? 'online' : 'offline';
        }

        $olt->update($updateData);
    }

    return back()->with('success', "Status checked: {$onlineCount} online, {$offlineCount} offline");
}

    /**
 * Check OLT status manually
 */

    public function create()
    {
        return view('olts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:olts',
            'telnet_port' => 'required|integer|min:1|max:65535',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'olt_type' => 'required|in:huawei,zte,fiberhome,bdcom,other',
            'model' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90', // Tambahkan range
            'longitude' => 'nullable|numeric|between:-180,180', // Tambahkan range
            'total_ports' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        // Convert empty strings to null untuk latitude/longitude
        if (empty($validated['latitude'])) {
            $validated['latitude'] = null;
        }
        if (empty($validated['longitude'])) {
            $validated['longitude'] = null;
        }

        // Convert latitude/longitude to float
        if ($validated['latitude']) {
            $validated['latitude'] = floatval($validated['latitude']);
        }
        if ($validated['longitude']) {
            $validated['longitude'] = floatval($validated['longitude']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $olt = OLT::create($validated);

        ActivityLog::log(
            'created',
            'OLT',
            $olt->id,
            "Created new OLT: {$olt->name} ({$olt->ip_address})",
            ['ip_address' => $olt->ip_address, 'olt_type' => $olt->olt_type]
        );

        return redirect()->route('olts.index')->with('success', 'OLT berhasil ditambahkan!');
    }

    public function show(OLT $olt)
    {
        $olt->loadCount('customers');

        // Try to get OLT status
        $oltStatus = ['online' => false];
        try {
            $oltService = new OLTService($olt);
            $oltStatus['online'] = $oltService->testConnection();
        } catch (\Exception $e) {
            $oltStatus['error'] = $e->getMessage();
        }

        return view('olts.show', compact('olt', 'oltStatus'));
    }

    public function edit(OLT $olt)
    {
        return view('olts.edit', compact('olt'));
    }

    public function update(Request $request, OLT $olt)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:olts,ip_address,' . $olt->id,
            'telnet_port' => 'required|integer|min:1|max:65535',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'olt_type' => 'required|in:huawei,zte,fiberhome,bdcom,other',
            'model' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_ports' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        // Convert empty strings to null
        if (empty($validated['latitude'])) {
            $validated['latitude'] = null;
        }
        if (empty($validated['longitude'])) {
            $validated['longitude'] = null;
        }

        // Convert to float
        if ($validated['latitude']) {
            $validated['latitude'] = floatval($validated['latitude']);
        }
        if ($validated['longitude']) {
            $validated['longitude'] = floatval($validated['longitude']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $olt->update($validated);

        ActivityLog::log(
            'updated',
            'OLT',
            $olt->id,
            "Updated OLT: {$olt->name}",
            ['ip_address' => $olt->ip_address]
        );

        return redirect()->route('olts.index')->with('success', 'OLT berhasil diupdate!');
    }

    public function destroy(OLT $olt)
    {
        if ($olt->customers_count > 0) {
            return back()->with('error', 'Tidak bisa menghapus OLT yang masih memiliki customer!');
        }

        $oltName = $olt->name;
        $oltIp = $olt->ip_address;

        $olt->delete();

        ActivityLog::log(
            'deleted',
            'OLT',
            $olt->id,
            "Deleted OLT: {$oltName} ({$oltIp})",
            ['name' => $oltName, 'ip_address' => $oltIp]
        );

        return redirect()->route('olts.index')->with('success', 'OLT berhasil dihapus!');
    }

    public function testConnection(OLT $olt)
    {
        try {
            $oltService = new OLTService($olt);
            $isOnline = $oltService->testConnection();

            if ($isOnline) {
                ActivityLog::log(
                    'connection_tested',
                    'OLT',
                    $olt->id,
                    "Connection test successful for OLT: {$olt->name}",
                    ['status' => 'online']
                );

                return back()->with('success', 'OLT is online and responding!');
            }
        } catch (\Exception $e) {
            ActivityLog::log(
                'connection_failed',
                'OLT',
                $olt->id,
                "Connection test failed for OLT: {$olt->name}",
                ['status' => 'offline', 'error' => $e->getMessage()]
            );

            return back()->with('error', 'Connection failed: ' . $e->getMessage());
        }

        return back()->with('error', 'Unable to connect to OLT');
    }

    public function getONTList(OLT $olt, Request $request)
    {
        $request->validate([
            'pon_port' => 'required|string',
        ]);

        try {
            $oltService = new OLTService($olt);
            $onts = $oltService->getONTList($request->pon_port);

            return view('olts.ont-list', compact('olt', 'onts', 'request'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to get ONT list: ' . $e->getMessage());
        }
    }

    public function getONTStatus(OLT $olt, Request $request)
    {
        $request->validate([
            'pon_port' => 'required|string',
            'ont_id' => 'required|integer',
        ]);

        try {
            $oltService = new OLTService($olt);
            $status = $oltService->getONTStatus($request->pon_port, $request->ont_id);

            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function executeSSHCommand(Request $request, OLT $olt)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        try {
            $oltService = new OLTService($olt);
            $output = $oltService->executeCommand($request->command);

            return response()->json([
                'success' => true,
                'output' => nl2br(htmlspecialchars($output))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

}
