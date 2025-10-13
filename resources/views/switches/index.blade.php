@extends('layouts.admin')

@section('title', 'Switch Management')
@section('page-title', 'Network Switches')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Managed Switches</h5>
        <p class="text-muted mb-0">Monitor and manage network switches</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('switches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Switch
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>IP Address</th>
                        <th>Location</th>
                        <th>Ports</th>
                        <th>Ping</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($switches as $switch)
                    <tr>
                        <td>
                            <strong>{{ $switch->name }}</strong>
                            <br><small class="text-muted">{{ $switch->model }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($switch->brand) }}</span>
                        </td>
                        <td><code>{{ $switch->ip_address }}</code></td>
                        <td>{{ $switch->location ?? '-' }}</td>
                        <td>{{ $switch->port_count ?? '-' }}</td>
                        <td>
                            @if($switch->ping_latency)
                                <span class="badge bg-success">{{ $switch->ping_latency }}ms</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="{{ $switch->getStatusBadgeClass() }}">{{ ucfirst($switch->status) }}</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('switches.show', $switch) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('switches.edit', $switch) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('switches.destroy', $switch) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No switches found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $switches->links() }}
    </div>
</div>
@endsection
