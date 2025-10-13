@extends('layouts.admin')

@section('title', 'Edit Access Point')
@section('page-title', 'Edit Access Point')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #mapPicker {
        height: 400px;
        width: 100%;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Edit Access Point: {{ $access_point->name }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('access-points.update', $access_point) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $access_point->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <select name="brand" class="form-select @error('brand') is-invalid @enderror">
                                <option value="">Select Brand</option>
                                <option value="Ubiquiti" {{ old('brand', $access_point->brand) == 'Ubiquiti' ? 'selected' : '' }}>Ubiquiti</option>
                                <option value="TP-Link" {{ old('brand', $access_point->brand) == 'TP-Link' ? 'selected' : '' }}>TP-Link</option>
                                <option value="MikroTik" {{ old('brand', $access_point->brand) == 'MikroTik' ? 'selected' : '' }}>MikroTik</option>
                                <option value="Cisco" {{ old('brand', $access_point->brand) == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="Aruba" {{ old('brand', $access_point->brand) == 'Aruba' ? 'selected' : '' }}>Aruba</option>
                                <option value="Other" {{ old('brand', $access_point->brand) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                   value="{{ old('model', $access_point->model) }}">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="ip_address" id="ip_address"
                                       class="form-control @error('ip_address') is-invalid @enderror"
                                       value="{{ old('ip_address', $access_point->ip_address) }}" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="testPing()">
                                    <i class="bi bi-wifi"></i> Test
                                </button>
                                @error('ip_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">MAC Address</label>
                            <div class="input-group">
                                <input type="text" name="mac_address" id="mac_address"
                                       class="form-control @error('mac_address') is-invalid @enderror"
                                       value="{{ old('mac_address', $access_point->mac_address) }}"
                                       placeholder="00:11:22:33:44:55">
                                <button type="button" class="btn btn-outline-secondary" onclick="detectMAC()">
                                    <i class="bi bi-search"></i> Detect
                                </button>
                                @error('mac_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">SSID</label>
                            <input type="text" name="ssid" class="form-control @error('ssid') is-invalid @enderror"
                                   value="{{ old('ssid', $access_point->ssid) }}" placeholder="MyWiFi">
                            @error('ssid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">WiFi Password</label>
                            <input type="text" name="wifi_password" class="form-control @error('wifi_password') is-invalid @enderror"
                                   value="{{ old('wifi_password', $access_point->wifi_password) }}">
                            @error('wifi_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-select @error('frequency') is-invalid @enderror">
                                <option value="">Select Frequency</option>
                                <option value="2.4GHz" {{ old('frequency', $access_point->frequency) == '2.4GHz' ? 'selected' : '' }}>2.4 GHz</option>
                                <option value="5GHz" {{ old('frequency', $access_point->frequency) == '5GHz' ? 'selected' : '' }}>5 GHz</option>
                                <option value="6GHz" {{ old('frequency', $access_point->frequency) == '6GHz' ? 'selected' : '' }}>6 GHz</option>
                                <option value="Dual Band" {{ old('frequency', $access_point->frequency) == 'Dual Band' ? 'selected' : '' }}>Dual Band</option>
                                <option value="Tri Band" {{ old('frequency', $access_point->frequency) == 'Tri Band' ? 'selected' : '' }}>Tri Band</option>
                            </select>
                            @error('frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Max Clients</label>
                            <input type="number" name="max_clients" class="form-control @error('max_clients') is-invalid @enderror"
                                   value="{{ old('max_clients', $access_point->max_clients) }}" min="1">
                            @error('max_clients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Connected Clients</label>
                            <input type="number" name="connected_clients" class="form-control @error('connected_clients') is-invalid @enderror"
                                   value="{{ old('connected_clients', $access_point->connected_clients) }}" min="0">
                            @error('connected_clients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $access_point->username) }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   value="{{ old('password', $access_point->password) }}">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">SSH Port</label>
                            <input type="number" name="ssh_port" class="form-control @error('ssh_port') is-invalid @enderror"
                                   value="{{ old('ssh_port', $access_point->ssh_port ?? 22) }}" min="1" max="65535">
                            @error('ssh_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="online" {{ old('status', $access_point->status) == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="offline" {{ old('status', $access_point->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                                <option value="maintenance" {{ old('status', $access_point->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', $access_point->is_active) ? 'checked' : '' }} value="1">
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude', $access_point->latitude) }}" placeholder="Latitude" required readonly>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude', $access_point->longitude) }}" placeholder="Longitude" required readonly>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="mapPicker"></div>
                            <small class="text-muted">Click on map to update location or drag marker</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2">{{ old('address', $access_point->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3">{{ old('notes', $access_point->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Access Point
                        </button>
                        <a href="{{ route('access-points.show', $access_point) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <form action="{{ route('access-points.destroy', $access_point) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Delete {{ $access_point->name }}? This action cannot be undone.')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Quick Actions</h6>
                <button type="button" class="btn btn-success w-100 mb-2" onclick="testPing()">
                    <i class="bi bi-wifi"></i> Test Connection
                </button>
                <button type="button" class="btn btn-info w-100 mb-2" onclick="detectMAC()">
                    <i class="bi bi-search"></i> Detect MAC Address
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tips</h6>
                <ul class="small mb-0">
                    <li>Test connection before saving</li>
                    <li>MAC address can be auto-detected if device is pingable</li>
                    <li>Click or drag marker to update location</li>
                    <li>Set status to 'Maintenance' during updates</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize map
const map = L.map('mapPicker').setView([{{ $access_point->latitude ?? -8.6705 }}, {{ $access_point->longitude ?? 115.2126 }}], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Marker
let marker = L.marker([{{ $access_point->latitude ?? -8.6705 }}, {{ $access_point->longitude ?? 115.2126 }}], {
    draggable: true
}).addTo(map);

// Update coordinates on marker drag
marker.on('dragend', function(e) {
    const position = marker.getLatLng();
    document.getElementById('latitude').value = position.lat.toFixed(6);
    document.getElementById('longitude').value = position.lng.toFixed(6);
});

// Click on map to set marker
map.on('click', function(e) {
    marker.setLatLng(e.latlng);
    document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
    document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
});

// Test ping
function testPing() {
    const ip = document.getElementById('ip_address').value;

    if (!ip) {
        alert('Please enter IP address first');
        return;
    }

    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Testing...';

    fetch('/access-points/ping-test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ip: ip })
    })
    .then(r => r.json())
    .then(data => {
        if (data.online) {
            alert(`✅ ${ip} is reachable!\nLatency: ${data.latency ? data.latency.toFixed(1) + ' ms' : 'N/A'}`);
        } else {
            alert(`❌ ${ip} is unreachable or offline`);
        }
    })
    .catch(err => {
        alert('⚠️ Test failed: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}

// Detect MAC address
function detectMAC() {
    const ip = document.getElementById('ip_address').value;

    if (!ip) {
        alert('Please enter IP address first');
        return;
    }

    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Detecting...';

    fetch('/access-points/get-mac', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ip: ip })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.mac) {
            document.getElementById('mac_address').value = data.mac;
            alert(`✅ MAC Address detected: ${data.mac}`);
        } else {
            alert('❌ MAC Address not found in ARP table.\nMake sure the device is on the same network and has been recently pinged.');
        }
    })
    .catch(err => {
        alert('⚠️ Detection failed: ' + err.message);
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
