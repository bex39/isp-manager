<?php

// File: app/Http/Controllers/ONTController.php

namespace App\Http\Controllers;

use App\Models\ONT;
use App\Models\OLT;
use App\Models\ODP;
use App\Models\Customer;

use App\Services\ONTProvisioningService;
use Illuminate\Http\Request;

class ONTController extends Controller
{
    protected $provisioningService;

    public function __construct(ONTProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    public function index(Request $request)
{
    $search = $request->input('search');
    $oltId = $request->input('olt_id');
    $odpId = $request->input('odp_id');
    $status = $request->input('status');

    $query = ONT::with(['customer', 'olt', 'odp']);

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sn', 'like', "%{$search}%")
              ->orWhere('management_ip', 'like', "%{$search}%");
        });
    }

    if ($oltId) {
        $query->where('olt_id', $oltId);
    }

    if ($odpId) {
        $query->where('odp_id', $odpId);
    }

    if ($status) {
        $query->where('status', $status);
    }

    $onts = $query->latest()->paginate(20)->withQueryString();

    // ✅ TAMBAH INI - untuk filter dropdown
    $olts = OLT::where('is_active', true)->orderBy('name')->get();
    $odps = ODP::where('is_active', true)->orderBy('name')->get();

    return view('onts.index', compact('onts', 'olts', 'odps'));
}
    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $olts = OLT::where('is_active', true)->get();
        $odps = ODP::where('is_active', true)->get(); // TAMBAH INI

        return view('onts.create', compact('customers', 'olts', 'odps'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'olt_id' => 'required|exists:olts,id',
        'customer_id' => 'nullable|exists:customers,id',
        'odp_id' => 'nullable|exists:odps,id',
        'odp_port' => 'nullable|integer|min:1',
        'name' => 'required|string',
        'sn' => 'required|string|unique:onts,sn',
        'management_ip' => 'nullable|ip',
        'username' => 'nullable|string',
        'password' => 'nullable|string',
        'model' => 'nullable|string',
        'pon_type' => 'nullable|string',
        'pon_port' => 'nullable|integer',
        'ont_id' => 'nullable|integer',
        'wifi_ssid' => 'nullable|string',
        'wifi_password' => 'nullable|string|min:8',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'address' => 'nullable|string',
        'installation_date' => 'nullable|date',
        'notes' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    $validated['status'] = 'offline';
    $validated['is_active'] = $request->has('is_active') ? 1 : 0;

    // Create ONT
    $ont = ONT::create($validated);

    // ✅ UPDATE ODP USED PORTS
    if ($ont->odp_id) {
        $odp = ODP::find($ont->odp_id);
        if ($odp) {
            // Check if port is already used
            $portUsed = ONT::where('odp_id', $ont->odp_id)
                ->where('odp_port', $ont->odp_port)
                ->where('id', '!=', $ont->id)
                ->exists();

            if ($portUsed) {
                $ont->delete();
                return back()->with('error', "ODP port {$ont->odp_port} is already in use!")
                    ->withInput();
            }

            // Increment used ports
            $odp->incrementUsedPorts();
        }
    }

    // Auto-provision jika ada PON port dan ONT ID
    if ($ont->pon_port && $ont->ont_id) {
        $result = $this->provisioningService->provisionONT($ont);

        if ($result['success']) {
            return redirect()->route('onts.show', $ont)
                ->with('success', 'ONT created and provisioned successfully!');
        } else {
            return redirect()->route('onts.show', $ont)
                ->with('warning', 'ONT created but provisioning failed: ' . $result['message']);
        }
    }

    return redirect()->route('onts.index')->with('success', 'ONT created successfully!');
}

    public function show(ONT $ont)
{
    $ont->load(['olt', 'customer', 'odp']); // ✅ Tambah 'odp'
    return view('onts.show', compact('ont'));
}

    public function edit(ONT $ont)
{
    $olts = OLT::where('is_active', true)->get();
    $customers = Customer::where('status', 'active')->get();
    $odps = ODP::where('is_active', true)->get(); // ✅ Tambah ini

    return view('onts.edit', compact('ont', 'olts', 'customers', 'odps'));
}

    public function update(Request $request, ONT $ont)
{
    $validated = $request->validate([
        'olt_id' => 'required|exists:olts,id',
        'customer_id' => 'nullable|exists:customers,id',
        'odp_id' => 'nullable|exists:odps,id',
        'odp_port' => 'nullable|integer|min:1',
        'name' => 'required|string',
        'sn' => 'required|string|unique:onts,sn,' . $ont->id,
        'management_ip' => 'nullable|ip',
        'username' => 'nullable|string',
        'password' => 'nullable|string',
        'model' => 'nullable|string',
        'pon_type' => 'nullable|string',
        'pon_port' => 'nullable|integer',
        'ont_id' => 'nullable|integer',
        'wifi_ssid' => 'nullable|string',
        'wifi_password' => 'nullable|string|min:8',
        'status' => 'nullable|in:online,offline,disabled,los',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'address' => 'nullable|string',
        'installation_date' => 'nullable|date',
        'notes' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    $validated['is_active'] = $request->has('is_active') ? 1 : 0;

    // ✅ HANDLE ODP CHANGE
    $oldOdpId = $ont->odp_id;
    $oldOdpPort = $ont->odp_port;
    $newOdpId = $validated['odp_id'] ?? null;
    $newOdpPort = $validated['odp_port'] ?? null;

    // Check if ODP or port changed
    if ($oldOdpId != $newOdpId || $oldOdpPort != $newOdpPort) {

        // Decrement old ODP used_ports
        if ($oldOdpId) {
            $oldOdp = ODP::find($oldOdpId);
            if ($oldOdp) {
                $oldOdp->decrementUsedPorts();
            }
        }

        // Increment new ODP used_ports
        if ($newOdpId) {
            $newOdp = ODP::find($newOdpId);
            if ($newOdp) {
                // Check if new port is available
                $portUsed = ONT::where('odp_id', $newOdpId)
                    ->where('odp_port', $newOdpPort)
                    ->where('id', '!=', $ont->id)
                    ->exists();

                if ($portUsed) {
                    return back()->with('error', "ODP port {$newOdpPort} is already in use!")
                        ->withInput();
                }

                $newOdp->incrementUsedPorts();
            }
        }
    }

    $ont->update($validated);

    return redirect()
        ->route('onts.index')
        ->with('success', 'ONT updated successfully!');
}

   public function destroy(ONT $ont)
{
    // ✅ DECREMENT ODP USED PORTS BEFORE DELETE
    if ($ont->odp_id) {
        $odp = ODP::find($ont->odp_id);
        if ($odp) {
            $odp->decrementUsedPorts();
        }
    }

    // Hapus dari OLT dulu (jika ada provisioning)
    if ($ont->pon_port && $ont->ont_id) {
        $result = $this->provisioningService->deleteONTFromOLT($ont);

        if (!$result['success']) {
            // Rollback ODP decrement jika gagal hapus dari OLT
            if ($ont->odp_id && $odp) {
                $odp->incrementUsedPorts();
            }

            return back()->with('warning', 'Failed to remove ONT from OLT: ' . $result['message']);
        }
    }

    $ont->delete();

    return redirect()
        ->route('onts.index')
        ->with('success', 'ONT deleted successfully!');
}

    /**
     * Manual provisioning ONT ke OLT
     */
    public function provision(ONT $ont)
    {
        if (!$ont->pon_port || !$ont->ont_id) {
            return back()->with('error', 'PON Port and ONT ID must be set before provisioning');
        }

        $result = $this->provisioningService->provisionONT($ont);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', 'Provisioning failed: ' . $result['message']);
        }
    }

    /**
     * Check signal strength
     */
    public function checkSignal(ONT $ont)
    {
        $result = $this->provisioningService->getONTSignal($ont);

        if ($result['success']) {
            $message = "Signal checked! RX: {$result['rx_power']} dBm, TX: {$result['tx_power']} dBm";
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Failed to check signal: ' . $result['message']);
        }
    }

    /**
     * Change WiFi settings
     */
    public function changeWiFi(Request $request, ONT $ont)
    {
        $request->validate([
            'wifi_ssid' => 'required|string',
            'wifi_password' => 'required|string|min:8',
        ]);

        $result = $this->provisioningService->configureWiFi(
            $ont,
            $request->wifi_ssid,
            $request->wifi_password
        );

        if ($result['success']) {
            return back()->with('success', 'WiFi settings updated successfully!');
        } else {
            // Jika gagal via SSH, update database saja
            $ont->update([
                'wifi_ssid' => $request->wifi_ssid,
                'wifi_password' => $request->wifi_password,
            ]);

            return back()->with('warning', 'WiFi settings saved to database. Manual configuration on ONT may be required.');
        }
    }

    /**
     * Ping ONT
     */
    public function ping(ONT $ont)
    {
        if (!$ont->management_ip) {
            return response()->json(['success' => false, 'message' => 'No IP address']);
        }

        exec("ping -c 4 {$ont->management_ip}", $output, $result);

        $isOnline = $result === 0;

        $ont->update([
            'status' => $isOnline ? 'online' : 'offline',
            'last_seen' => $isOnline ? now() : $ont->last_seen
        ]);

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'output' => implode("\n", $output)
        ]);
    }

    /**
     * Reboot ONT via OLT
     */
    public function reboot(ONT $ont)
    {
        $olt = $ont->olt;

        if (!$olt) {
            return back()->with('error', 'OLT not found');
        }

        try {
            $ssh = new \phpseclib3\Net\SSH2($olt->ip_address, $olt->ssh_port ?? 23);

            if (!$ssh->login($olt->username, $olt->password)) {
                return back()->with('error', 'Failed to connect to OLT');
            }

            $brand = strtolower($olt->brand ?? '');

            if (str_contains($brand, 'huawei')) {
                $ssh->write("config\n");
                sleep(1);
                $ssh->write("interface gpon 0/{$ont->pon_port}\n");
                sleep(1);
                $ssh->write("ont reset {$ont->pon_port} {$ont->ont_id}\n");
                sleep(1);
                $ssh->write("quit\n");
                $ssh->write("quit\n");
            } elseif (str_contains($brand, 'zte')) {
                $ssh->write("config terminal\n");
                sleep(1);
                $ssh->write("pon-onu-mng gpon-onu_1/{$ont->pon_port}:{$ont->ont_id}\n");
                sleep(1);
                $ssh->write("reboot\n");
                sleep(1);
                $ssh->write("exit\n");
            }

            return back()->with('success', 'Reboot command sent to ONT');

        } catch (\Exception $e) {
            return back()->with('error', 'Reboot failed: ' . $e->getMessage());
        }
    }

    /**
     * Get ONT status from OLT
     */
    public function getStatus(ONT $ont)
    {
        $result = $this->provisioningService->getONTSignal($ont);

        return response()->json($result);
    }
}
