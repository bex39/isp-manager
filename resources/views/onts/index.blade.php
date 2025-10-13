@extends('layouts.admin')

@section('title', 'ONT Management')
@section('page-title', 'ONT Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-4">
        <h5 class="fw-bold mb-1">ONT Management</h5>
        <p class="text-muted mb-0">Manage Optical Network Terminals</p>
    </div>

    <!-- Search & Filter -->
    <div class="col-md-8 text-end">
        <form action="{{ route('onts.index') }}" method="GET" class="d-flex justify-content-end gap-2 flex-wrap">
            <input type="text" name="search" class="form-control" style="max-width: 200px;"
                   placeholder="Search..." value="{{ request('search') }}">

            <select name="olt_id" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All OLTs</option>
                @foreach($olts as $olt)
                    <option value="{{ $olt->id }}" {{ request('olt_id') == $olt->id ? 'selected' : '' }}>
                        {{ $olt->name }}
                    </option>
                @endforeach
            </select>

            <select name="odp_id" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All ODPs</option>
                @foreach($odps as $odp)
                    <option value="{{ $odp->id }}" {{ request('odp_id') == $odp->id ? 'selected' : '' }}>
                        {{ $odp->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-select" style="max-width: 130px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                <option value="los" {{ request('status') == 'los' ? 'selected' : '' }}>LOS</option>
            </select>

            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>

            <a href="{{ route('onts.index') }}" class="btn btn-outline-dark" title="Reset">
                <i class="bi bi-arrow-repeat"></i>
            </a>

            <a href="{{ route('onts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total ONTs</p>
                        <h4 class="mb-0">{{ $onts->total() }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-modem text-primary" style="font-size: 2rem;"></i>
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
                        <h4 class="mb-0 text-success">{{ $onts->where('status', 'online')->count() }}</h4>
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
                        <p class="text-muted mb-1">Offline</p>
                        <h4 class="mb-0 text-danger">{{ $onts->where('status', 'offline')->count() }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
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
                        <p class="text-muted mb-1">With Customers</p>
                        <h4 class="mb-0 text-info">{{ $onts->whereNotNull('customer_id')->count() }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ONT Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Serial Number</th>
                        <th>OLT</th>
                        <th>ODP</th>
                        <th>Customer</th>
                        <th>Signal (RX)</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onts as $ont)
                    <tr>
                        <td>
                            <strong>{{ $ont->name }}</strong>
                            @if($ont->model)
                                <br><small class="text-muted">{{ $ont->model }}</small>
                            @endif
                        </td>
                        <td>
                            <code>{{ $ont->sn }}</code>
                            @if($ont->management_ip)
                                <br><small class="text-muted">{{ $ont->management_ip }}</small>
                            @endif
                        </td>
                        <td>
                            @if($ont->olt)
                                <a href="{{ route('olts.show', $ont->olt) }}">
                                    {{ $ont->olt->name }}
                                </a>
                                @if($ont->pon_port)
                                    <br><small class="text-muted">PON: {{ $ont->pon_port }}/{{ $ont->ont_id }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($ont->odp)
                                <a href="{{ route('odps.show', $ont->odp) }}">
                                    {{ $ont->odp->name }}
                                </a>
                                @if($ont->odp_port)
                                    <br><small class="text-muted">Port: {{ $ont->odp_port }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($ont->customer)
                                <a href="{{ route('customers.show', $ont->customer) }}">
                                    {{ $ont->customer->name }}
                                </a>
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @if($ont->rx_power)
                                <span class="{{ $ont->getSignalBadgeClass() }}">
                                    {{ $ont->rx_power }} dBm
                                </span>
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($ont->status === 'online')
                                <span class="badge bg-success">
                                    <i class="bi bi-circle-fill"></i> Online
                                </span>
                            @elseif($ont->status === 'los')
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle"></i> LOS
                                </span>
                            @elseif($ont->status === 'disabled')
                                <span class="badge bg-secondary">
                                    <i class="bi bi-dash-circle"></i> Disabled
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-x-circle"></i> Offline
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($ont->last_seen)
                                <small class="text-muted">{{ $ont->last_seen->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('onts.show', $ont) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('onts.edit', $ont) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('onts.destroy', $ont) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete {{ $ont->name }}?\n\nThis will also decrement ODP used ports.')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-modem" style="font-size: 2rem;"></i>
                            <p class="mt-2">No ONTs found.</p>
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
                    Showing {{ $onts->firstItem() ?? 0 }} to {{ $onts->lastItem() ?? 0 }}
                    of {{ $onts->total() }} ONTs
                </small>
            </div>
            <div>
                {{ $onts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
