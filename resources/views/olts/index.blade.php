@extends('layouts.admin')

@section('title', 'OLT Management')
@section('page-title', 'OLT Management')

@section('content')
<div class="custom-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Daftar OLT</h5>
        @can('create_olt')
        <a href="{{ route('olts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add OLT
        </a>
        @endcan
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('olts.index') }}">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search OLT..." value="{{ request('search') }}">
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

    @if($olts->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>IP Address</th>
                        <th>Type</th>
                        <th>Ports</th>
                        <th>Customers</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($olts as $olt)
                    <tr>
                        <td>
                            <strong>{{ $olt->name }}</strong>
                        </td>
                        <td>
                            <code>{{ $olt->ip_address }}</code>
                        </td>
                        <td>{{ $olt->getOltTypeLabel() }}</td>
                        <td>{{ $olt->total_ports }} ports</td>
                        <td>
                            <span class="badge bg-info">{{ $olt->customers_count }} customers</span>
                        </td>
                        <td>
                            <span class="{{ $olt->getStatusBadgeClass() }}">
                                {{ $olt->is_active ? ($olt->isOnline() ? 'Online' : 'Offline') : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $olt->last_seen ? $olt->last_seen->diffForHumans() : 'Never' }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('olts.show', $olt) }}" class="btn btn-outline-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('edit_olt')
                                <a href="{{ route('olts.edit', $olt) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan

                                @can('delete_olt')
                                <form action="{{ route('olts.destroy', $olt) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus OLT ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
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

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $olts->firstItem() }} - {{ $olts->lastItem() }} of {{ $olts->total() }} OLTs
            </div>
            <div>
                {{ $olts->links() }}
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-hdd-network" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">No OLTs</h5>
            <p class="text-muted">Click "Add OLT" to add your first OLT device.</p>
        </div>
    @endif
</div>
@endsection
