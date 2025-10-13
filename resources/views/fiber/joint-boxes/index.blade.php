@extends('layouts.admin')

@section('title', 'Joint Boxes')
@section('page-title', 'Joint Box Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Joint Box Management</h5>
        <p class="text-muted mb-0">Manage fiber splice closures and joint boxes</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('joint-boxes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Joint Box
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0">{{ $jointBoxes->total() }}</h3>
                <p class="text-muted mb-0 small">Total Joint Boxes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success mb-0">{{ $jointBoxes->where('is_active', true)->count() }}</h3>
                <p class="text-muted mb-0 small">Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0">{{ $jointBoxes->where('used_capacity', '>', 0)->count() }}</h3>
                <p class="text-muted mb-0 small">In Use</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-info mb-0">{{ $jointBoxes->sum('capacity') }}</h3>
                <p class="text-muted mb-0 small">Total Capacity</p>
            </div>
        </div>
    </div>
</div>

<!-- Joint Boxes Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jointBoxes as $jb)
                    <tr>
                        <td><code>{{ $jb->code }}</code></td>
                        <td><strong>{{ $jb->name }}</strong></td>
                        <td>
                            @if($jb->type === 'closure')
                                <span class="badge bg-primary">Closure</span>
                            @elseif($jb->type === 'manhole')
                                <span class="badge bg-secondary">Manhole</span>
                            @elseif($jb->type === 'pole')
                                <span class="badge bg-info">Pole</span>
                            @else
                                <span class="badge bg-dark">Cabinet</span>
                            @endif
                        </td>
                        <td>{{ $jb->address ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $jb->capacity }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 me-2">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar
                                            @if($jb->getUsagePercentage() >= 80) bg-danger
                                            @elseif($jb->getUsagePercentage() >= 60) bg-warning
                                            @else bg-success
                                            @endif"
                                            style="width: {{ $jb->getUsagePercentage() }}%">
                                            {{ $jb->used_capacity }}/{{ $jb->capacity }}
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">{{ number_format($jb->getUsagePercentage(), 1) }}%</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $jb->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $jb->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('joint-boxes.show', $jb) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('joint-boxes.edit', $jb) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('joint-boxes.destroy', $jb) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete joint box?')"
                                            {{ $jb->used_capacity > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No joint boxes found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $jointBoxes->links() }}
    </div>
</div>
@endsection
