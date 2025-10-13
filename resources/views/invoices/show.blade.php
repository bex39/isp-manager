@extends('layouts.admin')

@section('title', 'Invoice: ' . $invoice->invoice_number)
@section('page-title', 'Invoice Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $invoice->invoice_number }}</h4>
        <p class="text-muted mb-0">{{ $invoice->created_at->format('d M Y') }}</p>
    </div>
    <div class="col-md-4 text-end">
        @if($invoice->status === 'pending' || $invoice->status === 'unpaid')
            <a href="{{ route('payments.channels', $invoice) }}" class="btn btn-success">
                <i class="bi bi-credit-card"></i> Pay Invoice
            </a>
        @endif

        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        @can('edit_invoices')
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endcan
    </div>
</div>

<!-- Invoice Status -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h6 class="fw-bold">Status</h6>
                <div>
                    @if($invoice->status === 'paid')
                        <span class="badge bg-success">Paid</span>
                        <small class="text-muted ms-2">Paid on {{ $invoice->paid_at->format('d M Y') }}</small>
                    @elseif($invoice->status === 'pending' || $invoice->status === 'unpaid')
                        <span class="badge bg-warning">Unpaid</span>
                        <small class="text-muted ms-2">Due: {{ $invoice->due_date->format('d M Y') }}</small>
                    @elseif($invoice->status === 'overdue')
                        <span class="badge bg-danger">Overdue</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4 text-end">
                <h6 class="fw-bold">Total Amount</h6>
                <h3 class="text-primary mb-0">Rp {{ number_format($invoice->total, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Customer & Invoice Details -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Customer Information</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold">{{ $invoice->customer->name }}</h5>
                <p class="mb-1">
                    <i class="bi bi-credit-card"></i>
                    <strong>Customer Code:</strong> {{ $invoice->customer->customer_code }}
                </p>
                <p class="mb-1">
                    <i class="bi bi-envelope"></i>
                    {{ $invoice->customer->email }}
                </p>
                <p class="mb-1">
                    <i class="bi bi-telephone"></i>
                    {{ $invoice->customer->phone }}
                </p>
                <p class="mb-1">
                    <i class="bi bi-geo-alt"></i>
                    {{ $invoice->customer->address ?? 'No address' }}
                </p>
                <p class="mb-0">
                    <i class="bi bi-person-badge"></i>
                    Status:
                    @if($invoice->customer->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($invoice->customer->status === 'suspended')
                        <span class="badge bg-warning">Suspended</span>
                    @else
                        <span class="badge bg-danger">{{ ucfirst($invoice->customer->status) }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Invoice Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%"><strong>Invoice Number</strong></td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Issue Date</strong></td>
                        <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Due Date</strong></td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                    </tr>
                    @if($invoice->period)
                    <tr>
                        <td><strong>Billing Period</strong></td>
                        <td>{{ $invoice->period }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Package</strong></td>
                        <td>
                            @if($invoice->package)
                                <span class="badge bg-primary">{{ $invoice->package->name }}</span>
                            @else
                                <span class="text-muted">No package</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Items -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header">
        <h6 class="fw-bold mb-0">Invoice Items</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $invoice->package->name ?? 'Service Fee' }}</strong>
                            @if($invoice->description)
                                <br><small class="text-muted">{{ $invoice->description }}</small>
                            @endif
                        </td>
                        <td class="text-end">1</td>
                        <td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                        <td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->tax > 0)
                    <tr>
                        <td colspan="3" class="text-end">Tax</td>
                        <td class="text-end">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($invoice->discount > 0)
                    <tr>
                        <td colspan="3" class="text-end">Discount</td>
                        <td class="text-end">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="table-active">
                        <td colspan="3" class="text-end"><strong>TOTAL</strong></td>
                        <td class="text-end"><strong class="text-primary">Rp {{ number_format($invoice->total, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Payment History -->
@if($invoice->payments->count() > 0)
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="fw-bold mb-0">Payment History</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                        <td>{{ strtoupper($payment->payment_method) }}</td>
                        <td><code>{{ $payment->tripay_reference }}</code></td>
                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($invoice->notes)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold">Notes</h6>
        <p class="mb-0">{{ $invoice->notes }}</p>
    </div>
</div>
@endif
@endsection
