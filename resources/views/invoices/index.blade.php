@extends('layouts.admin')

@section('title', 'Invoice Management')
@section('page-title', 'Invoice Management')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Total Unpaid</p>
                <h4 class="fw-bold mb-0">Rp {{ number_format($stats['total_unpaid'], 0, ',', '.') }}</h4>
            </div>
            <div class="stats-icon orange">
                <i class="bi bi-exclamation-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Total Overdue</p>
                <h4 class="fw-bold mb-0 text-danger">Rp {{ number_format($stats['total_overdue'], 0, ',', '.') }}</h4>
            </div>
            <div class="stats-icon purple">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Paid This Month</p>
                <h4 class="fw-bold mb-0 text-success">Rp {{ number_format($stats['total_paid_this_month'], 0, ',', '.') }}</h4>
            </div>
            <div class="stats-icon green">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="custom-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Daftar Invoice</h5>
        @can('create_invoice')
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Generate Invoice
        </a>
        @endcan
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('invoices.index') }}">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari invoice..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="customer_id" class="form-select">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>
                            {{ $cust->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="Dari" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="Sampai" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>

    @if($invoices->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
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
                        <td>
                            <a href="{{ route('customers.show', $invoice->customer) }}">
                                {{ $invoice->customer->name }}
                            </a>
                        </td>
                        <td>
                            <small>{{ $invoice->issue_date->format('d M Y') }}</small>
                        </td>
                        <td>
                            <small>{{ $invoice->due_date->format('d M Y') }}</small>
                            @if($invoice->isOverdue())
                                <br><small class="text-danger">({{ $invoice->getDaysOverdue() }} hari terlambat)</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $invoice->getFormattedTotal() }}</strong>
                        </td>
                        <td>
                            <span class="{{ $invoice->getStatusBadgeClass() }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('mark_invoice_paid')
                                @if($invoice->isUnpaid() || $invoice->isOverdue())
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $invoice->id }}" title="Mark Paid">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                @endif
                                @endcan

                                @can('delete_invoice')
                                @if(!$invoice->isPaid())
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus invoice ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>

                    <!-- Mark as Paid Modal -->
                    <div class="modal fade" id="markPaidModal{{ $invoice->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Mark as Paid</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Method</label>
                                            <select name="payment_method" class="form-select" required>
                                                <option value="">Select Method</option>
                                                <option value="cash">Cash</option>
                                                <option value="transfer">Bank Transfer</option>
                                                <option value="credit_card">Tripay</option>
                                                <option value="xendit">Xendit</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Reference</label>
                                            <input type="text" name="payment_reference" class="form-control" placeholder="Transaction ID / No. Ref">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Paid Date</label>
                                            <input type="date" name="paid_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Mark as Paid</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Menampilkan {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} dari {{ $invoices->total() }} invoices
            </div>
            <div>
                {{ $invoices->links() }}
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-receipt" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">Belum Ada Invoice</h5>
            <p class="text-muted">Klik tombol "Generate Invoice" untuk membuat invoice baru.</p>
        </div>
    @endif
</div>
@endsection
