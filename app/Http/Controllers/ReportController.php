<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\Payment;
use App\Models\Package;
use App\Models\Router;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;
use App\Exports\CustomerReportExport;
use App\Exports\SupportReportExport;

class ReportController extends Controller
{
    public function index()
    {
        $recentReports = ReportLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('reports.index', compact('recentReports'));
    }

    public function financial(Request $request)
{
    $startDate = $request->input('start_date', now()->startOfMonth());
    $endDate = $request->input('end_date', now()->endOfMonth());

    $totalRevenue = Invoice::whereBetween('issue_date', [$startDate, $endDate])
        ->where('status', 'paid')
        ->sum('total');

    $pendingRevenue = Invoice::whereBetween('issue_date', [$startDate, $endDate])
        ->whereIn('status', ['unpaid', 'overdue'])
        ->sum('total');

    $paymentMethods = Payment::whereBetween('payment_date', [$startDate, $endDate])
        ->select('payment_method', DB::raw('SUM(amount) as total'))
        ->groupBy('payment_method')
        ->get();

    $monthlyRevenue = Invoice::where('status', 'paid')
        ->where('issue_date', '>=', now()->subMonths(12))
        ->selectRaw("TO_CHAR(issue_date, 'YYYY-MM') as month, SUM(total) as revenue")
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    // FIX: Spesifikkan table name untuk kolom status
    $packageRevenue = Invoice::where('invoices.status', 'paid')
        ->whereBetween('invoices.issue_date', [$startDate, $endDate])
        ->join('customers', 'invoices.customer_id', '=', 'customers.id')
        ->join('packages', 'customers.package_id', '=', 'packages.id')
        ->select('packages.name', DB::raw('SUM(invoices.total) as revenue'))
        ->groupBy('packages.id', 'packages.name')
        ->get();

    return view('reports.financial', compact(
        'totalRevenue',
        'pendingRevenue',
        'paymentMethods',
        'monthlyRevenue',
        'packageRevenue',
        'startDate',
        'endDate'
    ));
}

    public function customer(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $statusSummary = Customer::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();

        $customerGrowth = Customer::where('created_at', '>=', now()->subMonths(12))
        ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $customersByPackage = Customer::where('status', 'active')
            ->join('packages', 'customers.package_id', '=', 'packages.id')
            ->select('packages.name', DB::raw('COUNT(*) as count'))
            ->groupBy('packages.id', 'packages.name')
            ->get();

        $customersByRouter = Customer::whereNotNull('router_id')
            ->join('routers', 'customers.router_id', '=', 'routers.id')
            ->select('routers.name', DB::raw('COUNT(*) as count'))
            ->groupBy('routers.id', 'routers.name')
            ->get();

        $churnedCustomers = Customer::where('status', 'terminated')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $totalCustomers = Customer::count();
        $churnRate = $totalCustomers > 0 ? ($churnedCustomers / $totalCustomers) * 100 : 0;

        return view('reports.customer', compact(
            'statusSummary',
            'newCustomers',
            'customerGrowth',
            'customersByPackage',
            'customersByRouter',
            'churnRate',
            'startDate',
            'endDate'
        ));
    }

    public function support(Request $request)
{
    $startDate = $request->input('start_date', now()->startOfMonth());
    $endDate = $request->input('end_date', now()->endOfMonth());

    $ticketStatus = Ticket::whereBetween('created_at', [$startDate, $endDate])
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->get()
        ->pluck('count', 'status');

    $ticketPriority = Ticket::whereBetween('created_at', [$startDate, $endDate])
        ->select('priority', DB::raw('COUNT(*) as count'))
        ->groupBy('priority')
        ->get()
        ->pluck('count', 'priority');

    $ticketCategory = Ticket::whereBetween('created_at', [$startDate, $endDate])
        ->select('category', DB::raw('COUNT(*) as count'))
        ->groupBy('category')
        ->get()
        ->pluck('count', 'category');

    $avgResolutionTime = Ticket::whereBetween('created_at', [$startDate, $endDate])
        ->whereNotNull('resolved_at')
        ->selectRaw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at)) / 3600) as avg_hours')
        ->first()
        ->avg_hours ?? 0;

    // FIX: Spesifikkan tickets.created_at
    $ticketsByUser = Ticket::whereBetween('tickets.created_at', [$startDate, $endDate])
        ->whereNotNull('tickets.assigned_to')
        ->join('users', 'tickets.assigned_to', '=', 'users.id')
        ->select('users.name', DB::raw('COUNT(*) as count'))
        ->groupBy('users.id', 'users.name')
        ->get();

    return view('reports.support', compact(
        'ticketStatus',
        'ticketPriority',
        'ticketCategory',
        'avgResolutionTime',
        'ticketsByUser',
        'startDate',
        'endDate'
    ));
}

    public function exportFinancialPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $data = $this->getFinancialData($startDate, $endDate);
        $pdf = Pdf::loadView('reports.pdf.financial', $data);

        ReportLog::create([
            'user_id' => auth()->id(),
            'report_type' => 'financial',
            'format' => 'pdf',
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);

        return $pdf->download('financial-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFinancialData($startDate, $endDate)
    {
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalRevenue' => Invoice::whereBetween('issue_date', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('total'),
            'pendingRevenue' => Invoice::whereBetween('issue_date', [$startDate, $endDate])
                ->whereIn('status', ['unpaid', 'overdue'])
                ->sum('total'),
            'invoices' => Invoice::whereBetween('issue_date', [$startDate, $endDate])
                ->with('customer')
                ->get(),
        ];
    }

    public function exportFinancialExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        ReportLog::create([
            'user_id' => auth()->id(),
            'report_type' => 'financial',
            'format' => 'excel',
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);

        return Excel::download(
            new FinancialReportExport($startDate, $endDate),
            'financial-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCustomerExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        ReportLog::create([
            'user_id' => auth()->id(),
            'report_type' => 'customer',
            'format' => 'excel',
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);

        return Excel::download(
            new CustomerReportExport($startDate, $endDate),
            'customer-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportSupportExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        ReportLog::create([
            'user_id' => auth()->id(),
            'report_type' => 'support',
            'format' => 'excel',
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);

        return Excel::download(
            new SupportReportExport($startDate, $endDate),
            'support-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
