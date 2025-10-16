@extends('layouts.admin')

@section('title', 'Fiber Cable Segments')
@section('page-title', 'Fiber Cable Segments')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Fiber Cable Segments</h5>
        <p class="text-muted mb-0">Manage fiber optic cable segments and connections</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cable-segments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Cable Segment
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
                        <p class="text-muted mb-1">Total Segments</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-bezier text-primary" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Active Cables</p>
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
                        <p class="text-muted mb-1">Total Cores</p>
                        <h4 class="mb-0 text-info">{{ number_format($stats['total_cores']) }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-diagram-2 text-info" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Total Distance</p>
                        <h4 class="mb-0 text-warning">{{ $stats['total_distance'] }} km</h4>
                    </div>
                    <div>
                        <i class="bi bi-rulers text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('cable-segments.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search cable..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="cable_type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="backbone" {{ request('cable_type') == 'backbone' ? 'selected' : '' }}>Backbone</option>
                    <option value="distribution" {{ request('cable_type') == 'distribution' ? 'selected' : '' }}>Distribution</option>
                    <option value="drop" {{ request('cable_type') == 'drop' ? 'selected' : '' }}>Drop Cable</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="installation_type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Installation</option>
                    <option value="aerial" {{ request('installation_type') == 'aerial' ? 'selected' : '' }}>Aerial</option>
                    <option value="underground" {{ request('installation_type') == 'underground' ? 'selected' : '' }}>Underground</option>
                    <option value="duct" {{ request('installation_type') == 'duct' ? 'selected' : '' }}>Duct</option>
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

<!-- Cable Segments List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Cable Name / Code</th>
                        <th>From → To</th>
                        <th>Type</th>
                        <th>Cores</th>
                        <th>Distance</th>
                        <th>Installation</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($segments as $segment)
                    <tr>
                        <td>
                            <strong>{{ $segment->name }}</strong>
                            <br><small class="text-muted">{{ $segment->code }}</small>
                        </td>
                        <td>
                            {{-- ✅ FIXED: Safe display with nullsafe operator --}}
                            <div class="d-flex flex-column gap-1">
                                <div>
                                    <span class="badge bg-primary">{{ strtoupper($segment->start_point_type) }}</span>
                                    <small>
                                        @if($segment->start_point_id)
                                            {{ $segment->startPoint?->name ?? 'ID: ' . $segment->start_point_id }}
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>
                                <div class="text-center">
                                    <i class="bi bi-arrow-down"></i>
                                </div>
                                <div>
                                    <span class="badge bg-success">{{ strtoupper($segment->end_point_type) }}</span>
                                    <small>
                                        @if($segment->end_point_id)
                                            {{ $segment->endPoint?->name ?? 'ID: ' . $segment->end_point_id }}
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $segment->cable_type === 'backbone' ? 'danger' : ($segment->cable_type === 'distribution' ? 'warning' : 'info') }}">
                                {{ ucfirst($segment->cable_type) }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $segment->core_count }}</strong> cores
                            @if($segment->cores->count() > 0)
                                <br><small class="text-muted">
                                    {{ $segment->cores->where('status', 'used')->count() }} used
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($segment->distance)
                                <strong>{{ number_format($segment->distance / 1000, 2) }}</strong> km
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($segment->installation_type)
                                <span class="badge bg-secondary">
                                    {{ ucfirst($segment->installation_type) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $segment->status === 'active' ? 'success' : ($segment->status === 'damaged' ? 'danger' : 'warning') }}">
                                {{ ucfirst($segment->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cable-segments.show', $segment) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('cable-segments.edit', $segment) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('cable-segments.cores', $segment) }}" class="btn btn-outline-primary" title="Cores">
                                    <i class="bi bi-bezier2"></i>
                                </a>
                                <form action="{{ route('cable-segments.destroy', $segment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete this cable?')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-bezier" style="font-size: 3rem;"></i>
                            <p class="mt-2">No cable segments found.</p>
                            <a href="{{ route('cable-segments.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add First Cable Segment
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
                    Showing {{ $segments->firstItem() ?? 0 }} to {{ $segments->lastItem() ?? 0 }} of {{ $segments->total() }} segments
                </small>
            </div>
            <div>
                {{ $segments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
