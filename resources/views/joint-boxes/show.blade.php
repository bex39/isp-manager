@extends('layouts.admin')

@section('title', 'Joint Box Details')
@section('page-title', 'Joint Box Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <a href="{{ route('joint-boxes.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('joint-boxes.splices', $jointBox) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-bezier2"></i> View Splices
        </a>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('joint-boxes.edit', $jointBox) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('joint-boxes.destroy', $jointBox) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete this joint box?')">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Basic Information -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Name</label>
                        <h5>{{ $jointBox->name }}</h5>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Code</label>
                        <h5>{{ $jointBox->code ?? '-' }}</h5>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Type</label>
                        <div>
                            <span class="badge bg-{{ $jointBox->type === 'inline' ? 'primary' : ($jointBox->type === 'branch' ? 'warning' : 'info') }} fs-6">
                                {{ ucfirst($jointBox->type ?? '-') }}
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">
                            @if($jointBox->type === 'inline')
                                Straight connection point
                            @elseif($jointBox->type === 'branch')
                                Multiple output connections
                            @elseif($jointBox->type === 'terminal')
                                End point connection
                            @endif
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Status</label>
                        <div>
                            <span class="badge bg-{{ $jointBox->is_active ? 'success' : 'danger' }} fs-6">
                                {{ $jointBox->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($jointBox->status && $jointBox->status !== 'active')
                                <span class="badge bg-warning fs-6">{{ ucfirst($jointBox->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="text-muted small">Location</label>
                        <p class="mb-1">{{ $jointBox->location ?? '-' }}</p>
                        @if($jointBox->latitude && $jointBox->longitude)
                            <a href="https://www.google.com/maps?q={{ $jointBox->latitude }},{{ $jointBox->longitude }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-map"></i> View on Google Maps
                            </a>
                            <small class="text-muted d-block mt-1">
                                Coordinates: {{ $jointBox->latitude }}, {{ $jointBox->longitude }}
                            </small>
                        @endif
                    </div>
                </div>

                @if($jointBox->notes)
                <div class="row">
                    <div class="col-md-12">
                        <label class="text-muted small">Notes</label>
                        <p class="text-muted">{{ $jointBox->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Connected Cables -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bezier"></i> Connected Cables</h5>
            </div>
            <div class="card-body">
                @php
                    // Get cables connected to this joint box
                    $incomingCables = \App\Models\FiberCableSegment::where('end_point_type', 'joint_box')
                        ->where('end_point_id', $jointBox->id)
                        ->with('startPoint')
                        ->get();

                    $outgoingCables = \App\Models\FiberCableSegment::where('start_point_type', 'joint_box')
                        ->where('start_point_id', $jointBox->id)
                        ->with('endPoint')
                        ->get();
                @endphp

                <!-- Incoming Cables -->
                <h6 class="text-muted mb-2">
                    <i class="bi bi-arrow-down-circle text-success"></i> Incoming Cables ({{ $incomingCables->count() }})
                </h6>
                @if($incomingCables->count() > 0)
                    <div class="list-group mb-3">
                        @foreach($incomingCables as $cable)
                        <a href="{{ route('cable-segments.show', $cable) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $cable->name }}</strong>
                                    <small class="text-muted d-block">{{ $cable->code }}</small>
                                    <small class="text-muted">
                                        From: {{ $cable->startPoint?->name ?? 'Unknown' }}
                                        ({{ strtoupper($cable->start_point_type) }})
                                    </small>
                                </div>
                                <span class="badge bg-{{ $cable->cable_type === 'backbone' ? 'danger' : ($cable->cable_type === 'distribution' ? 'warning' : 'info') }}">
                                    {{ ucfirst($cable->cable_type) }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-3">No incoming cables</p>
                @endif

                <!-- Outgoing Cables -->
                <h6 class="text-muted mb-2">
                    <i class="bi bi-arrow-up-circle text-primary"></i> Outgoing Cables ({{ $outgoingCables->count() }})
                </h6>
                @if($outgoingCables->count() > 0)
                    <div class="list-group">
                        @foreach($outgoingCables as $cable)
                        <a href="{{ route('cable-segments.show', $cable) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $cable->name }}</strong>
                                    <small class="text-muted d-block">{{ $cable->code }}</small>
                                    <small class="text-muted">
                                        To: {{ $cable->endPoint?->name ?? 'Unknown' }}
                                        ({{ strtoupper($cable->end_point_type) }})
                                    </small>
                                </div>
                                <span class="badge bg-{{ $cable->cable_type === 'backbone' ? 'danger' : ($cable->cable_type === 'distribution' ? 'warning' : 'info') }}">
                                    {{ ucfirst($cable->cable_type) }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No outgoing cables</p>
                @endif
            </div>
        </div>

        <!-- Splices Overview -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Fiber Splices</h5>
                <a href="{{ route('joint-boxes.splices', $jointBox) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Manage Splices
                </a>
            </div>
            <div class="card-body">
                @if($jointBox->splices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Input</th>
                                    <th>Output</th>
                                    <th>Loss</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jointBox->splices->take(5) as $splice)
                                <tr>
                                    <td>
                                        <small>
                                            {{ $splice->inputSegment->name ?? 'N/A' }}
                                            <span class="text-muted">#{{ $splice->input_core_number }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $splice->outputSegment->name ?? 'N/A' }}
                                            <span class="text-muted">#{{ $splice->output_core_number }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        @if($splice->splice_loss)
                                            <span class="badge bg-{{ $splice->splice_loss <= 0.1 ? 'success' : ($splice->splice_loss <= 0.3 ? 'warning' : 'danger') }}">
                                                {{ $splice->splice_loss }} dB
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><small>{{ ucfirst($splice->splice_type ?? '-') }}</small></td>
                                    <td><small>{{ $splice->splice_date ? $splice->splice_date->format('d M Y') : '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($jointBox->splices->count() > 5)
                            <div class="text-center">
                                <a href="{{ route('joint-boxes.splices', $jointBox) }}" class="btn btn-sm btn-outline-primary">
                                    View All {{ $jointBox->splices->count() }} Splices
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-bezier2" style="font-size: 2rem;"></i>
                        <p>No splices yet</p>
                        <a href="{{ route('fiber-splices.create', ['joint_box_id' => $jointBox->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Add First Splice
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics & Info -->
    <div class="col-md-4">
        <!-- Capacity Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Capacity</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="mb-0">{{ $jointBox->splices->count() }} / {{ $jointBox->capacity ?? 0 }}</h2>
                    <small class="text-muted">Splices Used</small>
                </div>

                <div class="progress mb-2" style="height: 20px;">
                    @php
                        $usedPercentage = $jointBox->capacity > 0
                            ? ($jointBox->splices->count() / $jointBox->capacity) * 100
                            : 0;
                    @endphp
                    <div class="progress-bar bg-{{ $usedPercentage > 80 ? 'danger' : ($usedPercentage > 50 ? 'warning' : 'success') }}"
                         style="width: {{ min($usedPercentage, 100) }}%">
                        {{ number_format($usedPercentage, 1) }}%
                    </div>
                </div>

                <div class="d-flex justify-content-between text-muted small">
                    <span>Available: {{ $jointBox->capacity - $jointBox->splices->count() }}</span>
                    <span>{{ number_format($usedPercentage, 1) }}% used</span>
                </div>

                @if($usedPercentage > 80)
                    <div class="alert alert-warning mt-3 mb-0 small">
                        <i class="bi bi-exclamation-triangle"></i> Capacity almost full!
                    </div>
                @endif
            </div>
        </div>

        <!-- Installation Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-tools"></i> Installation</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Type</small>
                    <p class="mb-0">
                        @if($jointBox->installation_type)
                            <span class="badge bg-secondary">{{ ucfirst($jointBox->installation_type) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div>
                    <small class="text-muted">Date</small>
                    <p class="mb-0">
                        {{ $jointBox->installation_date ? $jointBox->installation_date->format('d M Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> History</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Created</small>
                    <p class="mb-0 small">{{ $jointBox->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 small">{{ $jointBox->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
