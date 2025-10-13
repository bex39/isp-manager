@extends('layouts.admin')

@section('title', 'Fiber Network Map')
@section('page-title', 'Fiber Network Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    #fiberMap {
        height: 700px;
        width: 100%;
        z-index: 1;
    }
    .drawing-mode-active {
        cursor: crosshair !important;
    }
    .drawing-mode-active * {
        cursor: crosshair !important;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Fiber Infrastructure Map</h5>
        <p class="text-muted mb-0">Fiber optic network topology and cable routes</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('map.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-map"></i> General Map
        </a>
    </div>
</div>

<!-- Map Legend -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="fw-bold text-muted">LEGEND:</small>
                <span class="ms-3"><i class="bi bi-router text-primary"></i> Router</span>
                <span class="ms-3"><i class="bi bi-hdd-network text-info"></i> OLT</span>
                <span class="ms-3"><i class="bi bi-box text-warning"></i> ODP</span>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end gap-3">
                    <span><span style="width: 30px; height: 3px; background: #ef4444; display: inline-block;"></span> Backbone</span>
                    <span><span style="width: 30px; height: 3px; background: #3b82f6; display: inline-block;"></span> Distribution</span>
                    <span><span style="width: 30px; height: 3px; background: #10b981; display: inline-block;"></span> Drop</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Container -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div id="fiberMap"></div>
    </div>
</div>

<!-- Drawing Tools -->
@can('edit_router')
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="fw-bold mb-3">Drawing Tools</h6>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-danger" id="drawBackbone" type="button">
                        <i class="bi bi-bezier2"></i> Backbone
                    </button>
                    <button class="btn btn-outline-primary" id="drawDistribution" type="button">
                        <i class="bi bi-bezier"></i> Distribution
                    </button>
                    <button class="btn btn-outline-success" id="drawDrop" type="button">
                        <i class="bi bi-arrow-down-right"></i> Drop Cable
                    </button>
                    <button class="btn btn-warning text-white" id="finishDrawing" style="display: none;" type="button">
                        <i class="bi bi-check-circle"></i> Finish
                    </button>
                    <button class="btn btn-secondary" id="cancelDrawing" type="button">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div id="drawingStatus" class="text-muted small">
                    Click a button to start drawing
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Save Cable Route Modal -->
<div class="modal fade" id="saveCableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Cable Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="saveCableForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="cableType" name="type">
                    <input type="hidden" id="cableCoordinates" name="path_coordinates">

                    <div class="mb-3">
                        <label class="form-label">Cable Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Backbone-OLT1-ODP5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cable Type/Count</label>
                        <select name="cable_type" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="12 Core">12 Core</option>
                            <option value="24 Core">24 Core</option>
                            <option value="48 Core">48 Core</option>
                            <option value="96 Core">96 Core</option>
                            <option value="144 Core">144 Core</option>
                            <option value="Drop Cable">Drop Cable</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Distance (meters)</label>
                        <input type="number" name="distance_meters" class="form-control" id="cableDistance" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Cable Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof L === 'undefined') {
        console.error('Leaflet not loaded');
        return;
    }

    var fiberMap = L.map('fiberMap').setView([-8.6705, 115.2126], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(fiberMap);

    var fiberData = @json($fiberData);

    // Icons
    var routerIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #3b82f6; width: 35px; height: 35px; border-radius: 5px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-router text-white"></i></div>',
        iconSize: [35, 35],
        iconAnchor: [17, 17]
    });

    var oltIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #0ea5e9; width: 35px; height: 35px; border-radius: 5px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-hdd-network text-white"></i></div>',
        iconSize: [35, 35],
        iconAnchor: [17, 17]
    });

    var odpIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #f59e0b; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-box text-white" style="font-size: 12px;"></i></div>',
        iconSize: [25, 25],
        iconAnchor: [12, 12]
    });

    // Add devices to map
    if (fiberData.routers) {
        fiberData.routers.forEach(function(router) {
            if (router.latitude && router.longitude) {
                L.marker([parseFloat(router.latitude), parseFloat(router.longitude)], { icon: routerIcon })
                    .bindPopup('<strong>' + router.name + '</strong><br>Type: Router<br>IP: ' + router.ip_address)
                    .addTo(fiberMap);
            }
        });
    }

    if (fiberData.olts) {
        fiberData.olts.forEach(function(olt) {
            if (olt.latitude && olt.longitude) {
                L.marker([parseFloat(olt.latitude), parseFloat(olt.longitude)], { icon: oltIcon })
                    .bindPopup('<strong>' + olt.name + '</strong><br>Type: OLT<br>IP: ' + olt.ip_address)
                    .addTo(fiberMap);
            }
        });
    }

    if (fiberData.odps) {
        fiberData.odps.forEach(function(odp) {
            if (odp.latitude && odp.longitude) {
                L.marker([parseFloat(odp.latitude), parseFloat(odp.longitude)], { icon: odpIcon })
                    .bindPopup('<strong>' + odp.name + '</strong><br>Type: ODP')
                    .addTo(fiberMap);
            }
        });
    }

    if (fiberData.cables) {
        fiberData.cables.forEach(function(cable) {
            if (cable.path && cable.path.length > 0) {
                var color = cable.type === 'backbone' ? '#ef4444' :
                            cable.type === 'distribution' ? '#3b82f6' : '#10b981';
                var weight = cable.type === 'backbone' ? 4 : cable.type === 'distribution' ? 3 : 2;

                L.polyline(cable.path, {
                    color: color,
                    weight: weight,
                    opacity: 0.7,
                    dashArray: cable.type === 'drop' ? '5, 5' : null
                }).bindPopup('<strong>' + cable.name + '</strong><br>Type: ' + cable.type).addTo(fiberMap);
            }
        });
    }

    // DRAWING
    var drawingMode = null;
    var drawingPoints = [];
    var tempPolyline = null;
    var pointMarkers = [];

    var colorMap = {
        'backbone': '#ef4444',
        'distribution': '#3b82f6',
        'drop': '#10b981'
    };

    function updateStatus(message) {
        document.getElementById('drawingStatus').innerHTML = message;
    }

    function clearDrawing() {
        drawingPoints = [];
        if (tempPolyline) {
            fiberMap.removeLayer(tempPolyline);
            tempPolyline = null;
        }
        pointMarkers.forEach(function(marker) {
            fiberMap.removeLayer(marker);
        });
        pointMarkers = [];
        document.getElementById('fiberMap').classList.remove('drawing-mode-active');
        var finishBtn = document.getElementById('finishDrawing');
        if (finishBtn) finishBtn.style.display = 'none';
    }

    function startDrawing(type) {
        clearDrawing();
        drawingMode = type;
        document.getElementById('fiberMap').classList.add('drawing-mode-active');
        var finishBtn = document.getElementById('finishDrawing');
        if (finishBtn) finishBtn.style.display = 'inline-block';
        updateStatus('<strong style="color: ' + colorMap[type] + '">Drawing ' + type + ' cable</strong> - Click on map');
    }

    function calculateDistance(latlngs) {
        var totalDistance = 0;
        for (var i = 0; i < latlngs.length - 1; i++) {
            totalDistance += fiberMap.distance(latlngs[i], latlngs[i + 1]);
        }
        return Math.round(totalDistance);
    }

    // Buttons
    document.getElementById('drawBackbone')?.addEventListener('click', function(e) {
        e.preventDefault();
        startDrawing('backbone');
    });

    document.getElementById('drawDistribution')?.addEventListener('click', function(e) {
        e.preventDefault();
        startDrawing('distribution');
    });

    document.getElementById('drawDrop')?.addEventListener('click', function(e) {
        e.preventDefault();
        startDrawing('drop');
    });

    document.getElementById('cancelDrawing')?.addEventListener('click', function(e) {
        e.preventDefault();
        clearDrawing();
        drawingMode = null;
        updateStatus('Drawing cancelled');
    });

    // FINISH BUTTON - GUNAKAN PROMPT UNTUK SIMPLICITY
    document.getElementById('finishDrawing')?.addEventListener('click', function(e) {
        e.preventDefault();

        if (drawingPoints.length < 2) {
            alert('Please add at least 2 points');
            return;
        }

        var cableName = prompt('Enter cable name (e.g., Backbone-OLT1-ODP5):');
        if (!cableName) return;

        var formData = new FormData();
        formData.append('name', cableName);
        formData.append('type', drawingMode);
        formData.append('path_coordinates', JSON.stringify(drawingPoints));
        formData.append('distance_meters', calculateDistance(drawingPoints));
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("cable-routes.store") }}', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Cable route saved successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) {
            alert('Error: ' + error.message);
        });
    });

    // Map click
    fiberMap.on('click', function(e) {
        if (!drawingMode) return;

        var latlng = [e.latlng.lat, e.latlng.lng];
        drawingPoints.push(latlng);

        var pointIcon = L.divIcon({
            className: 'point-marker',
            html: '<div style="width: 12px; height: 12px; background: ' + colorMap[drawingMode] + '; border: 2px solid white; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>',
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });

        var marker = L.marker(latlng, { icon: pointIcon }).addTo(fiberMap);
        pointMarkers.push(marker);

        if (tempPolyline) {
            fiberMap.removeLayer(tempPolyline);
        }

        if (drawingPoints.length > 1) {
            tempPolyline = L.polyline(drawingPoints, {
                color: colorMap[drawingMode],
                weight: drawingMode === 'backbone' ? 4 : drawingMode === 'distribution' ? 3 : 2,
                opacity: 0.7,
                dashArray: drawingMode === 'drop' ? '5, 5' : null
            }).addTo(fiberMap);
        }

        var distance = drawingPoints.length > 1 ? calculateDistance(drawingPoints) : 0;
        updateStatus('<strong>Points: ' + drawingPoints.length + '</strong> | Distance: ' + distance + 'm');
    });

    setTimeout(function() { fiberMap.invalidateSize(); }, 100);
});
</script>
@endpush
