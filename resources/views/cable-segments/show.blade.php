@extends('layouts.admin')

@section('title', 'Cable Segment: ' . $cableSegment->name)
@section('page-title', 'Cable Segment Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $cableSegment->name }}</h4>
        <p class="text-muted mb-0">
            Code: <code>{{ $cableSegment->code }}</code> |
            Type: <span class="badge bg-{{ $cableSegment->cable_type === 'backbone' ? 'danger' : ($cableSegment->cable_type === 'distribution' ? 'warning' : 'info') }}">
                {{ ucfirst($cableSegment->cable_type) }}
            </span> |
            Status: <span class="badge bg-{{ $cableSegment->status === 'active' ? 'success' : ($cableSegment->status === 'damaged' ? 'danger' : 'warning') }}">
                {{ ucfirst($cableSegment->status) }}
            </span>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cable-segments.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('cable-segments.cores', $cableSegment) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-diagram-2"></i> Cores
        </a>
        <a href="{{ route('cable-segments.edit', $cableSegment) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Visual Connection Diagram -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3"></i> Connection Diagram</h6>
    </div>
    <div class="card-body">
        <div class="row align-items-center text-center">
            <div class="col-md-4">
                <div class="p-4 bg-light rounded">
                    <h6 class="text-primary mb-2">START POINT</h6>
                    <div class="mb-2">
                        <span class="badge bg-primary">{{ strtoupper($cableSegment->start_point_type) }}</span>
                    </div>
                    <p class="mb-1">
                        @if($cableSegment->startPoint)
                            <strong>{{ $cableSegment->startPoint->name }}</strong>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </p>
                    @if($cableSegment->start_port)
                        <small class="text-muted">Port: {{ $cableSegment->start_port }}</small>
                    @endif
                    @if($cableSegment->start_connector_type)
                        <br><span class="badge bg-dark mt-1">{{ $cableSegment->start_connector_type }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-arrow-left-right text-primary" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-1">
                        <strong>{{ $cableSegment->core_count }} Cores</strong>
                    </p>
                    @if($cableSegment->distance)
                        <p class="mb-1">
                            <span class="badge bg-info">{{ number_format($cableSegment->distance / 1000, 2) }} km</span>
                        </p>
                    @endif
                    @if($cableSegment->installation_type)
                        <small class="text-muted">{{ ucfirst($cableSegment->installation_type) }}</small>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-light rounded">
                    <h6 class="text-success mb-2">END POINT</h6>
                    <div class="mb-2">
                        <span class="badge bg-success">{{ strtoupper($cableSegment->end_point_type) }}</span>
                    </div>
                    <p class="mb-1">
                        @if($cableSegment->endPoint)
                            <strong>{{ $cableSegment->endPoint->name }}</strong>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </p>
                    @if($cableSegment->end_port)
                        <small class="text-muted">Port: {{ $cableSegment->end_port }}</small>
                    @endif
                    @if($cableSegment->end_connector_type)
                        <br><span class="badge bg-dark mt-1">{{ $cableSegment->end_connector_type }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Core Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-primary">
                    {{ $coreStats['total'] }}
                </div>
                <small class="text-muted">Total Cores</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-info">
                    {{ $coreStats['created'] }}
                </div>
                <small class="text-muted">Cores Created</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-success">
                    {{ $coreStats['available'] }}
                </div>
                <small class="text-muted">Available</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-warning">
                    {{ $coreStats['used'] }}
                </div>
                <small class="text-muted">Used</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Cable Information -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Cable Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%" class="text-muted">Cable Name</td>
                        <td><strong>{{ $cableSegment->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cable Code</td>
                        <td><code>{{ $cableSegment->code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cable Type</td>
                        <td>
                            <span class="badge bg-{{ $cableSegment->cable_type === 'backbone' ? 'danger' : ($cableSegment->cable_type === 'distribution' ? 'warning' : 'info') }}">
                                {{ ucfirst($cableSegment->cable_type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Core Count</td>
                        <td><strong>{{ $cableSegment->core_count }}</strong> cores</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cable Brand</td>
                        <td>{{ $cableSegment->cable_brand ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cable Model</td>
                        <td>{{ $cableSegment->cable_model ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Distance</td>
                        <td>
                            @if($cableSegment->distance)
                                <strong>{{ number_format($cableSegment->distance / 1000, 2) }}</strong> km
                                <small class="text-muted">({{ number_format($cableSegment->distance) }} m)</small>
                            @else
                                <span class="text-muted">Not recorded</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Installation Type</td>
                        <td>
                            @if($cableSegment->installation_type)
                                <span class="badge bg-secondary">{{ ucfirst($cableSegment->installation_type) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Installation Date</td>
                        <td>{{ $cableSegment->installation_date ? $cableSegment->installation_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge bg-{{ $cableSegment->status === 'active' ? 'success' : ($cableSegment->status === 'damaged' ? 'danger' : 'warning') }}">
                                {{ ucfirst($cableSegment->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Connection Details -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Connection Details</h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary mb-2">Start Point</h6>
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td width="40%" class="text-muted">Equipment Type</td>
                        <td><span class="badge bg-primary">{{ strtoupper($cableSegment->start_point_type) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Equipment Name</td>
                        <td>
                            @if($cableSegment->startPoint)
                                <strong>{{ $cableSegment->startPoint->name }}</strong>
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Port</td>
                        <td>{{ $cableSegment->start_port ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Connector</td>
                        <td>
                            @if($cableSegment->start_connector_type)
                                <span class="badge bg-dark">{{ $cableSegment->start_connector_type }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>

                <hr>

                <h6 class="text-success mb-2">End Point</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%" class="text-muted">Equipment Type</td>
                        <td><span class="badge bg-success">{{ strtoupper($cableSegment->end_point_type) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Equipment Name</td>
                        <td>
                            @if($cableSegment->endPoint)
                                <strong>{{ $cableSegment->endPoint->name }}</strong>
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Port</td>
                        <td>{{ $cableSegment->end_port ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Connector</td>
                        <td>
                            @if($cableSegment->end_connector_type)
                                <span class="badge bg-dark">{{ $cableSegment->end_connector_type }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Fiber Cores Overview -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-diagram-2"></i> Fiber Cores Overview</h6>
                <a href="{{ route('cable-segments.cores', $cableSegment) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-diagram-2"></i> View All Cores
                </a>
            </div>
            <div class="card-body">
                @if($cableSegment->cores->count() > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Core Utilization</span>
                            <span><strong>{{ $coreStats['used'] }}/{{ $coreStats['created'] }}</strong></span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $coreStats['created'] > 0 ? ($coreStats['available'] / $coreStats['created'] * 100) : 0 }}%">
                                {{ $coreStats['available'] }} Available
                            </div>
                            <div class="progress-bar bg-warning" style="width: {{ $coreStats['created'] > 0 ? ($coreStats['used'] / $coreStats['created'] * 100) : 0 }}%">
                                {{ $coreStats['used'] }} Used
                            </div>
                            <div class="progress-bar bg-primary" style="width: {{ $coreStats['created'] > 0 ? ($coreStats['reserved'] / $coreStats['created'] * 100) : 0 }}%">
                                {{ $coreStats['reserved'] }} Reserved
                            </div>
                            <div class="progress-bar bg-danger" style="width: {{ $coreStats['created'] > 0 ? ($coreStats['damaged'] / $coreStats['created'] * 100) : 0 }}%">
                                {{ $coreStats['damaged'] }} Damaged
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Core #</th>
                                    <th>Color</th>
                                    <th>Status</th>
                                    <th>Loss (dB)</th>
                                    <th>Connected To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cableSegment->cores->take(10) as $core)
                                <tr>
                                    <td><strong>{{ $core->core_number }}</strong></td>
                                    <td>
                                        @if($core->core_color)
                                            @php $badge = $core->getColorBadge(); @endphp
                                            <span class="{{ $badge['class'] }}" @if($badge['style']) style="{{ $badge['style'] }}" @endif>
                                                {{ $core->core_color }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $core->getStatusBadgeClass() }}">
                                            {{ ucfirst($core->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $core->loss_db ? $core->loss_db . ' dB' : '-' }}</td>
                                    <td>
                                        @if($core->connectedTo)
                                            <small>{{ class_basename($core->connectedTo) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($cableSegment->cores->count() > 10)
                        <div class="text-center">
                            <small class="text-muted">Showing 10 of {{ $cableSegment->cores->count() }} cores</small>
                            <br>
                            <a href="{{ route('cable-segments.cores', $cableSegment) }}" class="btn btn-outline-primary btn-sm mt-2">
                                View All {{ $cableSegment->cores->count() }} Cores
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-diagram-2 text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-3 text-muted">No fiber cores created yet</p>
                        <a href="{{ route('cores.create', ['cable_segment_id' => $cableSegment->id]) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Create Cores
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($cableSegment->notes)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-sticky"></i> Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $cableSegment->notes }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <a href="{{ route('cable-segments.edit', $cableSegment) }}" class="btn btn-outline-warning w-100 btn-sm">
                            <i class="bi bi-pencil"></i> Edit Cable
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('cable-segments.cores', $cableSegment) }}" class="btn btn-outline-primary w-100 btn-sm">
                            <i class="bi bi-diagram-2"></i> Manage Cores
                        </a>
                    </div>
                    <div class="col-md-3">
                        @if($cableSegment->startPoint)
                            <a href="{{ route(Str::plural(Str::kebab($cableSegment->start_point_type)) . '.show', $cableSegment->start_point_id) }}" class="btn btn-outline-primary w-100 btn-sm">
                                <i class="bi bi-box-arrow-up-left"></i> View Start Point
                            </a>
                        @else
                            <button class="btn btn-outline-secondary w-100 btn-sm" disabled>
                                <i class="bi bi-box-arrow-up-left"></i> Start Point N/A
                            </button>
                        @endif
                    </div>
                    <div class="col-md-3">
                        @if($cableSegment->endPoint)
                            <a href="{{ route(Str::plural(Str::kebab($cableSegment->end_point_type)) . '.show', $cableSegment->end_point_id) }}" class="btn btn-outline-success w-100 btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i> View End Point
                            </a>
                        @else
                            <button class="btn btn-outline-secondary w-100 btn-sm" disabled>
                                <i class="bi bi-box-arrow-up-right"></i> End Point N/A
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
