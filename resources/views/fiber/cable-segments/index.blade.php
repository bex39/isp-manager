@extends('layouts.admin')

@section('title', 'Cable Segments')
@section('page-title', 'Fiber Cable Segments')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Fiber Cable Segment Management</h5>
        <p class="text-muted mb-0">Manage fiber optic cable routes and segments</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cable-segments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Cable Segment
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0">{{ $segments->total() }}</h3>
                <p class="text-muted mb-0 small">Total Segments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success mb-0">{{ $segments->where('status', 'active')->count() }}</h3>
                <p class="text-muted mb-0 small">Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0">{{ $segments->where('cable_type', 'backbone')->count() }}</h3>
                <p class="text-muted mb-0 small">Backbone</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-info mb-0">{{ $segments->sum('core_count') }}</h3>
                <p class="text-muted mb-0 small">Total Cores</p>
            </div>
        </div>
    </div>
</div>

<!-- Cable Segments Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Cores</th>
                        <th>Route</th>
                        <th>Distance</th>
                        <th>Core Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($segments as $segment)
                    <tr>
                        <td><code>{{ $segment->code }}</code></td>
                        <td><strong>{{ $segment->name }}</strong></td>
                        <td>
                            @if($segment->cable_type === 'backbone')
                                <span class="badge bg-danger">Backbone</span>
                            @elseif($segment->cable_type === 'distribution')
                                <span class="badge bg-warning">Distribution</span>
                            @else
                                <span class="badge bg-info">Drop</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $segment->core_count }} cores</span>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ class_basename($segment->start_point_type) }} → {{ class_basename($segment->end_point_type) }}
                            </small>
                        </td>
                        <td>{{ $segment->distance ? number_format($segment->distance, 2) . ' m' : '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 me-2">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar
                                            @if($segment->getCoreUsagePercentage() >= 80) bg-danger
                                            @elseif($segment->getCoreUsagePercentage() >= 60) bg-warning
                                            @else bg-success
                                            @endif"
                                            style="width: {{ $segment->getCoreUsagePercentage() }}%">
                                            {{ $segment->getUsedCores() }}/{{ $segment->core_count }}
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">{{ number_format($segment->getCoreUsagePercentage(), 1) }}%</small>
                            </div>
                        </td>
                        <td>
                            @if($segment->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($segment->status === 'damaged')
                                <span class="badge bg-danger">Damaged</span>
                            @else
                                <span class="badge bg-warning">Maintenance</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cable-segments.show', $segment) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('cable-segments.edit', $segment) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('cable-segments.destroy', $segment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete cable segment?')"
                                            {{ $segment->getUsedCores() > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No cable segments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $segments->links() }}
    </div>
</div>
@endsection
