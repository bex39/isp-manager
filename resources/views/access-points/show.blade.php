@extends('layouts.admin')

@section('title', 'Access Point: ' . $ap->name)
@section('page-title', 'Access Point Details')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #apMap {
        height: 400px;
        width: 100%;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $ap->name }}</h4>
        <p class="text-muted mb-0">
            <i class="bi bi-wifi"></i> {{ $ap->ssid ?? 'No SSID' }} |
            <code>{{ $ap->ip_address }}</code>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-success btn-sm" onclick="pingAP()" id="pingBtn">
            <i class="bi bi-wifi"></i> Ping Test
        </button>
        <a href="{{ route('access-points.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('access-points.edit', $ap) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Status Card -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div id="statusIndicator">
                    @if($ap->status === 'online')
                        <i class="bi bi-wifi text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-2 fw-bold text-success">Online</h5>
                    @elseif($ap->status === 'maintenance')
                        <i class="bi bi-tools text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-2 fw-bold text-warning">Maintenance</h5>
                    @else
                        <i class="bi bi-wifi-off text-danger" style="font-size: 3rem;"></i>
                        <h5 class="mt-2 fw-bold text-danger">Offline</h5>
                    @endif
                </div>
                @if($ap->last_seen)
                    <small class="text-muted">Last seen: {{ $ap->last_seen->diffForHumans() }}</small>
                @else
                    <small class="text-muted">Never seen online</small>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-speedometer2 text-primary" style="font-size: 3rem;"></i>
                <h5 class="mt-2 fw-bold" id="latencyDisplay">
                    {{ $ap->ping_latency ? number_format($ap->ping_latency, 1) . ' ms' : '-' }}
                </h5>
                <small class="text-muted">Latency</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-people text-info" style="font-size: 3rem;"></i>
                <h5 class="mt-2 fw-bold">
                    {{ $ap->connected_clients ?? 0 }} / {{ $ap->max_clients ?? '∞' }}
                </h5>
                <small class="text-muted">Connected Clients</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                @if($ap->frequency)
                    <i class="bi bi-broadcast text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold">{{ $ap->frequency }}</h5>
                    <small class="text-muted">Frequency</small>
                @else
                    <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold">-</h5>
                    <small class="text-muted">Frequency</small>
                @endif
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
                        <td width="35%" class="text-muted">Name</td>
                        <td><strong>{{ $ap->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Brand</td>
                        <td>
                            @if($ap->brand)
                                <span class="badge" style="background-color: {{
                                    $ap->brand == 'Ubiquiti' ? '#0097e6' :
                                    ($ap->brand == 'TP-Link' ? '#27ae60' :
                                    ($ap->brand == 'MikroTik' ? '#e74c3c' : '#95a5a6'))
                                }}">
                                    {{ $ap->brand }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Model</td>
                        <td>{{ $ap->model ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP Address</td>
                        <td>
                            <code>{{ $ap->ip_address }}</code>
                            <button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ $ap->ip_address }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">MAC Address</td>
                        <td>
                            @if($ap->mac_address)
                                <code>{{ $ap->mac_address }}</code>
                                <button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ $ap->mac_address }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">SSH Port</td>
                        <td>{{ $ap->ssh_port ?? 22 }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($ap->is_active)
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

    <!-- WiFi Configuration -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">WiFi Configuration</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="35%" class="text-muted">SSID</td>
                        <td>
                            @if($ap->ssid)
                                <strong>{{ $ap->ssid }}</strong>
                                <button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ $ap->ssid }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">WiFi Password</td>
                        <td>
                            @if($ap->wifi_password)
                                <code id="wifiPassword">{{ str_repeat('•', strlen($ap->wifi_password)) }}</code>
                                <button class="btn btn-sm btn-link p-0" onclick="togglePassword()" id="toggleBtn">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ $ap->wifi_password }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Frequency</td>
                        <td>
                            @if($ap->frequency)
                                <span class="badge bg-secondary">{{ $ap->frequency }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Max Clients</td>
                        <td>{{ $ap->max_clients ?? 'Unlimited' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Connected Clients</td>
                        <td><span class="badge bg-primary">{{ $ap->connected_clients ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Username</td>
                        <td>{{ $ap->username ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Password</td>
                        <td>
                            @if($ap->password)
                                <code>{{ str_repeat('•', 8) }}</code>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Location</h6>
            </div>
            <div class="card-body">
                @if($ap->latitude && $ap->longitude)
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Coordinates:</strong><br>
                            <code>{{ $ap->latitude }}, {{ $ap->longitude }}</code>
                        </div>
                        <div class="col-md-8">
                            <strong>Address:</strong><br>
                            {{ $ap->address ?? $ap->location ?? '-' }}
                        </div>
                    </div>
                    <div id="apMap"></div>
                @else
                    <p class="text-muted mb-0">No location data available</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($ap->notes)
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $ap->notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize map
@if($ap->latitude && $ap->longitude)
const map = L.map('apMap').setView([{{ $ap->latitude }}, {{ $ap->longitude }}], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Custom icon
const apIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #8b5cf6; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-wifi text-white" style="font-size: 20px;"></i></div>',
    iconSize: [40, 40],
    iconAnchor: [20, 20]
});

L.marker([{{ $ap->latitude }}, {{ $ap->longitude }}], {icon: apIcon})
    .bindPopup('<strong>{{ $ap->name }}</strong><br>{{ $ap->ssid ?? "No SSID" }}')
    .addTo(map);
@endif

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied: ' + text);
    });
}

// Toggle password visibility
let passwordVisible = false;
function togglePassword() {
    const passwordEl = document.getElementById('wifiPassword');
    const toggleBtn = document.getElementById('toggleBtn');

    if (passwordVisible) {
        passwordEl.textContent = '{{ str_repeat("•", strlen($ap->wifi_password ?? "")) }}';
        toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
    } else {
        passwordEl.textContent = '{{ $ap->wifi_password }}';
        toggleBtn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    }
    passwordVisible = !passwordVisible;
}

// Ping test
function pingAP() {
    const btn = document.getElementById('pingBtn');
    const originalHTML = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Pinging...';

    fetch('/access-points/{{ $ap->id }}/ping', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update status
            const statusDiv = document.getElementById('statusIndicator');
            if (data.status === 'online') {
                statusDiv.innerHTML = `
                    <i class="bi bi-wifi text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold text-success">Online</h5>
                `;
            } else {
                statusDiv.innerHTML = `
                    <i class="bi bi-wifi-off text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold text-danger">Offline</h5>
                `;
            }

            // Update latency
            const latencyDiv = document.getElementById('latencyDisplay');
            latencyDiv.textContent = data.latency ? data.latency.toFixed(1) + ' ms' : '-';

            alert(data.status === 'online'
                ? `✅ Access Point is online (${data.latency ? data.latency.toFixed(1) + ' ms' : 'N/A'})`
                : '❌ Access Point is offline or unreachable');

            // Reload to update last_seen
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(err => {
        alert('⚠️ Ping test failed: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
@endsection
