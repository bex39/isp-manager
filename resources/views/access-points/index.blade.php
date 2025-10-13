@extends('layouts.admin')

@section('title', 'Access Points')
@section('page-title', 'Access Point Management')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-4">
        <h5 class="fw-bold mb-1">Access Points</h5>
        <p class="text-muted mb-0">Monitor and manage WiFi access points</p>
    </div>

    <!-- 🔍 Search & Filter -->
    <div class="col-md-8 text-end">
        <form action="{{ route('access-points.index') }}" method="GET" class="d-flex justify-content-end gap-2 flex-wrap">
            <input type="text" name="search" class="form-control" style="max-width: 200px;"
                   placeholder="Search..." value="{{ request('search') }}">

            <select name="brand" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                        {{ ucfirst($brand) }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>

            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>

            <a href="{{ route('access-points.index') }}" class="btn btn-outline-dark" title="Reset">
                <i class="bi bi-arrow-repeat"></i>
            </a>

            <button type="button" class="btn btn-outline-success" onclick="bulkPing()" title="Ping All">
                <i class="bi bi-wifi"></i> Ping All
            </button>

            <a href="{{ route('access-points.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card border-0 shadow-sm mb-3" id="bulkActions" style="display: none;">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <span><strong><span id="selectedCount">0</span></strong> AP(s) selected</span>
            <button type="button" class="btn btn-success btn-sm" onclick="bulkPingSelected()">
                <i class="bi bi-wifi"></i> Ping Selected
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAll()">
                <i class="bi bi-x"></i> Deselect All
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>SSID</th>
                        <th>WiFi Password</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>Frequency</th>
                        <th>Clients</th>
                        <th>Latency</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accessPoints as $ap)
                    <tr id="ap-row-{{ $ap->id }}">
                        <td>
                            <input type="checkbox" class="ap-checkbox" value="{{ $ap->id }}" onchange="updateBulkActions()">
                        </td>
                        <td>
                            <strong>{{ $ap->name }}</strong>
                            @if($ap->model)
                                <br><small class="text-muted">{{ $ap->model }}</small>
                            @endif
                        </td>
                        <td>
                            @if($ap->brand)
                                <span class="badge" style="background-color: {{
                                    $ap->brand == 'Ubiquiti' ? '#0097e6' :
                                    ($ap->brand == 'TP-Link' ? '#27ae60' :
                                    ($ap->brand == 'MikroTik' ? '#e74c3c' : '#95a5a6'))
                                }}">
                                    {{ $ap->brand }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($ap->ssid)
                                <i class="bi bi-wifi"></i> <strong>{{ $ap->ssid }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($ap->wifi_password)
                                <code class="small">{{ $ap->wifi_password }}</code>
                                <button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ $ap->wifi_password }}')" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <code>{{ $ap->ip_address }}</code>
                            <button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ $ap->ip_address }}')" title="Copy">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </td>
                        <td>
                            @if($ap->mac_address)
                                <small class="text-muted">{{ $ap->mac_address }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($ap->frequency)
                                <span class="badge bg-secondary">{{ $ap->frequency }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center" id="clients-{{ $ap->id }}">
                            <span class="badge bg-primary">
                                {{ $ap->connected_clients ?? 0 }}/{{ $ap->max_clients ?? '-' }}
                            </span>
                        </td>
                        <td id="latency-{{ $ap->id }}">
                            @if($ap->ping_latency)
                                <span class="badge bg-success">{{ number_format($ap->ping_latency, 1) }} ms</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td id="status-{{ $ap->id }}">
                            @if($ap->status === 'online')
                                <span class="badge bg-success">
                                    <i class="bi bi-circle-fill"></i> Online
                                </span>
                            @elseif($ap->status === 'maintenance')
                                <span class="badge bg-warning">
                                    <i class="bi bi-tools"></i> Maintenance
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle-fill"></i> Offline
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($ap->last_seen)
                                <small class="text-muted">{{ $ap->last_seen->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('access-points.show', $ap) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('access-points.edit', $ap) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-outline-success"
                                        onclick="pingAP({{ $ap->id }}, '{{ $ap->ip_address }}')"
                                        id="ping-btn-{{ $ap->id }}"
                                        title="Ping Test">
                                    <i class="bi bi-wifi"></i>
                                </button>
                                <form action="{{ route('access-points.destroy', $ap) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete {{ $ap->name }}?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted py-4">
                            <i class="bi bi-wifi-off" style="font-size: 2rem;"></i>
                            <p class="mt-2">No access points found.</p>
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
                    Showing {{ $accessPoints->firstItem() ?? 0 }} to {{ $accessPoints->lastItem() ?? 0 }}
                    of {{ $accessPoints->total() }} access points
                </small>
            </div>
            <div>
                {{ $accessPoints->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied: ' + text);
    });
}

// Ping single AP
function pingAP(id, ip) {
    const btn = document.getElementById(`ping-btn-${id}`);
    const originalHTML = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';

    fetch(`/access-points/${id}/ping`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update status badge
            const statusBadge = document.getElementById(`status-${id}`);
            if (data.status === 'online') {
                statusBadge.innerHTML = '<span class="badge bg-success"><i class="bi bi-circle-fill"></i> Online</span>';
            } else {
                statusBadge.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Offline</span>';
            }

            // Update latency
            const latencyCell = document.getElementById(`latency-${id}`);
            if (data.latency) {
                latencyCell.innerHTML = `<span class="badge bg-success">${data.latency.toFixed(1)} ms</span>`;
            } else {
                latencyCell.innerHTML = '<span class="badge bg-secondary">-</span>';
            }

            alert(data.status === 'online'
                ? `✅ ${ip} is online (${data.latency ? data.latency.toFixed(1) + ' ms' : 'N/A'})`
                : `❌ ${ip} is offline or unreachable`);
        }
    })
    .catch(err => {
        alert('⚠️ Ping test failed: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}

// Bulk select
const selectedAPs = new Set();

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.ap-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedAPs.add(cb.value);
        } else {
            selectedAPs.delete(cb.value);
        }
    });
    updateBulkActions();
}

function updateBulkActions() {
    selectedAPs.clear();
    document.querySelectorAll('.ap-checkbox:checked').forEach(cb => {
        selectedAPs.add(cb.value);
    });

    const count = selectedAPs.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActions').style.display = count > 0 ? 'block' : 'none';

    const allCheckboxes = document.querySelectorAll('.ap-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.ap-checkbox:checked');
    document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
}

function deselectAll() {
    document.querySelectorAll('.ap-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Bulk ping selected
function bulkPingSelected() {
    if (selectedAPs.size === 0) {
        alert('Please select at least one access point');
        return;
    }

    const apIds = Array.from(selectedAPs).join(',');
    bulkPingExecute(apIds);
}

// Ping all visible APs
function bulkPing() {
    const allIds = Array.from(document.querySelectorAll('.ap-checkbox')).map(cb => cb.value);
    if (allIds.length === 0) {
        alert('No access points to ping');
        return;
    }

    if (!confirm(`Ping all ${allIds.length} access points?`)) {
        return;
    }

    bulkPingExecute(allIds.join(','));
}

function bulkPingExecute(apIds) {
    // Show loading
    document.body.style.cursor = 'wait';

    fetch('/access-points/bulk-ping', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ap_ids: apIds })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            data.results.forEach(result => {
                const statusCell = document.getElementById(`status-${result.id}`);
                const latencyCell = document.getElementById(`latency-${result.id}`);

                if (result.status === 'online') {
                    statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-circle-fill"></i> Online</span>';
                    if (result.latency) {
                        latencyCell.innerHTML = `<span class="badge bg-success">${result.latency.toFixed(1)} ms</span>`;
                    }
                } else {
                    statusCell.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Offline</span>';
                    latencyCell.innerHTML = '<span class="badge bg-secondary">-</span>';
                }
            });

            alert(`✅ Ping completed for ${data.results.length} access point(s)`);
            location.reload();
        }
    })
    .catch(err => {
        alert('⚠️ Bulk ping failed: ' + err.message);
    })
    .finally(() => {
        document.body.style.cursor = 'default';
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
