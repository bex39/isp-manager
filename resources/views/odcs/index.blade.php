@extends('layouts.admin')

@section('title', 'ODC Management')
@section('page-title', 'ODC (Optical Distribution Cabinet)')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">ODC Management</h5>
        <p class="text-muted mb-0">Manage Optical Distribution Cabinets in Field</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odcs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New ODC
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total ODCs</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-server text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Active</p>
                        <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Ports</p>
                        <h4 class="mb-0 text-info">{{ number_format($stats['total_ports']) }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-plug text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Connected Splitters</p>
                        <h4 class="mb-0 text-warning">{{ $stats['splitters'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-shuffle text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('odcs.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="odf_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All ODFs</option>
                    @foreach($odfs as $odf)
                        <option value="{{ $odf->id }}" {{ request('odf_id') == $odf->id ? 'selected' : '' }}>
                            {{ $odf->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="location" class="form-select" onchange="this.form.submit()">
                    <option value="">All Locations</option>
                    <option value="outdoor" {{ request('location') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                    <option value="indoor" {{ request('location') == 'indoor' ? 'selected' : '' }}>Indoor</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ODC List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name / Code</th>
                        <th>ODF Source</th>
                        <th>Location</th>
                        <th>Ports Usage</th>
                        <th>Splitters</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($odcs as $odc)
                    <tr>
                        <td>
                            <strong>{{ $odc->name }}</strong>
                            <br><small class="text-muted">{{ $odc->code }}</small>
                        </td>
                        <td>
                            @if($odc->odf)
                                <a href="{{ route('odfs.show', $odc->odf) }}">
                                    {{ $odc->odf->name }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $odc->location === 'outdoor' ? 'warning' : 'info' }}">
                                {{ ucfirst($odc->location) }}
                            </span>
                            @if($odc->address)
                                <br><small class="text-muted">{{ Str::limit($odc->address, 30) }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $odc->getUsageBadgeClass() }}"
                                             style="width: {{ $odc->getUsagePercentage() }}%">
                                            {{ $odc->used_ports }}/{{ $odc->total_ports }}
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $odc->getUsagePercentage() }}%</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $odc->splitters->count() }}</span>
                        </td>
                        <td>
                            @if($odc->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('odcs.show', $odc) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('odcs.ports', $odc) }}" class="btn btn-outline-primary" title="Ports">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                                <a href="{{ route('odcs.edit', $odc) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('odcs.destroy', $odc) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete ODC {{ $odc->name }}?\n\nThis will also remove all connections.')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-server" style="font-size: 3rem;"></i>
                            <p class="mt-2">No ODCs found.</p>
                            <a href="{{ route('odcs.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add First ODC
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Showing {{ $odcs->firstItem() ?? 0 }} to {{ $odcs->lastItem() ?? 0 }} of {{ $odcs->total() }} ODCs
                </small>
            </div>
            <div>
                {{ $odcs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
