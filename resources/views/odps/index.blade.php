@extends('layouts.admin')

@section('title', 'ODP Management')
@section('page-title', 'Optical Distribution Point')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">ODP Management</h5>
        <p class="text-muted mb-0">Manage optical distribution points</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('odps.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add ODP
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>OLT</th>
                        <th>Location</th>
                        <th>Ports</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($odps as $odp)
                    <tr>
                        <td><code>{{ $odp->code }}</code></td>
                        <td><strong>{{ $odp->name }}</strong></td>
                        <td>{{ $odp->olt->name ?? '-' }}</td>
                        <td>{{ $odp->address ?? '-' }}</td>
                        <td>
                            <span class="badge bg-success">{{ $odp->getAvailablePorts() }}</span> /
                            <span class="badge bg-secondary">{{ $odp->total_ports }}</span>
                            <br><small class="text-muted">Available / Total</small>
                        </td>
                        <td>
                            <span class="badge {{ $odp->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $odp->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('odps.show', $odp) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('odps.edit', $odp) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('odps.destroy', $odp) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete ODP?')"
                                            {{ $odp->used_ports > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No ODP found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $odps->links() }}
    </div>
</div>
@endsection
