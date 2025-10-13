<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\XenditService;

class InvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_invoices', only: ['index', 'show']),
            new Middleware('can:create_invoice', only: ['create', 'store']),
            new Middleware('can:edit_invoice', only: ['edit', 'update']),
            new Middleware('can:delete_invoice', only: ['destroy']),
            new Middleware('can:mark_invoice_paid', only: ['markAsPaid']),
        ];
    }

    public function index(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status');
    $customerId = $request->input('customer_id');

    $query = Invoice::with(['customer', 'package']);

    // Search filter
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhereHas('customer', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    // Status filter
    if ($status) {
        if ($status === 'overdue') {
            $query->where('status', 'unpaid')
                  ->where('due_date', '<', now());
        } else {
            $query->where('status', $status);
        }
    }

    // Customer filter
    if ($customerId) {
        $query->where('customer_id', $customerId);
    }

    $invoices = $query->latest()->paginate(15);
    $customers = Customer::where('status', 'active')->get();

    // Summary stats
    $stats = [
        'total_unpaid' => Invoice::unpaid()->sum('total') ?? 0,
        'total_overdue' => Invoice::overdue()->sum('total') ?? 0,
        'total_paid_this_month' => Invoice::paid()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total') ?? 0,
        'count_unpaid' => Invoice::unpaid()->count(),
        'count_overdue' => Invoice::overdue()->count(),
        'count_paid_this_month' => Invoice::paid()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->count(),
    ];

    return view('invoices.index', compact('invoices', 'customers', 'stats'));
}

    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('invoices.create', compact('customers'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'package_id' => 'nullable|exists:packages,id',
        'issue_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:issue_date',
        'period' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'items' => 'nullable|array',
        'tax_percentage' => 'nullable|numeric|min:0|max:100',
        'discount' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
    ]);

    /// Calculate amounts (sama seperti sebelumnya)
    $items = $request->items ?? [];
    $subtotal = 0;
    foreach ($items as $item) {
        $qty = floatval($item['qty'] ?? 1);
        $price = floatval($item['price'] ?? 0);
        $subtotal += $qty * $price;
    }
    $taxPercentage = floatval($request->tax_percentage ?? 0);
    $taxAmount = ($subtotal * $taxPercentage) / 100;
    $discount = floatval($request->discount ?? 0);
    $total = $subtotal + $taxAmount - $discount;

    // Generate invoice number dengan TIMESTAMP untuk uniqueness
    $prefix = 'INV-' . date('Ym');
    $timestamp = now()->format('His'); // HourMinuteSecond
    $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    $invoiceNumber = $prefix . '-' . $timestamp . $random; // INV-202510-143525001

    // Prepare data
    $validated['invoice_number'] = $invoiceNumber;
    $validated['items'] = $items;
    $validated['subtotal'] = $subtotal;
    $validated['tax_percentage'] = $taxPercentage;
    $validated['tax'] = $taxAmount;
    $validated['discount'] = $discount;
    $validated['late_fee'] = 0;
    $validated['total'] = $total;
    $validated['status'] = 'unpaid';

    $invoice = Invoice::create($validated);

    // Log activity
    ActivityLog::log(
        'created',
        'Invoice',
        $invoice->id,
        "Created invoice {$invoice->invoice_number} for customer: {$invoice->customer->name}",
        [
            'total' => $invoice->total,
            'customer_id' => $invoice->customer_id,
            'due_date' => $invoice->due_date->format('Y-m-d')
        ]
    );

    return redirect()
        ->route('invoices.index')
        ->with('success', 'Invoice created successfully!');
}


    /**
 * Generate unique invoice number
 */
