@extends('layouts.admin')

@section('title', 'ONT: ' . $ont->name)
@section('page-title', 'ONT Details')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #ontMap {
        height: 300px;
        width: 100%;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $ont->name }}</h4>
        <p class="text-muted mb-0">
            SN: <code>{{ $ont->sn }}</code> |
            OLT: {{ $ont->olt->name ?? 'N/A' }}
            @if($ont->odp)
                | ODP: {{ $ont->odp->name }} (Port {{ $ont->odp_port }})
            @endif
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('onts.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('onts.edit', $ont) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Status Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                @if($ont->status === 'online')
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold text-success">Online</h5>
                @elseif($ont->status === 'los')
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold text-danger">LOS</h5>
                @else
                    <i class="bi bi-x-circle text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold text-secondary">{{ ucfirst($ont->status) }}</h5>
                @endif
                @if($ont->last_seen)
                    <small class="text-muted">{{ $ont->last_seen->diffForHumans() }}</small>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-reception-4 text-primary" style="font-size: 3rem;"></i>
                <h5 class="mt-2 fw-bold">
                    @if($ont->rx_power)
                        {{ $ont->rx_power }} dBm
                    @else
                        N/A
                    @endif
                </h5>
                <small class="text-muted">RX Power</small>
                @if($ont->rx_power)
                    <br><span class="{{ $ont->getSignalBadgeClass() }}">{{ ucfirst($ont->getSignalQuality()) }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-broadcast text-info" style="font-size: 3rem;"></i>
                <h5 class="mt-2 fw-bold">
                    @if($ont->tx_power)
                        {{ $ont->tx_power }} dBm
                    @else
                        N/A
                    @endif
                </h5>
                <small class="text-muted">TX Power</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-speedometer text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-2 fw-bold">
                    @if($ont->distance)
                        {{ number_format($ont->distance, 2) }} km
                    @else
                        N/A
                    @endif
                </h5>
                <small class="text-muted">Distance</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Device Information -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Device Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%" class="text-muted">Name</td>
                        <td><strong>{{ $ont->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Serial Number</td>
                        <td><code>{{ $ont->sn }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Model</td>
                        <td>{{ $ont->model ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">OLT</td>
                        <td>
                            @if($ont->olt)
                                <a href="{{ route('olts.show', $ont->olt) }}">{{ $ont->olt->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">ODP</td>
                        <td>
                            @if($ont->odp)
                                <a href="{{ route('odps.show', $ont->odp) }}">
                                    {{ $ont->odp->name }} - Port {{ $ont->odp_port }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">PON Port / ONT ID</td>
                        <td>
                            @if($ont->pon_port && $ont->ont_id)
                                {{ $ont->pon_port }} / {{ $ont->ont_id }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Management IP</td>
                        <td>{{ $ont->management_ip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Installation Date</td>
                        <td>{{ $ont->installation_date ? $ont->installation_date->format('d M Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer & WiFi Info -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Customer & WiFi</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%" class="text-muted">Customer</td>
                        <td>
                            @if($ont->customer)
                                <a href="{{ route('customers.show', $ont->customer) }}">
                                    {{ $ont->customer->name }}
                                </a>
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">WiFi SSID</td>
                        <td>{{ $ont->wifi_ssid ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">WiFi Password</td>
                        <td>
                            @if($ont->wifi_password)
                                <code>{{ $ont->wifi_password }}</code>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Username</td>
                        <td>{{ $ont->username ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($ont->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Location Map -->
    @if($ont->latitude && $ont->longitude)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Location</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Coordinates:</strong> {{ $ont->latitude }}, {{ $ont->longitude }}
                </p>
                @if($ont->address)
                    <p class="mb-2"><strong>Address:</strong> {{ $ont->address }}</p>
                @endif
                <div id="ontMap"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($ont->notes)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $ont->notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
@if($ont->latitude && $ont->longitude)
// Initialize map
const map = L.map('ontMap').setView([{{ $ont->latitude }}, {{ $ont->longitude }}], 16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Custom ONT icon
const ontIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #f59e0b; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-modem text-white" style="font-size: 20px;"></i></div>',
    iconSize: [40, 40],
    iconAnchor: [20, 20]
});

L.marker([{{ $ont->latitude }}, {{ $ont->longitude }}], {icon: ontIcon})
    .bindPopup('<strong>{{ $ont->name }}</strong><br>{{ $ont->customer->name ?? "Unassigned" }}')
    .addTo(map);
@endif
</script>
@endpush
@endsection
