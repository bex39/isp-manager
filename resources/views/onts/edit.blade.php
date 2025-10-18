@extends('layouts.admin')

@section('title', 'Edit ONT')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 400px; width: 100%; border-radius: 8px; }
    .leaflet-container { z-index: 1; }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('onts.index') }}">ONTs</a></li>
                <li class="breadcrumb-item"><a href="{{ route('onts.show', $ont) }}">{{ $ont->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Edit ONT</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('onts.update', $ont) }}" method="POST" id="ontForm">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-info-circle"></i> Basic Information
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ONT Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $ont->name) }}" required>
                            <small class="text-muted">Unique identifier for this ONT</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number (SN) <span class="text-danger">*</span></label>
                            <input type="text" name="sn" class="form-control @error('sn') is-invalid @enderror"
                                   value="{{ old('sn', $ont->sn) }}" required>
                            <small class="text-muted">Unique serial number from device</small>
                            @error('sn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">OLT <span class="text-danger">*</span></label>
                            <select name="olt_id" class="form-select @error('olt_id') is-invalid @enderror" required>
                                <option value="">Select OLT</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ old('olt_id', $ont->olt_id) == $olt->id ? 'selected' : '' }}>
                                        {{ $olt->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Parent OLT device</small>
                            @error('olt_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">No Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $ont->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Assign to customer (optional)</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- PON Configuration -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-diagram-3"></i> PON Configuration
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Frame <span class="text-danger">*</span></label>
                            <input type="number" name="frame" class="form-control"
                                   value="{{ old('frame', $ont->frame) }}" required min="0">
                            <small class="text-muted">Frame number</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slot <span class="text-danger">*</span></label>
                            <input type="number" name="slot" class="form-control"
                                   value="{{ old('slot', $ont->slot) }}" required min="0">
                            <small class="text-muted">Slot number</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Port <span class="text-danger">*</span></label>
                            <input type="number" name="port" class="form-control"
                                   value="{{ old('port', $ont->port) }}" required min="0">
                            <small class="text-muted">Port number</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ONT ID <span class="text-danger">*</span></label>
                            <input type="number" name="ont_id" class="form-control"
                                   value="{{ old('ont_id', $ont->ont_id) }}" required min="0">
                            <small class="text-muted">ONT ID on port (0-127)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Profile</label>
                            <input type="text" name="service_profile" class="form-control"
                                   value="{{ old('service_profile', $ont->service_profile) }}">
                            <small class="text-muted">Service profile name</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Current Status -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-activity"></i> Current Status
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <div>
                                <span class="{{ $ont->getStatusBadgeClass() }}">
                                    {{ ucfirst($ont->status) }}
                                </span>
                            </div>
                            <small class="text-muted">Current operational status</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RX Power</label>
                            <div>
                                @if($ont->rx_power)
                                    <span class="{{ $ont->getSignalBadgeClass() }}">
                                        {{ $ont->rx_power }} dBm
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                            <small class="text-muted">Received signal power</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Seen</label>
                            <div>
                                @if($ont->last_seen)
                                    <small>{{ $ont->last_seen->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </div>
                            <small class="text-muted">Last communication</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Location Information -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-geo-alt"></i> Location Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $ont->address) }}</textarea>
                        <small class="text-muted">Physical installation address</small>
                    </div>

                    <!-- Coordinates with GPS & Map -->
                    <div class="mb-3">
                        <label class="form-label">GPS Coordinates</label>
                        <div class="btn-group w-100 mb-2">
                            <button type="button" class="btn btn-success" onclick="getGPSLocation()">
                                <i class="bi bi-geo-alt-fill"></i> Get GPS Location
                            </button>
                            <button type="button" class="btn btn-info" onclick="openGoogleMaps()">
                                <i class="bi bi-map"></i> Open Google Maps
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearCoordinates()">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" id="latitude" class="form-control"
                                   value="{{ old('latitude', $ont->latitude) }}" step="any">
                            <small class="text-muted">Example: -6.175110</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" id="longitude" class="form-control"
                                   value="{{ old('longitude', $ont->longitude) }}" step="any">
                            <small class="text-muted">Example: 106.827153</small>
                        </div>
                    </div>

                    <!-- Map Picker -->
                    <div class="mb-3">
                        <label class="form-label">Map Picker</label>
                        <div id="map"></div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> Click on the map or drag the marker to update ONT location
                        </small>
                    </div>

                    <hr class="my-4">

                    <!-- Additional Information -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-file-text"></i> Additional Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $ont->description) }}</textarea>
                        <small class="text-muted">Optional notes, installation details, or configurations</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update ONT
                        </button>
                        <a href="{{ route('onts.show', $ont) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Current Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Current Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%">PON Address:</td>
                        <td><code>{{ $ont->frame }}/{{ $ont->slot }}/{{ $ont->port }}:{{ $ont->ont_id }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="{{ $ont->getStatusBadgeClass() }}">
                                {{ ucfirst($ont->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($ont->rx_power)
                    <tr>
                        <td class="text-muted">RX Power:</td>
                        <td>
                            <span class="{{ $ont->getSignalBadgeClass() }}">
                                {{ $ont->rx_power }} dBm
                            </span>
                        </td>
                    </tr>
                    @endif
                    @if($ont->tx_power)
                    <tr>
                        <td class="text-muted">TX Power:</td>
                        <td>{{ $ont->tx_power }} dBm</td>
                    </tr>
                    @endif
                    @if($ont->distance)
                    <tr>
                        <td class="text-muted">Distance:</td>
                        <td>{{ $ont->distance }} m</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $ont->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td>{{ $ont->updated_at->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Location Guide -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Location Guide</h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">
                    <i class="bi bi-geo-alt"></i> Update Coordinates
                </h6>
                <ul class="small mb-3">
                    <li><strong>GPS Button:</strong> Get current device location</li>
                    <li><strong>Google Maps:</strong> Find exact ONT location</li>
                    <li><strong>Map Click:</strong> Click anywhere on map</li>
                    <li><strong>Drag Marker:</strong> Move red marker to adjust</li>
                </ul>

                <h6 class="text-primary">
                    <i class="bi bi-map"></i> From Google Maps
                </h6>
                <ol class="small mb-0">
                    <li>Right-click on ONT location</li>
                    <li>Click coordinates at top to copy</li>
                    <li>Paste into fields above</li>
                </ol>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('onts.show', $ont) }}" class="btn btn-info">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    @if($ont->olt)
                        <a href="{{ route('olts.show', $ont->olt) }}" class="btn btn-secondary">
                            <i class="bi bi-hdd-network"></i> View OLT
                        </a>
                    @endif
                    @if($ont->customer)
                        <a href="{{ route('customers.show', $ont->customer) }}" class="btn btn-secondary">
                            <i class="bi bi-person"></i> View Customer
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-danger">Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Delete this ONT permanently. This action cannot be undone.</p>
                <form action="{{ route('onts.destroy', $ont) }}" method="POST"
                      onsubmit="return confirm('Are you sure? All ONT data will be lost!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Delete ONT
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;
const defaultLat = {{ $ont->latitude ?? -6.175110 }};
const defaultLng = {{ $ont->longitude ?? 106.827153 }};

document.addEventListener('DOMContentLoaded', function() {
    let initLat = document.getElementById('latitude').value || defaultLat;
    let initLng = document.getElementById('longitude').value || defaultLng;

    map = L.map('map').setView([initLat, initLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    marker = L.marker([initLat, initLng], {
        draggable: true
    }).addTo(map);

    marker.bindPopup(`<b>{{ $ont->name }}</b><br>SN: {{ $ont->sn }}<br>Lat: ${initLat}<br>Lng: ${initLng}`).openPopup();

    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    document.getElementById('latitude').addEventListener('change', updateMarkerFromInputs);
    document.getElementById('longitude').addEventListener('change', updateMarkerFromInputs);
});

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    marker.setPopupContent(`<b>{{ $ont->name }}</b><br>SN: {{ $ont->sn }}<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
}

function updateMarkerFromInputs() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 13);
        marker.setPopupContent(`<b>{{ $ont->name }}</b><br>SN: {{ $ont->sn }}<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
    }
}

function getGPSLocation() {
    if (!navigator.geolocation) {
        alert('❌ Geolocation not supported');
        return;
    }

    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Getting location...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            updateCoordinates(lat, lng);
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);

            btn.innerHTML = '<i class="bi bi-check-circle"></i> Success!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }, 2000);

            alert(`✓ ONT location updated!\n\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}\nAccuracy: ±${Math.round(position.coords.accuracy)}m`);
        },
        function(error) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            let errorMsg = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = "Location denied. Enable in browser settings.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = "Location unavailable. Enable GPS or go outdoors.";
                    break;
                case error.TIMEOUT:
                    errorMsg = "Request timed out. Try again.";
                    break;
                default:
                    errorMsg = "Unknown error.";
            }
            alert('❌ ' + errorMsg);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

function openGoogleMaps() {
    const lat = document.getElementById('latitude').value || defaultLat;
    const lng = document.getElementById('longitude').value || defaultLng;
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');

    alert('📍 Google Maps Instructions:\n\n1. Find exact ONT location\n2. Right-click on spot\n3. Click coordinates to copy\n4. Paste into Latitude/Longitude fields');
}

function clearCoordinates() {
    if (confirm('Clear ONT coordinates?')) {
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        marker.setLatLng([defaultLat, defaultLng]);
        map.setView([defaultLat, defaultLng], 5);
    }
}

document.getElementById('ontForm').addEventListener('submit', function() {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
});
</script>
@endpush
