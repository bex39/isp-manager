@extends('layouts.admin')

@section('title', 'Switch Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Network Switches</h5>
        <p class="text-muted mb-0">Monitor and manage network switches</p>
    </div>
    <div class="col-md-4 text-end">
        <form action="{{ route('switches.check-all-status') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-info btn-sm">
                <i class="bi bi-arrow-repeat"></i> Check All
            </button>
        </form>
        <a href="{{ route('switches.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add Switch
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Switches</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-success">{{ $stats['online'] ?? 0 }}</h4>
                <small class="text-muted">Online</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $stats['managed'] }}</h4>
                <small class="text-muted">Managed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-secondary">{{ $stats['unmanaged'] }}</h4>
                <small class="text-muted">Unmanaged</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('switches.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search switches..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="brand" class="form-select">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                {{ ucfirst($brand) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Switches Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Switch</th>
                        <th>Brand/Model</th>
                        <th>IP Address</th>
                        <th>Location</th>
                        <th>Ports</th>
                        <th>Latency</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($switches as $switch)
                    <tr>
                        <td>
                            <strong>{{ $switch->name }}</strong>
                            @if($switch->mac_address)
                                <br><small class="text-muted">MAC: {{ $switch->mac_address }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $switch->getBrandDisplayName() }}</span>
                            @if($switch->model)
                                <br><small class="text-muted">{{ $switch->model }}</small>
                            @endif
                        </td>
                        <td>
                            @if($switch->ip_address)
                                <code>{{ $switch->ip_address }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($switch->location)
                                <i class="bi bi-geo-alt"></i> {{ $switch->location }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($switch->port_count)
                                <span class="badge bg-info">{{ $switch->port_count }} ports</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($switch->ping_latency)
                                <span class="{{ $switch->getLatencyColorClass() }}">
                                    <i class="bi bi-reception-4"></i> {{ $switch->ping_latency }}ms
                                </span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $switch->getStatusBadgeClass() }}">
                                {{ ucfirst($switch->status ?? 'Unknown') }}
                            </span>
                            @if($switch->last_seen)
                                <br><small class="text-muted">{{ $switch->last_seen->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td>
                            @if($switch->isManaged())
                                <span class="badge bg-success">Managed</span>
                            @else
                                <span class="badge bg-secondary">Unmanaged</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('switches.show', $switch) }}"
                                   class="btn btn-outline-info"
                                   title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($switch->isManaged())
                                    <form action="{{ route('switches.check-status', $switch) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-outline-success"
                                                title="Ping">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('switches.edit', $switch) }}"
                                   class="btn btn-outline-primary"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('switches.destroy', $switch) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this switch?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-outline-danger"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-hdd-rack" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No switches found</p>
                            <a href="{{ route('switches.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add First Switch
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
                    Showing {{ $switches->firstItem() ?? 0 }} to {{ $switches->lastItem() ?? 0 }}
                    of {{ $switches->total() }} switches
                </small>
            </div>
            <div>
                {{ $switches->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh status every 30 seconds for online switches
setInterval(() => {
    const hasOnline = {{ $switches->where('status', 'online')->count() > 0 ? 'true' : 'false' }};
    if (hasOnline) {
        // Optional: Auto-refresh page
        // location.reload();
    }
}, 30000);
</script>
@endpush
