@extends('layouts.admin')

@section('title', 'Joint Boxes')
@section('page-title', 'Joint Box Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Joint Box Management</h5>
        <p class="text-muted mb-0">Manage fiber optic joint boxes and splices</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('joint-boxes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Joint Box
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
                        <p class="text-muted mb-1">Total Joint Boxes</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-box text-primary" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Total Splices</p>
                        <h4 class="mb-0 text-info">{{ $stats['total_splices'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-bezier2 text-info" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Available Capacity</p>
                        <h4 class="mb-0 text-warning">{{ $stats['available_capacity'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-pie-chart text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('joint-boxes.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search joint box..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="inline" {{ request('type') == 'inline' ? 'selected' : '' }}>Inline</option>
                    <option value="branch" {{ request('type') == 'branch' ? 'selected' : '' }}>Branch</option>
                    <option value="terminal" {{ request('type') == 'terminal' ? 'selected' : '' }}>Terminal</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="installation_type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Installation</option>
                    <option value="aerial" {{ request('installation_type') == 'aerial' ? 'selected' : '' }}>Aerial</option>
                    <option value="underground" {{ request('installation_type') == 'underground' ? 'selected' : '' }}>Underground</option>
                    <option value="buried" {{ request('installation_type') == 'buried' ? 'selected' : '' }}>Buried</option>
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

<!-- Joint Boxes List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name / Code</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Splices</th>
                        <th>Installation</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jointBoxes as $jointBox)
                    <tr>
                        <td>
                            <strong>{{ $jointBox->name }}</strong>
                            @if($jointBox->code)
                                <br><small class="text-muted">{{ $jointBox->code }}</small>
                            @endif
                        </td>
                        <td>
                            @if($jointBox->location)
                                <i class="bi bi-geo-alt text-danger"></i>
                                <small>{{ $jointBox->location }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                            @if($jointBox->latitude && $jointBox->longitude)
                                <br>
                                <a href="https://www.google.com/maps?q={{ $jointBox->latitude }},{{ $jointBox->longitude }}"
                                   target="_blank"
                                   class="badge bg-info text-white text-decoration-none">
                                    <i class="bi bi-map"></i> Map
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($jointBox->type)
                                <span class="badge bg-{{ $jointBox->type === 'inline' ? 'primary' : ($jointBox->type === 'branch' ? 'warning' : 'info') }}">
                                    {{ ucfirst($jointBox->type) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($jointBox->capacity)
                                <strong>{{ $jointBox->capacity }}</strong> ports
                                <br>
                                <div class="progress" style="height: 5px;">
                                    @php
                                        $usedPercentage = $jointBox->capacity > 0
                                            ? ($jointBox->splices_count / $jointBox->capacity) * 100
                                            : 0;
                                    @endphp
                                    <div class="progress-bar bg-{{ $usedPercentage > 80 ? 'danger' : ($usedPercentage > 50 ? 'warning' : 'success') }}"
                                         style="width: {{ min($usedPercentage, 100) }}%">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ number_format($usedPercentage, 1) }}% used
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-primary">{{ $jointBox->splices_count }}</strong> splices
                            @if($jointBox->capacity)
                                <br><small class="text-muted">
                                    {{ $jointBox->capacity - $jointBox->splices_count }} available
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($jointBox->installation_type)
                                <span class="badge bg-secondary">
                                    {{ ucfirst($jointBox->installation_type) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                            @if($jointBox->installation_date)
                                <br><small class="text-muted">
                                    {{ \Carbon\Carbon::parse($jointBox->installation_date)->format('M Y') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $jointBox->is_active ? 'success' : 'danger' }}">
                                {{ $jointBox->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($jointBox->status && $jointBox->status !== 'active')
                                <br>
                                <span class="badge bg-warning">
                                    {{ ucfirst($jointBox->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('joint-boxes.show', $jointBox) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('joint-boxes.splices', $jointBox) }}" class="btn btn-outline-primary" title="Splices">
                                    <i class="bi bi-bezier2"></i>
                                </a>
                                <a href="{{ route('joint-boxes.edit', $jointBox) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('joint-boxes.destroy', $jointBox) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete joint box {{ $jointBox->name }}?')"
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
                            <i class="bi bi-box" style="font-size: 3rem;"></i>
                            <p class="mt-2">No joint boxes found.</p>
                            <a href="{{ route('joint-boxes.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add First Joint Box
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
                    Showing {{ $jointBoxes->firstItem() ?? 0 }} to {{ $jointBoxes->lastItem() ?? 0 }} of {{ $jointBoxes->total() }} joint boxes
                </small>
            </div>
            <div>
                {{ $jointBoxes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
