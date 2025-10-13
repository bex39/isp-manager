@extends('layouts.admin')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Payment Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-4">
                @if($payment->status === 'paid')
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">Payment Successful!</h4>
                    <p class="text-muted">Your payment has been received and verified</p>
                @elseif($payment->status === 'pending')
                    <i class="bi bi-clock text-warning" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">Waiting for Payment</h4>
                    <p class="text-muted">Please complete your payment before it expires</p>
                @elseif($payment->status === 'expired')
                    <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">Payment Expired</h4>
                    <p class="text-muted">This payment has expired. Please create a new payment.</p>
                @else
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">Payment {{ ucfirst($payment->status) }}</h4>
                @endif
            </div>
        </div>

        <!-- Payment Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Payment Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Invoice Number</strong></td>
                        <td>
                            <a href="{{ route('invoices.show', $payment->invoice) }}">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Amount</strong></td>
                        <td><strong class="text-primary">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Payment Method</strong></td>
                        <td>{{ strtoupper($payment->payment_method) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Reference</strong></td>
                        <td><code>{{ $payment->tripay_reference }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @if($payment->expired_at && $payment->status === 'pending')
                    <tr>
                        <td><strong>Expires At</strong></td>
                        <td>
                            {{ $payment->expired_at->format('d M Y H:i') }}
                            <br><small class="text-danger">{{ $payment->expired_at->diffForHumans() }}</small>
                        </td>
                    </tr>
                    @endif
                    @if($payment->paid_at)
                    <tr>
                        <td><strong>Paid At</strong></td>
                        <td>{{ $payment->paid_at->format('d M Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Payment Instructions -->
        @if($payment->status === 'pending')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Payment Instructions</h6>

                @if($payment->checkout_url)
                    <a href="{{ $payment->checkout_url }}" target="_blank" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="bi bi-credit-card"></i> Continue to Payment
                    </a>
                @endif

                @if($payment->qr_url)
                    <div class="text-center">
                        <p class="fw-bold">Scan QR Code to Pay:</p>
                        <img src="{{ $payment->qr_url }}" alt="QR Code" style="max-width: 300px;">
                    </div>
                @endif

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                    Payment will be verified automatically. Page will update once payment is received.
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Actions</h6>

                @if($payment->status === 'pending')
                    <button type="button" class="btn btn-info w-100 mb-2" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Status
                    </button>

                    <form action="{{ route('payments.cancel', $payment) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Cancel this payment?')">
                            <i class="bi bi-x-circle"></i> Cancel Payment
                        </button>
                    </form>
                @endif

                @if($payment->status === 'paid')
                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-primary w-100">
                        <i class="bi bi-receipt"></i> View Invoice
                    </a>
                @endif

                @if(in_array($payment->status, ['expired', 'failed', 'cancelled']))
                    <a href="{{ route('payments.channels', $payment->invoice) }}" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-repeat"></i> Create New Payment
                    </a>
                @endif
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Customer</h6>
                <p class="mb-1"><strong>{{ $payment->invoice->customer->name }}</strong></p>
                <p class="mb-1 small text-muted">{{ $payment->invoice->customer->email }}</p>
                <p class="mb-0 small text-muted">{{ $payment->invoice->customer->phone }}</p>
            </div>
        </div>
    </div>
</div>

@if($payment->status === 'pending')
@push('scripts')
<script>
// Auto refresh every 30 seconds to check payment status
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endpush
@endif
@endsection
