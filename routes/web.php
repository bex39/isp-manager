<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OLTController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ONTController;
use App\Http\Controllers\SwitchController;
use App\Http\Controllers\AccessPointController;
use App\Http\Controllers\ODPController;
use App\Http\Controllers\SplitterController;
use App\Http\Controllers\JointBoxController;
use App\Http\Controllers\FiberCableSegmentController;
use App\Http\Controllers\FiberCoreController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ODFController;
use App\Http\Controllers\ODCController;
use App\Http\Controllers\FiberSpliceController;
use App\Http\Controllers\ODPPortController;
use App\Http\Controllers\FiberTestResultController;

use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\Customer\TicketController as CustomerTicketController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== USER MANAGEMENT ====================
    Route::resource('users', UserController::class);

    // ==================== CUSTOMER MANAGEMENT ====================
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/suspend', [CustomerController::class, 'suspend'])->name('customers.suspend');
    Route::post('customers/{customer}/activate', [CustomerController::class, 'activate'])->name('customers.activate');
    Route::post('customers/{customer}/change-package', [CustomerController::class, 'changePackage'])->name('customers.change-package');

    // ==================== PACKAGE MANAGEMENT ====================
    Route::resource('packages', PackageController::class);

    // ==================== ROUTER MANAGEMENT ====================
    Route::resource('routers', RouterController::class);
    Route::post('routers/{router}/test', [RouterController::class, 'testConnection'])->name('routers.test');
    Route::post('routers/{router}/reboot', [RouterController::class, 'reboot'])->name('routers.reboot');
    Route::get('routers/{router}/pppoe-users', [RouterController::class, 'pppoeUsers'])->name('routers.pppoe-users');
    Route::get('routers/{router}/ssh-terminal', [RouterController::class, 'sshTerminal'])->name('routers.ssh-terminal');
    Route::post('routers/{router}/ssh-command', [RouterController::class, 'executeSSHCommand'])->name('routers.ssh-command');
    Route::post('routers/{router}/backup-config', [RouterController::class, 'backupConfig'])->name('routers.backup-config');
    Route::post('routers/{router}/export-config', [RouterController::class, 'exportConfig'])->name('routers.export-config');

    // ==================== INVOICE MANAGEMENT ====================
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');
    Route::get('invoices/{invoice}/pdf/view', [InvoiceController::class, 'viewPdf'])->name('invoices.view-pdf');
    Route::post('invoices/{invoice}/payment/va', [InvoiceController::class, 'createVirtualAccount'])->name('invoices.payment.va');
    Route::post('invoices/{invoice}/payment/qris', [InvoiceController::class, 'createQRIS'])->name('invoices.payment.qris');
    Route::post('invoices/{invoice}/payment/ewallet', [InvoiceController::class, 'createEWallet'])->name('invoices.payment.ewallet');

    // ==================== PAYMENT MANAGEMENT ====================
    Route::get('payments/return', [PaymentController::class, 'return'])->name('payments.return');
    Route::get('invoices/{invoice}/payment', [PaymentController::class, 'paymentChannels'])->name('payments.channels');
    Route::post('invoices/{invoice}/payment', [PaymentController::class, 'createPayment'])->name('payments.create');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    // ==================== TICKET MANAGEMENT ====================
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/response', [TicketController::class, 'addResponse'])->name('tickets.add-response');
    Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
    Route::get('tickets/{ticket}/pdf', [TicketController::class, 'downloadPdf'])->name('tickets.download-pdf');

    // ==================== REPORTS ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('financial/pdf', [ReportController::class, 'exportFinancialPdf'])->name('financial.pdf');
        Route::get('financial/excel', [ReportController::class, 'exportFinancialExcel'])->name('financial.excel');
        Route::get('customer', [ReportController::class, 'customer'])->name('customer');
        Route::get('customer/excel', [ReportController::class, 'exportCustomerExcel'])->name('customer.excel');
        Route::get('support', [ReportController::class, 'support'])->name('support');
        Route::get('support/excel', [ReportController::class, 'exportSupportExcel'])->name('support.excel');

        // ✅ NEW REPORTS
        Route::get('capacity', [ReportController::class, 'capacity'])->name('capacity');
        Route::get('fiber-usage', [ReportController::class, 'fiberUsage'])->name('fiber-usage');
        Route::get('port-utilization', [ReportController::class, 'portUtilization'])->name('port-utilization');
    });

    // ==================== NETWORK INFRASTRUCTURE ====================

    // OLT Management
    Route::resource('olts', OLTController::class);
    Route::post('olts/{olt}/test', [OLTController::class, 'testConnection'])->name('olts.test');
    Route::post('olts/{olt}/ont-list', [OLTController::class, 'getONTList'])->name('olts.ont-list');
    Route::get('olts/{olt}/ont-status', [OLTController::class, 'getONTStatus'])->name('olts.ont-status');
    Route::post('olts/{olt}/ssh-command', [OLTController::class, 'executeSSHCommand'])->name('olts.ssh-command');

    // ✅ ODF Management (NEW)
    Route::resource('odfs', ODFController::class);
    Route::get('odfs/{odf}/ports', [ODFController::class, 'ports'])->name('odfs.ports');
    Route::get('odfs/{odf}/port-map', [ODFController::class, 'portMap'])->name('odfs.port-map');
    Route::get('odfs/{odf}/available-ports', [ODFController::class, 'getAvailablePorts'])->name('odfs.available-ports');

    // ✅ ODC Management (NEW)
    Route::resource('odcs', ODCController::class);
    Route::get('odcs/{odc}/ports', [ODCController::class, 'ports'])->name('odcs.ports');
    Route::get('odcs/{odc}/port-map', [ODCController::class, 'portMap'])->name('odcs.port-map');
    Route::get('odcs/{odc}/available-ports', [ODCController::class, 'getAvailablePorts'])->name('odcs.available-ports');

    // Splitter Management (UPDATED)
    Route::resource('splitters', SplitterController::class);
    Route::get('splitters/{splitter}/ports', [SplitterController::class, 'ports'])->name('splitters.ports');

    // ODP Management
    Route::resource('odps', ODPController::class);
    Route::get('odps/{odp}/available-ports', [ODPController::class, 'getAvailablePorts'])->name('odps.available-ports');
    Route::get('odps/{odp}/port/{port}', [ODPController::class, 'getPortDetails'])->name('odps.port-details');
    Route::get('odps/{odp}/port-map', [ODPController::class, 'portMap'])->name('odps.port-map');

    // ONT Management
    Route::resource('onts', ONTController::class);
    Route::post('onts/{ont}/provision', [ONTController::class, 'provision'])->name('onts.provision');
    Route::post('onts/{ont}/check-signal', [ONTController::class, 'checkSignal'])->name('onts.check-signal');
    Route::post('onts/{ont}/change-wifi', [ONTController::class, 'changeWiFi'])->name('onts.change-wifi');
    Route::post('onts/{ont}/reboot', [ONTController::class, 'reboot'])->name('onts.reboot');
    Route::get('onts/{ont}/status', [ONTController::class, 'getStatus'])->name('onts.status');
    Route::post('onts/{ont}/ping', [ONTController::class, 'ping'])->name('onts.ping');

    // ==================== FIBER INFRASTRUCTURE ====================

    // Joint Box Management
    Route::resource('joint-boxes', JointBoxController::class);
    Route::get('joint-boxes/{jointBox}/splices', [JointBoxController::class, 'splices'])->name('joint-boxes.splices');


    // ============================================
    // FIBER CABLE SEGMENT MANAGEMENT
    // ============================================
    Route::resource('cable-segments', FiberCableSegmentController::class, [
        'parameters' => ['cable-segments' => 'cableSegment']
    ]);
    // Additional Cable Segment Routes
    Route::get('cable-segments/{cableSegment}/cores', [FiberCableSegmentController::class, 'cores'])->name('cable-segments.cores');
    Route::get('cable-segments/{cableSegment}/map', [FiberCableSegmentController::class, 'map'])->name('cable-segments.map');

    // Fiber Core Management
    Route::resource('cores', FiberCoreController::class);
    Route::post('cores/{core}/assign', [FiberCoreController::class, 'assign'])->name('cores.assign');
    Route::post('cores/{core}/release', [FiberCoreController::class, 'release'])->name('cores.release');
    Route::post('cores/bulk-create', [FiberCoreController::class, 'bulkCreate'])->name('cores.bulk-create');
    Route::delete('cores/bulk-delete', [FiberCoreController::class, 'bulkDelete'])->name('cores.bulk-delete');
    Route::post('cores/{core}/test', [FiberCoreController::class, 'test'])->name('cores.test');
    Route::get('cores/{core}/history', [FiberCoreController::class, 'history'])->name('cores.history');

    // ✅ Fiber Splice Management (NEW)
    Route::resource('fiber-splices', FiberSpliceController::class);
    Route::get('joint-boxes/{jointBox}/add-splice', [FiberSpliceController::class, 'createForJointBox'])->name('fiber-splices.create-for-joint-box');
    Route::get('fiber-splices/available-cores', [FiberSpliceController::class, 'getAvailableCores'])->name('fiber-splices.available-cores');

    // ✅ ODP Port Management (NEW)
    Route::resource('odp-ports', ODPPortController::class)->except(['index']);
    Route::get('odps/{odp}/ports-detail', [ODPPortController::class, 'index'])->name('odp-ports.index');
    Route::post('odp-ports/{port}/assign', [ODPPortController::class, 'assign'])->name('odp-ports.assign');
    Route::post('odp-ports/{port}/release', [ODPPortController::class, 'release'])->name('odp-ports.release');

    // ✅ Fiber Test Result Management (NEW)
    Route::resource('fiber-test-results', FiberTestResultController::class);
    Route::get('fiber-test-results/{result}/download', [FiberTestResultController::class, 'download'])->name('fiber-test-results.download');

    // ==================== OTHER NETWORK EQUIPMENT ====================

    // Switch Management
    Route::resource('switches', SwitchController::class);
    Route::get('switches/{switch}/ssh-terminal', [SwitchController::class, 'sshTerminal'])->name('switches.ssh-terminal');
    Route::post('switches/{switch}/ssh-command', [SwitchController::class, 'executeSSH'])->name('switches.ssh-command');
    Route::post('switches/{switch}/ping', [SwitchController::class, 'ping'])->name('switches.ping');

    // Access Point Management
    Route::resource('access-points', AccessPointController::class);
    Route::post('access-points/{access_point}/ping', [AccessPointController::class, 'ping'])->name('access-points.ping');
    Route::post('access-points/ping-test', [AccessPointController::class, 'pingTest'])->name('access-points.ping-test');
    Route::post('access-points/get-mac', [AccessPointController::class, 'getMac'])->name('access-points.get-mac');
    Route::post('access-points/bulk-ping', [AccessPointController::class, 'bulkPing'])->name('access-points.bulk-ping');

    // ==================== NETWORK VISUALIZATION ====================

    // Network Map
    Route::get('map', [MapController::class, 'index'])->name('map.index');
    Route::get('map/fiber', [MapController::class, 'fiberMap'])->name('map.fiber');
    Route::post('cable-routes/store', [MapController::class, 'storeCableRoute'])->name('cable-routes.store');

    // ✅ Network Topology & Path Tracing (NEW)
    Route::get('network-topology', [DashboardController::class, 'networkTopology'])->name('network.topology');
    Route::get('network-map', [DashboardController::class, 'networkMap'])->name('network.map');
    Route::get('fiber-path/{ont}', [DashboardController::class, 'fiberPath'])->name('network.fiber-path');

    // ==================== PROFILE ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ==================== WEBHOOKS (NO AUTH) ====================
