@extends('layouts.admin')

@section('title', 'Package Management')
@section('page-title', 'Package Management')

@section('content')
<div class="custom-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Daftar Paket Internet</h5>
        @can('create_package')
        <a href="{{ route('packages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Paket
        </a>
        @endcan
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('packages.index') }}">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama paket..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="billing_cycle" class="form-select">
                    <option value="">Semua Billing Cycle</option>
                    <option value="daily" {{ request('billing_cycle') == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ request('billing_cycle') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ request('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ request('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </div>
    </form>

    @if($packages->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Paket</th>
                        <th>Speed</th>
                        <th>Harga</th>
                        <th>FUP</th>
                        <th>Billing</th>
                        <th>Customers</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $package->name }}</div>
                            <small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $package->getSpeedLabel() }}</span>
                        </td>
                        <td>
                            <strong>{{ $package->getFormattedPrice() }}</strong>
                        </td>
                        <td>
                            @if($package->has_fup)
                                <span class="badge bg-warning text-dark">{{ $package->fup_quota }} GB</span>
                            @else
                                <span class="badge bg-success">Unlimited</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ ucfirst($package->billing_cycle) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $package->customers_count }} users</span>
                        </td>
                        <td>
                            @if($package->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('edit_package')
                                <a href="{{ route('packages.edit', $package) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan

                                @can('delete_package')
                                <form action="{{ route('packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket ini?')">
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
                Menampilkan {{ $packages->firstItem() }} - {{ $packages->lastItem() }} dari {{ $packages->total() }} packages
            </div>
            <div>
                {{ $packages->links() }}
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-box-seam" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">Belum Ada Paket</h5>
            <p class="text-muted">Klik tombol "Tambah Paket" untuk menambah paket baru.</p>
        </div>
    @endif
</div>
@endsection
