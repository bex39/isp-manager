@extends('layouts.admin')

@section('title', 'ODP: ' . $odp->name)
@section('page-title', 'ODP Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $odp->name }}</h4>
        <p class="text-muted mb-0">Code: {{ $odp->code }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odps.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('odps.edit', $odp) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Status Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-primary mb-0">{{ $odp->total_ports }}</h2>
                <p class="text-muted mb-0 small">Total Ports</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-success mb-0">{{ $odp->getAvailablePorts() }}</h2>
                <p class="text-muted mb-0 small">Available</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-danger mb-0">{{ $odp->used_ports }}</h2>
                <p class="text-muted mb-0 small">Used</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-info mb-0">{{ $odp->splitters->count() }}</h2>
                <p class="text-muted mb-0 small">Splitters</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- ODP Info -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">ODP Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Code</strong></td>
                        <td><code>{{ $odp->code }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td>{{ $odp->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Connected OLT</strong></td>
                        <td>
                            @if($odp->olt)
                                <a href="{{ route('olts.show', $odp->olt) }}">{{ $odp->olt->name }}</a>
                            @else
                                <span class="text-muted">Not connected</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Address</strong></td>
                        <td>{{ $odp->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Coordinates</strong></td>
                        <td>
                            @if($odp->latitude && $odp->longitude)
                                {{ $odp->latitude }}, {{ $odp->longitude }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <span class="badge {{ $odp->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $odp->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Splitters -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Splitters</h6>
                <a href="{{ route('splitters.create', ['odp_id' => $odp->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add
                </a>
            </div>
            <div class="card-body">
                @if($odp->splitters->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Ratio</th>
                                    <th>Ports</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($odp->splitters as $splitter)
                                <tr>
                                    <td>{{ $splitter->name }}</td>
                                    <td><span class="badge bg-info">{{ $splitter->ratio }}</span></td>
                                    <td>
                                        <small>{{ $splitter->getAvailableOutputs() }} / {{ $splitter->output_ports }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('splitters.show', $splitter) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No splitters configured</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