Route::post('/webhooks/xendit', [WebhookController::class, 'handleXenditPayment']);
Route::post('payments/tripay/callback', [PaymentController::class, 'callback'])->name('payments.callback');

// ==================== CUSTOMER PORTAL ====================
Route::prefix('customer')->name('customer.')->group(function () {
    // Authentication
    Route::get('login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [CustomerAuthController::class, 'login']);
    Route::post('logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Invoices
    Route::get('invoices', [CustomerInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [CustomerInvoiceController::class, 'show'])->name('invoices.show');

    // Tickets
    Route::resource('tickets', CustomerTicketController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('tickets/{ticket}/response', [CustomerTicketController::class, 'addResponse'])->name('tickets.add-response');
});

// ==================== API ROUTES (AJAX) ====================
    // Get equipment by type
    Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {

    // Get equipment by type
    Route::get('equipment/{type}', function($type) {
        $model = match($type) {
            'olt' => \App\Models\OLT::class,
            'odf' => \App\Models\ODF::class,
            'odc' => \App\Models\ODC::class,
            'joint_box' => \App\Models\JointBox::class,
            'splitter' => \App\Models\Splitter::class,
            'odp' => \App\Models\ODP::class,
            'ont' => \App\Models\ONT::class,
            default => null,
        };

        if (!$model) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        try {
            // Get all records without hardcoded columns
            $items = $model::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function($item) use ($type) {
                    // Get identifier based on equipment type
                    $identifier = '';

                    if ($type === 'olt') {
                        // OLT: use IP address or model
                        $identifier = $item->ip_address ?? $item->model ?? '';
                    } elseif ($type === 'ont') {
                        // ONT: use serial_number
                        $identifier = $item->serial_number ?? '';
                    } else {
                        // Others: use code
                        $identifier = $item->code ?? '';
                    }

                    // Build display name
                    $displayName = $item->name;
                    if ($identifier) {
                        $displayName .= ' (' . $identifier . ')';
                    }
                    if (!$item->is_active) {
                        $displayName .= ' [OFFLINE]';
                    }

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'code' => $identifier,
                        'is_active' => $item->is_active ?? true,
                        'display_name' => $displayName
                    ];
                });

            return response()->json($items);

        } catch (\Exception $e) {
            \Log::error("Equipment API error for type {$type}: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'type' => $type
            ], 500);
        }
    })->name('equipment.get');

    // Get available ports
    Route::get('{type}/{id}/ports', function($type, $id) {
        $model = match($type) {
            'odf' => \App\Models\ODF::class,
            'odc' => \App\Models\ODC::class,
            'odp' => \App\Models\ODP::class,
            'splitter' => \App\Models\Splitter::class,
            default => null,
        };

        if (!$model) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $equipment = $model::find($id);

        if (!$equipment) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'total_ports' => $equipment->total_ports ?? $equipment->output_ports,
            'used_ports' => $equipment->used_ports ?? $equipment->used_outputs,
            'available_ports' => method_exists($equipment, 'getAvailablePorts')
                ? $equipment->getAvailablePorts()
                : ($equipment->getAvailableOutputs() ?? 0),
            'next_available' => method_exists($equipment, 'getNextAvailablePort')
                ? $equipment->getNextAvailablePort()
                : null,
        ]);
    })->name('ports.get');

    // Network path tracer
    Route::get('trace-path/{ont}', function($ontId) {
        $ont = \App\Models\ONT::with([
            'odp.splitters.odc.odf.olt'
        ])->find($ontId);

        if (!$ont) {
            return response()->json(['error' => 'ONT not found'], 404);
        }

        $path = [];

        // Build path from ONT to OLT
        $path[] = ['type' => 'ONT', 'id' => $ont->id, 'name' => $ont->name];

        if ($ont->odp) {
            $path[] = ['type' => 'ODP', 'id' => $ont->odp->id, 'name' => $ont->odp->name];

            // Get splitter connected to this ODP
            $splitter = $ont->odp->splitters()->first();
            if ($splitter) {
                $path[] = ['type' => 'Splitter', 'id' => $splitter->id, 'name' => $splitter->name];

                if ($splitter->odc) {
                    $path[] = ['type' => 'ODC', 'id' => $splitter->odc->id, 'name' => $splitter->odc->name];

                    if ($splitter->odc->odf) {
                        $path[] = ['type' => 'ODF', 'id' => $splitter->odc->odf->id, 'name' => $splitter->odc->odf->name];

                        if ($splitter->odc->odf->olt) {
                            $path[] = ['type' => 'OLT', 'id' => $splitter->odc->odf->olt->id, 'name' => $splitter->odc->odf->olt->name];
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'path' => array_reverse($path)
        ]);
    })->name('trace-path');
});

