<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Manual check di awal method
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = auth('customer')->user();
        $customer->load(['package', 'router', 'invoices' => function($q) {
            $q->latest()->take(5);
        }]);

        $stats = [
            'unpaid_invoices' => $customer->invoices()->where('status', 'unpaid')->count(),
            'total_unpaid_amount' => $customer->invoices()->whereIn('status', ['unpaid', 'overdue'])->sum('total'),
            'total_tickets' => $customer->tickets()->count(),
            'open_tickets' => $customer->tickets()->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('customers.dashboard', compact('customer', 'stats'));
    }
}
