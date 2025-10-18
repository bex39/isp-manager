<?php

namespace App\Http\Controllers;

use App\Models\ONT;
use App\Models\OLT;
use App\Models\AcsDeviceSession;
use App\Models\AcsConfigHistory;
use App\Models\AcsAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class AcsManagementController extends Controller
{
    /**
     * Main device list (GenieACS-style)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $oltId = $request->input('olt_id');
        $status = $request->input('status');
        $signal = $request->input('signal');
        $acsStatus = $request->input('acs_status');

        $query = ONT::with(['olt', 'customer', 'session', 'odp']);

        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sn', 'like', "%{$search}%")
                  ->orWhere('management_ip', 'like', "%{$search}%");
            });
        }

        // Filter by OLT
        if ($oltId) {
            $query->where('olt_id', $oltId);
        }

        // Filter by status
        if ($status === 'online') {
            $query->where('status', 'online');
        } elseif ($status === 'offline') {
            $query->where('status', 'offline');
        } elseif ($status === 'los') {
            $query->where('status', 'los');
        }

        // Filter by signal
        if ($signal === 'good') {
            $query->where('rx_power', '>=', -20);
        } elseif ($signal === 'warning') {
            $query->whereBetween('rx_power', [-25, -20]);
        } elseif ($signal === 'bad') {
            $query->where('rx_power', '<', -25);
        }

        // Filter by ACS status
        if ($acsStatus === 'managed') {
            $query->whereHas('session');
        } elseif ($acsStatus === 'unmanaged') {
            $query->whereDoesntHave('session');
        }

        $devices = $query->latest('updated_at')->paginate(50)->withQueryString();

        // Statistics
        $stats = [
            'total' => ONT::count(),
            'online' => ONT::where('status', 'online')->count(),
            'offline' => ONT::where('status', 'offline')->count(),
            'los' => ONT::where('status', 'los')->count(),
            'acs_managed' => ONT::whereHas('session')->count(),
            'unprovisioned' => ONT::whereNull('last_provision_at')->count(),
            'good_signal' => ONT::where('rx_power', '>=', -20)->count(),
            'warning_signal' => ONT::whereBetween('rx_power', [-25, -20])->count(),
            'bad_signal' => ONT::where('rx_power', '<', -25)->count(),
            'active_alerts' => AcsAlert::whereIn('status', ['new', 'acknowledged'])->count(),
        ];

        // Get OLTs for filter
        $olts = OLT::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('acs.index', compact('devices', 'stats', 'olts'));
    }

    /**
     * Device details
     */
    public function show(ONT $ont)
    {
        // Load basic relations (always exists)
        $ont->load(['olt', 'customer', 'odp']);

        // ✅ Initialize empty collections for ACS relations
        $ont->setRelation('configHistories', collect());
        $ont->setRelation('alerts', collect());
        $ont->setRelation('session', null);
        $ont->setRelation('provisionTemplate', null);

        // ✅ Check if ACS tables exist before loading
        if (Schema::hasTable('acs_device_sessions')) {
            $ont->load('session');
        }

        if (Schema::hasTable('acs_config_templates')) {
            $ont->load('provisionTemplate');
        }

        if (Schema::hasTable('acs_config_histories')) {
            $ont->load(['configHistories' => function($q) {
                $q->latest()->take(20);
            }]);
        }

        if (Schema::hasTable('acs_alerts')) {
            $ont->load(['alerts' => function($q) {
                $q->whereIn('status', ['new', 'acknowledged'])->latest();
            }]);
        }

        // Get statistics (with safe checks)
        $deviceStats = [
            'total_configs' => $ont->configHistories ? $ont->configHistories->count() : 0,
            'successful_configs' => $ont->configHistories
                ? $ont->configHistories->where('status', 'success')->count()
                : 0,
            'failed_configs' => $ont->configHistories
                ? $ont->configHistories->where('status', 'failed')->count()
                : 0,
            'active_alerts' => $ont->alerts
                ? $ont->alerts->whereIn('status', ['new', 'acknowledged'])->count()
                : 0,
            'days_since_provision' => $ont->last_provision_at
                ? $ont->last_provision_at->diffInDays(now())
                : null,
        ];

        return view('acs.show', compact('ont', 'deviceStats'));
    }
    /**
     * Provision device
     */
    public function provision(ONT $ont)
    {
        try {
            // Add to provisioning queue
            $ont->queueForProvisioning('manual', 'high');

            // Log action
            $ont->logConfigChange('provision', [
                'triggered_by' => 'manual',
                'user_id' => auth()->id(),
            ], 'pending', auth()->id());

            return back()->with('success', 'Device added to provisioning queue!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to queue provisioning: ' . $e->getMessage());
        }
    }

    /**
     * Re-provision device (for reset devices)
     */
    public function reprovision(ONT $ont)
    {
        try {
            // Get last known config
            $lastConfig = $ont->getLastProvisionInfo();

            $configData = $lastConfig ? $lastConfig->parameters : [
                'wifi_ssid' => $ont->wifi_ssid,
                'wifi_password' => $ont->wifi_password,
                'template_id' => $ont->provision_template_id,
            ];

            // Add to provisioning queue
            $ont->queueForProvisioning('re_provision', 'high', $configData);

            // Log action
            $ont->logConfigChange('re_provision', $configData, 'pending', auth()->id());

            return back()->with('success', 'Device queued for re-provisioning with last known config!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to queue re-provisioning: ' . $e->getMessage());
        }
    }

    /**
     * Reboot device
     */
    public function reboot(ONT $ont)
    {
        try {
            $olt = $ont->olt;

            if (!$olt) {
                return back()->with('error', 'OLT not found for this device');
            }

            // Create SSH connection
            $ssh = new \phpseclib3\Net\SSH2($olt->ip_address, $olt->ssh_port ?? 23);

            if (!$ssh->login($olt->username, $olt->password)) {
                throw new \Exception('Failed to connect to OLT');
            }

            $brand = strtolower($olt->brand ?? '');

            // Execute reboot command based on OLT brand
            if (str_contains($brand, 'huawei')) {
                $ssh->write("enable\n");
                sleep(1);
                $ssh->write("config\n");
                sleep(1);
                $ssh->write("interface gpon 0/{$ont->pon_port}\n");
                sleep(1);
                $ssh->write("ont reset {$ont->pon_port} {$ont->ont_id}\n");
                sleep(1);
            } elseif (str_contains($brand, 'zte')) {
                $ssh->write("enable\n");
                sleep(1);
                $ssh->write("config terminal\n");
                sleep(1);
                $ssh->write("pon-onu-mng gpon-onu_1/{$ont->pon_port}:{$ont->ont_id}\n");
                sleep(1);
                $ssh->write("reboot\n");
                sleep(1);
            }

            // Log action
            $ont->logConfigChange('reboot', [
                'method' => 'via_olt',
                'olt_id' => $olt->id,
            ], 'success', auth()->id());

            return back()->with('success', 'Reboot command sent successfully!');

        } catch (\Exception $e) {
            // Log failure
            $ont->logConfigChange('reboot', [], 'failed', auth()->id());

            return back()->with('error', 'Reboot failed: ' . $e->getMessage());
        }
    }

    /**
     * Check signal strength
     */
    public function checkSignal(ONT $ont)
    {
        try {
            $olt = $ont->olt;

            if (!$olt) {
                return back()->with('error', 'OLT not found');
            }

            $ssh = new \phpseclib3\Net\SSH2($olt->ip_address, $olt->ssh_port ?? 23);

            if (!$ssh->login($olt->username, $olt->password)) {
                throw new \Exception('Failed to connect to OLT');
            }

            $brand = strtolower($olt->brand ?? '');

            // Get signal info based on OLT brand
            if (str_contains($brand, 'huawei')) {
                $ssh->write("display ont optical-info 0/{$ont->pon_port} {$ont->ont_id}\n");
                sleep(2);
                $output = $ssh->read();

                // Parse signal values
                preg_match('/RX power.*?:\s*([-\d.]+)/', $output, $rxMatch);
                preg_match('/TX power.*?:\s*([-\d.]+)/', $output, $txMatch);

                $rxPower = $rxMatch[1] ?? null;
                $txPower = $txMatch[1] ?? null;

            } elseif (str_contains($brand, 'zte')) {
                $ssh->write("show gpon onu detail-info gpon-onu_1/{$ont->pon_port}:{$ont->ont_id}\n");
                sleep(2);
                $output = $ssh->read();

                // Parse ZTE output
                preg_match('/ONU RX power.*?:\s*([-\d.]+)/', $output, $rxMatch);
                preg_match('/OLT RX power.*?:\s*([-\d.]+)/', $output, $txMatch);

                $rxPower = $rxMatch[1] ?? null;
                $txPower = $txMatch[1] ?? null;
            }

            // Update ONT with signal values
            if ($rxPower || $txPower) {
                $ont->update([
                    'rx_power' => $rxPower,
                    'tx_power' => $txPower,
                    'last_seen' => now(),
                ]);

                return back()->with('success', "Signal checked! RX: {$rxPower} dBm, TX: {$txPower} dBm");
            }

            return back()->with('warning', 'Signal data not available');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to check signal: ' . $e->getMessage());
        }
    }

    /**
     * Configure device (WiFi, VLAN, etc)
     */
    public function configure(Request $request, ONT $ont)
    {
        $validated = $request->validate([
            'config_type' => 'required|in:wifi,vlan,port,custom',
            'parameters' => 'required|array',
        ]);

        try {
            // Log config change
            $history = $ont->logConfigChange(
                'configure_' . $validated['config_type'],
                $validated['parameters'],
                'pending',
                auth()->id()
            );

            // Add to provisioning queue for processing
            $ont->queueForProvisioning('configure', 'normal', [
                'config_type' => $validated['config_type'],
                'parameters' => $validated['parameters'],
                'history_id' => $history->id,
            ]);

            return back()->with('success', 'Configuration queued for processing!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to queue configuration: ' . $e->getMessage());
        }
    }

    /**
     * Enroll device to ACS management
     */
    public function enrollToAcs(ONT $ont)
    {
        try {
            // Create ACS session
            $ont->createOrUpdateSession([
                'parameters' => [
                    'sn' => $ont->sn,
                    'model' => $ont->model,
                    'wifi_ssid' => $ont->wifi_ssid,
                    'enrolled_by' => auth()->id(),
                    'enrolled_at' => now(),
                ],
            ]);

            // Enable auto-provision
            $ont->update(['auto_provision_enabled' => true]);

            return back()->with('success', 'Device enrolled to ACS management!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to enroll device: ' . $e->getMessage());
        }
    }

    /**
     * Unenroll device from ACS
     */
    public function unenrollFromAcs(ONT $ont)
    {
        try {
            // Delete session
            $ont->session()->delete();

            // Disable auto-provision
            $ont->update(['auto_provision_enabled' => false]);

            return back()->with('success', 'Device unenrolled from ACS management!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to unenroll device: ' . $e->getMessage());
        }
    }

    /**
     * Refresh device session
     */
    public function refreshSession(ONT $ont)
    {
        try {
            if (!$ont->session) {
                return back()->with('error', 'Device not enrolled in ACS');
            }

            $ont->session->update([
                'last_inform' => now(),
                'parameters' => [
                    'sn' => $ont->sn,
                    'model' => $ont->model,
                    'status' => $ont->status,
                    'refreshed_at' => now(),
                ],
            ]);

            return back()->with('success', 'Session refreshed!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to refresh session: ' . $e->getMessage());
        }
    }

    /**
     * Scan all OLTs for unprovisioned devices
     */
    public function scanDevices()
    {
        try {
            // Trigger scan command (will be processed by artisan command)
            \Artisan::call('acs:scan-unprovisioned');

            return back()->with('success', 'Device scan started! Check provisioning queue for results.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start scan: ' . $e->getMessage());
        }
    }

    /**
     * Scan specific OLT
     */
    public function scanOlt(OLT $olt)
    {
        try {
            // Trigger scan for specific OLT
            \Artisan::call('acs:scan-unprovisioned', ['--olt_id' => $olt->id]);

            return back()->with('success', "Scan started for OLT: {$olt->name}");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to scan OLT: ' . $e->getMessage());
        }
    }

    /**
     * Show unprovisioned devices
     */
    public function unprovisionedDevices()
    {
        $devices = ONT::whereNull('last_provision_at')
            ->orWhere('status', 'offline')
            ->with(['olt', 'customer'])
            ->latest()
            ->paginate(50);

        return view('acs.unprovisioned', compact('devices'));
    }

    /**
     * Statistics dashboard
     */
    /**
 * Statistics dashboard
 */
public function statistics()
{
    // Check if ACS tables exist
    $hasAcsTables = Schema::hasTable('acs_config_histories')
        && Schema::hasTable('acs_alerts');

    if (!$hasAcsTables) {
        return view('acs.statistics', [
            'stats' => [
                'devices' => [
                    'total' => ONT::count(),
                    'online' => ONT::where('status', 'online')->count(),
                    'offline' => ONT::where('status', 'offline')->count(),
                    'los' => ONT::where('status', 'los')->count(),
                ],
                'acs' => [
                    'managed' => 0,
                    'unmanaged' => ONT::count(),
                    'auto_provision_enabled' => 0,
                ],
                'signal' => [
                    'excellent' => ONT::where('rx_power', '>=', -20)->count(),
                    'good' => ONT::whereBetween('rx_power', [-23, -20])->count(),
                    'fair' => ONT::whereBetween('rx_power', [-25, -23])->count(),
                    'poor' => ONT::where('rx_power', '<', -25)->count(),
                ],
                'provisioning' => [
                    'total' => 0,
                    'today' => 0,
                    'success' => 0,
                    'failed' => 0,
                ],
                'alerts' => [
                    'total' => 0,
                    'new' => 0,
                    'acknowledged' => 0,
                    'critical' => 0,
                ],
            ],
            'recentActivities' => collect(),
        ]);
    }

    // Original code with tables existing
    $stats = [
        'devices' => [
            'total' => ONT::count(),
            'online' => ONT::where('status', 'online')->count(),
            'offline' => ONT::where('status', 'offline')->count(),
            'los' => ONT::where('status', 'los')->count(),
        ],
        'acs' => [
            'managed' => ONT::whereHas('session')->count(),
            'unmanaged' => ONT::whereDoesntHave('session')->count(),
            'auto_provision_enabled' => ONT::where('auto_provision_enabled', true)->count(),
        ],
        'signal' => [
            'excellent' => ONT::where('rx_power', '>=', -20)->count(),
            'good' => ONT::whereBetween('rx_power', [-23, -20])->count(),
            'fair' => ONT::whereBetween('rx_power', [-25, -23])->count(),
            'poor' => ONT::where('rx_power', '<', -25)->count(),
        ],
        'provisioning' => [
            'total' => AcsConfigHistory::count(),
            'today' => AcsConfigHistory::whereDate('created_at', today())->count(),
            'success' => AcsConfigHistory::where('status', 'success')->count(),
            'failed' => AcsConfigHistory::where('status', 'failed')->count(),
        ],
        'alerts' => [
            'total' => AcsAlert::count(),
            'new' => AcsAlert::where('status', 'new')->count(),
            'acknowledged' => AcsAlert::where('status', 'acknowledged')->count(),
            'critical' => AcsAlert::where('severity', 'critical')->whereIn('status', ['new', 'acknowledged'])->count(),
        ],
    ];

    // Recent activities
    $recentActivities = AcsConfigHistory::with(['ont', 'executor'])
        ->latest()
        ->take(20)
        ->get();

    return view('acs.statistics', compact('stats', 'recentActivities'));
}

    /**
     * API: Get device status (for AJAX)
     */
    public function deviceStatus(ONT $ont)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ont->id,
                'name' => $ont->name,
                'sn' => $ont->sn,
                'status' => $ont->status,
                'rx_power' => $ont->rx_power,
                'tx_power' => $ont->tx_power,
                'last_seen' => $ont->last_seen?->toIso8601String(),
                'acs_managed' => $ont->isAcsManaged(),
                'acs_status' => $ont->getAcsStatus(),
                'signal_quality' => $ont->getSignalQuality(),
                'active_alerts' => $ont->getActiveAlerts()->count(),
            ]
        ]);
    }

    /**
     * API: Get device parameters (for AJAX)
     */
    public function deviceParameters(ONT $ont)
    {
        $session = $ont->session;

        return response()->json([
            'success' => true,
            'data' => [
                'basic' => [
                    'sn' => $ont->sn,
                    'model' => $ont->model,
                    'firmware' => $session?->parameters['firmware'] ?? 'Unknown',
                    'mac_address' => $session?->parameters['mac_address'] ?? 'Unknown',
                ],
                'network' => [
                    'management_ip' => $ont->management_ip,
                    'wifi_ssid' => $ont->wifi_ssid,
                    'status' => $ont->status,
                ],
                'signal' => [
                    'rx_power' => $ont->rx_power,
                    'tx_power' => $ont->tx_power,
                    'quality' => $ont->getSignalQuality(),
                ],
                'acs' => [
                    'managed' => $ont->isAcsManaged(),
                    'last_inform' => $session?->last_inform?->toIso8601String(),
                    'last_boot' => $session?->last_boot?->toIso8601String(),
                    'uptime' => $session?->uptime,
                ],
            ]
        ]);
    }

    /**
     * API: Get devices stats (for dashboard widgets)
     */
    public function devicesStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => ONT::count(),
                'online' => ONT::where('status', 'online')->count(),
                'offline' => ONT::where('status', 'offline')->count(),
                'alerts' => AcsAlert::where('status', 'new')->count(),
            ]
        ]);
    }
}
