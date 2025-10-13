@extends('customers.layouts.app')

@section('title', 'My Invoices')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">My Invoices</h2>
        <p class="text-muted">View and manage your billing history</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if($invoices->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td>
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </td>
                        <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                        <td>
                            <strong>{{ $invoice->getFormattedTotal() }}</strong>
                        </td>
                        <td>
                            <span class="{{ $invoice->getStatusBadgeClass() }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('customer.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
            </div>
            <div>
                {{ $invoices->links() }}
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-receipt" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">No Invoices Yet</h5>
            <p class="text-muted">Your billing history will appear here</p>
        </div>
        @endif
    </div>
</div>
@endsection
