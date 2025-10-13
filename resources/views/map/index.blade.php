@extends('layouts.admin')

@section('title', 'Network Map')
@section('page-title', 'Network Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #networkMap {
        height: 700px;
        width: 100%;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Network Infrastructure Map</h5>
        <p class="text-muted mb-0">Real-time network topology visualization</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('map.fiber') }}" class="btn btn-info btn-sm">
            <i class="bi bi-diagram-3"></i> Fiber Map
        </a>
    </div>
</div>

<!-- Toggle Controls -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary" id="showAllLayers">
                    <i class="bi bi-eye"></i> Show All
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary" id="hideAllLayers">
                    <i class="bi bi-eye-slash"></i> Hide All
                </button>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleRouters" data-layer="routers" checked>
                    <label class="form-check-label" for="toggleRouters">
                        <i class="bi bi-router text-primary"></i> Routers
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleOLTs" data-layer="olts" checked>
                    <label class="form-check-label" for="toggleOLTs">
                        <i class="bi bi-hdd-network" style="color: #0ea5e9;"></i> OLTs
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleONTs" data-layer="onts" checked>
                    <label class="form-check-label" for="toggleONTs">
                        <i class="bi bi-modem text-warning"></i> ONTs
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleODPs" data-layer="odps" checked>
                    <label class="form-check-label" for="toggleODPs">
                        <i class="bi bi-boxes" style="color: #06b6d4;"></i> ODPs
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleJointBoxes" data-layer="jointBoxes" checked>
                    <label class="form-check-label" for="toggleJointBoxes">
                        <i class="bi bi-box-seam" style="color: #f97316;"></i> Joint Boxes
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleCables" data-layer="cables" checked>
                    <label class="form-check-label" for="toggleCables">
                        <i class="bi bi-bezier text-danger"></i> Cables
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleSwitches" data-layer="switches">
                    <label class="form-check-label" for="toggleSwitches">
                        <i class="bi bi-diagram-3 text-success"></i> Switches
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleAPs" data-layer="accessPoints">
                    <label class="form-check-label" for="toggleAPs">
                        <i class="bi bi-wifi" style="color: #8b5cf6;"></i> Access Points
                    </label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-switch">
                    <input class="form-check-input layer-toggle" type="checkbox" id="toggleCustomers" data-layer="customers">
                    <label class="form-check-label" for="toggleCustomers">
                        <i class="bi bi-house text-secondary"></i> Customers
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center text-center">
            <div class="col">
                <i class="bi bi-router text-primary" style="font-size: 1.5rem;"></i>
                <br><small>Router</small>
            </div>
            <div class="col">
                <i class="bi bi-hdd-network" style="font-size: 1.5rem; color: #0ea5e9;"></i>
                <br><small>OLT</small>
            </div>
            <div class="col">
                <i class="bi bi-modem text-warning" style="font-size: 1.5rem;"></i>
                <br><small>ONT</small>
            </div>
            <div class="col">
                <i class="bi bi-boxes" style="font-size: 1.5rem; color: #06b6d4;"></i>
                <br><small>ODP</small>
            </div>
            <div class="col">
                <i class="bi bi-box-seam" style="font-size: 1.5rem; color: #f97316;"></i>
                <br><small>Joint Box</small>
            </div>
            <div class="col">
                <i class="bi bi-diagram-3 text-success" style="font-size: 1.5rem;"></i>
                <br><small>Switch</small>
            </div>
            <div class="col">
                <i class="bi bi-wifi" style="font-size: 1.5rem; color: #8b5cf6;"></i>
                <br><small>AP</small>
            </div>
            <div class="col">
                <i class="bi bi-house text-secondary" style="font-size: 1.5rem;"></i>
                <br><small>Customer</small>
            </div>
        </div>
    </div>
</div>

<!-- Map Container -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div id="networkMap"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const networkMap = L.map('networkMap').setView([-8.6705, 115.2126], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(networkMap);

const mapData = @json($mapData);

// Custom Icons
const routerIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #3b82f6; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-router text-white"></i></div>',
    iconSize: [35, 35],
    iconAnchor: [17, 17]
});

const oltIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #0ea5e9; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-hdd-network text-white"></i></div>',
    iconSize: [35, 35],
    iconAnchor: [17, 17]
});

const ontIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #f59e0b; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-modem text-white" style="font-size: 12px;"></i></div>',
    iconSize: [28, 28],
    iconAnchor: [14, 14]
});

const odpIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #06b6d4; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-boxes text-white" style="font-size: 14px;"></i></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

const switchIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #10b981; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-diagram-3 text-white" style="font-size: 14px;"></i></div>',
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

const apIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #8b5cf6; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-wifi text-white" style="font-size: 14px;"></i></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

const customerIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #6b7280; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-house text-white" style="font-size: 10px;"></i></div>',
    iconSize: [20, 20],
    iconAnchor: [10, 10]
});

const jointBoxIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: #f97316; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-box-seam text-white" style="font-size: 14px;"></i></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

// Layers
const layers = {
    routers: L.layerGroup().addTo(networkMap),
    olts: L.layerGroup().addTo(networkMap),
    onts: L.layerGroup().addTo(networkMap),
    odps: L.layerGroup().addTo(networkMap),
    jointBoxes: L.layerGroup().addTo(networkMap),
    cables: L.layerGroup().addTo(networkMap),
    switches: L.layerGroup(),
    accessPoints: L.layerGroup(),
    customers: L.layerGroup(),
};

// Add routers dengan warna dinamis
mapData.routers.forEach(router => {
    // Tentukan warna berdasarkan status online/offline
    const isOnline = router.is_online; // pastikan backend kirim field ini
    const routerColor = isOnline ? '#3b82f6' : '#ef4444'; // Biru kalau online, merah kalau offline

    const dynamicRouterIcon = L.divIcon({
        className: 'custom-marker',
        html: `
            <div style="
                background: ${routerColor};
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 3px solid white;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            ">
                <i class="bi bi-router text-white"></i>
            </div>
        `,
        iconSize: [35, 35],
        iconAnchor: [17, 17]
    });

    const statusBadge = `<span class="badge bg-${isOnline ? 'success' : 'danger'}">${isOnline ? 'Online' : 'Offline'}</span>`;

    L.marker([router.lat, router.lng], { icon: dynamicRouterIcon })
        .bindPopup(`
            <div class="p-2">
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-router"></i> ${router.name} ${statusBadge}
                </h6>
                <p class="mb-1 small"><strong>IP:</strong> ${router.ip}</p>
                <a href="${router.url}" class="btn btn-sm btn-primary mt-2 w-100">View Details</a>
            </div>
        `)
        .addTo(layers.routers);
});

// Add OLTs
mapData.olts.forEach(olt => {
    const statusBadge = olt.is_online ? '<span class="badge bg-success">Online</span>' : '<span class="badge bg-danger">Offline</span>';
    L.marker([olt.lat, olt.lng], { icon: oltIcon }).bindPopup(`
        <div class="p-2">
            <h6 class="fw-bold mb-2"><i class="bi bi-hdd-network"></i> ${olt.name} ${statusBadge}</h6>
            <p class="mb-1 small"><strong>IP:</strong> ${olt.ip}</p>
            <p class="mb-1 small"><strong>ONTs:</strong> ${olt.customers_count}</p>
            <a href="${olt.url}" class="btn btn-sm btn-info mt-2 w-100">View Details</a>
        </div>
    `).addTo(layers.olts);
});

