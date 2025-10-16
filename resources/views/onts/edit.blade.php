@extends('layouts.admin')

@section('title', 'Edit ONT')
@section('page-title', 'Edit ONT')

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
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('onts.show', $ont) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to ONT
        </a>
        <a href="{{ route('onts.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list"></i> All ONTs
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit ONT: {{ $ont->name }}</h5>
            </div>
            <div class="card-body">
                <!-- ✅ EDIT FORM START -->
                <form action="{{ route('onts.update', $ont) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary">Basic Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ONT Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $ont->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Serial Number (SN) <span class="text-danger">*</span></label>
                            <input type="text" name="sn" class="form-control @error('sn') is-invalid @enderror"
                                   value="{{ old('sn', $ont->sn) }}" required>
                            @error('sn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: HWTC12345678</small>
                        </div>

                        <!-- OLT Selection -->
                        <div class="col-md-6">
                            <label class="form-label">OLT <span class="text-danger">*</span></label>
                            <select name="olt_id" class="form-select @error('olt_id') is-invalid @enderror" required>
                                <option value="">Select OLT</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ old('olt_id', $ont->olt_id) == $olt->id ? 'selected' : '' }}>
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
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $ont->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ODP & Port -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">ODP Connection</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ODP</label>
                            <select name="odp_id" id="odp_id" class="form-select @error('odp_id') is-invalid @enderror" onchange="loadODPInfo(this.value)">
                                <option value="">Select ODP (Optional)</option>
                                @foreach($odps as $odp)
                                    <option value="{{ $odp->id }}"
                                            data-available="{{ $odp->getAvailablePorts() }}"
                                            data-total="{{ $odp->total_ports }}"
                                            {{ old('odp_id', $ont->odp_id) == $odp->id ? 'selected' : '' }}>
                                        {{ $odp->name }} ({{ $odp->code }}) - {{ $odp->total_ports - $odp->used_ports }}/{{ $odp->total_ports }} available
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="odpInfo"></small>
                            @error('odp_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ODP Port</label>
                            <input type="number" name="odp_port" id="odp_port"
                                   class="form-control @error('odp_port') is-invalid @enderror"
                                   value="{{ old('odp_port', $ont->odp_port) }}"
                                   min="1" placeholder="Port number (1-48)">
                            @error('odp_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- PON Configuration -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">PON Configuration</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PON Type</label>
                            <select name="pon_type" class="form-select @error('pon_type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="GPON" {{ old('pon_type', $ont->pon_type) == 'GPON' ? 'selected' : '' }}>GPON</option>
                                <option value="EPON" {{ old('pon_type', $ont->pon_type) == 'EPON' ? 'selected' : '' }}>EPON</option>
                            </select>
                            @error('pon_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PON Port</label>
                            <input type="number" name="pon_port" class="form-control @error('pon_port') is-invalid @enderror"
                                   value="{{ old('pon_port', $ont->pon_port) }}" min="0" placeholder="0-15">
                            @error('pon_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ONT ID</label>
                            <input type="number" name="ont_id" class="form-control @error('ont_id') is-invalid @enderror"
                                   value="{{ old('ont_id', $ont->ont_id) }}" min="0" placeholder="0-127">
                            @error('ont_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Device Info -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">Device Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                   value="{{ old('model', $ont->model) }}" placeholder="e.g., HG8245H">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Management IP</label>
                            <input type="text" name="management_ip" class="form-control @error('management_ip') is-invalid @enderror"
                                   value="{{ old('management_ip', $ont->management_ip) }}" placeholder="192.168.1.1">
                            @error('management_ip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $ont->username) }}" placeholder="admin">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   value="{{ old('password', $ont->password) }}" placeholder="Leave empty to keep current">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- WiFi Configuration -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">WiFi Configuration</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">WiFi SSID</label>
                            <input type="text" name="wifi_ssid" class="form-control @error('wifi_ssid') is-invalid @enderror"
                                   value="{{ old('wifi_ssid', $ont->wifi_ssid) }}" placeholder="MyWiFi">
                            @error('wifi_ssid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">WiFi Password</label>
                            <input type="text" name="wifi_password" class="form-control @error('wifi_password') is-invalid @enderror"
                                   value="{{ old('wifi_password', $ont->wifi_password) }}" placeholder="Min. 8 characters">
                            @error('wifi_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">Status</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="online" {{ old('status', $ont->status) == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="offline" {{ old('status', $ont->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                                <option value="disabled" {{ old('status', $ont->status) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                <option value="los" {{ old('status', $ont->status) == 'los' ? 'selected' : '' }}>LOS (Loss of Signal)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block">Active Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', $ont->is_active) ? 'checked' : '' }} value="1">
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">Location</h6>
                        </div>

                        <div class="col-12">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="latitude"
                                           class="form-control @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude', $ont->latitude) }}"
                                           placeholder="Latitude" readonly>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="longitude"
                                           class="form-control @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude', $ont->longitude) }}"
                                           placeholder="Longitude" readonly>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="mapPicker" class="mb-2"></div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Click on map to update location or drag the marker
                            </small>
                        </div>

                        <!--<div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2" placeholder="Full address">{{ old('address', $ont->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         Additional Info
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary">Additional Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date"
                                   class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date', $ont->installation_date ? $ont->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>-->

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes...">{{ old('notes', $ont->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- ✅ UPDATE BUTTONS -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update ONT
                        </button>
                        <a href="{{ route('onts.show', $ont) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
                <!-- ✅ EDIT FORM END -->

                <!-- ✅ DELETE FORM (SEPARATE) -->
                <hr class="my-4">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> Danger Zone
                    </h6>
                    <p class="mb-2 small">Deleting this ONT will:</p>
                    <ul class="small mb-3">
                        <li>Remove ONT from database permanently</li>
                        <li>Decrement ODP used ports</li>
                        <li>Attempt to unprovision from OLT</li>
                    </ul>
                    <form action="{{ route('onts.destroy', $ont) }}" method="POST" onsubmit="return confirmDelete()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Delete This ONT
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Current Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Current Status</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="40%" class="text-muted">Status:</td>
                        <td>
                            @if($ont->status === 'online')
                                <span class="badge bg-success">Online</span>
                            @elseif($ont->status === 'offline')
                                <span class="badge bg-secondary">Offline</span>
                            @elseif($ont->status === 'los')
                                <span class="badge bg-danger">LOS</span>
                            @else
                                <span class="badge bg-warning">{{ ucfirst($ont->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Signal:</td>
                        <td>
                            @if($ont->rx_power)
                                <span class="{{ $ont->getSignalBadgeClass() }}">
                                    {{ $ont->rx_power }} dBm
                                </span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Seen:</td>
                        <td>
                            @if($ont->last_seen)
                                {{ $ont->last_seen->diffForHumans() }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ODP Status Card -->
        <div class="card border-0 shadow-sm" id="odpStatusCard" style="display: none;">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">ODP Status</h6>
            </div>
            <div class="card-body">
                <div id="odpStatusContent"></div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Information
                </h6>
            </div>
            <div class="card-body">
                <small>
                    <strong>PON Configuration:</strong><br>
                    PON Port and ONT ID are required for auto-provisioning to OLT.
                    <hr class="my-2">
                    <strong>ODP Port:</strong><br>
                    Select ODP first to see available ports. Make sure port is not already in use.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ✅ DELETE CONFIRMATION
function confirmDelete() {
    return confirm('⚠️ DELETE ONT: {{ $ont->name }}?\n\n' +
                   '❌ This will:\n' +
                   '• Remove ONT from database permanently\n' +
                   '• Decrement ODP used ports\n' +
                   '• Attempt to unprovision from OLT\n\n' +
                   '⚠️ THIS ACTION CANNOT BE UNDONE!\n\n' +
                   'Type YES to confirm and press OK to proceed.');
}

// ✅ MAP INITIALIZATION
const defaultLat = {{ $ont->latitude ?? -8.6705 }};
const defaultLng = {{ $ont->longitude ?? 115.2126 }};

const map = L.map('mapPicker').setView([defaultLat, defaultLng], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Marker
let marker = L.marker([defaultLat, defaultLng], {
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

// ✅ ODP INFO DISPLAY
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

        // Adjust for current ONT if editing same ODP
        const currentOdpId = {{ $ont->odp_id ?? 'null' }};
        let adjustedAvailable = available;
        if (currentOdpId == odpId) {
            adjustedAvailable = available + 1; // Current ONT occupies 1 port
        }

        if (adjustedAvailable == 0) {
            infoDiv.innerHTML = '<span class="text-danger">⚠️ ODP is full!</span>';
            statusCard.style.display = 'block';
            statusContent.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle"></i> ODP is full!<br>
                    <small>All ${total} ports are occupied</small>
                </div>
            `;
        } else {
            infoDiv.innerHTML = `<span class="text-success">✓ ${adjustedAvailable} port(s) available</span>`;
            statusCard.style.display = 'block';

            const badgeClass = percentage >= 80 ? 'bg-danger' : (percentage >= 60 ? 'bg-warning' : 'bg-success');
            statusContent.innerHTML = `
                <p class="mb-2"><strong>Port Usage:</strong></p>
                <div class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar ${badgeClass}" style="width: ${percentage}%">
                        ${used}/${total}
                    </div>
                </div>
                <small class="text-muted">${adjustedAvailable} port(s) available</small>
            `;
        }
    } else {
        infoDiv.innerHTML = '';
        statusCard.style.display = 'none';
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
