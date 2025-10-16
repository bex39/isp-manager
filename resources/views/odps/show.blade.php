@extends('layouts.admin')

@section('title', 'ODP Details')
@section('page-title', 'ODP Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <a href="{{ route('odps.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odps.edit', $odp) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('odps.destroy', $odp) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete this ODP?')">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                @php
                    $connectedONTs = $odp->onts ? $odp->onts->count() : 0;
                @endphp
                <h2 class="text-primary mb-0">{{ $connectedONTs }}</h2>
                <p class="text-muted mb-0 small">Connected ONTs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                @php
                    $availablePorts = $odp->port_capacity - $connectedONTs;
                @endphp
                <h2 class="text-success mb-0">{{ $availablePorts }}</h2>
                <p class="text-muted mb-0 small">Available Ports</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                @php
                    $usagePercent = $odp->port_capacity > 0
                        ? round(($connectedONTs / $odp->port_capacity) * 100, 1)
                        : 0;
                @endphp
                <h2 class="text-warning mb-0">{{ $usagePercent }}%</h2>
                <p class="text-muted mb-0 small">Port Usage</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-{{ $odp->is_active ? 'success' : 'danger' }} mb-0">
                    {{ $odp->is_active ? 'Active' : 'Inactive' }}
                </h2>
                <p class="text-muted mb-0 small">Status</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- ODP Info -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">ODP Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Name</label>
                        <h5>{{ $odp->name }}</h5>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Code</label>
                        <h5>{{ $odp->code ?? '-' }}</h5>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Port Capacity</label>
                        <p class="mb-0">{{ $odp->port_capacity }} ports</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Type</label>
                        <p class="mb-0">
                            @if($odp->type)
                                <span class="badge bg-secondary">{{ ucfirst($odp->type) }}</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>

                @if($odp->location)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="text-muted small">Location</label>
                        <p class="mb-0">{{ $odp->location }}</p>
                        @if($odp->latitude && $odp->longitude)
                            <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-info mt-2">
                                <i class="bi bi-map"></i> View on Map
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($odp->notes)
                <div class="row">
                    <div class="col-md-12">
                        <label class="text-muted small">Notes</label>
                        <p class="text-muted">{{ $odp->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Connected ONTs -->
        @if($odp->onts && $odp->onts->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Connected ONTs ({{ $odp->onts->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ONT Name</th>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($odp->onts->take(10) as $ont)
                            <tr>
                                <td>{{ $ont->name }}</td>
                                <td><code>{{ $ont->serial_number }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $ont->is_active ? 'success' : 'danger' }}">
                                        {{ $ont->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('onts.show', $ont) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-{{ $odp->is_active ? 'success' : 'danger' }} text-white">
                <h6 class="mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Active Status</small>
                    <p class="mb-0">
                        <span class="badge bg-{{ $odp->is_active ? 'success' : 'danger' }}">
                            {{ $odp->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div>
                    <small class="text-muted">Operational Status</small>
                    <p class="mb-0">
                        <span class="badge bg-secondary">{{ ucfirst($odp->status ?? 'active') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Installation Info -->
        @if($odp->installation_date)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">Installation</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">Installation Date</small>
                <p class="mb-0">{{ $odp->installation_date->format('d M Y') }}</p>
            </div>
        </div>
        @endif

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> History</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Created</small>
                    <p class="mb-0 small">{{ $odp->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 small">{{ $odp->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
