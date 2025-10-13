<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Router;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Models\RouterUptimeLog;
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
}
