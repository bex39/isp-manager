<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Exception;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;


class RouterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_routers', only: ['index', 'show']),
            new Middleware('can:create_router', only: ['create', 'store']),
            new Middleware('can:edit_router', only: ['edit', 'update']),
            new Middleware('can:delete_router', only: ['destroy']),
            new Middleware('can:access_router', only: ['testConnection', 'pppoeUsers']),
            new Middleware('can:reboot_router', only: ['reboot']),
        ];
    }

    public function index(Request $request)
    {
        $query = Router::withCount('customers');

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

        $routers = $query->latest()->paginate(15);

        return view('routers.index', compact('routers'));
    }

    public function create()
    {
        return view('routers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'api_port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'ros_version' => 'required|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'coverage_radius' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $router = Router::create($validated);

        // Log activity
        ActivityLog::log(
            'created',
            'Router',
            $router->id,
            "Created new router: {$router->name} ({$router->ip_address})",
            [
                'ip_address' => $router->ip_address,
                'ssh_port' => $router->ssh_port,
                'api_port' => $router->api_port
            ]
        );

        return redirect()->route('routers.index')->with('success', 'Router berhasil ditambahkan!');
    }

    public function show(Router $router)
    {
        $router->loadCount('customers');

        // Get router info
        $routerInfo = ['online' => false];

        try {
            $mikrotik = new MikrotikService($router);
            $routerInfo = $mikrotik->getSystemInfo();
            $routerInfo['online'] = true;
        } catch (\Exception $e) {
            $routerInfo['error'] = $e->getMessage();
        }

        return view('routers.show', compact('router', 'routerInfo'));
    }

    public function edit(Router $router)
    {
        return view('routers.edit', compact('router'));
    }

    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers,ip_address,' . $router->id,
            'ssh_port' => 'required|integer|min:1|max:65535',
            'api_port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'ros_version' => 'required|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'coverage_radius' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $router->update($validated);

        // Log activity
        ActivityLog::log(
            'updated',
            'Router',
            $router->id,
            "Updated router: {$router->name}",
            ['ip_address' => $router->ip_address]
        );

        return redirect()->route('routers.index')->with('success', 'Router berhasil diupdate!');
    }

    public function destroy(Router $router)
    {
        // Check if router has customers
        $customerCount = $router->customers()->count();

        if ($customerCount > 0) {
            return back()->with('error', "Cannot delete router! This router has {$customerCount} connected customers. Please move customers first.");
        }

        $routerName = $router->name;
        $routerIp = $router->ip_address;

        $router->delete();

        ActivityLog::log(
            'deleted',
            'Router',
            $router->id,
            "Deleted router: {$routerName} ({$routerIp})",
            ['name' => $routerName, 'ip' => $routerIp]
        );

        return redirect()->route('routers.index')->with('success', 'Router deleted successfully!');
    }

    public function testConnection(Router $router)
    {
        try {
            $mikrotikService = new MikrotikService($router); // Gunakan MikrotikService
            $result = $mikrotikService->testConnection();

            if ($result['success']) {
                $router->update([
                    'last_seen' => now(),
                    'is_active' => true
                ]);

                ActivityLog::log(
                    'connection_tested',
                    'Router',
                    $router->id,
                    "Connection test successful: {$router->name}",
                    ['status' => 'online']
                );

                return back()->with('success', 'Router is online!');
            } else {
                $router->update(['is_active' => false]);

                ActivityLog::log(
                    'connection_failed',
                    'Router',
                    $router->id,
                    "Connection failed: {$router->name}",
                    ['error' => $result['message']]
                );

                return back()->with('error', 'Connection failed: ' . $result['message']);
            }

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reboot(Router $router)
    {
        try {
            $mikrotik = new MikrotikService($router);
            $mikrotik->rebootRouter();

            // Log activity
            ActivityLog::log(
                'rebooted',
                'Router',
                $router->id,
                "Rebooted router: {$router->name} by " . auth()->user()->name,
                ['initiated_by' => auth()->user()->name]
            );

            return back()->with('success', 'Router reboot command sent successfully!');
        } catch (\Exception $e) {
            // Log activity
            ActivityLog::log(
                'reboot_failed',
                'Router',
                $router->id,
                "Failed to reboot router: {$router->name}",
                ['error' => $e->getMessage()]
            );

            return back()->with('error', 'Failed to reboot: ' . $e->getMessage());
        }
    }

    public function pppoeUsers(Router $router)
    {
        try {
            $mikrotik = new MikrotikService($router);
            $secrets = $mikrotik->getPPPoESecrets();
            $activeSessions = $mikrotik->getActivePPPoESessions();

            return view('routers.pppoe-users', compact('router', 'secrets', 'activeSessions'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to get PPPoE users: ' . $e->getMessage());
        }
    }

    public function sshTerminal(Router $router)
{
    return view('routers.ssh-terminal', compact('router'));
}

public function executeSSHCommand(Request $request, Router $router)
{
    $request->validate([
        'command' => 'required|string',
    ]);

    try {
        $ssh = new SSH2($router->ip_address, $router->ssh_port ?? 22);

        if (!$ssh->login($router->username, $router->password)) {
            return response()->json([
                'success' => false,
                'output' => 'Login failed! Check credentials.'
            ]);
        }

        // Execute command
        $output = $ssh->exec($request->command);

        // Log activity
        ActivityLog::log(
            'ssh_command',
            'Router',
            $router->id,
            "Executed SSH command on {$router->name}: {$request->command}",
            ['command' => $request->command, 'output' => substr($output, 0, 500)]
        );

        return response()->json([
            'success' => true,
            'output' => $output
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'output' => 'Error: ' . $e->getMessage()
        ]);
    }
}

public function backupConfig(Router $router)
{
    try {
        $ssh = new SSH2($router->ip_address, $router->ssh_port ?? 22);

        if (!$ssh->login($router->username, $router->password)) {
            return back()->with('error', 'SSH login failed!');
        }

        // Generate backup filename dengan timestamp
        $timestamp = now()->format('Y-m-d_His');
        $backupName = "backup-{$router->name}-{$timestamp}";

        // Create backup di Mikrotik
        $ssh->exec("/system backup save name={$backupName}");
        sleep(2); // Tunggu backup selesai

        // Export config juga (format .rsc)
        $exportName = "export-{$router->name}-{$timestamp}";
        $ssh->exec("/export file={$exportName}");
        sleep(2);

        // Download backup file via SFTP
        $sftp = new SFTP($router->ip_address, $router->ssh_port ?? 22);

        if (!$sftp->login($router->username, $router->password)) {
            return back()->with('error', 'SFTP login failed!');
        }

        // Download .backup file
        $backupContent = $sftp->get("{$backupName}.backup");

        if ($backupContent === false) {
            return back()->with('error', 'Failed to download backup file');
        }

        // Hapus file dari Mikrotik setelah download
        $ssh->exec("/file remove {$backupName}.backup");
        $ssh->exec("/file remove {$exportName}.rsc");

        // Log activity
        ActivityLog::log(
            'backup',
            'Router',
            $router->id,
            "Downloaded backup from {$router->name}",
            ['filename' => "{$backupName}.backup"]
        );

        // Return file untuk download
        return response($backupContent)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "attachment; filename=\"{$backupName}.backup\"");

    } catch (\Exception $e) {
        return back()->with('error', 'Backup failed: ' . $e->getMessage());
    }
}

public function exportConfig(Router $router)
{
    try {
        $ssh = new SSH2($router->ip_address, $router->ssh_port ?? 22);

        if (!$ssh->login($router->username, $router->password)) {
            return back()->with('error', 'SSH login failed!');
        }

        $timestamp = now()->format('Y-m-d_His');
        $exportName = "export-{$router->name}-{$timestamp}";

        // Export config
        $ssh->exec("/export file={$exportName}");
        sleep(2);

        // Download via SFTP
        $sftp = new SFTP($router->ip_address, $router->ssh_port ?? 22);

        if (!$sftp->login($router->username, $router->password)) {
            return back()->with('error', 'SFTP login failed!');
        }

        $exportContent = $sftp->get("{$exportName}.rsc");

        if ($exportContent === false) {
            return back()->with('error', 'Failed to download export file');
        }

        // Hapus dari Mikrotik
        $ssh->exec("/file remove {$exportName}.rsc");

        ActivityLog::log(
            'export',
            'Router',
            $router->id,
            "Exported config from {$router->name}",
            ['filename' => "{$exportName}.rsc"]
        );

        return response($exportContent)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$exportName}.rsc\"");

    } catch (\Exception $e) {
        return back()->with('error', 'Export failed: ' . $e->getMessage());
    }
}

}
