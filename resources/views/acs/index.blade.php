@extends('layouts.admin')

@section('title', 'ACS Management')

@push('styles')
<style>
    .device-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    .signal-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .signal-excellent { background-color: #22c55e; }
    .signal-good { background-color: #84cc16; }
    .signal-fair { background-color: #eab308; }
    .signal-poor { background-color: #ef4444; }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">ACS Device Management</h5>
        <p class="text-muted mb-0">Centralized ONT management - GenieACS inspired</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-info btn-sm" onclick="scanDevices()">
            <i class="bi bi-radar"></i> Scan Devices
        </button>
        <a href="{{ route('acs.templates.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-file-text"></i> Templates
        </a>
        <a href="{{ route('acs.statistics') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-graph-up"></i> Statistics
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Devices</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-success">{{ $stats['online'] }}</h4>
                <small class="text-muted">Online</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['offline'] }}</h4>
                <small class="text-muted">Offline</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $stats['acs_managed'] }}</h4>
                <small class="text-muted">ACS Managed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-warning">{{ $stats['unprovisioned'] }}</h4>
                <small class="text-muted">Unprovisioned</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['active_alerts'] }}</h4>
                <small class="text-muted">Active Alerts</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('acs.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search SN, name, IP..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="olt_id" class="form-select">
                        <option value="">All OLTs</option>
                        @foreach($olts as $olt)
                            <option value="{{ $olt->id }}" {{ request('olt_id') == $olt->id ? 'selected' : '' }}>
                                {{ $olt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="los" {{ request('status') == 'los' ? 'selected' : '' }}>LOS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="signal" class="form-select">
                        <option value="">All Signals</option>
                        <option value="good" {{ request('signal') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="warning" {{ request('signal') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="bad" {{ request('signal') == 'bad' ? 'selected' : '' }}>Bad</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="acs_status" class="form-select">
                        <option value="">ACS Status</option>
                        <option value="managed" {{ request('acs_status') == 'managed' ? 'selected' : '' }}>Managed</option>
                        <option value="unmanaged" {{ request('acs_status') == 'unmanaged' ? 'selected' : '' }}>Unmanaged</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span id="selectedCount">0</span> device(s) selected
            </div>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-warning" onclick="bulkAction('reboot')">
                    <i class="bi bi-arrow-repeat"></i> Reboot
                </button>
                <button class="btn btn-success" onclick="bulkAction('provision')">
                    <i class="bi bi-lightning"></i> Provision
                </button>
                <button class="btn btn-info" onclick="bulkAction('wifi')">
                    <i class="bi bi-wifi"></i> WiFi Update
                </button>
                <button class="btn btn-primary" onclick="bulkAction('template')">
                    <i class="bi bi-file-text"></i> Apply Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Device Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Device</th>
                        <th>OLT</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Signal</th>
                        <th>ACS</th>
                        <th>Last Seen</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr class="device-row">
                        <td>
                            <input type="checkbox" class="device-check" value="{{ $device->id }}"
                                   onchange="updateBulkActions()">
                        </td>
                        <td>
                            <div>
                                <strong>{{ $device->name }}</strong>
                                <br><small class="text-muted">{{ $device->sn }}</small>
                                @if($device->management_ip)
                                    <br><small class="text-muted">{{ $device->management_ip }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($device->olt)
                                <small>{{ $device->olt->name }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($device->customer)
                                <small>{{ $device->customer->name }}</small>
                            @else
                                <span class="text-muted">No customer</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $device->getStatusBadgeClass() }}">
                                {{ ucfirst($device->status) }}
                            </span>
                        </td>
                        <td>
                            @if($device->rx_power)
                                @php
                                    $power = (float) $device->rx_power;
                                    if ($power >= -20) {
                                        $class = 'signal-excellent';
                                        $quality = 'Excellent';
                                    } elseif ($power >= -23) {
                                        $class = 'signal-good';
                                        $quality = 'Good';
                                    } elseif ($power >= -25) {
                                        $class = 'signal-fair';
                                        $quality = 'Fair';
                                    } else {
                                        $class = 'signal-poor';
                                        $quality = 'Poor';
                                    }
                                @endphp
                                <span class="signal-indicator {{ $class }}"></span>
                                <small>{{ $device->rx_power }} dBm</small>
                                <br><small class="text-muted">{{ $quality }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($device->isAcsManaged())
                                <span class="badge bg-success">Managed</span>
                            @else
                                <span class="badge bg-secondary">Unmanaged</span>
                            @endif
                        </td>
                        <td>
                            @if($device->last_seen)
                                <small>{{ $device->last_seen->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('acs.show', $device) }}"
                                   class="btn btn-outline-info"
                                   title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-outline-success"
                                        onclick="quickProvision({{ $device->id }})"
                                        title="Provision">
                                    <i class="bi bi-lightning"></i>
                                </button>
                                <button class="btn btn-outline-warning"
                                        onclick="quickReboot({{ $device->id }})"
                                        title="Reboot">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button class="btn btn-outline-primary"
                                        onclick="checkSignal({{ $device->id }})"
                                        title="Check Signal">
                                    <i class="bi bi-reception-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No devices found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Showing {{ $devices->firstItem() ?? 0 }} to {{ $devices->lastItem() ?? 0 }}
                    of {{ $devices->total() }} devices
                </small>
            </div>
            <div>
                {{ $devices->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select All Toggle
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.device-check').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

// Update Bulk Actions Bar
function updateBulkActions() {
    const checked = document.querySelectorAll('.device-check:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');

    countSpan.textContent = checked.length;
    bulkBar.style.display = checked.length > 0 ? 'block' : 'none';
}

// Get Selected Device IDs
function getSelectedDevices() {
    return Array.from(document.querySelectorAll('.device-check:checked'))
        .map(cb => cb.value);
}

// Quick Provision
function quickProvision(deviceId) {
    if (!confirm('Provision this device?')) return;

    fetch(`/acs/devices/${deviceId}/provision`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? '✅ Provisioning started!' : '❌ Error: ' + data.message);
        if (data.success) location.reload();
    });
}

// Quick Reboot
function quickReboot(deviceId) {
    if (!confirm('Reboot this device?')) return;

    fetch(`/acs/devices/${deviceId}/reboot`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? '✅ Reboot command sent!' : '❌ Error: ' + data.message);
    });
}

// Check Signal
function checkSignal(deviceId) {
    fetch(`/acs/devices/${deviceId}/check-signal`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

// Bulk Actions
function bulkAction(action) {
    const selected = getSelectedDevices();

    if (selected.length === 0) {
        alert('Please select devices first!');
        return;
    }

    if (!confirm(`Perform ${action} on ${selected.length} device(s)?`)) return;

    let url = `/acs/bulk/${action}`;
    let body = { ont_ids: selected };

    if (action === 'wifi') {
        const ssid = prompt('Enter WiFi SSID:');
        const password = prompt('Enter WiFi Password (min 8 chars):');

        if (!ssid || !password || password.length < 8) {
            alert('Invalid WiFi credentials!');
            return;
        }

        url = '/acs/bulk/wifi-update';
        body.wifi_ssid = ssid;
        body.wifi_password = password;
    }

    if (action === 'template') {
        const templateId = prompt('Enter Template ID:');
        if (!templateId) return;

        url = '/acs/bulk/apply-template';
        body.template_id = templateId;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

// Scan Devices
function scanDevices() {
    if (!confirm('Start scanning all OLTs for unprovisioned devices?')) return;

    fetch('/acs/scan-devices', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    });
}
</script>
@endpush
