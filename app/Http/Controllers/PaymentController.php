<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Services\TripayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    /**
     * Display payment channels
     */
    public function paymentChannels(Invoice $invoice)
    {
        // Check if invoice already paid
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('info', 'Invoice already paid');
        }

        // Get available payment channels from Tripay
        $channels = $this->tripayService->getPaymentChannels();

        if (!$channels['success']) {
            return back()->with('error', 'Failed to load payment channels');
        }

        // Group channels by group
        $groupedChannels = collect($channels['data'])
            ->groupBy('group')
            ->map(function($group) {
                return $group->sortBy('name');
            });

        return view('payments.channels', compact('invoice', 'groupedChannels'));
    }

    /**
     * Create payment transaction
     */
    public function createPayment(Request $request, Invoice $invoice)
{
    $request->validate([
        'payment_method' => 'required|string',
    ]);

    // Check if invoice already paid
    if ($invoice->status === 'paid') {
        return redirect()->route('invoices.show', $invoice)
            ->with('info', 'Invoice already paid');
    }

    // Check if there's pending payment
    $existingPayment = Payment::where('invoice_id', $invoice->id)
        ->where('status', 'pending')
        ->first();

    if ($existingPayment) {
        return redirect()->route('payments.show', $existingPayment)
            ->with('info', 'You already have a pending payment for this invoice');
    }

    // Prepare order items
    $orderItems = [
        [
            'name' => 'Invoice #' . $invoice->invoice_number,
            'price' => (int) $invoice->total,
            'quantity' => 1,
        ]
    ];

    // Get customer email
    $customerEmail = $invoice->customer->email;

    // Create transaction via Tripay
    $result = $this->tripayService->createTransaction([
        'payment_method' => $request->payment_method,
        'amount' => (int) $invoice->total,
        'customer_name' => $invoice->customer->name,
        'customer_email' => $customerEmail,
        'customer_phone' => $invoice->customer->phone,
        'order_items' => $orderItems,
        'return_url' => route('payments.return'),
    ]);

    if (!$result['success']) {
        return back()->with('error', $result['message']);
    }

    $tripayData = $result['data'];

    // Save payment to database
    $payment = Payment::create([
        'invoice_id' => $invoice->id,
        'amount' => $invoice->total,
        'payment_method' => $request->payment_method,
        'payment_date' => now(), // SET payment_date saat create
        'tripay_reference' => $tripayData['reference'],
        'tripay_merchant_ref' => $tripayData['merchant_ref'],
        'checkout_url' => $tripayData['checkout_url'] ?? null,
        'qr_url' => $tripayData['qr_url'] ?? null,
        'expired_at' => date('Y-m-d H:i:s', $tripayData['expired_time']),
        'status' => 'pending',
    ]);

    return redirect()->route('payments.show', $payment)
        ->with('success', 'Payment created successfully! Please complete the payment.');
}

    /**
     * Show payment detail
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.customer']);

        // Get fresh transaction status from Tripay
        if ($payment->status === 'pending' && $payment->tripay_reference) {
            $result = $this->tripayService->getTransactionDetail($payment->tripay_reference);

            if ($result['success']) {
                $tripayData = $result['data'];

                // Update payment status if changed
                if ($tripayData['status'] === 'PAID' && $payment->status !== 'paid') {
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    // Update invoice status
                    $payment->invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                } elseif ($tripayData['status'] === 'EXPIRED') {
                    $payment->update(['status' => 'expired']);
                } elseif ($tripayData['status'] === 'FAILED') {
                    $payment->update(['status' => 'failed']);
                }

                $payment->refresh();
            }
        }

        return view('payments.show', compact('payment'));
    }

    /**
     * Tripay callback handler
     */
    public function callback(Request $request)
    {
        // Handle callback from Tripay
        $result = $this->tripayService->handleCallback($request);

        if (!$result['success']) {
            return response()->json(['success' => false], 400);
        }

        // Find payment by merchant_ref or reference
        $payment = Payment::where('tripay_reference', $result['reference'])
            ->orWhere('tripay_merchant_ref', $result['merchant_ref'])
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Update payment status based on callback
        if ($result['status'] === 'PAID') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => $result['paid_at'] ?? now(),
            ]);

            // Update invoice
            $payment->invoice->update([
                'status' => 'paid',
                'paid_at' => $result['paid_at'] ?? now(),
            ]);

            // TODO: Send notification to customer

        } elseif ($result['status'] === 'EXPIRED') {
            $payment->update(['status' => 'expired']);
        } elseif ($result['status'] === 'FAILED') {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['success' => true]);
    }

    /**
 * Return URL after payment
 */
public function return(Request $request)
{
    // Get parameters dari URL
    $reference = $request->get('tripay_reference') ?? $request->get('reference');
    $merchantRef = $request->get('tripay_merchant_ref') ?? $request->get('merchant_ref');

    \Log::info('Payment Return', [
        'all_params' => $request->all(),
        'reference' => $reference,
        'merchant_ref' => $merchantRef
    ]);

    // Find payment by reference
    $payment = null;

    if ($reference) {
        $payment = Payment::where('tripay_reference', $reference)->first();
    }

    if (!$payment && $merchantRef) {
        $payment = Payment::where('tripay_merchant_ref', $merchantRef)->first();
    }

    if (!$payment) {
        return redirect()->route('dashboard')
            ->with('error', 'Payment not found');
    }

    // Check latest payment status from Tripay
    $tripayService = new \App\Services\TripayService();
    $result = $tripayService->getTransactionDetail($payment->tripay_reference);

    if ($result['success']) {
        $tripayData = $result['data'];

        // Update payment status
        if ($tripayData['status'] === 'PAID' && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => $tripayData['paid_at'] ?? now(),
            ]);

            // Update invoice
            $payment->invoice->update([
                'status' => 'paid',
                'paid_at' => $tripayData['paid_at'] ?? now(),
            ]);
        } elseif ($tripayData['status'] === 'EXPIRED') {
            $payment->update(['status' => 'expired']);
        } elseif ($tripayData['status'] === 'FAILED') {
            $payment->update(['status' => 'failed']);
        }

        $payment->refresh();
    }

    return redirect()->route('payments.show', $payment);
}

    /**
     * Cancel payment
     */
    public function cancel(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Cannot cancel this payment');
        }

        $payment->update(['status' => 'cancelled']);

        return redirect()->route('invoices.show', $payment->invoice)
            ->with('success', 'Payment cancelled');
    }
}
