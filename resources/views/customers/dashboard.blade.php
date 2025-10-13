<!--// resources/views/customers/dashboard.blade.php-->

@extends('customers.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-1">Welcome, {{ $customer->name }}!</h2>
        <p class="text-muted">Customer ID: {{ $customer->customer_code }}</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Package</p>
                        <h5 class="fw-bold mb-0">{{ $customer->package->name }}</h5>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-box-seam text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Status</p>
                        <h5 class="fw-bold mb-0">
                            <span class="badge {{ $customer->getStatusBadgeClass() }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </h5>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Unpaid Invoices</p>
                        <h5 class="fw-bold mb-0 text-danger">{{ $stats['unpaid_invoices'] }}</h5>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-receipt text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Open Tickets</p>
                        <h5 class="fw-bold mb-0">{{ $stats['open_tickets'] }}</h5>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-ticket-perforated text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connection Info & Recent Invoices -->
<div class="row g-4">
    <!-- Connection Info -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Connection Information</h5>

                <div class="mb-3">
                    <small class="text-muted">Package</small>
                    <p class="mb-0 fw-semibold">{{ $customer->package->name }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Speed</small>
                    <p class="mb-0 fw-semibold">{{ $customer->package->getSpeedLabel() }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Monthly Price</small>
                    <p class="mb-0 fw-semibold">{{ $customer->package->getFormattedPrice() }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Connection Type</small>
                    <p class="mb-0">{{ $customer->getConnectionTypeLabel() }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Installation Date</small>
                    <p class="mb-0">{{ $customer->installation_date->format('d M Y') }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Next Billing Date</small>
                    <p class="mb-0 fw-semibold text-primary">{{ $customer->next_billing_date->format('d M Y') }}</p>
                </div>

                @if($stats['total_unpaid_amount'] > 0)
                <div class="alert alert-warning mt-3">
                    <strong>Outstanding Balance:</strong> Rp {{ number_format($stats['total_unpaid_amount'], 0, ',', '.') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Invoices</h5>
                    <a href="{{ route('customer.invoices.index') }}" class="text-decoration-none">View All →</a>
                </div>

                @forelse($customer->invoices as $invoice)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <a href="{{ route('customer.invoices.show', $invoice) }}" class="text-decoration-none">
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </a>
                        <br>
                        <small class="text-muted">{{ $invoice->issue_date->format('d M Y') }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">{{ $invoice->getFormattedTotal() }}</div>
                        <span class="{{ $invoice->getStatusBadgeClass() }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-receipt" style="font-size: 3rem;"></i>
                    <p class="mt-2">No invoices yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Tickets -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Support Tickets</h5>
                    <div>
                        <a href="{{ route('customer.tickets.create') }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-plus-circle"></i> Create Ticket
                        </a>
                        <a href="{{ route('customer.tickets.index') }}" class="text-decoration-none">View All →</a>
                    </div>
                </div>

                @if($customer->tickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->tickets->take(5) as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('customer.tickets.show', $ticket) }}">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td>{{ $ticket->title }}</td>
                                <td>
                                    <span class="{{ $ticket->getPriorityBadgeClass() }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $ticket->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->created_at->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-ticket-perforated" style="font-size: 3rem;"></i>
                    <p class="mt-2">No support tickets</p>
                    <a href="{{ route('customer.tickets.create') }}" class="btn btn-primary mt-2">
                        Create Your First Ticket
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
