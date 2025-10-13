@extends('layouts.admin')

@section('title', 'Add Access Point')
@section('page-title', 'Add Access Point')

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
                <h6 class="fw-bold mb-0">Access Point Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('access-points.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <select name="brand" class="form-select @error('brand') is-invalid @enderror">
                                <option value="">Select Brand</option>
                                <option value="Ubiquiti" {{ old('brand') == 'Ubiquiti' ? 'selected' : '' }}>Ubiquiti</option>
                                <option value="TP-Link" {{ old('brand') == 'TP-Link' ? 'selected' : '' }}>TP-Link</option>
                                <option value="MikroTik" {{ old('brand') == 'MikroTik' ? 'selected' : '' }}>MikroTik</option>
                                <option value="Cisco" {{ old('brand') == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="Aruba" {{ old('brand') == 'Aruba' ? 'selected' : '' }}>Aruba</option>
                                <option value="Other" {{ old('brand') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror"
                                   value="{{ old('model') }}">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                                   value="{{ old('ip_address') }}" required placeholder="192.168.1.1">
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">MAC Address</label>
                            <input type="text" name="mac_address" class="form-control @error('mac_address') is-invalid @enderror"
                                   value="{{ old('mac_address') }}" placeholder="00:11:22:33:44:55">
                            @error('mac_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">SSID</label>
                            <input type="text" name="ssid" class="form-control @error('ssid') is-invalid @enderror"
                                   value="{{ old('ssid') }}" placeholder="MyWiFi">
                            @error('ssid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-select @error('frequency') is-invalid @enderror">
                                <option value="">Select Frequency</option>
                                <option value="2.4GHz" {{ old('frequency') == '2.4GHz' ? 'selected' : '' }}>2.4 GHz</option>
                                <option value="5GHz" {{ old('frequency') == '5GHz' ? 'selected' : '' }}>5 GHz</option>
                                <option value="6GHz" {{ old('frequency') == '6GHz' ? 'selected' : '' }}>6 GHz</option>
                                <option value="Dual Band" {{ old('frequency') == 'Dual Band' ? 'selected' : '' }}>Dual Band</option>
                                <option value="Tri Band" {{ old('frequency') == 'Tri Band' ? 'selected' : '' }}>Tri Band</option>
                            </select>
                            @error('frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Max Clients</label>
                            <input type="number" name="max_clients" class="form-control @error('max_clients') is-invalid @enderror"
                                   value="{{ old('max_clients', 50) }}" min="1">
                            @error('max_clients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="online" {{ old('status') == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="offline" {{ old('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
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
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude', -8.6705) }}" placeholder="Latitude" required readonly>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude', 115.2126) }}" placeholder="Longitude" required readonly>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="mapPicker"></div>
                            <small class="text-muted">Click on map to set location</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <i class="bi bi-save"></i> Save Access Point
                        </button>
                        <a href="{{ route('access-points.index') }}" class="btn btn-secondary">
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
                    <li>Set accurate location on map for better network visualization</li>
                    <li>Use unique SSID for easy identification</li>
                    <li>Configure max clients based on AP capacity</li>
                    <li>Regular monitoring ensures optimal performance</li>
                </ul>
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
</script>
@endpush
@endsection
