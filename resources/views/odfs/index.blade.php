@extends('layouts.admin')

@section('title', 'ODF Management')
@section('page-title', 'ODF (Optical Distribution Frame)')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">ODF Management</h5>
        <p class="text-muted mb-0">Manage Optical Distribution Frames in Central Office</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odfs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New ODF
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
                        <p class="text-muted mb-1">Total ODFs</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-columns text-primary" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Used Ports</p>
                        <h4 class="mb-0 text-warning">{{ number_format($stats['used_ports']) }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-ethernet text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('odfs.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="olt_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All OLTs</option>
                    @foreach($olts as $olt)
                        <option value="{{ $olt->id }}" {{ request('olt_id') == $olt->id ? 'selected' : '' }}>
                            {{ $olt->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="full" {{ request('status') == 'full' ? 'selected' : '' }}>Full</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
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

<!-- ODF List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name / Code</th>
                        <th>OLT</th>
                        <th>Location</th>
                        <th>Rack / Position</th>
                        <th>Ports Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($odfs as $odf)
                    <tr>
                        <td>
                            <strong>{{ $odf->name }}</strong>
                            <br><small class="text-muted">{{ $odf->code }}</small>
                        </td>
                        <td>
                            @if($odf->olt)
                                <a href="{{ route('olts.show', $odf->olt) }}">
                                    {{ $odf->olt->name }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $odf->location === 'indoor' ? 'info' : 'warning' }}">
                                {{ ucfirst($odf->location) }}
                            </span>
                            @if($odf->address)
                                <br><small class="text-muted">{{ Str::limit($odf->address, 30) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($odf->rack_number)
                                <span class="badge bg-secondary">{{ $odf->rack_number }}</span>
                            @endif
                            @if($odf->position)
                                <small class="text-muted">{{ $odf->position }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $odf->getUsageBadgeClass() }}"
                                             style="width: {{ $odf->getUsagePercentage() }}%">
                                            {{ $odf->used_ports }}/{{ $odf->total_ports }}
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $odf->getUsagePercentage() }}%</small>
                            </div>
                        </td>
                        <td>
                            @if($odf->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('odfs.show', $odf) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('odfs.ports', $odf) }}" class="btn btn-outline-primary" title="Ports">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                                <a href="{{ route('odfs.edit', $odf) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('odfs.destroy', $odf) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete ODF {{ $odf->name }}?\n\nThis will also remove all connections.')"
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
                            <i class="bi bi-columns" style="font-size: 3rem;"></i>
                            <p class="mt-2">No ODFs found.</p>
                            <a href="{{ route('odfs.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add First ODF
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
                    Showing {{ $odfs->firstItem() ?? 0 }} to {{ $odfs->lastItem() ?? 0 }} of {{ $odfs->total() }} ODFs
                </small>
            </div>
            <div>
                {{ $odfs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
