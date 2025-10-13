@extends('layouts.admin')

@section('title', 'Detail Customer')
@section('page-title', 'Detail Customer')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="custom-table mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Informasi Customer</h5>
                <div class="d-flex gap-2">
                    @can('edit_customer')
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">
                        Edit
                    </a>
                    @endcan
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                        Kembali
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="180" class="fw-semibold">Customer Code</td>
                            <td>: <span class="badge bg-secondary">{{ $customer->customer_code }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Nama Lengkap</td>
                            <td>: {{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Email</td>
                            <td>: {{ $customer->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">No. Telepon</td>
                            <td>: {{ $customer->phone }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">No. KTP</td>
                            <td>: {{ $customer->id_card_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Status</td>
                            <td>: <span class="badge {{ $customer->getStatusBadgeClass() }}">{{ ucfirst($customer->status) }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="180" class="fw-semibold">Alamat</td>
                            <td>: {{ $customer->address }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Koordinat GPS</td>
                            <td>: {{ $customer->latitude && $customer->longitude ? $customer->latitude . ', ' . $customer->longitude : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Tanggal Instalasi</td>
                            <td>: {{ $customer->installation_date?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Next Billing</td>
                            <td>: {{ $customer->next_billing_date?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Teknisi</td>
                            <td>: {{ $customer->teknisi?->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Konfigurasi Koneksi</h6>
            <table class="table table-borderless">
                <tr>
                    <td width="200" class="fw-semibold">Tipe Koneksi</td>
                    <td>: <span class="badge bg-info">{{ $customer->getConnectionTypeLabel() }}</span></td>
                </tr>
                <tr>
                    <td class="fw-semibold">Paket</td>
                    <td>: {{ $customer->package?->name ?? '-' }} ({{ $customer->package?->getSpeedLabel() ?? '-' }})</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Router</td>
                    <td>: {{ $customer->router?->name ?? '-' }} ({{ $customer->router?->ip_address ?? '-' }})</td>
                </tr>

                @if($customer->connection_type == 'pppoe_direct' || $customer->connection_type == 'pppoe_mikrotik')
                <tr>
                    <td class="fw-semibold">PPPoE Username</td>
                    <td>: {{ $customer->connection_config['username'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">PPPoE Password</td>
                    <td>: {{ $customer->connection_config['password'] ?? '-' }}</td>
                </tr>
                @endif

                @if($customer->connection_type == 'static_ip')
                <tr>
                    <td class="fw-semibold">IP Address</td>
                    <td>: {{ $customer->connection_config['ip'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Subnet / Gateway</td>
                    <td>: {{ $customer->connection_config['subnet'] ?? '-' }} / {{ $customer->connection_config['gateway'] ?? '-' }}</td>
                </tr>
                @endif

                @if($customer->connection_type == 'pppoe_mikrotik')
                <tr>
                    <td class="fw-semibold">Customer MikroTik IP</td>
                    <td>: {{ $customer->customer_mikrotik_ip ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">MikroTik Username</td>
                    <td>: {{ $customer->customer_mikrotik_username ?? '-' }}</td>
                </tr>
                @endif
            </table>

            @if($customer->olt)
            <hr>
            <h6 class="fw-bold mb-3">Konfigurasi Fiber</h6>
            <table class="table table-borderless">
                <tr>
                    <td width="200" class="fw-semibold">OLT</td>
                    <td>: {{ $customer->olt->name }} ({{ $customer->olt->getOltTypeLabel() }})</td>
                </tr>
                <tr>
                    <td class="fw-semibold">ONT Serial Number</td>
                    <td>: {{ $customer->ont_serial_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">PON Port</td>
                    <td>: {{ $customer->pon_port ?? '-' }}</td>
                </tr>
            </table>
            @endif

            @if($customer->notes)
            <hr>
            <h6 class="fw-bold mb-3">Catatan</h6>
            <p>{{ $customer->notes }}</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                @can('suspend_customer')
                @if($customer->status === 'active')
                <form action="{{ route('customers.suspend', $customer) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Suspend customer ini?')">
                        Suspend Customer
                    </button>
                </form>
                @endif
                @endcan

                @can('activate_customer')
                @if($customer->status === 'suspended')
                <form action="{{ route('customers.activate', $customer) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        Activate Customer
                    </button>
                </form>
                @endif
                @endcan

                <button class="btn btn-outline-primary" disabled>
                    View Usage (Coming Soon)
                </button>
                <button class="btn btn-outline-primary" disabled>
                    Create Invoice (Coming Soon)
                </button>
                <button class="btn btn-outline-primary" disabled>
                    Create Ticket (Coming Soon)
                </button>
            </div>
        </div>

        <div class="custom-table">
            <h6 class="fw-bold mb-3">Timeline</h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <small class="text-muted">Terdaftar</small><br>
                    {{ $customer->created_at->format('d M Y H:i') }}
                </li>
                <li class="mb-2">
                    <small class="text-muted">Last Update</small><br>
                    {{ $customer->updated_at->format('d M Y H:i') }}
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
