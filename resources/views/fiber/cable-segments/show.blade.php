@extends('layouts.admin')

@section('title', 'Cable Segment: ' . $cableSegment->name)
@section('page-title', 'Cable Segment Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $cableSegment->name }}</h4>
        <p class="text-muted mb-0">Code: {{ $cableSegment->code }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cable-segments.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('cable-segments.edit', $cableSegment) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('cores.index', ['cable_segment_id' => $cableSegment->id]) }}" class="btn btn-info btn-sm">
            <i class="bi bi-list"></i> View Cores
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-primary mb-0">{{ $cableSegment->core_count }}</h2>
                <p class="text-muted mb-0 small">Total Cores</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-success mb-0">{{ $cableSegment->getAvailableCores() }}</h2>
                <p class="text-muted mb-0 small">Available</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-danger mb-0">{{ $cableSegment->getUsedCores() }}</h2>
                <p class="text-muted mb-0 small">Used</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-info mb-0">{{ $cableSegment->distance ? number_format($cableSegment->distance) . ' m' : '-' }}</h2>
                <p class="text-muted mb-0 small">Distance</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Cable Info -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Cable Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Code</strong></td>
                        <td><code>{{ $cableSegment->code }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td>{{ $cableSegment->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cable Type</strong></td>
                        <td>
                            @if($cableSegment->cable_type === 'backbone')
                                <span class="badge bg-danger">Backbone</span>
                            @elseif($cableSegment->cable_type === 'distribution')
                                <span class="badge bg-warning">Distribution</span>
                            @else
                                <span class="badge bg-info">Drop</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Core Count</strong></td>
                        <td><span class="badge bg-secondary">{{ $cableSegment->core_count }} cores</span></td>
                    </tr>
                    <tr>
                        <td><strong>Brand/Model</strong></td>
                        <td>{{ $cableSegment->cable_brand ?? '-' }} {{ $cableSegment->cable_model ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Installation</strong></td>
                        <td>
                            {{ ucfirst(str_replace('_', ' ', $cableSegment->installation_type ?? 'N/A')) }}
                            @if($cableSegment->installation_date)
                                <br><small class="text-muted">{{ $cableSegment->installation_date->format('d M Y') }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Core Usage</strong></td>
                        <td>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar
                                    @if($cableSegment->getCoreUsagePercentage() >= 80) bg-danger
                                    @elseif($cableSegment->getCoreUsagePercentage() >= 60) bg-warning
                                    @else bg-success
                                    @endif"
                                    style="width: {{ $cableSegment->getCoreUsagePercentage() }}%">
                                    {{ number_format($cableSegment->getCoreUsagePercentage(), 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            @if($cableSegment->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($cableSegment->status === 'damaged')
                                <span class="badge bg-danger">Damaged</span>
                            @else
                                <span class="badge bg-warning">Maintenance</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Route Info -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Route Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <strong>Start Point</strong>
                    <div class="mt-2 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-circle-fill text-success me-2"></i>
                            <div>
                                <strong>{{ $cableSegment->startPoint->name ?? 'Unknown' }}</strong>
                                <br><small class="text-muted">{{ class_basename($cableSegment->start_point_type) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center my-3">
                    <i class="bi bi-arrow-down-circle fs-4 text-primary"></i>
                    <br>
                    <small class="text-muted">
                        {{ $cableSegment->distance ? number_format($cableSegment->distance, 2) . ' meters' : 'Distance unknown' }}
                    </small>
                </div>

                <div>
                    <strong>End Point</strong>
                    <div class="mt-2 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-circle-fill text-danger me-2"></i>
                            <div>
                                <strong>{{ $cableSegment->endPoint->name ?? 'Unknown' }}</strong>
                                <br><small class="text-muted">{{ class_basename($cableSegment->end_point_type) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fiber Cores List -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Fiber Cores ({{ $cableSegment->cores->count() }})</h6>
        <a href="{{ route('cores.create', ['cable_segment_id' => $cableSegment->id]) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Add Core
        </a>
    </div>
    <div class="card-body">
        @if($cableSegment->cores->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Core #</th>
                            <th>Color</th>
                            <th>Tube</th>
                            <th>Status</th>
                            <th>Connected To</th>
                            <th>Loss (dB)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cableSegment->cores->sortBy('core_number') as $core)
                        <tr>
                            <td><strong>{{ $core->core_number }}</strong></td>
                            <td>
                                <span class="badge" style="background-color: {{ $core->core_color }}; color: white;">
                                    {{ $core->core_color }}
                                </span>
                            </td>
                            <td>{{ $core->tube_number ?? '-' }}</td>
                            <td>
                                @if($core->status === 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($core->status === 'used')
                                    <span class="badge bg-primary">Used</span>
                                @elseif($core->status === 'reserved')
                                    <span class="badge bg-warning">Reserved</span>
                                @else
                                    <span class="badge bg-danger">Damaged</span>
                                @endif
                            </td>
                            <td>
                                @if($core->connected_to_type)
                                    <small>{{ class_basename($core->connected_to_type) }} #{{ $core->connected_to_id }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $core->loss_db ? number_format($core->loss_db, 2) : '-' }}</td>
                            <td>
                                <a href="{{ route('cores.show', $core) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-3">No fiber cores found. Cores will be auto-created when cable segment is saved.</p>
        @endif
    </div>
</div>

@if($cableSegment->notes)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold">Notes</h6>
        <p class="mb-0">{{ $cableSegment->notes }}</p>
    </div>
</div>
@endif
@endsection
