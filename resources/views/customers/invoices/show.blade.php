@extends('customers.layouts.app')

@section('title', 'Invoice Detail')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('customer.invoices.index') }}" class="btn btn-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left"></i> Back to Invoices
        </a>
        <h2 class="fw-bold">{{ $invoice->invoice_number }}</h2>
        <span class="{{ $invoice->getStatusBadgeClass() }}">{{ ucfirst($invoice->status) }}</span>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">Invoice Details</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">Issue Date:</td>
                        <td><strong>{{ $invoice->issue_date->format('d M Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Due Date:</td>
                        <td><strong>{{ $invoice->due_date->format('d M Y') }}</strong></td>
                    </tr>
                    @if($invoice->paid_at)
                    <tr>
                        <td class="text-muted">Paid Date:</td>
                        <td><strong class="text-success">{{ $invoice->paid_at->format('d M Y') }}</strong></td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="fw-bold mb-3">Amount Due</h6>
                <h2 class="text-primary">{{ $invoice->getFormattedTotal() }}</h2>
            </div>
        </div>

        <hr>

        <h6 class="fw-bold mb-3">Items</h6>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="text-center">{{ $item['qty'] }}</td>
                        <td class="text-end">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                @if($invoice->notes)
                <p class="text-muted small">{{ $invoice->notes }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end"><strong>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</strong></td>
                    </tr>
                    @if($invoice->tax > 0)
                    <tr>
                        <td>Tax ({{ number_format($invoice->tax_percentage, 2) }}%):</td>
                        <td class="text-end">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($invoice->discount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end text-danger">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><h4 class="mb-0">{{ $invoice->getFormattedTotal() }}</h4></td>
                    </tr>
                </table>
            </div>
        </div>

        @if($invoice->isUnpaid() || $invoice->isOverdue())
        <hr>
        <div class="alert alert-info">
            <h6 class="fw-bold">Payment Instructions</h6>
            <p class="mb-2">Please transfer to:</p>
            <ul>
                <li>Bank BCA - 1234567890</li>
                <li>a.n. ISP MANAGER</li>
                <li>Amount: <strong>{{ $invoice->getFormattedTotal() }}</strong></li>
            </ul>
            <p class="mb-0"><small>After payment, please contact us with payment confirmation.</small></p>
        </div>
        @endif

        @if($invoice->isPaid())
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> <strong>Payment Received</strong><br>
            <small>Paid on {{ $invoice->paid_at->format('d M Y H:i') }} via {{ ucfirst($invoice->payment_method) }}</small>
        </div>
        @endif
    </div>
</div>
@endsection
