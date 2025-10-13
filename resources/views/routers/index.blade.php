@extends('layouts.admin')

@section('title', 'Router Management')
@section('page-title', 'Router Management')

@section('content')
<div class="custom-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Daftar Router MikroTik</h5>
        @can('create_router')
        <a href="{{ route('routers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Router
        </a>
        @endcan
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('routers.index') }}">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau IP router..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </div>
    </form>

    @if($routers->count() > 0)
        <div class="row g-4 mb-4">
            @foreach($routers as $router)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $router->name }}</h6>
                                <small class="text-muted">{{ $router->ip_address }}</small>
                            </div>
                            <div>
                                @if($router->isOnline())
                                    <span class="badge bg-success">Online</span>
                                @else
                                    <span class="badge bg-danger">Offline</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">RouterOS</small>
                                <small><span class="badge bg-info">v{{ $router->ros_version }}</span></small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Customers</small>
                                <small class="fw-semibold">{{ $router->customers_count }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Last Seen</small>
                                <small>{{ $router->last_seen ? $router->last_seen->diffForHumans() : 'Never' }}</small>
                            </div>
                            @if($router->address)
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Location</small>
                                <small>{{ Str::limit($router->address, 20) }}</small>
                            </div>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('routers.show', $router) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detail
                            </a>

                            <div class="btn-group">
                                @can('access_router')
                                <form action="{{ route('routers.test', $router) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                        <i class="bi bi-wifi"></i> Test
                                    </button>
                                </form>
                                @endcan

                                @can('edit_router')
                                <a href="{{ route('routers.edit', $router) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                @endcan

                                @can('delete_router')
                                @if($router->customers_count > 0)
                                    <button type="button" class="btn btn-outline-danger" disabled
                                            data-bs-toggle="tooltip"
                                            title="Cannot delete: {{ $router->customers_count }} customers connected">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <form action="{{ route('routers.destroy', $router) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('⚠️ Delete Router: {{ $router->name }}?\n\nIP: {{ $router->ip_address }}\n\nThis action cannot be undone!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Router">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Menampilkan {{ $routers->firstItem() }} - {{ $routers->lastItem() }} dari {{ $routers->total() }} routers
            </div>
            <div>
                {{ $routers->links() }}
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-router" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">Belum Ada Router</h5>
            <p class="text-muted">Klik tombol "Tambah Router" untuk menambah router baru.</p>
        </div>
    @endif
</div>
@endsection