// Add ONTs dengan efek menyala & blink saat offline
mapData.onts.forEach(ont => {
    const isOnline = ont.is_online;

    // Warna dasar
    const color = isOnline ? '#22c55e' : '#ef4444'; // Hijau / Merah
    const glow = isOnline ? '0 0 15px #22c55e' : '0 0 15px #ef4444';

    // Kalau offline, tambahkan animasi blink pakai keyframes inline
    const blinkStyle = !isOnline
        ? `animation: blink 1s infinite;`
        : '';

    const ontIconDynamic = L.divIcon({
        className: 'custom-marker',
        html: `
            <div style="
                background: ${color};
                width: 28px;
                height: 28px;
                border-radius: 50%;
                border: 2px solid white;
                box-shadow: ${glow};
                display: flex;
                align-items: center;
                justify-content: center;
                ${blinkStyle}
            ">
                <i class="bi bi-hdd-network text-white" style="font-size:14px"></i>
            </div>
            <style>
                @keyframes blink {
                    0%, 100% { opacity: 1; transform: scale(1); }
                    50% { opacity: 0.4; transform: scale(0.85); }
                }
            </style>
        `,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    const statusBadge = `<span class="badge bg-${isOnline ? 'success' : 'danger'}">${isOnline ? 'Online' : 'Offline'}</span>`;

    L.marker([ont.lat, ont.lng], { icon: ontIconDynamic })
        .bindPopup(`
            <div class="p-2">
                <h6 class="fw-bold mb-2"><i class="bi bi-hdd-network"></i> ${ont.name} ${statusBadge}</h6>
                <p class="mb-1 small"><strong>Serial:</strong> ${ont.serial_number}</p>
                <p class="mb-1 small"><strong>OLT Port:</strong> ${ont.olt_port}</p>
                <a href="${ont.url}" class="btn btn-sm btn-primary mt-2 w-100">View ONT</a>
            </div>
        `)
        .addTo(layers.onts);
});

// Add ODPs
if (mapData.odps) {
    mapData.odps.forEach(odp => {
        const usagePercent = ((odp.used_ports / odp.total_ports) * 100).toFixed(1);
        const usageColor = usagePercent >= 80 ? 'danger' : (usagePercent >= 60 ? 'warning' : 'success');
        L.marker([odp.lat, odp.lng], { icon: odpIcon }).bindPopup(`
            <div class="p-2">
                <h6 class="fw-bold mb-2"><i class="bi bi-boxes"></i> ${odp.name}</h6>
                <p class="mb-1 small"><strong>Code:</strong> ${odp.code}</p>
                <p class="mb-1 small"><strong>Ports:</strong> ${odp.available_ports}/${odp.total_ports}</p>
                <div class="progress mb-2" style="height: 20px;">
                    <div class="progress-bar bg-${usageColor}" style="width: ${usagePercent}%">${usagePercent}%</div>
                </div>
                <a href="${odp.url}" class="btn btn-sm btn-info mt-2 w-100">View Details</a>
            </div>
        `).addTo(layers.odps);
    });
}

// Add Joint Boxes
if (mapData.jointBoxes) {
    mapData.jointBoxes.forEach(jb => {
        const usagePercent = ((jb.used_capacity / jb.capacity) * 100).toFixed(1);
        const usageColor = usagePercent >= 80 ? 'danger' : (usagePercent >= 60 ? 'warning' : 'success');
        L.marker([jb.lat, jb.lng], { icon: jointBoxIcon }).bindPopup(`
            <div class="p-2">
                <h6 class="fw-bold mb-2"><i class="bi bi-box-seam"></i> ${jb.name}</h6>
                <p class="mb-1 small"><strong>Type:</strong> ${jb.type}</p>
                <p class="mb-1 small"><strong>Capacity:</strong> ${jb.used_capacity}/${jb.capacity}</p>
                <div class="progress mb-2" style="height: 20px;">
                    <div class="progress-bar bg-${usageColor}" style="width: ${usagePercent}%">${usagePercent}%</div>
                </div>
                <a href="${jb.url}" class="btn btn-sm btn-warning mt-2 w-100">View Details</a>
            </div>
        `).addTo(layers.jointBoxes);
    });
}

// Add Cable Segments
if (mapData.cableSegments) {
    mapData.cableSegments.forEach(cable => {
        if (cable.start_lat && cable.start_lng && cable.end_lat && cable.end_lng) {
            let color = '#0dcaf0';
            if (cable.cable_type === 'backbone') color = '#dc3545';
            if (cable.cable_type === 'distribution') color = '#ffc107';
            let dashArray = cable.status === 'maintenance' ? '10, 5' : (cable.status === 'damaged' ? '2, 5' : null);
            const usagePercent = (cable.used_cores / cable.core_count) * 100;
            let weight = usagePercent >= 80 ? 6 : (usagePercent >= 60 ? 4 : 3);
            L.polyline([[cable.start_lat, cable.start_lng], [cable.end_lat, cable.end_lng]],
                { color: color, weight: weight, dashArray: dashArray, opacity: 0.7 }
            ).bindPopup(`
                <div class="p-2">
                    <h6 class="fw-bold mb-2">${cable.name}</h6>
                    <p class="mb-1 small"><strong>Type:</strong> ${cable.cable_type}</p>
                    <p class="mb-1 small"><strong>Cores:</strong> ${cable.used_cores}/${cable.core_count}</p>
                    <a href="${cable.url}" class="btn btn-sm btn-primary mt-2 w-100">View Details</a>
                </div>
            `).addTo(layers.cables);
        }
    });
}

// Add Switches
mapData.switches.forEach(sw => {
    const statusColor = sw.status === 'online' ? 'success' : 'secondary';
    L.marker([sw.lat, sw.lng], { icon: switchIcon }).bindPopup(`
        <div class="p-2">
            <h6 class="fw-bold mb-2"><i class="bi bi-diagram-3"></i> ${sw.name} <span class="badge bg-${statusColor}">${sw.status}</span></h6>
            <p class="mb-1 small"><strong>IP:</strong> ${sw.ip}</p>
            <a href="${sw.url}" class="btn btn-sm btn-success mt-2 w-100">View Details</a>
        </div>
    `).addTo(layers.switches);
});

// Add Access Points
mapData.accessPoints.forEach(ap => {
    const statusColor = ap.status === 'online' ? 'success' : 'secondary';
    L.marker([ap.lat, ap.lng], { icon: apIcon }).bindPopup(`
        <div class="p-2">
            <h6 class="fw-bold mb-2"><i class="bi bi-wifi"></i> ${ap.name} <span class="badge bg-${statusColor}">${ap.status}</span></h6>
            <p class="mb-1 small"><strong>SSID:</strong> ${ap.ssid || 'N/A'}</p>
            <a href="${ap.url}" class="btn btn-sm mt-2 w-100" style="background: #8b5cf6; color: white;">View Details</a>
        </div>
    `).addTo(layers.accessPoints);
});

// Add Customers
mapData.customers.forEach(customer => {
    L.marker([customer.lat, customer.lng], { icon: customerIcon }).bindPopup(`
        <div class="p-2">
            <h6 class="fw-bold mb-2"><i class="bi bi-house"></i> ${customer.name}</h6>
            <p class="mb-1 small"><strong>Package:</strong> ${customer.package}</p>
            <a href="${customer.url}" class="btn btn-sm btn-secondary mt-2 w-100">View Details</a>
        </div>
    `).addTo(layers.customers);
});

// Toggle Controls
document.querySelectorAll('.layer-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const layerName = this.dataset.layer;
        if (this.checked) {
            networkMap.addLayer(layers[layerName]);
        } else {
            networkMap.removeLayer(layers[layerName]);
        }
    });
});

document.getElementById('showAllLayers').addEventListener('click', () => {
    Object.values(layers).forEach(layer => networkMap.addLayer(layer));
    document.querySelectorAll('.layer-toggle').forEach(cb => cb.checked = true);
});

document.getElementById('hideAllLayers').addEventListener('click', () => {
    Object.values(layers).forEach(layer => networkMap.removeLayer(layer));
    document.querySelectorAll('.layer-toggle').forEach(cb => cb.checked = false);
});

setTimeout(() => networkMap.invalidateSize(), 100);



</script>
@endpush
