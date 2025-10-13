<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class AccessPointController extends Controller
{
    /** 🧾 List & Filter */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $brand = $request->input('brand');
        $status = $request->input('status');

        $query = AccessPoint::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('ip_address', 'like', "%$search%")
                  ->orWhere('ssid', 'like', "%$search%")
                  ->orWhere('mac_address', 'like', "%$search%");
            });
        }

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $accessPoints = $query->latest()->paginate(20);
        $brands = AccessPoint::select('brand')->distinct()->whereNotNull('brand')->pluck('brand');

        return view('access-points.index', compact('accessPoints', 'brands', 'search', 'brand', 'status'));
    }

    /** ➕ Create Form */
    public function create()
    {
        return view('access-points.create');
    }

    /** 💾 Store Data */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'ip_address' => 'required|ip|unique:access_points,ip_address',
            'mac_address' => 'nullable|string|max:17',
            'ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|max:50',
            'max_clients' => 'nullable|integer|min:1',
            'connected_clients' => 'nullable|integer|min:0',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'ssh_port' => 'nullable|integer|min:1|max:65535',
            'status' => 'nullable|in:online,offline,maintenance',
            'is_active' => 'nullable|boolean',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Set defaults
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['status'] = $validated['status'] ?? 'offline';
        $validated['ssh_port'] = $validated['ssh_port'] ?? 22;

        // 🔍 Auto detect MAC address if not provided
        if (empty($validated['mac_address']) && !empty($validated['ip_address'])) {
            $mac = $this->getMacAddress($validated['ip_address']);
            if ($mac) {
                $validated['mac_address'] = $mac;
            }
        }

        AccessPoint::create($validated);

        return redirect()
            ->route('access-points.index')
            ->with('success', 'Access Point created successfully!');
    }

    /** 🔍 Show Detail */
    public function show(AccessPoint $access_point)
    {
        return view('access-points.show', ['ap' => $access_point]);
    }

    /** ✏️ Edit Form */
    public function edit(AccessPoint $access_point)
    {
        return view('access-points.edit', compact('access_point'));
    }

    /** 💾 Update Data */
    public function update(Request $request, AccessPoint $access_point)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'ip_address' => 'required|ip|unique:access_points,ip_address,' . $access_point->id,
            'mac_address' => 'nullable|string|max:17',
            'ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|max:50',
            'max_clients' => 'nullable|integer|min:1',
            'connected_clients' => 'nullable|integer|min:0',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'ssh_port' => 'nullable|integer|min:1|max:65535',
            'status' => 'nullable|in:online,offline,maintenance',
            'is_active' => 'nullable|boolean',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Update defaults
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Auto detect MAC if empty
        if (empty($validated['mac_address']) && !empty($validated['ip_address'])) {
            $mac = $this->getMacAddress($validated['ip_address']);
            if ($mac) {
                $validated['mac_address'] = $mac;
            }
        }

        $access_point->update($validated);

        return redirect()
            ->route('access-points.index')
            ->with('success', 'Access Point updated successfully!');
    }

    /** 🗑️ Delete Data */
    public function destroy(AccessPoint $access_point)
    {
        $access_point->delete();

        return redirect()
            ->route('access-points.index')
            ->with('success', 'Access Point deleted successfully!');
    }

    /** 🧠 Ping via AJAX button */
    public function ping(AccessPoint $access_point)
    {
        $ip = $access_point->ip_address;

        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $pingCmd = $isWindows
                ? ["ping", "-n", "1", "-w", "1000", $ip]
                : ["/bin/ping", "-c", "1", "-W", "1", $ip];

            $process = new Process($pingCmd);
            $process->run();

            $output = $process->getOutput();
            $success = $process->isSuccessful();

            $latency = null;
            if ($success && preg_match('/time[=<]\s*([\d\.]+)/i', $output, $m)) {
                $latency = (float) $m[1];
            }

            $status = $success ? 'online' : 'offline';

            // Update AP status
            $access_point->update([
                'status' => $status,
                'ping_latency' => $latency,
                'last_seen' => $success ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'status' => $status,
                'latency' => $latency,
                'last_seen' => $access_point->last_seen ? $access_point->last_seen->format('Y-m-d H:i:s') : null,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** 🧠 Ping Test (untuk form create/edit) */
    public function pingTest(Request $request)
    {
        $ip = $request->input('ip');

        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return response()->json(['error' => 'Valid IP address is required'], 400);
        }

        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $pingCmd = $isWindows
                ? ["ping", "-n", "1", "-w", "1000", $ip]
                : ["/bin/ping", "-c", "1", "-W", "1", $ip];

            $process = new Process($pingCmd);
            $process->run();

            $output = $process->getOutput();
            $success = $process->isSuccessful();

            $latency = null;
            if ($success && preg_match('/time[=<]\s*([\d\.]+)/i', $output, $m)) {
                $latency = (float) $m[1];
            }

            return response()->json([
                'online' => $success,
                'latency' => $latency,
                'message' => $success
                    ? "Host is reachable (latency: {$latency}ms)"
                    : "Host is unreachable",
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** 🧩 Get MAC Address via AJAX */
    public function getMac(Request $request)
    {
        $ip = $request->input('ip');

        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return response()->json(['error' => 'Valid IP address is required'], 400);
        }

        $mac = $this->getMacAddress($ip);

        return response()->json([
            'success' => $mac ? true : false,
            'mac' => $mac,
            'message' => $mac ? 'MAC address detected' : 'MAC address not found in ARP table'
        ]);
    }

    /** 🔄 Bulk Ping - Ping multiple APs */
    public function bulkPing(Request $request)
    {
        $apIds = explode(',', $request->input('ap_ids', ''));

        if (empty($apIds)) {
            return response()->json(['error' => 'No Access Points selected'], 400);
        }

        $results = [];
        $aps = AccessPoint::whereIn('id', $apIds)->get();

        foreach ($aps as $ap) {
            try {
                $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
                $pingCmd = $isWindows
                    ? ["ping", "-n", "1", "-w", "1000", $ap->ip_address]
                    : ["/bin/ping", "-c", "1", "-W", "1", $ap->ip_address];

                $process = new Process($pingCmd);
                $process->run();

                $success = $process->isSuccessful();
                $latency = null;

                if ($success && preg_match('/time[=<]\s*([\d\.]+)/i', $process->getOutput(), $m)) {
                    $latency = (float) $m[1];
                }

                $status = $success ? 'online' : 'offline';

                $ap->update([
                    'status' => $status,
                    'ping_latency' => $latency,
                    'last_seen' => $success ? now() : null,
                ]);

                $results[] = [
                    'id' => $ap->id,
                    'name' => $ap->name,
                    'status' => $status,
                    'latency' => $latency,
                ];

            } catch (\Throwable $e) {
                $results[] = [
                    'id' => $ap->id,
                    'name' => $ap->name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /** 🧠 Helper: Get MAC Address via ARP */
    private function getMacAddress(?string $ip): ?string
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

            if ($isWindows) {
                // Windows: arp -a IP
                $output = shell_exec("arp -a " . escapeshellarg($ip));
            } else {
                // Linux/Mac: arp -n IP
                $output = shell_exec("arp -n " . escapeshellarg($ip));
            }

            // Match MAC address pattern
            if ($output && preg_match('/(([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})/', $output, $matches)) {
                return strtoupper(str_replace('-', ':', $matches[1]));
            }

        } catch (\Throwable $e) {
            \Log::error('MAC Address Detection Failed', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
