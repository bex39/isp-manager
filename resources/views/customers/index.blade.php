@extends('layouts.admin')

@section('title', 'Customer Management')
@section('page-title', 'Customer Management')

@section('content')
<div class="custom-table">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Daftar Customer</h5>
        @can('create_customer')
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Customer
        </a>
        @endcan
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('customers.index') }}">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="connection_type" class="form-select">
                    <option value="">Semua Tipe Koneksi</option>
                    <option value="pppoe_direct" {{ request('connection_type') == 'pppoe_direct' ? 'selected' : '' }}>PPPoE Direct</option>
                    <option value="pppoe_mikrotik" {{ request('connection_type') == 'pppoe_mikrotik' ? 'selected' : '' }}>PPPoE via MikroTik</option>
                    <option value="static_ip" {{ request('connection_type') == 'static_ip' ? 'selected' : '' }}>Static IP</option>
                    <option value="hotspot" {{ request('connection_type') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                    <option value="dhcp" {{ request('connection_type') == 'dhcp' ? 'selected' : '' }}>DHCP</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="package_id" class="form-select">
                    <option value="">Semua Paket</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }}
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

    @if($customers->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Customer Code</th>
                        <th>Nama</th>
                        <th>Phone</th>
                        <th>Package</th>
                        <th>Tipe Koneksi</th>
                        <th>Status</th>
                        <th>Billing Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">{{ $customer->customer_code }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                            <small class="text-muted">{{ $customer->email }}</small>
                        </td>
                        <td>{{ $customer->phone }}</td>
                        <td>
                            @if($customer->package)
                                <span class="badge bg-info">{{ $customer->package->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $customer->getConnectionTypeLabel() }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $customer->getStatusBadgeClass() }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $customer->next_billing_date ? $customer->next_billing_date->format('d M Y') : '-' }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('edit_customer')
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan

                                @can('suspend_customer')
                                @if($customer->status === 'active')
                                <form action="{{ route('customers.suspend', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning" title="Suspend">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan

                                @can('activate_customer')
                                @if($customer->status === 'suspended')
                                <form action="{{ route('customers.activate', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Activate">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan

                                @can('delete_customer')
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus customer ini?')">
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
                Menampilkan {{ $customers->firstItem() }} - {{ $customers->lastItem() }} dari {{ $customers->total() }} customers
            </div>
            <div>
                {{ $customers->links() }}
            </div>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-person-badge" style="font-size: 4rem; color: #ccc;"></i>
            <h5 class="mt-3">Belum Ada Customer</h5>
            <p class="text-muted">Klik tombol "Tambah Customer" untuk menambah customer baru.</p>
        </div>
    @endif
</div>
@endsection
