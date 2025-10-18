@extends('layouts.admin')

@section('title', 'Unprovisioned Devices')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Unprovisioned Devices</h5>
        <p class="text-muted mb-0">Devices that need provisioning or are offline</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-success btn-sm" onclick="provisionAll()">
            <i class="bi bi-lightning"></i> Provision All
        </button>
        <a href="{{ route('acs.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mb-3">
    <i class="bi bi-info-circle"></i>
    <strong>Note:</strong> These devices have never been provisioned or are currently offline.
    You can provision them individually or in bulk.
</div>

<!-- Bulk Actions Bar -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span id="selectedCount">0</span> device(s) selected
            </div>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-success" onclick="bulkProvision()">
                    <i class="bi bi-lightning"></i> Provision Selected
                </button>
                <button class="btn btn-info" onclick="bulkEnroll()">
                    <i class="bi bi-plus-circle"></i> Enroll to ACS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Devices List -->
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
                        <th>Last Seen</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr>
                        <td>
                            <input type="checkbox" class="device-check" value="{{ $device->id }}"
                                   onchange="updateBulkActions()">
                        </td>
                        <td>
                            <a href="{{ route('acs.show', $device) }}">
                                <strong>{{ $device->name }}</strong>
                            </a>
                            <br><small class="text-muted">{{ $device->sn }}</small>
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
                                <span class="{{ $device->getSignalBadgeClass() }}">
                                    {{ $device->rx_power }} dBm
                                </span>
                            @else
                                <span class="text-muted">N/A</span>
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
                                <form action="{{ route('acs.provision', $device) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Provision">
                                        <i class="bi bi-lightning"></i>
                                    </button>
                                </form>
                                @if(!$device->isAcsManaged())
                                    <form action="{{ route('acs.enroll', $device) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary" title="Enroll to ACS">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-check-circle" style="font-size: 3rem; color: #22c55e;"></i>
                            <h5 class="mt-3">All Devices Provisioned!</h5>
                            <p class="text-muted">No unprovisioned devices found</p>
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
                {{ $devices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.device-check').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.device-check:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');

    countSpan.textContent = checked.length;
    bulkBar.style.display = checked.length > 0 ? 'block' : 'none';
}

function getSelectedDevices() {
    return Array.from(document.querySelectorAll('.device-check:checked'))
        .map(cb => cb.value);
}

function provisionAll() {
    if (!confirm('Provision all unprovisioned devices?')) return;

    const allDevices = Array.from(document.querySelectorAll('.device-check'))
        .map(cb => cb.value);

    fetch('/acs/bulk/provision', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ont_ids: allDevices })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}

function bulkProvision() {
    const selected = getSelectedDevices();

    if (selected.length === 0) {
        alert('Please select devices first!');
        return;
    }

    if (!confirm(`Provision ${selected.length} device(s)?`)) return;

    fetch('/acs/bulk/provision', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ont_ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}

function bulkEnroll() {
    const selected = getSelectedDevices();

    if (selected.length === 0) {
        alert('Please select devices first!');
        return;
    }

    if (!confirm(`Enroll ${selected.length} device(s) to ACS?`)) return;

    // Process each device individually
    let promises = selected.map(id => {
        return fetch(`/acs/devices/${id}/enroll`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    });

    Promise.all(promises).then(() => {
        alert('Devices enrolled successfully!');
        location.reload();
    });
}
</script>
@endpush
