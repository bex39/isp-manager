@extends('layouts.admin')

@section('title', 'Device Details - ' . $ont->name)

@push('styles')
<style>
    .info-card {
        border-left: 4px solid #3b82f6;
    }
    .signal-meter {
        height: 20px;
        background: linear-gradient(to right, #ef4444, #eab308, #22c55e);
        border-radius: 10px;
        position: relative;
    }
    .signal-pointer {
        position: absolute;
        width: 3px;
        height: 100%;
        background: #000;
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="row mb-3">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.index') }}">ACS Management</a></li>
                <li class="breadcrumb-item active">{{ $ont->name }}</li>
            </ol>
        </nav>
        <h5 class="fw-bold mb-1">{{ $ont->name }}</h5>
        <p class="text-muted mb-0">SN: {{ $ont->sn }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('onts.show', $ont) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-box-arrow-up-right"></i> ONT Detail
        </a>
        <a href="{{ route('acs.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h5 class="mb-1">
                    <span class="{{ $ont->getStatusBadgeClass() }}">
                        {{ ucfirst($ont->status) }}
                    </span>
                </h5>
                <small class="text-muted">Device Status</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h5 class="mb-1">
                    @if($ont->rx_power)
                        {{ $ont->rx_power }} dBm
                    @else
                        N/A
                    @endif
                </h5>
                <small class="text-muted">Signal Strength</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h5 class="mb-1">
                    @if($ont->isAcsManaged())
                        <span class="badge bg-success">Managed</span>
                    @else
                        <span class="badge bg-secondary">Unmanaged</span>
                    @endif
                </h5>
                <small class="text-muted">ACS Status</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h5 class="mb-1 text-danger">{{ $deviceStats['active_alerts'] }}</h5>
                <small class="text-muted">Active Alerts</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- ========== LEFT COLUMN (col-lg-8) ========== -->
    <div class="col-lg-8">
        <!-- Device Info -->
        <div class="card border-0 shadow-sm mb-3 info-card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Device Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="text-muted">Serial Number:</td>
                                <td><code>{{ $ont->sn }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Model:</td>
                                <td>{{ $ont->model ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Management IP:</td>
                                <td>
                                    @if($ont->management_ip)
                                        <code>{{ $ont->management_ip }}</code>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">PON Type:</td>
                                <td>{{ $ont->pon_type ?? 'GPON' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">PON Port:</td>
                                <td>{{ $ont->pon_port ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">ONT ID:</td>
                                <td>{{ $ont->ont_id ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="text-muted">OLT:</td>
                                <td>
                                    @if($ont->olt)
                                        <a href="{{ route('olts.show', $ont->olt) }}">
                                            {{ $ont->olt->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Customer:</td>
                                <td>
                                    @if($ont->customer)
                                        {{ $ont->customer->name }}
                                    @else
                                        <span class="text-muted">No customer</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">ODP:</td>
                                <td>
                                    @if($ont->odp)
                                        {{ $ont->odp->name }} (Port: {{ $ont->odp_port }})
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">WiFi SSID:</td>
                                <td>{{ $ont->wifi_ssid ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Seen:</td>
                                <td>
                                    @if($ont->last_seen)
                                        {{ $ont->last_seen->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Provision:</td>
                                <td>
                                    @if($ont->last_provision_at)
                                        {{ $ont->last_provision_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signal Quality -->
        @if($ont->rx_power)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Signal Quality</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">RX Power</label>
                        <h4>{{ $ont->rx_power }} dBm</h4>
                        <div class="signal-meter">
                            @php
                                $power = (float) $ont->rx_power;
                                $position = (($power + 30) / 10) * 100;
                                $position = max(0, min(100, $position));
                            @endphp
                            <div class="signal-pointer" style="left: {{ $position }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-danger">Poor (-30)</small>
                            <small class="text-warning">Fair (-25)</small>
                            <small class="text-success">Good (-20)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TX Power</label>
                        <h4>{{ $ont->tx_power ?? 'N/A' }} dBm</h4>
                        <p class="text-muted mb-0">Quality: <strong>{{ $ont->getSignalQuality() }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Configuration History -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Configuration History</h6>
            </div>
            <div class="card-body">
                @if(isset($ont->configHistories) && $ont->configHistories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>User</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ont->configHistories as $history)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $history->action)) }}</td>
                                    <td>
                                        @if($history->status === 'success')
                                            <span class="badge bg-success">Success</span>
                                        @elseif($history->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($history->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($history->executor)
                                            {{ $history->executor->name }}
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $history->created_at->diffForHumans() }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No configuration history available</p>
                @endif
            </div>
        </div>

        <!-- Active Alerts -->
        @if(isset($ont->alerts) && $ont->alerts->count() > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Active Alerts</h6>
            </div>
            <div class="card-body">
                @foreach($ont->alerts as $alert)
                <div class="alert alert-{{ $alert->severity === 'critical' ? 'danger' : 'warning' }} mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $alert->alert_type }}</strong>
                            <p class="mb-0">{{ $alert->message }}</p>
                            <small class="text-muted">{{ $alert->triggered_at->diffForHumans() }}</small>
                        </div>
                        <div>
                            <form action="{{ route('acs.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    Acknowledge
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    <!-- ✅ CLOSE LEFT COLUMN -->

    <!-- ========== RIGHT COLUMN (col-lg-4) ========== -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(!$ont->isAcsManaged())
                        <form action="{{ route('acs.enroll', $ont) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Enroll to ACS
                            </button>
                        </form>
                    @else
                        <form action="{{ route('acs.unenroll', $ont) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Unenroll from ACS
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('acs.provision', $ont) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-lightning"></i> Provision Device
                        </button>
                    </form>

                    <form action="{{ route('acs.reprovision', $ont) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-arrow-repeat"></i> Re-provision
                        </button>
                    </form>

                    <form action="{{ route('acs.reboot', $ont) }}" method="POST"
                          onsubmit="return confirm('Reboot this device?')">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-arrow-clockwise"></i> Reboot Device
                        </button>
                    </form>

                    <form action="{{ route('acs.check-signal', $ont) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-reception-4"></i> Check Signal
                        </button>
                    </form>

                    <form action="{{ route('acs.refresh-session', $ont) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Session
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Configs:</td>
                        <td class="text-end"><strong>{{ $deviceStats['total_configs'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Successful:</td>
                        <td class="text-end text-success"><strong>{{ $deviceStats['successful_configs'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Failed:</td>
                        <td class="text-end text-danger"><strong>{{ $deviceStats['failed_configs'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Active Alerts:</td>
                        <td class="text-end text-warning"><strong>{{ $deviceStats['active_alerts'] }}</strong></td>
                    </tr>
                    @if($deviceStats['days_since_provision'])
                    <tr>
                        <td class="text-muted">Days Since Provision:</td>
                        <td class="text-end"><strong>{{ $deviceStats['days_since_provision'] }}</strong></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- ACS Session Info -->
        @if($ont->session)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">ACS Session</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Session ID:</td>
                        <td><small><code>{{ substr($ont->session->session_id, 0, 12) }}...</code></small></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Inform:</td>
                        <td>
                            @if($ont->session->last_inform)
                                <small>{{ $ont->session->last_inform->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Never</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Boot:</td>
                        <td>
                            @if($ont->session->last_boot)
                                <small>{{ $ont->session->last_boot->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Unknown</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Uptime:</td>
                        <td><small>{{ $ont->session->uptime ?? 'Unknown' }}</small></td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>
    <!-- ✅ CLOSE RIGHT COLUMN -->
</div>
<!-- ✅ CLOSE MAIN ROW -->
@endsection
