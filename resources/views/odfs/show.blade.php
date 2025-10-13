@extends('layouts.admin')

@section('title', 'ODF: ' . $odf->name)
@section('page-title', 'ODF Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $odf->name }}</h4>
        <p class="text-muted mb-0">
            Code: <code>{{ $odf->code }}</code> |
            OLT: {{ $odf->olt->name ?? 'N/A' }} |
            Location: {{ ucfirst($odf->location) }}
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odfs.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('odfs.ports', $odf) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-diagram-3"></i> Port Map
        </a>
        <a href="{{ route('odfs.edit', $odf) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Port Usage Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-primary">
                    {{ $portUsage['total'] }}
                </div>
                <small class="text-muted">Total Ports</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-warning">
                    {{ $portUsage['used'] }}
                </div>
                <small class="text-muted">Used Ports</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 text-success">
                    {{ $portUsage['available'] }}
                </div>
                <small class="text-muted">Available Ports</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 {{ $portUsage['percentage'] >= 80 ? 'text-danger' : 'text-info' }}">
                    {{ $portUsage['percentage'] }}%
                </div>
                <small class="text-muted">Usage</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Basic Information -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Basic Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%" class="text-muted">Name</td>
                        <td><strong>{{ $odf->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Code</td>
                        <td><code>{{ $odf->code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">OLT</td>
                        <td>
                            @if($odf->olt)
                                <a href="{{ route('olts.show', $odf->olt) }}">{{ $odf->olt->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Location</td>
                        <td>
                            <span class="badge bg-{{ $odf->location === 'indoor' ? 'info' : 'warning' }}">
                                {{ ucfirst($odf->location) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Rack / Position</td>
                        <td>
                            {{ $odf->rack_number ?? '-' }}
                            @if($odf->position)
                                / {{ $odf->position }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Installation Date</td>
                        <td>{{ $odf->installation_date ? $odf->installation_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($odf->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Port Usage -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Port Usage</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Port Utilization</span>
                        <span><strong>{{ $portUsage['used'] }}/{{ $portUsage['total'] }}</strong></span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $portUsage['percentage'] >= 80 ? 'bg-danger' : ($portUsage['percentage'] >= 60 ? 'bg-warning' : 'bg-success') }}"
                             style="width: {{ $portUsage['percentage'] }}%">
                            {{ $portUsage['percentage'] }}%
                        </div>
                    </div>
                </div>

                <table class="table table-sm">
                    <tr>
                        <td>Total Ports:</td>
                        <td><strong>{{ $portUsage['total'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Used Ports:</td>
                        <td><span class="badge bg-warning">{{ $portUsage['used'] }}</span></td>
                    </tr>
                    <tr>
                        <td>Available Ports:</td>
                        <td><span class="badge bg-success">{{ $portUsage['available'] }}</span></td>
                    </tr>
                </table>

                <a href="{{ route('odfs.ports', $odf) }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-diagram-3"></i> View Full Port Map
                </a>
            </div>
        </div>
    </div>

    <!-- Connected ODCs -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Connected ODCs ({{ $odf->odcs->count() }})</h6>
            </div>
            <div class="card-body">
                @if($odf->odcs->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($odf->odcs->take(5) as $odc)
                            <a href="{{ route('odcs.show', $odc) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $odc->name }}</strong>
                                        <br><small class="text-muted">{{ $odc->code }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $odc->getUsageBadgeClass() }}">
                                            {{ $odc->used_ports }}/{{ $odc->total_ports }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if($odf->odcs->count() > 5)
                        <div class="text-center mt-2">
                            <small class="text-muted">+ {{ $odf->odcs->count() - 5 }} more ODCs</small>
                        </div>
                    @endif
                @else
                    <p class="text-muted text-center mb-0">No ODCs connected yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Incoming Cables -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Incoming Cables ({{ $incomingCables->count() }})</h6>
            </div>
            <div class="card-body">
                @if($incomingCables->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($incomingCables->take(5) as $cable)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $cable->name }}</strong>
                                        <br><small class="text-muted">
                                            From: {{ class_basename($cable->startPoint) }} - {{ $cable->startPoint->name }}
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-info">{{ $cable->core_count }} cores</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No incoming cables</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Location -->
    @if($odf->latitude && $odf->longitude)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Location</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Coordinates:</strong> {{ $odf->latitude }}, {{ $odf->longitude }}
                </p>
                @if($odf->address)
                    <p class="mb-0"><strong>Address:</strong> {{ $odf->address }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($odf->notes)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $odf->notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
