@extends('layouts.admin')

@section('title', 'Ticket Detail')
@section('page-title', 'Ticket Detail')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Ticket Header -->
        <div class="custom-table mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 class="fw-bold mb-1">{{ $ticket->ticket_number }}</h3>
                    <h5 class="mb-2">{{ $ticket->title }}</h5>
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
                <div class="text-end">
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Customer Info:</h6>
                    <div>
                        <a href="{{ route('customers.show', $ticket->customer) }}">
                            <strong>{{ $ticket->customer->name }}</strong>
                        </a><br>
                        <small class="text-muted">{{ $ticket->customer->customer_code }}</small><br>
                        Phone: {{ $ticket->customer->phone }}<br>
                        @if($ticket->customer->email)
                        Email: {{ $ticket->customer->email }}
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">Created by:</small><br>
                    <strong>{{ $ticket->createdBy?->name ?? 'System' }}</strong><br>
                    <small>{{ $ticket->created_at->format('d M Y H:i') }}</small>
                </div>
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Description</h6>
            <p style="white-space: pre-wrap;">{{ $ticket->description }}</p>
        </div>

        <!-- Responses -->
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Responses ({{ $ticket->responses->count() }})</h6>

            @forelse($ticket->responses as $response)
            <div class="card mb-3 {{ $response->is_internal ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>{{ $response->user->name }}</strong>
                            @if($response->is_internal)
                                <span class="badge bg-warning text-dark ms-2">Internal Note</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $response->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $response->message }}</p>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-chat" style="font-size: 3rem;"></i>
                <p class="mt-2">No responses yet</p>
            </div>
            @endforelse

            <!-- Add Response Form -->
            @can('update_ticket')
            @if(!$ticket->isClosed())
            <div class="card border-primary mt-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Add Response</h6>
                    <form action="{{ route('tickets.add-response', $ticket) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="4" placeholder="Type your response here..." required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="isInternal">
                            <label class="form-check-label" for="isInternal">
                                Internal note (not visible to customer)
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Response
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                @can('update_ticket')
                @if(!$ticket->isClosed())
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="bi bi-person-plus"></i> Assign Teknisi
                </button>

                <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#statusModal">
                    <i class="bi bi-gear"></i> Change Status
                </button>
                @endif
                @endcan

                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-printer"></i> Print (Coming Soon)
                </button>

                <a href="{{ route('tickets.download-pdf', $ticket) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Download PDF
                </a>
            </div>
        </div>

        <!-- Assignment Info -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Assignment</h6>
            @if($ticket->assignedTo)
                <div class="text-center">
                    <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                    <p class="mb-0 mt-2"><strong>{{ $ticket->assignedTo->name }}</strong></p>
                    <small class="text-muted">{{ $ticket->assignedTo->getRoleNames()->first() }}</small>
                </div>
            @else
                <div class="text-center text-muted">
                    <i class="bi bi-person-x" style="font-size: 3rem;"></i>
                    <p class="mt-2">Not assigned yet</p>
                </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="custom-table">
            <h6 class="fw-bold mb-3">Timeline</h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <small class="text-muted">Created</small><br>
                    {{ $ticket->created_at->format('d M Y H:i') }}
                </li>
                @if($ticket->resolved_at)
                <li class="mb-2">
                    <small class="text-muted">Resolved</small><br>
                    <span class="text-success">{{ $ticket->resolved_at->format('d M Y H:i') }}</span>
                </li>
                @endif
                @if($ticket->closed_at)
                <li class="mb-2">
                    <small class="text-muted">Closed</small><br>
                    <span class="text-secondary">{{ $ticket->closed_at->format('d M Y H:i') }}</span>
                </li>
                @endif
            </ul>

            @if($ticket->getResponseTime())
            <hr>
            <small class="text-muted">Response Time:</small><br>
            <strong>{{ $ticket->getResponseTime() }} minutes</strong>
            @endif

            @if($ticket->getResolutionTime())
            <hr>
            <small class="text-muted">Resolution Time:</small><br>
            <strong>{{ round($ticket->getResolutionTime() / 60, 1) }} hours</strong>
            @endif
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Teknisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Teknisi</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">-- Select Teknisi --</option>
                            @foreach($teknisis as $teknisi)
                                <option value="{{ $teknisi->id }}" {{ $ticket->assigned_to == $teknisi->id ? 'selected' : '' }}>
                                    {{ $teknisi->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.update-status', $ticket) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Status: <strong>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</strong></label>
                        <select name="status" class="form-select" required>
                            <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="waiting_customer" {{ $ticket->status == 'waiting_customer' ? 'selected' : '' }}>Waiting Customer</option>
                            <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
