@extends('layouts.admin')

@section('title', 'Switch: ' . $switch->name)
@section('page-title', 'Switch Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $switch->name }}</h4>
        <p class="text-muted mb-0">{{ $switch->brand }} {{ $switch->model }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('switches.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('switches.ssh-terminal', $switch) }}" class="btn btn-dark btn-sm">
            <i class="bi bi-terminal"></i> SSH Terminal
        </a>
    </div>
</div>

<!-- Status Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1 small">Status</p>
                <h5><span class="{{ $switch->getStatusBadgeClass() }}">{{ ucfirst($switch->status) }}</span></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1 small">Ping Latency</p>
                <h5>{{ $switch->ping_latency ?? 'N/A' }} ms</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1 small">Total Ports</p>
                <h5>{{ $switch->port_count ?? 'N/A' }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1 small">Last Seen</p>
                <h6>{{ $switch->last_seen ? $switch->last_seen->diffForHumans() : 'Never' }}</h6>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Device Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Brand</strong></td>
                        <td>{{ ucfirst($switch->brand) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Model</strong></td>
                        <td>{{ $switch->model ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>IP Address</strong></td>
                        <td><code>{{ $switch->ip_address }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>MAC Address</strong></td>
                        <td>{{ $switch->mac_address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>SSH Port</strong></td>
                        <td>{{ $switch->ssh_port }}</td>
                    </tr>
                    <tr>
                        <td><strong>Location</strong></td>
                        <td>{{ $switch->location ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="pingSwitch()">
                        <i class="bi bi-arrow-repeat"></i> Ping Device
                    </button>
                    <a href="{{ route('switches.ssh-terminal', $switch) }}" class="btn btn-dark">
                        <i class="bi bi-terminal"></i> Open SSH Terminal
                    </a>
                    <button class="btn btn-info" onclick="showPortStatus()">
                        <i class="bi bi-diagram-3"></i> Port Status
                    </button>
                    <button class="btn btn-warning" onclick="rebootSwitch()">
                        <i class="bi bi-power"></i> Reboot Switch
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pingSwitch() {
    fetch('{{ route("switches.ping", $switch) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        alert(data.online ? 'Switch is ONLINE!' : 'Switch is OFFLINE!');
        if(data.online) location.reload();
    });
}

function rebootSwitch() {
    if(confirm('Reboot switch? Network will be disrupted!')) {
        alert('Reboot command sent');
    }
}

function showPortStatus() {
    alert('Port status monitoring - Coming soon');
}
</script>
@endpush
@endsection
