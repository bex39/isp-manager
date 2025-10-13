<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Router;
use App\Models\OLT;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_customers', only: ['index', 'show']),
            new Middleware('can:create_customer', only: ['create', 'store']),
            new Middleware('can:edit_customer', only: ['edit', 'update']),
            new Middleware('can:delete_customer', only: ['destroy']),
            new Middleware('can:suspend_customer', only: ['suspend']),
            new Middleware('can:activate_customer', only: ['activate']),
        ];
    }

    public function index(Request $request)
    {
        $query = Customer::with(['package', 'router', 'olt', 'teknisi']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by connection type
        if ($request->filled('connection_type')) {
            $query->where('connection_type', $request->connection_type);
        }

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        $customers = $query->latest()->paginate(15);
        $packages = Package::where('is_active', true)->get();

        return view('customers.index', compact('customers', 'packages'));
    }

    public function create()
    {
        $packages = Package::where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();
        $olts = OLT::where('is_active', true)->get();
        $teknisis = User::role('teknisi')->get();

        return view('customers.create', compact('packages', 'routers', 'olts', 'teknisis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'id_card_number' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            'connection_type' => 'required|in:pppoe_direct,pppoe_mikrotik,static_ip,hotspot,dhcp',
            'package_id' => 'required|exists:packages,id',
            'router_id' => 'nullable|exists:routers,id',
            'installation_date' => 'required|date',
            'assigned_teknisi_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Generate customer code
        $validated['customer_code'] = Customer::generateCustomerCode();

        // Set next billing date (1 month from installation)
        $validated['next_billing_date'] = now()->parse($validated['installation_date'])->addMonth();

        // Set status
        $validated['status'] = 'active';

        // Handle connection config based on type
        $connectionConfig = [];

        if ($request->connection_type === 'pppoe_direct' || $request->connection_type === 'pppoe_mikrotik') {
            $request->validate([
                'pppoe_username' => 'required|string',
                'pppoe_password' => 'required|string',
            ]);
            $connectionConfig = [
                'username' => $request->pppoe_username,
                'password' => $request->pppoe_password,
            ];
        }

        if ($request->connection_type === 'static_ip') {
            $request->validate([
                'static_ip' => 'required|ip',
                'static_subnet' => 'required|string',
                'static_gateway' => 'required|ip',
            ]);
            $connectionConfig = [
                'ip' => $request->static_ip,
                'subnet' => $request->static_subnet,
                'gateway' => $request->static_gateway,
            ];
        }

        if ($request->connection_type === 'pppoe_mikrotik') {
            $request->validate([
                'customer_mikrotik_ip' => 'nullable|ip',
                'customer_mikrotik_username' => 'nullable|string',
                'customer_mikrotik_password' => 'nullable|string',
            ]);
            $validated['customer_mikrotik_ip'] = $request->customer_mikrotik_ip;
            $validated['customer_mikrotik_username'] = $request->customer_mikrotik_username;
            $validated['customer_mikrotik_password'] = $request->customer_mikrotik_password;
        }

        // OLT/Fiber info
        if ($request->filled('olt_id')) {
            $request->validate([
                'ont_serial_number' => 'nullable|string',
                'pon_port' => 'nullable|string',
            ]);
            $validated['olt_id'] = $request->olt_id;
            $validated['ont_serial_number'] = $request->ont_serial_number;
            $validated['pon_port'] = $request->pon_port;
        }

        $validated['connection_config'] = $connectionConfig;

        // Create customer
        $customer = Customer::create($validated);

        // Log activity
        ActivityLog::log(
            'created',
            'Customer',
            $customer->id,
            "Created new customer: {$customer->name} ({$customer->customer_code})",
            [
                'customer_code' => $customer->customer_code,
                'package_id' => $customer->package_id,
                'connection_type' => $customer->connection_type,
                'phone' => $customer->phone
            ]
        );

        // ========================================
        // AUTO CREATE PPPoE USER IN MIKROTIK
        // ========================================
        if (($request->connection_type === 'pppoe_direct' || $request->connection_type === 'pppoe_mikrotik')
            && $customer->router_id) {
            try {
                $router = Router::findOrFail($customer->router_id);
                $mikrotik = new MikrotikService($router);

                // Get package speed
                $package = $customer->package;
                $profileName = "profile_" . $package->download_speed . "M";

                // Create profile
                $mikrotik->createProfile($profileName, $package->download_speed, $package->upload_speed);

                // Create PPPoE user
                $mikrotik->createPPPoEUser(
                    $connectionConfig['username'],
                    $connectionConfig['password'],
                    $profileName
                );

                // Log activity
                ActivityLog::log(
                    'pppoe_created',
                    'Customer',
                    $customer->id,
                    "Created PPPoE user for {$customer->name} in router {$router->name}",
                    [
                        'username' => $connectionConfig['username'],
                        'router_id' => $router->id,
                        'profile' => $profileName
                    ]
                );

                return redirect()->route('customers.index')
                    ->with('success', 'Customer berhasil ditambahkan dan PPPoE user telah dibuat di router!');

            } catch (\Exception $e) {
                // Log error
                ActivityLog::log(
                    'pppoe_failed',
                    'Customer',
                    $customer->id,
                    "Failed to create PPPoE user for {$customer->name}: {$e->getMessage()}",
                    ['error' => $e->getMessage()]
                );

                return redirect()->route('customers.index')
                    ->with('warning', 'Customer berhasil ditambahkan, tetapi gagal membuat PPPoE user: ' . $e->getMessage());
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['package', 'router', 'olt', 'teknisi']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::where('is_active', true)->get();
        $routers = Router::where('is_active', true)->get();
        $olts = OLT::where('is_active', true)->get();
        $teknisis = User::role('teknisi')->get();

        return view('customers.edit', compact('customer', 'packages', 'routers', 'olts', 'teknisis'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'id_card_number' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            'connection_type' => 'required|in:pppoe_direct,pppoe_mikrotik,static_ip,hotspot,dhcp',
            'package_id' => 'required|exists:packages,id',
            'router_id' => 'nullable|exists:routers,id',
            'installation_date' => 'required|date',
            'assigned_teknisi_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,suspended,terminated',
            'notes' => 'nullable|string',
        ]);

        // Cek apakah package berubah
        $packageChanged = $customer->package_id != $request->package_id;

        // Handle connection config
        $connectionConfig = [];

        if ($request->connection_type === 'pppoe_direct' || $request->connection_type === 'pppoe_mikrotik') {
            $connectionConfig = [
                'username' => $request->pppoe_username,
                'password' => $request->pppoe_password,
            ];
        }

        if ($request->connection_type === 'static_ip') {
            $connectionConfig = [
                'ip' => $request->static_ip,
                'subnet' => $request->static_subnet,
                'gateway' => $request->static_gateway,
            ];
        }

        if ($request->connection_type === 'pppoe_mikrotik') {
            $validated['customer_mikrotik_ip'] = $request->customer_mikrotik_ip;
            $validated['customer_mikrotik_username'] = $request->customer_mikrotik_username;
            $validated['customer_mikrotik_password'] = $request->customer_mikrotik_password;
        }

        if ($request->filled('olt_id')) {
            $validated['olt_id'] = $request->olt_id;
            $validated['ont_serial_number'] = $request->ont_serial_number;
            $validated['pon_port'] = $request->pon_port;
        }

        $validated['connection_config'] = $connectionConfig;

        $customer->update($validated);

        // Log activity
        ActivityLog::log(
            'updated',
            'Customer',
            $customer->id,
            "Updated customer: {$customer->name}",
            [
                'package_changed' => $packageChanged,
                'new_package_id' => $request->package_id
            ]
        );

        // ========================================
        // UPDATE BANDWIDTH DI MIKROTIK JIKA PACKAGE BERUBAH
        // ========================================
        if ($packageChanged
            && ($customer->connection_type === 'pppoe_direct' || $customer->connection_type === 'pppoe_mikrotik')
            && $customer->router_id
            && isset($customer->connection_config['username'])) {
            try {
                $router = Router::findOrFail($customer->router_id);
                $mikrotik = new MikrotikService($router);

                $newPackage = $customer->package;

                // Change speed
                $mikrotik->changeUserSpeed(
                    $customer->connection_config['username'],
                    $newPackage->download_speed,
                    $newPackage->upload_speed
                );

                // Log activity
                ActivityLog::log(
                    'bandwidth_changed',
                    'Customer',
                    $customer->id,
                    "Changed bandwidth for {$customer->name} to {$newPackage->name}",
                    [
                        'username' => $customer->connection_config['username'],
                        'new_download' => $newPackage->download_speed,
                        'new_upload' => $newPackage->upload_speed
                    ]
                );

                return redirect()->route('customers.index')
                    ->with('success', 'Customer berhasil diupdate dan bandwidth telah diubah di router!');

            } catch (\Exception $e) {
                // Log error
                ActivityLog::log(
                    'bandwidth_failed',
                    'Customer',
                    $customer->id,
                    "Failed to change bandwidth for {$customer->name}: {$e->getMessage()}",
                    ['error' => $e->getMessage()]
                );

                return redirect()->route('customers.index')
                    ->with('warning', 'Customer berhasil diupdate, tetapi gagal ubah bandwidth: ' . $e->getMessage());
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diupdate!');
    }

    public function destroy(Customer $customer)
    {
        $customerName = $customer->name;
        $customerCode = $customer->customer_code;

        // ========================================
        // DELETE PPPoE USER FROM MIKROTIK
        // ========================================
        if (($customer->connection_type === 'pppoe_direct' || $customer->connection_type === 'pppoe_mikrotik')
            && $customer->router_id
            && isset($customer->connection_config['username'])) {
            try {
                $router = Router::findOrFail($customer->router_id);
                $mikrotik = new MikrotikService($router);
                $mikrotik->deletePPPoEUser($customer->connection_config['username']);

                // Log activity
                ActivityLog::log(
                    'pppoe_deleted',
                    'Customer',
                    $customer->id,
                    "Deleted PPPoE user for {$customerName} from router {$router->name}",
                    ['username' => $customer->connection_config['username']]
                );
            } catch (\Exception $e) {
                // Log error but continue deletion
                logger()->error('Failed to delete PPPoE user: ' . $e->getMessage());
            }
        }

        $customer->delete();

        // Log activity
        ActivityLog::log(
            'deleted',
            'Customer',
            $customer->id,
            "Deleted customer: {$customerName} ({$customerCode})",
            [
                'customer_code' => $customerCode,
                'name' => $customerName
            ]
        );

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus!');
    }

    public function suspend(Customer $customer)
    {
        $customer->update(['status' => 'suspended']);

        // Log activity
        ActivityLog::log(
            'suspended',
            'Customer',
            $customer->id,
            "Suspended customer: {$customer->name}",
            ['customer_code' => $customer->customer_code]
        );

        return back()->with('success', 'Customer berhasil disuspend!');
    }

    public function activate(Customer $customer)
    {
        $customer->update(['status' => 'active']);

        // Log activity
        ActivityLog::log(
            'activated',
            'Customer',
            $customer->id,
            "Activated customer: {$customer->name}",
            ['customer_code' => $customer->customer_code]
        );

        return back()->with('success', 'Customer berhasil diaktifkan!');
    }

    public function changePackage(Request $request, Customer $customer)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $oldPackage = $customer->package;
        $newPackage = Package::findOrFail($request->package_id);

        // Update package
        $customer->update(['package_id' => $request->package_id]);

        // Log activity
        ActivityLog::log(
            'package_changed',
            'Customer',
            $customer->id,
            "Changed package for {$customer->name} from {$oldPackage->name} to {$newPackage->name}",
            [
                'old_package_id' => $oldPackage->id,
                'new_package_id' => $newPackage->id
            ]
        );

        // Update bandwidth di MikroTik jika PPPoE
        if (($customer->connection_type === 'pppoe_direct' || $customer->connection_type === 'pppoe_mikrotik')
            && $customer->router_id
            && isset($customer->connection_config['username'])) {
            try {
                $router = Router::findOrFail($customer->router_id);
                $mikrotik = new MikrotikService($router);

                // Change speed
                $mikrotik->changeUserSpeed(
                    $customer->connection_config['username'],
                    $newPackage->download_speed,
                    $newPackage->upload_speed
                );

                // Log activity
                ActivityLog::log(
                    'bandwidth_changed',
                    'Customer',
                    $customer->id,
                    "Changed bandwidth for {$customer->name} to {$newPackage->getSpeedLabel()}",
                    [
                        'username' => $customer->connection_config['username'],
                        'download' => $newPackage->download_speed,
                        'upload' => $newPackage->upload_speed
                    ]
                );

                return back()->with('success', 'Paket berhasil diubah dan bandwidth telah diupdate di router!');

            } catch (\Exception $e) {
                // Log error
                ActivityLog::log(
                    'bandwidth_failed',
                    'Customer',
                    $customer->id,
                    "Failed to update bandwidth: {$e->getMessage()}",
                    ['error' => $e->getMessage()]
                );

                return back()->with('warning', 'Paket berhasil diubah, tetapi gagal update bandwidth: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Paket customer berhasil diubah!');
    }
}
