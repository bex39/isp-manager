@extends('layouts.admin')

@section('title', 'Detail Paket')
@section('page-title', 'Detail Paket')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="custom-table mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">{{ $package->name }}</h5>
                <div class="d-flex gap-2">
                    @can('edit_package')
                    <a href="{{ route('packages.edit', $package) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @endcan
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <p class="text-muted">{{ $package->description }}</p>

            <hr>

            <h6 class="fw-bold mb-3">Spesifikasi</h6>
            <table class="table table-borderless">
                <tr>
                    <td width="200" class="fw-semibold">Download Speed</td>
                    <td>: {{ $package->download_speed }} Mbps</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Upload Speed</td>
                    <td>: {{ $package->upload_speed }} Mbps</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Harga</td>
                    <td>: <strong class="text-primary">{{ $package->getFormattedPrice() }}</strong></td>
                </tr>
                <tr>
                    <td class="fw-semibold">Billing Cycle</td>
                    <td>: {{ ucfirst($package->billing_cycle) }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Grace Period</td>
                    <td>: {{ $package->grace_period }} hari</td>
                </tr>
            </table>

            <hr>

            <h6 class="fw-bold mb-3">FUP (Fair Usage Policy)</h6>
            @if($package->has_fup)
                <table class="table table-borderless">
                    <tr>
                        <td width="200" class="fw-semibold">Status FUP</td>
                        <td>: <span class="badge bg-warning text-dark">Aktif</span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Kuota</td>
                        <td>: {{ $package->fup_quota }} GB</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Speed Setelah FUP</td>
                        <td>: {{ $package->fup_speed }} Mbps</td>
                    </tr>
                </table>
            @else
                <p class="text-muted">Paket ini tidak menggunakan FUP (Unlimited)</p>
            @endif

            <hr>

            <h6 class="fw-bold mb-3">Pengaturan Lanjutan</h6>
            <table class="table table-borderless">
                <tr>
                    <td width="200" class="fw-semibold">Burst Limit</td>
                    <td>: {{ $package->burst_limit ? $package->burst_limit . ' Mbps' : 'Tidak diset' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Priority (QoS)</td>
                    <td>: {{ $package->priority }}/10</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Connection Limit</td>
                    <td>: {{ $package->connection_limit ?? 'Unlimited' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Tersedia Untuk</td>
                    <td>:
                        @if($package->available_for)
                            @foreach($package->available_for as $type)
                                <span class="badge bg-secondary">{{ ucfirst($type) }}</span>
                            @endforeach
                        @else
                            Semua tipe koneksi
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="fw-semibold">Status</td>
                    <td>:
                        @if($package->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Statistik</h6>
            <div class="text-center py-3">
                <h2 class="mb-0">{{ $package->customers_count }}</h2>
                <p class="text-muted mb-0">Customer Aktif</p>
            </div>
        </div>

        <div class="custom-table">
            <h6 class="fw-bold mb-3">Timeline</h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <small class="text-muted">Dibuat</small><br>
                    {{ $package->created_at->format('d M Y H:i') }}
                </li>
                <li class="mb-2">
                    <small class="text-muted">Last Update</small><br>
                    {{ $package->updated_at->format('d M Y H:i') }}
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
