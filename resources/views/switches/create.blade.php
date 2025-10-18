@extends('layouts.admin')

@section('title', 'Add New Switch')

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
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Add New Switch</h6>
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

                <form action="{{ route('switches.store') }}" method="POST" id="switchForm">
                    @csrf

                    <!-- Basic Information -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-info-circle"></i> Basic Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Switch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g., Core Switch 1">
                        <small class="text-muted">Unique name to identify this switch</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand</label>
                            <select name="brand" class="form-select">
                                <option value="">Select Brand</option>
                                <option value="cisco" {{ old('brand') == 'cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="mikrotik" {{ old('brand') == 'mikrotik' ? 'selected' : '' }}>MikroTik</option>
                                <option value="ubiquiti" {{ old('brand') == 'ubiquiti' ? 'selected' : '' }}>Ubiquiti</option>
                                <option value="tp-link" {{ old('brand') == 'tp-link' ? 'selected' : '' }}>TP-Link</option>
                                <option value="hp" {{ old('brand') == 'hp' ? 'selected' : '' }}>HP/Aruba</option>
                                <option value="huawei" {{ old('brand') == 'huawei' ? 'selected' : '' }}>Huawei</option>
                                <option value="d-link" {{ old('brand') == 'd-link' ? 'selected' : '' }}>D-Link</option>
                                <option value="other" {{ old('brand') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <small class="text-muted">Switch manufacturer</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="{{ old('model') }}" placeholder="e.g., WS-C2960X-24TS-L">
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
                        <strong>Managed Switch:</strong> Fill IP Address, Username, and Password to enable remote management and automatic ping monitoring.
                        <br><strong>Unmanaged Switch:</strong> Leave IP/credentials empty - no remote monitoring will be performed.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IP Address</label>
                            <input type="text" name="ip_address" class="form-control" value="{{ old('ip_address') }}" placeholder="192.168.1.1">
                            <small class="text-muted">Management IP address (leave empty for unmanaged)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MAC Address</label>
                            <input type="text" name="mac_address" class="form-control" value="{{ old('mac_address') }}" placeholder="AA:BB:CC:DD:EE:FF">
                            <small class="text-muted">Physical MAC address</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="admin">
                            <small class="text-muted">SSH/Telnet username</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                            <small class="text-muted">SSH/Telnet password</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SSH Port</label>
                            <input type="number" name="ssh_port" class="form-control" value="{{ old('ssh_port', 22) }}" min="1" max="65535">
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
                            <input type="number" name="port_count" class="form-control" value="{{ old('port_count', 24) }}" min="1" placeholder="24">
                            <small class="text-muted">Number of physical ports</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g., Server Room A, Floor 3">
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
                            <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="-6.175110" step="any">
                            <small class="text-muted">Example: -6.175110</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="106.827153" step="any">
                            <small class="text-muted">Example: 106.827153</small>
                        </div>
                    </div>

                    <!-- Map Picker -->
                    <div class="mb-3">
                        <label class="form-label">Map Picker</label>
                        <div id="map"></div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> Click on the map to set coordinates, or drag the marker to adjust position
                        </small>
                    </div>

                    <hr class="my-4">

                    <!-- Additional Info -->
                    <h6 class="mb-3 text-primary">
                        <i class="bi bi-file-text"></i> Additional Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Any additional information about this switch...">{{ old('notes') }}</textarea>
                        <small class="text-muted">Optional notes, configuration details, or maintenance information</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                <strong>Active</strong> - Enable monitoring and management
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Switch
                        </button>
                        <a href="{{ route('switches.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Guide -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Quick Guide</h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">
                    <i class="bi bi-hdd-network"></i> Switch Types
                </h6>
                <ul class="small mb-3">
                    <li><strong>Managed Switch:</strong> Can be monitored and configured remotely. Requires IP, username, and password for SSH/Telnet access.</li>
                    <li><strong>Unmanaged Switch:</strong> Simple plug-and-play device. No remote management capabilities.</li>
                </ul>

                <h6 class="text-primary">
                    <i class="bi bi-geo-alt"></i> Location Tips
                </h6>
                <ul class="small mb-3">
                    <li><strong>GPS Button:</strong> Click to automatically get your current device location</li>
                    <li><strong>Google Maps:</strong> Opens Google Maps to manually find and copy coordinates</li>
                    <li><strong>Map Picker:</strong> Click anywhere on the map to set coordinates</li>
                    <li><strong>Drag Marker:</strong> Move the red marker to adjust position</li>
                </ul>

                <h6 class="text-primary">
                    <i class="bi bi-building"></i> Common Brands
                </h6>
                <ul class="small mb-0">
                    <li><strong>Cisco:</strong> Enterprise-grade, Catalyst series</li>
                    <li><strong>MikroTik:</strong> Cost-effective, RouterOS based</li>
                    <li><strong>Ubiquiti:</strong> UniFi switches, cloud-managed</li>
                    <li><strong>TP-Link:</strong> Budget-friendly, easy setup</li>
                    <li><strong>HP/Aruba:</strong> Enterprise networking solutions</li>
                </ul>
            </div>
        </div>

        <!-- Coordinate Format Help -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Coordinate Format</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Decimal Degrees (Recommended):</strong></p>
                <code class="d-block mb-2">-6.175110, 106.827153</code>

                <p class="small mb-2 mt-3"><strong>From Google Maps:</strong></p>
                <ol class="small mb-0">
                    <li>Right-click on location</li>
                    <li>Click the coordinates to copy</li>
                    <li>Paste into Latitude/Longitude fields</li>
                </ol>
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
const defaultLat = -6.175110;
const defaultLng = 106.827153;

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
}

function updateMarkerFromInputs() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 13);
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

            alert(`Location retrieved!\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}\nAccuracy: ±${Math.round(position.coords.accuracy)}m`);
        },
        function(error) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;

            let errorMsg = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = "Permission denied. Please allow location access in your browser settings.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMsg = "The request to get location timed out.";
                    break;
                default:
                    errorMsg = "An unknown error occurred.";
            }
            alert('Error getting location: ' + errorMsg);
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

    alert('Instructions:\n1. Right-click on the exact location\n2. Click the coordinates at the top to copy\n3. Paste into Latitude/Longitude fields\n\nNote: First number is Latitude, second is Longitude');
}

// Clear coordinates
function clearCoordinates() {
    if (confirm('Clear coordinates?')) {
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
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
});
</script>
@endpush
