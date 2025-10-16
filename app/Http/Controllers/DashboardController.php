<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Router;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Models\RouterUptimeLog;
use App\Models\OLT;
use App\Models\ODF;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\ONT;
use App\Models\JointBox;
use App\Models\Splitter;
use App\Models\FiberCableSegment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Revenue Stats
        $revenueThisMonth = Invoice::paid()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $revenueLastMonth = Invoice::paid()
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('total');

        $revenueGrowth = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : 0;

        // Customer Stats
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $suspendedCustomers = Customer::where('status', 'suspended')->count();

        $newCustomersThisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Invoice Stats
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $totalUnpaidAmount = Invoice::whereIn('status', ['unpaid', 'overdue'])->sum('total');

        // Router Stats
        $totalRouters = Router::count();
        $activeRouters = Router::where('is_active', true)->count();

        // Revenue Chart (Last 6 months)
        $revenueChart = $this->getRevenueChartData();

        // Customer Growth Chart (Last 6 months)
        $customerGrowthChart = $this->getCustomerGrowthData();

        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Recent Invoices
        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->take(5)
            ->get();

        // Recent Customers
        $recentCustomers = Customer::with('package')
            ->latest()
            ->take(5)
            ->get();

        // Top Packages
        $topPackages = Package::withCount('customers')
            ->orderBy('customers_count', 'desc')
            ->take(5)
            ->get();

        // Router Uptime (last 24 hours)
        $routerUptimeData = $this->getRouterUptimeData();

        return view('dashboard', compact(
            'revenueThisMonth',
            'revenueLastMonth',
            'revenueGrowth',
            'totalCustomers',
            'activeCustomers',
            'suspendedCustomers',
            'newCustomersThisMonth',
            'unpaidInvoices',
            'overdueInvoices',
            'totalUnpaidAmount',
            'totalRouters',
            'activeRouters',
            'revenueChart',
            'customerGrowthChart',
            'recentInvoices',
            'recentCustomers',
            'topPackages',
            'recentActivities',
            'routerUptimeData'
        ));
    }

    private function getRevenueChartData()
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $revenue = Invoice::paid()
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('total');

            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ];
        }

        return $data;
    }

    private function getCustomerGrowthData()
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $count = Customer::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $data[] = [
                'month' => $date->format('M Y'),
                'customers' => $count
            ];
        }

        return $data;
    }

    private function getRouterUptimeData()
    {
        $routers = Router::where('is_active', true)->get();

        return $routers->map(function ($router) {
            // Count total checks in last 24 hours
            $totalChecks = RouterUptimeLog::where('router_id', $router->id)
                ->where('checked_at', '>=', now()->subDay())
                ->count();

            // Count successful checks (online)
            $onlineChecks = RouterUptimeLog::where('router_id', $router->id)
                ->where('checked_at', '>=', now()->subDay())
                ->where('is_online', true)
                ->count();

            // Calculate uptime percentage
            $uptime = $totalChecks > 0 ? ($onlineChecks / $totalChecks) * 100 : 100;

            return [
                'name' => $router->name,
                'uptime' => round($uptime, 2)
            ];
        });
    }

     public function networkTopology()
{
    // Helper function to safely count with is_active check
    $safeCount = function($model) {
        $tableName = (new $model)->getTable();
        if (\Schema::hasColumn($tableName, 'is_active')) {
            return $model::where('is_active', true)->count();
        }
        return $model::count();
    };

    // Get all equipment counts
    $stats = [
        'olts' => $safeCount(OLT::class),
        'odfs' => $safeCount(ODF::class),
        'odcs' => $safeCount(ODC::class),
        'joint_boxes' => $safeCount(JointBox::class),
        'splitters' => $safeCount(Splitter::class),
        'odps' => $safeCount(ODP::class),
        'onts' => $safeCount(ONT::class),
        'cables' => FiberCableSegment::where('status', 'active')->count(),
    ];

    // Get all equipment for dropdown filters (safe way)
    $olts = $this->safeGetActive(OLT::class);
    $odps = $this->safeGetActive(ODP::class);

    return view('network.topology', compact('stats', 'olts', 'odps'));
}

/**
 * Get topology data (also needs safe queries)
 */
