@extends('customers.layouts.app')

@section('title', 'My Tickets')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold">Support Tickets</h2>
        <p class="text-muted">Track and manage your support requests</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="{{ route('customer.tickets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Ticket
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if($tickets->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
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
                        <td>{{ $ticket->created_at->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('customer.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
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
                Showing {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets
            </div>
            <div>
                {{ $tickets->links() }}
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-ticket-perforated" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">No Support Tickets</h5>
            <p class="text-muted">You haven't created any support tickets yet</p>
            <a href="{{ route('customer.tickets.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle"></i> Create Your First Ticket
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
