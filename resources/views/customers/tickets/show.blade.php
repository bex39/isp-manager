@extends('customers.layouts.app')

@section('title', 'Ticket Detail')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('customer.tickets.index') }}" class="btn btn-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left"></i> Back to Tickets
        </a>
        <h2 class="fw-bold">{{ $ticket->ticket_number }}</h2>
        <div class="d-flex gap-2">
            <span class="{{ $ticket->getStatusBadgeClass() }}">
                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
            </span>
            <span class="{{ $ticket->getPriorityBadgeClass() }}">
                {{ ucfirst($ticket->priority) }}
            </span>
            <span class="badge bg-secondary">{{ ucfirst($ticket->category) }}</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Ticket Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">{{ $ticket->title }}</h5>
                <div class="mb-3">
                    <small class="text-muted">Created on {{ $ticket->created_at->format('d M Y H:i') }}</small>
                </div>
                <hr>
                <p style="white-space: pre-wrap;">{{ $ticket->description }}</p>
            </div>
        </div>

        <!-- Responses -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-4">Conversation ({{ $ticket->responses->count() }})</h6>

                @forelse($ticket->responses as $response)
                <div class="card mb-3 {{ $response->user_id ? 'bg-light' : 'border-primary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>
                                    @if($response->user_id)
                                        {{ $response->user->name }}
                                        <span class="badge bg-success text-white ms-2">Support Team</span>
                                    @else
                                        You
                                    @endif
                                </strong>
                            </div>
                            <small class="text-muted">{{ $response->created_at->format('d M Y H:i') }}</small>
                        </div>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $response->message }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-chat" style="font-size: 3rem;"></i>
                    <p class="mt-2">No responses yet. Our team will reply soon.</p>
                </div>
                @endforelse

                <!-- Add Response Form -->
                @if(!$ticket->isClosed())
                <div class="card border-primary mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Add Response</h6>
                        <form action="{{ route('customer.tickets.add-response', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="4"
                                          placeholder="Type your message here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <div class="alert alert-secondary mt-4">
                    <i class="bi bi-lock"></i> This ticket is closed. You cannot add more responses.
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Ticket Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Ticket Information</h6>

                <div class="mb-3">
                    <small class="text-muted">Status</small>
                    <p class="mb-0">
                        <span class="{{ $ticket->getStatusBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Priority</small>
                    <p class="mb-0">
                        <span class="{{ $ticket->getPriorityBadgeClass() }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Category</small>
                    <p class="mb-0">{{ ucfirst($ticket->category) }}</p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Created</small>
                    <p class="mb-0">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                </div>

                @if($ticket->assignedTo)
                <div class="mb-3">
                    <small class="text-muted">Assigned To</small>
                    <p class="mb-0">{{ $ticket->assignedTo->name }}</p>
                </div>
                @endif

                @if($ticket->resolved_at)
                <div class="mb-3">
                    <small class="text-muted">Resolved</small>
                    <p class="mb-0 text-success">{{ $ticket->resolved_at->format('d M Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Help -->
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Need More Help?</h6>
                <p class="small mb-2">You can also reach us via:</p>
                <p class="small mb-0">
                    <i class="bi bi-telephone"></i> 0361-1234567<br>
                    <i class="bi bi-whatsapp"></i> +62 812-3456-7890<br>
                    <i class="bi bi-envelope"></i> support@ispmanager.com
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
