@extends('layouts.admin')

@section('title', 'Alerts')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Device Alerts</h5>
        <p class="text-muted mb-0">Monitor and manage device alerts</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('acs.alert-rules.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-gear"></i> Alert Rules
        </a>
        <a href="{{ route('acs.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Alerts</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['new'] }}</h4>
                <small class="text-muted">New</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-warning">{{ $stats['acknowledged'] }}</h4>
                <small class="text-muted">Acknowledged</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['critical'] }}</h4>
                <small class="text-muted">Critical</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('acs.alerts.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="acknowledged" {{ request('status') == 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="severity" class="form-select">
                        <option value="">All Severity</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Info</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="alert_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="offline" {{ request('alert_type') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="signal_low" {{ request('alert_type') == 'signal_low' ? 'selected' : '' }}>Signal Low</option>
                        <option value="los" {{ request('alert_type') == 'los' ? 'selected' : '' }}>LOS</option>
                        <option value="no_inform" {{ request('alert_type') == 'no_inform' ? 'selected' : '' }}>No Inform</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span id="selectedCount">0</span> alert(s) selected
            </div>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-success" onclick="bulkAcknowledge()">
                    <i class="bi bi-check-circle"></i> Acknowledge
                </button>
                <button class="btn btn-primary" onclick="bulkResolve()">
                    <i class="bi bi-check-all"></i> Resolve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alerts List -->
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
                        <th>Alert</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Triggered</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr>
                        <td>
                            <input type="checkbox" class="alert-check" value="{{ $alert->id }}"
                                   onchange="updateBulkActions()">
                        </td>
                        <td>
                            <a href="{{ route('acs.show', $alert->ont) }}">
                                {{ $alert->ont->name }}
                            </a>
                            <br><small class="text-muted">{{ $alert->ont->sn }}</small>
                        </td>
                        <td>
                            <strong>{{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}</strong>
                            <br><small class="text-muted">{{ Str::limit($alert->message, 50) }}</small>
                        </td>
                        <td>
                            @php
                                $severityClass = match($alert->severity) {
                                    'critical' => 'danger',
                                    'warning' => 'warning',
                                    'info' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $severityClass }}">
                                {{ ucfirst($alert->severity) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($alert->status) {
                                    'new' => 'danger',
                                    'acknowledged' => 'warning',
                                    'resolved' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($alert->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $alert->triggered_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('acs.alerts.show', $alert) }}"
                                   class="btn btn-outline-info"
                                   title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($alert->status === 'new')
                                    <form action="{{ route('acs.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Acknowledge">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($alert->status, ['new', 'acknowledged']))
                                    <form action="{{ route('acs.alerts.resolve', $alert) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary" title="Resolve">
                                            <i class="bi bi-check-all"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No alerts found</p>
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
                    Showing {{ $alerts->firstItem() ?? 0 }} to {{ $alerts->lastItem() ?? 0 }}
                    of {{ $alerts->total() }} alerts
                </small>
            </div>
            <div>
                {{ $alerts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.alert-check').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.alert-check:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');

    countSpan.textContent = checked.length;
    bulkBar.style.display = checked.length > 0 ? 'block' : 'none';
}

function getSelectedAlerts() {
    return Array.from(document.querySelectorAll('.alert-check:checked'))
        .map(cb => cb.value);
}

function bulkAcknowledge() {
    const selected = getSelectedAlerts();

    if (selected.length === 0) {
        alert('Please select alerts first!');
        return;
    }

    if (!confirm(`Acknowledge ${selected.length} alert(s)?`)) return;

    fetch('/acs/alerts/bulk-acknowledge', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ alert_ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}

function bulkResolve() {
    const selected = getSelectedAlerts();

    if (selected.length === 0) {
        alert('Please select alerts first!');
        return;
    }

    if (!confirm(`Resolve ${selected.length} alert(s)?`)) return;

    fetch('/acs/alerts/bulk-resolve', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ alert_ids: selected })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
</script>
@endpush
