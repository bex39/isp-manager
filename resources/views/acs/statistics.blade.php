@extends('layouts.admin')

@section('title', 'ACS Statistics')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">ACS Statistics Dashboard</h5>
        <p class="text-muted mb-0">Overview of ACS management system performance</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('acs.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Devices
        </a>
    </div>
</div>

<!-- Devices Statistics -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">Device Statistics</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-primary mb-0">{{ $stats['devices']['total'] }}</h3>
                    <small class="text-muted">Total Devices</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-success mb-0">{{ $stats['devices']['online'] }}</h3>
                    <small class="text-muted">Online</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-danger mb-0">{{ $stats['devices']['offline'] }}</h3>
                    <small class="text-muted">Offline</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-warning mb-0">{{ $stats['devices']['los'] }}</h3>
                    <small class="text-muted">LOS</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ACS Management Statistics -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">ACS Management</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-center">
                    <h3 class="text-info mb-0">{{ $stats['acs']['managed'] }}</h3>
                    <small class="text-muted">ACS Managed</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h3 class="text-secondary mb-0">{{ $stats['acs']['unmanaged'] }}</h3>
                    <small class="text-muted">Unmanaged</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h3 class="text-success mb-0">{{ $stats['acs']['auto_provision_enabled'] }}</h3>
                    <small class="text-muted">Auto-Provision Enabled</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signal Quality Statistics -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">Signal Quality Distribution</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-success mb-0">{{ $stats['signal']['excellent'] }}</h3>
                    <small class="text-muted">Excellent (≥ -20 dBm)</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-info mb-0">{{ $stats['signal']['good'] }}</h3>
                    <small class="text-muted">Good (-23 to -20 dBm)</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-warning mb-0">{{ $stats['signal']['fair'] }}</h3>
                    <small class="text-muted">Fair (-25 to -23 dBm)</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h3 class="text-danger mb-0">{{ $stats['signal']['poor'] }}</h3>
                    <small class="text-muted">Poor (< -25 dBm)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <!-- Provisioning Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Provisioning Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Provisions:</td>
                        <td class="text-end"><strong>{{ $stats['provisioning']['total'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Today:</td>
                        <td class="text-end"><strong>{{ $stats['provisioning']['today'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Successful:</td>
                        <td class="text-end text-success"><strong>{{ $stats['provisioning']['success'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Failed:</td>
                        <td class="text-end text-danger"><strong>{{ $stats['provisioning']['failed'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Success Rate:</td>
                        <td class="text-end">
                            <strong>
                                @php
                                    $rate = $stats['provisioning']['total'] > 0
                                        ? round(($stats['provisioning']['success'] / $stats['provisioning']['total']) * 100, 1)
                                        : 0;
                                @endphp
                                {{ $rate }}%
                            </strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Alerts Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Alert Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Alerts:</td>
                        <td class="text-end"><strong>{{ $stats['alerts']['total'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">New:</td>
                        <td class="text-end text-danger"><strong>{{ $stats['alerts']['new'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Acknowledged:</td>
                        <td class="text-end text-warning"><strong>{{ $stats['alerts']['acknowledged'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Critical:</td>
                        <td class="text-end text-danger"><strong>{{ $stats['alerts']['critical'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Active Alerts:</td>
                        <td class="text-end">
                            <strong>{{ $stats['alerts']['new'] + $stats['alerts']['acknowledged'] }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">Recent Activities</h6>
    </div>
    <div class="card-body">
        @if($recentActivities->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Device</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                        <tr>
                            <td>
                                <a href="{{ route('acs.show', $activity->ont) }}">
                                    {{ $activity->ont->name }}
                                </a>
                                <br><small class="text-muted">{{ $activity->ont->sn }}</small>
                            </td>
                            <td>
                                <small>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</small>
                            </td>
                            <td>
                                @if($activity->status === 'success')
                                    <span class="badge bg-success">Success</span>
                                @elseif($activity->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($activity->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $activity->executor ? $activity->executor->name : 'System' }}</small>
                            </td>
                            <td>
                                <small>{{ $activity->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center mb-0">No recent activities</p>
        @endif
    </div>
</div>
@endsection
