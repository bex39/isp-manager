@extends('layouts.admin')

@section('title', 'Add ONT')
@section('page-title', 'Add ONT')

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
                <h6 class="fw-bold mb-0">ONT Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('onts.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">ONT Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="e.g., ONT-001">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Serial Number (SN) <span class="text-danger">*</span></label>
                            <input type="text" name="sn" class="form-control @error('sn') is-invalid @enderror"
                                   value="{{ old('sn') }}" required placeholder="e.g., ZTEG12345678">
                            @error('sn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- OLT Selection -->
                        <div class="col-md-6">
                            <label class="form-label">OLT <span class="text-danger">*</span></label>
                            <select name="olt_id" id="olt_id" class="form-select @error('olt_id') is-invalid @enderror" required>
                                <option value="">Select OLT</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                                        {{ $olt->name }} ({{ $olt->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                            @error('olt_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Customer Selection -->
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                <option value="">Select Customer (Optional)</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ODP Selection -->
                        <div class="col-md-6">
                            <label class="form-label">ODP</label>
                            <select name="odp_id" id="odp_id" class="form-select @error('odp_id') is-invalid @enderror" onchange="loadODPInfo(this.value)">
                                <option value="">Select ODP (Optional)</option>
                                @foreach($odps as $odp)
                                    <option value="{{ $odp->id }}"
                                            data-available="{{ $odp->getAvailablePorts() }}"
                                            data-total="{{ $odp->total_ports }}"
                                            {{ old('odp_id') == $odp->id ? 'selected' : '' }}>
                                        {{ $odp->name }} ({{ $odp->code }}) - {{ $odp->getAvailablePorts() }}/{{ $odp->total_ports }} available
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="odpInfo"></small>
                            @error('odp_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ODP Port -->
                        <div class="col-md-6">
                            <label class="form-label">ODP Port</label>
                            <input type="number" name="odp_port" id="odp_port"
                                   class="form-control @error('odp_port') is-invalid @enderror"
                                   value="{{ old('odp_port') }}"
                                   min="1"
                                   placeholder="Auto-assign if empty">
                            <small class="text-muted">Leave empty to auto-assign next available port</small>
                            @error('odp_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- PON Configuration -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">PON Configuration</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PON Type</label>
                            <select name="pon_type" class="form-select @error('pon_type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="GPON" {{ old('pon_type') == 'GPON' ? 'selected' : '' }}>GPON</option>
                                <option value="EPON" {{ old('pon_type') == 'EPON' ? 'selected' : '' }}>EPON</option>
                            </select>
                            @error('pon_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PON Port</label>
                            <input type="number" name="pon_port" class="form-control @error('pon_port') is-invalid @enderror"
                                   value="{{ old('pon_port') }}" min="0" placeholder="e.g., 1">
                            @error('pon_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ONT ID</label>
                            <input type="number" name="ont_id" class="form-control @error('ont_id') is-invalid @enderror"
                                   value="{{ old('ont_id') }}" min="0" placeholder="e.g., 1">
                            @error('ont_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Device Info -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Device Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                   value="{{ old('model') }}" placeholder="e.g., HG8310M">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Management IP</label>
                            <input type="text" name="management_ip" class="form-control @error('management_ip') is-invalid @enderror"
                                   value="{{ old('management_ip') }}" placeholder="192.168.1.1">
                            @error('management_ip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', 'admin') }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   value="{{ old('password') }}">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- WiFi Configuration -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">WiFi Configuration</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">WiFi SSID</label>
                            <input type="text" name="wifi_ssid" class="form-control @error('wifi_ssid') is-invalid @enderror"
                                   value="{{ old('wifi_ssid') }}" placeholder="MyWiFi">
                            @error('wifi_ssid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">WiFi Password</label>
                            <input type="text" name="wifi_password" class="form-control @error('wifi_password') is-invalid @enderror"
                                   value="{{ old('wifi_password') }}" placeholder="Min. 8 characters">
                            @error('wifi_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Location</h6>
                        </div>

                        <div class="col-12">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" name="latitude" id="latitude"
                                           class="form-control @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude', -8.6705) }}"
                                           placeholder="Latitude" readonly>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="longitude" id="longitude"
                                           class="form-control @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude', 115.2126) }}"
                                           placeholder="Longitude" readonly>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="mapPicker"></div>
                            <small class="text-muted">Click on map to set location or drag marker</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Info -->
                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date"
                                   class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }} value="1">
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save ONT
                        </button>
                        <a href="{{ route('onts.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Quick Tips</h6>
                <ul class="small">
                    <li>Serial Number (SN) must be unique</li>
                    <li>Select ODP to auto-assign port</li>
                    <li>PON Port & ONT ID required for auto-provisioning</li>
                    <li>Click map to set ONT location</li>
                    <li>WiFi password minimum 8 characters</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3" id="odpStatusCard" style="display: none;">
            <div class="card-body">
                <h6 class="fw-bold mb-3">ODP Status</h6>
                <div id="odpStatusContent"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize map
const map = L.map('mapPicker').setView([-8.6705, 115.2126], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Marker
let marker = L.marker([-8.6705, 115.2126], {
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

// Set initial marker from input values
const initLat = parseFloat(document.getElementById('latitude').value);
const initLng = parseFloat(document.getElementById('longitude').value);
if (initLat && initLng) {
    marker.setLatLng([initLat, initLng]);
    map.setView([initLat, initLng], 13);
}

// ODP Info Display
function loadODPInfo(odpId) {
    const select = document.getElementById('odp_id');
    const option = select.querySelector(`option[value="${odpId}"]`);
    const infoDiv = document.getElementById('odpInfo');
    const statusCard = document.getElementById('odpStatusCard');
    const statusContent = document.getElementById('odpStatusContent');

    if (option && odpId) {
        const available = parseInt(option.dataset.available);
        const total = parseInt(option.dataset.total);
        const used = total - available;
        const percentage = ((used / total) * 100).toFixed(1);

        if (available == 0) {
            infoDiv.innerHTML = '<span class="text-danger">⚠️ ODP is full!</span>';
            document.getElementById('odp_port').disabled = true;
            statusCard.style.display = 'block';
            statusContent.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle"></i> ODP is full!<br>
                    <small>All ${total} ports are occupied</small>
                </div>
            `;
        } else {
            infoDiv.innerHTML = `<span class="text-success">✓ ${available} port(s) available</span>`;
            document.getElementById('odp_port').disabled = false;
            statusCard.style.display = 'block';

            const badgeClass = percentage >= 80 ? 'bg-danger' : (percentage >= 60 ? 'bg-warning' : 'bg-success');
            statusContent.innerHTML = `
                <p class="mb-2"><strong>Port Usage:</strong></p>
                <div class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar ${badgeClass}" style="width: ${percentage}%">
                        ${used}/${total}
                    </div>
                </div>
                <small class="text-muted">${available} ports available</small>
            `;
        }
    } else {
        infoDiv.innerHTML = '';
        statusCard.style.display = 'none';
        document.getElementById('odp_port').disabled = false;
    }
}

// Load info on page load if ODP selected
document.addEventListener('DOMContentLoaded', function() {
    const odpId = document.getElementById('odp_id').value;
    if (odpId) {
        loadODPInfo(odpId);
    }
});
</script>
@endpush
@endsection
