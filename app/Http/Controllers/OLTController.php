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
        $query = OLT::withCount('customers');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $olts = $query->latest()->paginate(15);

        return view('olts.index', compact('olts'));
    }

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