private function generateUniqueInvoiceNumber()
{
    $prefix = 'INV-' . date('Ym');

    // Get last invoice number for this month
    $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
        ->lockForUpdate() // Lock row to prevent race condition
        ->orderBy('invoice_number', 'desc')
        ->first();

    if ($lastInvoice) {
        // Extract number from last invoice
        $lastNumber = intval(substr($lastInvoice->invoice_number, -5));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return $prefix . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
}

    public function show(Invoice $invoice)
{
    // Load relationships
    $invoice->load(['customer', 'package', 'payments']);

    // Recalculate totals jika total = 0
    if ($invoice->total == 0 && $invoice->subtotal > 0) {
        // Database menggunakan kolom: tax, discount, total (bukan tax_amount, discount_amount, total_amount)

        // Calculate tax
        $invoice->tax = $invoice->subtotal * (($invoice->tax_percentage ?? 0) / 100);

        // Calculate total
        $invoice->total = $invoice->subtotal + $invoice->tax - ($invoice->discount ?? 0);

        // Save to database
        $invoice->save();

        // Refresh invoice
        $invoice->refresh();
    }

    return view('invoices.show', compact('invoice'));
}

    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'paid_at' => 'required|date',
        ]);

        $oldStatus = $invoice->status;

        $invoice->update([
            'status' => 'paid',
            'paid_at' => $request->paid_at,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
        ]);

        // Log activity
        ActivityLog::log(
            'paid',
            'Invoice',
            $invoice->id,
            "Invoice {$invoice->invoice_number} marked as paid by " . auth()->user()->name,
            [
                'amount' => $invoice->total,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'old_status' => $oldStatus,
                'customer_id' => $invoice->customer_id
            ]
        );

        return back()->with('success', 'Invoice ditandai sebagai paid!');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Tidak bisa menghapus invoice yang sudah paid!');
        }

        $invoiceNumber = $invoice->invoice_number;
        $customerName = $invoice->customer->name;
        $total = $invoice->total;

        $invoice->delete();

        // Log activity
        ActivityLog::log(
            'deleted',
            'Invoice',
            $invoice->id,
            "Deleted invoice {$invoiceNumber} (Customer: {$customerName})",
            [
                'invoice_number' => $invoiceNumber,
                'customer_name' => $customerName,
                'total' => $total
            ]
        );

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus!');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load('customer');

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $filename = $invoice->invoice_number . '.pdf';

        // Log activity
        ActivityLog::log(
            'downloaded',
            'Invoice',
            $invoice->id,
            "Downloaded PDF for invoice {$invoice->invoice_number}",
            ['customer_id' => $invoice->customer_id]
        );

        return $pdf->download($filename);
    }

    public function viewPdf(Invoice $invoice)
    {
        $invoice->load('customer');

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream();
    }

    public function createVirtualAccount(Request $request, Invoice $invoice)
    {
        try {
            $xendit = new XenditService();
            $va = $xendit->createVirtualAccount($invoice, $request->bank);

            // Log activity
            ActivityLog::log(
                'payment_created',
                'Invoice',
                $invoice->id,
                "Created Virtual Account ({$request->bank}) for invoice {$invoice->invoice_number}",
                [
                    'bank' => $request->bank,
                    'va_number' => $va['account_number'],
                    'amount' => $invoice->total
                ]
            );

            return back()->with('success', 'Virtual Account created! VA Number: ' . $va['account_number']);
        } catch (\Exception $e) {
            // Log error
            ActivityLog::log(
                'payment_failed',
                'Invoice',
                $invoice->id,
                "Failed to create VA for invoice {$invoice->invoice_number}: {$e->getMessage()}",
                ['error' => $e->getMessage()]
            );

            return back()->with('error', 'Failed to create VA: ' . $e->getMessage());
        }
    }

    public function createQRIS(Invoice $invoice)
    {
        try {
            $xendit = new XenditService();
            $qris = $xendit->createQRIS($invoice);

            // Log activity
            ActivityLog::log(
                'payment_created',
                'Invoice',
                $invoice->id,
                "Created QRIS payment for invoice {$invoice->invoice_number}",
                ['amount' => $invoice->total]
            );

            return view('invoices.qris', compact('invoice', 'qris'));
        } catch (\Exception $e) {
            // Log error
            ActivityLog::log(
                'payment_failed',
                'Invoice',
                $invoice->id,
                "Failed to create QRIS for invoice {$invoice->invoice_number}: {$e->getMessage()}",
                ['error' => $e->getMessage()]
            );

            return back()->with('error', 'Failed to create QRIS: ' . $e->getMessage());
        }
    }

    public function createEWallet(Request $request, Invoice $invoice)
    {
        try {
            $xendit = new XenditService();
            $ewallet = $xendit->createEWallet($invoice, $request->ewallet);

            // Log activity
            ActivityLog::log(
                'payment_created',
                'Invoice',
                $invoice->id,
                "Created E-Wallet ({$request->ewallet}) payment for invoice {$invoice->invoice_number}",
                [
                    'ewallet_type' => $request->ewallet,
                    'amount' => $invoice->total
                ]
            );

            return redirect($ewallet['actions']['mobile_web_checkout_url']);
        } catch (\Exception $e) {
            // Log error
            ActivityLog::log(
                'payment_failed',
                'Invoice',
                $invoice->id,
                "Failed to create E-Wallet payment for invoice {$invoice->invoice_number}: {$e->getMessage()}",
                ['error' => $e->getMessage()]
            );

            return back()->with('error', 'Failed to create E-Wallet payment: ' . $e->getMessage());
        }
    }
}
