<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        $customer = auth('customer')->user();
        $invoices = $customer->invoices()->latest()->paginate(10);

        return view('customers.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }

        // Check ownership
        if ($invoice->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        return view('customers.invoices.show', compact('invoice'));
    }
}
