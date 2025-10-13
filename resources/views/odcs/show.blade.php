@extends('layouts.admin')

@section('title', 'ODC: ' . $odc->name)
@section('page-title', 'ODC Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $odc->name }}</h4>
        <p class="text-muted mb-0">
            Code: <code>{{ $odc->code }}</code> |
            ODF: {{ $odc->odf->name ?? 'N/A' }} |
            Location: {{ ucfirst($odc->location) }}
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odcs.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('odcs.ports', $odc) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-diagram-3"></i> Port Map
        </a>
        <a href="{{ route('odcs.edit', $odc) }}" class="btn btn-warning btn-sm">
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
                        <td><strong>{{ $odc->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Code</td>
                        <td><code>{{ $odc->code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">ODF Source</td>
                        <td>
                            @if($odc->odf)
                                <a href="{{ route('odfs.show', $odc->odf) }}">{{ $odc->odf->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Location Type</td>
                        <td>
                            <span class="badge bg-{{ $odc->location === 'outdoor' ? 'warning' : 'info' }}">
                                {{ ucfirst($odc->location) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cabinet Type</td>
                        <td>{{ $odc->cabinet_type ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cabinet Size</td>
                        <td>{{ $odc->cabinet_size ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Installation Date</td>
                        <td>{{ $odc->installation_date ? $odc->installation_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($odc->is_active)
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

                <a href="{{ route('odcs.ports', $odc) }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-diagram-3"></i> View Full Port Map
                </a>
            </div>
        </div>
    </div>

    <!-- Connected Splitters -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Connected Splitters ({{ $odc->splitters->count() }})</h6>
            </div>
            <div class="card-body">
                @if($odc->splitters->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($odc->splitters->take(5) as $splitter)
                            <a href="{{ route('splitters.show', $splitter) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $splitter->name }}</strong>
                                        <br><small class="text-muted">Ratio: {{ $splitter->ratio }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-info">
                                            Port {{ $splitter->odc_port }}
                                        </span>
                                        <br><small class="text-muted">{{ $splitter->output_ports }} ports</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if($odc->splitters->count() > 5)
                        <div class="text-center mt-2">
                            <small class="text-muted">+ {{ $odc->splitters->count() - 5 }} more splitters</small>
                        </div>
                    @endif
                @else
                    <p class="text-muted text-center mb-0">No splitters connected yet</p>
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
                                        <br><span class="badge bg-secondary">{{ ucfirst($cable->cable_type) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($incomingCables->count() > 5)
                        <div class="text-center mt-2">
                            <small class="text-muted">+ {{ $incomingCables->count() - 5 }} more cables</small>
                        </div>
                    @endif
                @else
                    <p class="text-muted text-center mb-0">No incoming cables</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Location Map -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt"></i> Location</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Coordinates:</strong>
                            <span class="badge bg-dark">{{ $odc->latitude }}, {{ $odc->longitude }}</span>
                        </p>
                        @if($odc->address)
                            <p class="mb-2"><strong>Address:</strong> {{ $odc->address }}</p>
                        @endif
                        <a href="https://www.google.com/maps?q={{ $odc->latitude }},{{ $odc->longitude }}"
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-map"></i> Open in Google Maps
                        </a>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3 text-center">
                            <i class="bi bi-geo-alt-fill text-danger" style="font-size: 3rem;"></i>
                            <p class="mb-0 mt-2 small text-muted">
                                GPS Location: {{ ucfirst($odc->location) }} Cabinet
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coverage Area (if ODPs exist) -->
    @if($odc->splitters->count() > 0)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3"></i> Coverage Area</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-primary">{{ $odc->splitters->count() }}</h3>
                            <small class="text-muted">Total Splitters</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-success">{{ $odc->splitters->sum('output_ports') }}</h3>
                            <small class="text-muted">Total Splitter Outputs</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-info">{{ $odc->splitters->sum('used_outputs') }}</h3>
                            <small class="text-muted">Used Outputs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($odc->notes)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-sticky"></i> Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $odc->notes }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart"></i> Quick Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h5 class="mb-1 text-primary">{{ $odc->total_ports }}</h5>
                            <small class="text-muted">Total Ports</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h5 class="mb-1 text-warning">{{ $odc->used_ports }}</h5>
                            <small class="text-muted">Ports in Use</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h5 class="mb-1 text-success">{{ $odc->getAvailablePorts() }}</h5>
                            <small class="text-muted">Available Ports</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h5 class="mb-1 text-info">{{ $odc->splitters->count() }}</h5>
                            <small class="text-muted">Connected Splitters</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
