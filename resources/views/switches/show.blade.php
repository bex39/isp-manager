@extends('layouts.admin')

@section('title', 'Switch Details - ' . $switch->name)

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('switches.index') }}">Switches</a></li>
                <li class="breadcrumb-item active">{{ $switch->name }}</li>
            </ol>
        </nav>
        <h5 class="fw-bold mb-1">{{ $switch->name }}</h5>
        <p class="text-muted mb-0">
            <span class="{{ $switch->getStatusBadgeClass() }}">
                {{ ucfirst($switch->status ?? 'Unknown') }}
            </span>
            @if($switch->isManaged())
                <span class="badge bg-success">Managed</span>
            @else
                <span class="badge bg-secondary">Unmanaged</span>
            @endif
            @if(!$switch->is_active)
                <span class="badge bg-danger">Inactive</span>
            @endif
        </p>
    </div>
    <div class="col-md-4 text-end">
        @if($switch->isManaged())
            <form action="{{ route('switches.check-status', $switch) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-arrow-repeat"></i> Check Status
                </button>
            </form>
        @endif
        <a href="{{ route('switches.edit', $switch) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('switches.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Basic Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="30%" class="text-muted">Switch Name:</td>
                        <td><strong>{{ $switch->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Brand:</td>
                        <td>
                            <span class="badge bg-secondary">{{ $switch->getBrandDisplayName() }}</span>
                        </td>
                    </tr>
                    @if($switch->model)
                    <tr>
                        <td class="text-muted">Model:</td>
                        <td><code>{{ $switch->model }}</code></td>
                    </tr>
                    @endif
                    @if($switch->ip_address)
                    <tr>
                        <td class="text-muted">IP Address:</td>
                        <td><code>{{ $switch->ip_address }}</code></td>
                    </tr>
                    @endif
                    @if($switch->mac_address)
                    <tr>
                        <td class="text-muted">MAC Address:</td>
                        <td><code>{{ $switch->mac_address }}</code></td>
                    </tr>
                    @endif
                    @if($switch->port_count)
                    <tr>
                        <td class="text-muted">Port Count:</td>
                        <td><span class="badge bg-info">{{ $switch->port_count }} ports</span></td>
                    </tr>
                    @endif
                    @if($switch->location)
                    <tr>
                        <td class="text-muted">Location:</td>
                        <td>
                            <i class="bi bi-geo-alt"></i> {{ $switch->location }}
                            @if($switch->latitude && $switch->longitude)
                                <br><small class="text-muted">
                                    Coordinates: {{ $switch->latitude }}, {{ $switch->longitude }}
                                </small>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Active:</td>
                        <td>
                            @if($switch->is_active)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Management Info (for Managed Switches) -->
        @if($switch->isManaged())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Management Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="30%" class="text-muted">Username:</td>
                        <td><code>{{ $switch->username }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">SSH Port:</td>
                        <td><code>{{ $switch->ssh_port ?? 22 }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Connection String:</td>
                        <td>
                            <code>ssh {{ $switch->username }}@{{ $switch->ip_address }} -p {{ $switch->ssh_port ?? 22 }}</code>
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('ssh {{ $switch->username }}@{{ $switch->ip_address }} -p {{ $switch->ssh_port ?? 22 }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Status Information (for Managed Switches) -->
        @if($switch->isManaged())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Status Information</h6>
                <form action="{{ route('switches.check-status', $switch) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-arrow-repeat"></i> Refresh
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <h3 class="mb-0">
                            <span class="{{ $switch->getStatusBadgeClass() }}">
                                {{ ucfirst($switch->status ?? 'Unknown') }}
                            </span>
                        </h3>
                        <small class="text-muted">Current Status</small>
                    </div>
                    <div class="col-md-4 text-center">
                        @if($switch->ping_latency)
                            <h3 class="mb-0 {{ $switch->getLatencyColorClass() }}">
                                {{ $switch->ping_latency }}ms
                            </h3>
                            <small class="text-muted">Latency</small>
                        @else
                            <h3 class="mb-0 text-muted">N/A</h3>
                            <small class="text-muted">Latency</small>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        @if($switch->last_seen)
                            <h6 class="mb-0">{{ $switch->last_seen->diffForHumans() }}</h6>
                            <small class="text-muted">Last Seen</small>
                        @else
                            <h6 class="mb-0 text-muted">Never</h6>
                            <small class="text-muted">Last Seen</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($switch->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $switch->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('switches.edit', $switch) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Switch
                    </a>

                    @if($switch->isManaged())
                        <form action="{{ route('switches.check-status', $switch) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-arrow-repeat"></i> Check Status
                            </button>
                        </form>
                    @endif

                    @if($switch->ip_address)
                        <a href="http://{{ $switch->ip_address }}" target="_blank" class="btn btn-info">
                            <i class="bi bi-box-arrow-up-right"></i> Open Web Interface
                        </a>
                    @endif

                    <form action="{{ route('switches.destroy', $switch) }}" method="POST"
                          onsubmit="return confirm('Delete this switch?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete Switch
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Timestamps</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $switch->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td>{{ $switch->updated_at->diffForHumans() }}</td>
                    </tr>
                    @if($switch->deleted_at)
                    <tr>
                        <td class="text-muted">Deleted:</td>
                        <td>{{ $switch->deleted_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    });
}
</script>
@endpush
