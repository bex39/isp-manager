@extends('layouts.admin')

@section('title', 'Alert Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.alerts.index') }}">Alerts</a></li>
                <li class="breadcrumb-item active">Alert #{{ $alert->id }}</li>
            </ol>
        </nav>
        <h5 class="fw-bold mb-1">{{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}</h5>
        <p class="text-muted mb-0">
            <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">
                {{ ucfirst($alert->severity) }}
            </span>
            <span class="badge bg-{{ $alert->status === 'new' ? 'danger' : ($alert->status === 'acknowledged' ? 'warning' : 'success') }}">
                {{ ucfirst($alert->status) }}
            </span>
        </p>
    </div>
    <div class="col-md-4 text-end">
        @if($alert->status === 'new')
            <form action="{{ route('acs.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-check-circle"></i> Acknowledge
                </button>
            </form>
        @endif
        @if(in_array($alert->status, ['new', 'acknowledged']))
            <form action="{{ route('acs.alerts.resolve', $alert) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-all"></i> Resolve
                </button>
            </form>
        @endif
        <a href="{{ route('acs.alerts.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Alert Message -->
        <div class="alert alert-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }} mb-3">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}
            </h5>
            <p class="mb-0">{{ $alert->message }}</p>
        </div>

        <!-- Alert Details -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Alert Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="30%" class="text-muted">Alert Type:</td>
                        <td><strong>{{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Severity:</td>
                        <td>
                            <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($alert->severity) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="badge bg-{{ $alert->status === 'new' ? 'danger' : ($alert->status === 'acknowledged' ? 'warning' : 'success') }}">
                                {{ ucfirst($alert->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Triggered At:</td>
                        <td>{{ $alert->triggered_at->format('M d, Y H:i:s') }} ({{ $alert->triggered_at->diffForHumans() }})</td>
                    </tr>
                    @if($alert->acknowledged_at)
                    <tr>
                        <td class="text-muted">Acknowledged At:</td>
                        <td>{{ $alert->acknowledged_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Acknowledged By:</td>
                        <td>{{ $alert->acknowledgedBy ? $alert->acknowledgedBy->name : 'Unknown' }}</td>
                    </tr>
                    @endif
                    @if($alert->resolved_at)
                    <tr>
                        <td class="text-muted">Resolved At:</td>
                        <td>{{ $alert->resolved_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                    @endif
                    @if($alert->rule)
                    <tr>
                        <td class="text-muted">Alert Rule:</td>
                        <td>
                            <a href="{{ route('acs.alert-rules.show', $alert->rule) }}">
                                {{ $alert->rule->name }}
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Device Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Affected Device</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">{{ $alert->ont->name }}</h6>
                        <p class="text-muted mb-2">SN: <code>{{ $alert->ont->sn }}</code></p>
                        <div class="mb-2">
                            <span class="{{ $alert->ont->getStatusBadgeClass() }}">
                                {{ ucfirst($alert->ont->status) }}
                            </span>
                            @if($alert->ont->rx_power)
                                <span class="badge bg-secondary">Signal: {{ $alert->ont->rx_power }} dBm</span>
                            @endif
                        </div>
                        @if($alert->ont->customer)
                            <p class="mb-0"><small class="text-muted">Customer: {{ $alert->ont->customer->name }}</small></p>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('acs.show', $alert->ont) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye"></i> View Device
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Data -->
        @if($alert->alert_data)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Alert Data</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded mb-0"><code>{{ json_encode($alert->alert_data, JSON_PRETTY_PRINT) }}</code></pre>
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
                    @if($alert->status === 'new')
                        <form action="{{ route('acs.alerts.acknowledge', $alert) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Acknowledge Alert
                            </button>
                        </form>
                    @endif

                    @if(in_array($alert->status, ['new', 'acknowledged']))
                        <form action="{{ route('acs.alerts.resolve', $alert) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-all"></i> Resolve Alert
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('acs.show', $alert->ont) }}" class="btn btn-info">
                        <i class="bi bi-hdd-network"></i> Go to Device
                    </a>

                    <form action="{{ route('acs.alerts.destroy', $alert) }}" method="POST"
                          onsubmit="return confirm('Delete this alert?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete Alert
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Timeline</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-circle text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Alert Triggered</strong>
                                <br><small class="text-muted">{{ $alert->triggered_at->format('M d, Y H:i:s') }}</small>
                            </div>
                        </div>
                    </li>
                    @if($alert->acknowledged_at)
                    <li class="mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Acknowledged</strong>
                                <br><small class="text-muted">{{ $alert->acknowledged_at->format('M d, Y H:i:s') }}</small>
                                <br><small class="text-muted">By: {{ $alert->acknowledgedBy ? $alert->acknowledgedBy->name : 'Unknown' }}</small>
                            </div>
                        </div>
                    </li>
                    @endif
                    @if($alert->resolved_at)
                    <li>
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-all text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Resolved</strong>
                                <br><small class="text-muted">{{ $alert->resolved_at->format('M d, Y H:i:s') }}</small>
                            </div>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
