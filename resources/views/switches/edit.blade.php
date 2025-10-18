@extends('layouts.admin')

@section('title', 'Edit Switch')

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
                <li class="breadcrumb-item"><a href="{{ route('switches.index') }}">Switches</a></li>
                <li class="breadcrumb-item"><a href="{{ route('switches.show', $switch) }}">{{ $switch->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Edit Switch</h6>
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

                <form action="{{ route('switches.update', $switch) }}" method="POST" id="switchForm">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-info-circle"></i> Basic Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Switch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $switch->name) }}" required>
                        <small class="text-muted">Unique name to identify this switch</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand</label>
                            <select name="brand" class="form-select">
                                <option value="">Select Brand</option>
                                <option value="cisco" {{ $switch->brand == 'cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="mikrotik" {{ $switch->brand == 'mikrotik' ? 'selected' : '' }}>MikroTik</option>
                                <option value="ubiquiti" {{ $switch->brand == 'ubiquiti' ? 'selected' : '' }}>Ubiquiti</option>
                                <option value="tp-link" {{ $switch->brand == 'tp-link' ? 'selected' : '' }}>TP-Link</option>
                                <option value="hp" {{ $switch->brand == 'hp' ? 'selected' : '' }}>HP/Aruba</option>
                                <option value="huawei" {{ $switch->brand == 'huawei' ? 'selected' : '' }}>Huawei</option>
                                <option value="d-link" {{ $switch->brand == 'd-link' ? 'selected' : '' }}>D-Link</option>
                            </select>
                            <small class="text-muted">Switch manufacturer</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="{{ old('model', $switch->model) }}">
                            <small class="text-muted">Model number or series</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Network Configuration -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-hdd-network"></i> Network Configuration
                    </h6>

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Managed Switch:</strong> Fill IP Address, Username, and Password to enable remote management.
                        <br><strong>Unmanaged Switch:</strong> Leave IP/credentials empty.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IP Address</label>
                            <input type="text" name="ip_address" class="form-control" value="{{ old('ip_address', $switch->ip_address) }}">
                            <small class="text-muted">Management IP address</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MAC Address</label>
                            <input type="text" name="mac_address" class="form-control" value="{{ old('mac_address', $switch->mac_address) }}">
                            <small class="text-muted">Physical MAC address</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $switch->username) }}">
                            <small class="text-muted">SSH/Telnet username</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave empty to keep current">
                            <small class="text-muted">SSH/Telnet password</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SSH Port</label>
                            <input type="number" name="ssh_port" class="form-control" value="{{ old('ssh_port', $switch->ssh_port ?? 22) }}">
                            <small class="text-muted">Default: 22</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Physical Location -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-geo-alt"></i> Physical Location
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Port Count</label>
                            <input type="number" name="port_count" class="form-control" value="{{ old('port_count', $switch->port_count) }}">
                            <small class="text-muted">Number of physical ports</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $switch->location) }}">
                            <small class="text-muted">Physical location description</small>
                        </div>
                    </div>

                    <!-- Coordinates with GPS & Map -->
                    <div class="mb-3">
                        <label class="form-label">Coordinates</label>
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
                            <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', $switch->latitude) }}" step="any">
                            <small class="text-muted">Example: -6.175110</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', $switch->longitude) }}" step="any">
                            <small class="text-muted">Example: 106.827153</small>
                        </div>
                    </div>

                    <!-- Map Picker -->
                    <div class="mb-3">
                        <label class="form-label">Map Picker</label>
                        <div id="map"></div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> Click on the map or drag the marker to update coordinates
                        </small>
                    </div>

                    <hr class="my-4">

                    <!-- Additional Info -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-file-text"></i> Additional Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes', $switch->notes) }}</textarea>
                        <small class="text-muted">Optional notes or maintenance information</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" {{ $switch->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active</strong> - Enable monitoring and management
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Switch
                        </button>
                        <a href="{{ route('switches.show', $switch) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                            </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Current Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Current Status</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="{{ $switch->getStatusBadgeClass() }}">
                                {{ ucfirst($switch->status ?? 'Unknown') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type:</td>
                        <td>
                            @if($switch->isManaged())
                                <span class="badge bg-success">Managed</span>
                            @else
                                <span class="badge bg-secondary">Unmanaged</span>
                            @endif
                        </td>
                    </tr>
                    @if($switch->ping_latency)
                    <tr>
                        <td class="text-muted">Latency:</td>
                        <td>
                            <span class="{{ $switch->getLatencyColorClass() }}">
                                {{ $switch->ping_latency }}ms
                            </span>
                        </td>
                    </tr>
                    @endif
                    @if($switch->last_seen)
                    <tr>
                        <td class="text-muted">Last Seen:</td>
                        <td>{{ $switch->last_seen->diffForHumans() }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $switch->created_at->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Quick Guide -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Location Guide</h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">
                    <i class="bi bi-geo-alt"></i> Getting Coordinates
                </h6>
                <ul class="small mb-3">
                    <li><strong>GPS Button:</strong> Get your current device location automatically</li>
                    <li><strong>Google Maps:</strong> Open Google Maps to find exact location</li>
                    <li><strong>Map Click:</strong> Click anywhere on the map</li>
                    <li><strong>Drag Marker:</strong> Move the red marker to adjust</li>
                </ul>

                <h6 class="text-primary">
                    <i class="bi bi-map"></i> From Google Maps
                </h6>
                <ol class="small mb-0">
                    <li>Right-click on the location</li>
                    <li>Click the coordinates at the top</li>
                    <li>Paste into Latitude/Longitude fields</li>
                </ol>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-danger">Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Delete this switch permanently. This action cannot be undone.</p>
                <form action="{{ route('switches.destroy', $switch) }}" method="POST" onsubmit="return confirm('Are you sure? All data will be lost!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Delete Switch
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
const defaultLat = {{ $switch->latitude ?? -6.175110 }};
const defaultLng = {{ $switch->longitude ?? 106.827153 }};

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    // Get initial coordinates from form
    let initLat = document.getElementById('latitude').value || defaultLat;
    let initLng = document.getElementById('longitude').value || defaultLng;

    // Initialize map
    map = L.map('map').setView([initLat, initLng], 13);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Add marker
    marker = L.marker([initLat, initLng], {
        draggable: true
    }).addTo(map);

    // Popup with coordinates
    marker.bindPopup(`<b>{{ $switch->name }}</b><br>Lat: ${initLat}<br>Lng: ${initLng}`).openPopup();

    // Update coordinates when marker is dragged
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    // Update coordinates when map is clicked
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    // Update marker when input fields change
    document.getElementById('latitude').addEventListener('change', updateMarkerFromInputs);
    document.getElementById('longitude').addEventListener('change', updateMarkerFromInputs);
});

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    marker.setPopupContent(`<b>{{ $switch->name }}</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
}

function updateMarkerFromInputs() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 13);
        marker.setPopupContent(`<b>{{ $switch->name }}</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
    }
}

