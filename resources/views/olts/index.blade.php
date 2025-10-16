@extends('layouts.admin')

@section('title', 'OLT Management')
@section('page-title', 'OLT Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">OLT Management</h5>
        <p class="text-muted mb-0">Manage OLT devices and monitor their status</p>
    </div>
    <div class="col-md-4 text-end">
        <!-- Check All Status Button -->
        <form action="{{ route('olts.check-all-status') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-info btn-sm" title="Refresh all OLT status">
                <i class="bi bi-arrow-repeat"></i> Check All
            </button>
        </form>

        @can('create_olt')
        <a href="{{ route('olts.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add OLT
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total OLTs</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-router text-primary" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Online</p>
                        <h4 class="mb-0 text-info">{{ $stats['online'] ?? 0 }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-wifi text-info" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">Offline</p>
                        <h4 class="mb-0 text-danger">{{ $stats['offline'] ?? 0 }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-wifi-off text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('olts.index') }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by name, IP address..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- OLT Table -->
@if($olts->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Brand/Model</th>
                            <th>Ports</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($olts as $olt)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $olt->name }}</strong>
                                    @if($olt->code)
                                        <br><small class="text-muted">{{ $olt->code }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <code>{{ $olt->ip_address }}</code>
                                @if($olt->telnet_port || $olt->ssh_port)
                                    <br><small class="text-muted">
                                        @if($olt->telnet_port)Telnet: {{ $olt->telnet_port }}@endif
                                        @if($olt->ssh_port) SSH: {{ $olt->ssh_port }}@endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($olt->brand || $olt->model)
                                    {{ $olt->brand ?? '' }} {{ $olt->model ?? '' }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $olt->total_ports ?? 0 }} ports</span>
                            </td>
                            <td>
                                @php
                                    // Determine status display
                                    if (!$olt->is_active) {
                                        $statusClass = 'badge bg-secondary';
                                        $statusText = 'Inactive';
                                    } elseif (isset($olt->status)) {
                                        // If status column exists
                                        if ($olt->status === 'online') {
                                            $statusClass = 'badge bg-success';
                                            $statusText = 'Online';
                                        } elseif ($olt->status === 'offline') {
                                            $statusClass = 'badge bg-danger';
                                            $statusText = 'Offline';
                                        } else {
                                            $statusClass = 'badge bg-warning';
                                            $statusText = ucfirst($olt->status);
                                        }
                                    } else {
                                        // Fallback: check last_seen
                                        if ($olt->last_seen && $olt->last_seen->gt(now()->subMinutes(10))) {
                                            $statusClass = 'badge bg-success';
                                            $statusText = 'Online';
                                        } else {
                                            $statusClass = 'badge bg-danger';
                                            $statusText = 'Offline';
                                        }
                                    }
                                @endphp

                                <span class="{{ $statusClass }}">
                                    <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td>
                                @if($olt->last_seen)
                                    <small class="text-muted">
                                        {{ $olt->last_seen->diffForHumans() }}
                                    </small>
                                @else
                                    <small class="text-muted">Never</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('olts.show', $olt) }}"
                                       class="btn btn-outline-info"
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <!-- Check Status Button -->
                                    <form action="{{ route('olts.check-status', $olt) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-outline-warning"
                                                title="Check Status">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>

                                    @can('edit_olt')
                                    <a href="{{ route('olts.edit', $olt) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('delete_olt')
                                    <form action="{{ route('olts.destroy', $olt) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this OLT?\n\nThis will also remove all associated ONTs and data.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Showing {{ $olts->firstItem() ?? 0 }} to {{ $olts->lastItem() ?? 0 }} of {{ $olts->total() }} OLTs
                    </small>
                </div>
                <div>
                    {{ $olts->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-router" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">No OLTs Found</h5>
            <p class="text-muted">
                @if(request('search'))
                    No OLTs match your search criteria.
                    <a href="{{ route('olts.index') }}">Clear filters</a>
                @else
                    Get started by adding your first OLT device.
                @endif
            </p>
            @can('create_olt')
                @if(!request('search'))
                    <a href="{{ route('olts.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Add Your First OLT
                    </a>
                @endif
            @endcan
        </div>
    </div>
@endif

@endsection

@push('styles')
<style>
/* Status indicator pulse animation for online status */
.badge.bg-success i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Hover effect for table rows */
.table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>
@endpush
