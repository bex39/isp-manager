@extends('layouts.admin')

@section('title', 'Ticket Management')
@section('page-title', 'Ticket Management')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Open Tickets</p>
                <h4 class="fw-bold mb-0">{{ $stats['open'] }}</h4>
            </div>
            <div class="stats-icon blue">
                <i class="bi bi-inbox"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">In Progress</p>
                <h4 class="fw-bold mb-0">{{ $stats['in_progress'] }}</h4>
            </div>
            <div class="stats-icon orange">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Resolved</p>
                <h4 class="fw-bold mb-0">{{ $stats['resolved'] }}</h4>
            </div>
            <div class="stats-icon green">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Urgent</p>
                <h4 class="fw-bold mb-0 text-danger">{{ $stats['urgent'] }}</h4>
            </div>
            <div class="stats-icon purple">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

<div class="custom-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Daftar Ticket</h5>
        @can('create_ticket')
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Ticket
        </a>
        @endcan
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('tickets.index') }}">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search tickets..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_customer" {{ request('status') == 'waiting_customer' ? 'selected' : '' }}>Waiting Customer</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All Category</option>
                    <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                    <option value="billing" {{ request('category') == 'billing' ? 'selected' : '' }}>Billing</option>
                    <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                    <option value="complaint" {{ request('category') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="assigned_to" class="form-select">
                    <option value="">All Teknisi</option>
                    @foreach($teknisis as $teknisi)
                        <option value="{{ $teknisi->id }}" {{ request('assigned_to') == $teknisi->id ? 'selected' : '' }}>
                            {{ $teknisi->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>

    @if($tickets->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Customer</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td>
                            <strong>{{ $ticket->ticket_number }}</strong>
                        </td>
                        <td>
                            <a href="{{ route('customers.show', $ticket->customer) }}">
                                {{ $ticket->customer->name }}
                            </a>
                        </td>
                        <td>{{ $ticket->title }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($ticket->category) }}</span>
                        </td>
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
                        <td>
                            @if($ticket->assignedTo)
                                {{ $ticket->assignedTo->name }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $ticket->created_at->format('d M Y H:i') }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('delete_ticket')
                                @if($ticket->isClosed())
                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus ticket ini?')">
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
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets
            </div>
            <div>
                {{ $tickets->links() }}
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-ticket-perforated" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">No Tickets</h5>
            <p class="text-muted">No tickets found matching your filters.</p>
        </div>
    @endif
</div>
@endsection