// Get GPS location from device
function getGPSLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    // Show loading
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Getting location...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Update inputs
            updateCoordinates(lat, lng);

            // Update map
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);

            // Reset button
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Location Retrieved!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }, 2000);

            alert(`✓ Location retrieved successfully!\n\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}\nAccuracy: ±${Math.round(position.coords.accuracy)} meters`);
        },
        function(error) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            let errorMsg = '';
            let helpText = '';

            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = "Location access denied";
                    helpText = "\n\nPlease:\n1. Click the lock icon in address bar\n2. Allow location access\n3. Refresh the page and try again";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = "Location information unavailable";
                    helpText = "\n\nTry:\n1. Enable GPS on your device\n2. Check internet connection\n3. Try outdoors for better GPS signal";
                    break;
                case error.TIMEOUT:
                    errorMsg = "Location request timed out";
                    helpText = "\n\nPlease try again in a moment.";
                    break;
                default:
                    errorMsg = "An unknown error occurred";
            }

            alert('❌ ' + errorMsg + helpText);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Open Google Maps to find location
function openGoogleMaps() {
    const lat = document.getElementById('latitude').value || defaultLat;
    const lng = document.getElementById('longitude').value || defaultLng;
    const url = `https://www.google.com/maps?q=${lat},${lng}`;
    window.open(url, '_blank');

    alert('📍 Google Maps Instructions:\n\n1. Find your exact location on the map\n2. Right-click on the precise spot\n3. Click the coordinates (numbers at top)\n4. Coordinates will be copied automatically\n5. Paste into Latitude field (first number)\n6. Paste into Longitude field (second number)\n\n💡 Tip: First number is Latitude, second is Longitude');
}

// Clear coordinates
function clearCoordinates() {
    if (confirm('Clear coordinates and reset to default location?')) {
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        marker.setLatLng([defaultLat, defaultLng]);
        map.setView([defaultLat, defaultLng], 5);
    }
}

// Prevent double submission
document.getElementById('switchForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
});
</script>
@endpush