public function getTopologyData(Request $request)
{
    $nodes = [];
    $edges = [];

    // Get all active equipment (safely)
    $olts = $this->safeGetActive(OLT::class);
    $odfs = $this->safeGetActive(ODF::class);
    $odcs = $this->safeGetActive(ODC::class);
    $jointBoxes = $this->safeGetActive(JointBox::class);
    $splitters = $this->safeGetActive(Splitter::class);
    $odps = $this->safeGetActive(ODP::class);
    $onts = $this->safeGetActive(ONT::class, 50); // Limit ONTs

    // Add OLTs as nodes
    foreach ($olts as $olt) {
        $nodes[] = [
            'id' => "olt-{$olt->id}",
            'label' => $olt->name,
            'type' => 'olt',
            'group' => 'olt',
            'title' => $this->getNodeTooltip($olt, 'OLT'),
            'color' => '#3b82f6',
            'shape' => 'box',
            'font' => ['color' => '#ffffff'],
        ];
    }

    // Add ODFs as nodes
    foreach ($odfs as $odf) {
        $nodes[] = [
            'id' => "odf-{$odf->id}",
            'label' => $odf->name,
            'type' => 'odf',
            'group' => 'odf',
            'title' => $this->getNodeTooltip($odf, 'ODF'),
            'color' => '#10b981',
            'shape' => 'box',
        ];
    }

    // Add ODCs as nodes
    foreach ($odcs as $odc) {
        $nodes[] = [
            'id' => "odc-{$odc->id}",
            'label' => $odc->name,
            'type' => 'odc',
            'group' => 'odc',
            'title' => $this->getNodeTooltip($odc, 'ODC'),
            'color' => '#f59e0b',
            'shape' => 'box',
        ];
    }

    // Add Joint Boxes as nodes
    foreach ($jointBoxes as $jb) {
        $nodes[] = [
            'id' => "jointbox-{$jb->id}",
            'label' => $jb->name,
            'type' => 'joint_box',
            'group' => 'joint_box',
            'title' => $this->getNodeTooltip($jb, 'Joint Box'),
            'color' => '#f97316',
            'shape' => 'diamond',
        ];
    }

    // Add Splitters as nodes
    foreach ($splitters as $splitter) {
        $nodes[] = [
            'id' => "splitter-{$splitter->id}",
            'label' => $splitter->name,
            'type' => 'splitter',
            'group' => 'splitter',
            'title' => $this->getNodeTooltip($splitter, 'Splitter'),
            'color' => '#8b5cf6',
            'shape' => 'triangle',
        ];
    }

    // Add ODPs as nodes
    foreach ($odps as $odp) {
        $nodes[] = [
            'id' => "odp-{$odp->id}",
            'label' => $odp->name,
            'type' => 'odp',
            'group' => 'odp',
            'title' => $this->getNodeTooltip($odp, 'ODP'),
            'color' => '#ec4899',
            'shape' => 'dot',
        ];
    }

    // Add ONTs as nodes
    foreach ($onts as $ont) {
        $nodes[] = [
            'id' => "ont-{$ont->id}",
            'label' => $ont->name,
            'type' => 'ont',
            'group' => 'ont',
            'title' => $this->getNodeTooltip($ont, 'ONT'),
            'color' => '#6b7280',
            'shape' => 'dot',
            'size' => 10,
        ];
    }

    // Get all cable segments and create edges
    $cables = FiberCableSegment::where('status', 'active')
        ->with(['startPoint', 'endPoint'])
        ->get();

    foreach ($cables as $cable) {
        if ($cable->startPoint && $cable->endPoint) {
            $fromId = strtolower($cable->start_point_type) . "-{$cable->start_point_id}";
            $toId = strtolower($cable->end_point_type) . "-{$cable->end_point_id}";

            $edges[] = [
                'from' => $fromId,
                'to' => $toId,
                'label' => $cable->name,
                'title' => $this->getCableTooltip($cable),
                'color' => $this->getCableColor($cable->cable_type),
                'width' => $this->getCableWidth($cable->cable_type),
                'arrows' => 'to',
            ];
        }
    }

    return response()->json([
        'nodes' => $nodes,
        'edges' => $edges,
    ]);
}

/**
 * Safely get active records checking if is_active column exists
 */
private function safeGetActive($modelClass, $limit = null)
{
    $model = new $modelClass;
    $tableName = $model->getTable();

    $query = $modelClass::query();

    // Check if is_active column exists
    if (\Schema::hasColumn($tableName, 'is_active')) {
        $query->where('is_active', true);
    }

    $query->orderBy('name');

    if ($limit) {
        $query->take($limit);
    }

    return $query->get();
}

/**
 * Generate node tooltip
 */
private function getNodeTooltip($equipment, $type)
{
    $tooltip = "<strong>{$type}: {$equipment->name}</strong><br>";

    if (isset($equipment->code)) {
        $tooltip .= "Code: {$equipment->code}<br>";
    }

    if (isset($equipment->ip_address)) {
        $tooltip .= "IP: {$equipment->ip_address}<br>";
    }

    if (isset($equipment->location)) {
        $tooltip .= "Location: {$equipment->location}<br>";
    }

    if (isset($equipment->address)) {
        $tooltip .= "Address: {$equipment->address}<br>";
    }

    if (isset($equipment->status)) {
        $tooltip .= "Status: " . ucfirst($equipment->status);
    } elseif (isset($equipment->is_active)) {
        $tooltip .= "Status: " . ($equipment->is_active ? 'Active' : 'Inactive');
    }

    return $tooltip;
}

/**
 * Generate cable tooltip
 */
private function getCableTooltip($cable)
{
    $tooltip = "<strong>{$cable->name}</strong><br>";
    $tooltip .= "Type: " . ucfirst($cable->cable_type) . "<br>";
    $tooltip .= "Cores: {$cable->core_count}<br>";

    if ($cable->distance) {
        $tooltip .= "Distance: " . number_format($cable->distance / 1000, 2) . " km<br>";
    }

    $tooltip .= "Status: " . ucfirst($cable->status);

    return $tooltip;
}

/**
 * Get cable color by type
 */
private function getCableColor($type)
{
    return match($type) {
        'backbone' => '#ef4444',
        'distribution' => '#f59e0b',
        'drop' => '#22c55e',
        default => '#6b7280',
    };
}

/**
 * Get cable width by type
 */
private function getCableWidth($type)
{
    return match($type) {
        'backbone' => 4,
        'distribution' => 2,
        'drop' => 1,
        default => 1,
    };
}
}
