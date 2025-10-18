@extends('layouts.admin')

@section('title', 'Alert Rule Details - ' . $rule->name)

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.alert-rules.index') }}">Alert Rules</a></li>
                <li class="breadcrumb-item active">{{ $rule->name }}</li>
            </ol>
        </nav>
        <h5 class="fw-bold mb-1">{{ $rule->name }}</h5>
        <p class="text-muted mb-0">
            @if($rule->is_active)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-secondary">Inactive</span>
            @endif
            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $rule->condition_type)) }}</span>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <form action="{{ route('acs.alert-rules.toggle', $rule) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-{{ $rule->is_active ? 'warning' : 'success' }} btn-sm">
                <i class="bi bi-toggle-{{ $rule->is_active ? 'on' : 'off' }}"></i>
                {{ $rule->is_active ? 'Disable' : 'Enable' }}
            </button>
        </form>
        <a href="{{ route('acs.alert-rules.edit', $rule) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('acs.alert-rules.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total_alerts'] }}</h4>
                <small class="text-muted">Total Alerts</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $stats['today'] }}</h4>
                <small class="text-muted">Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-warning">{{ $stats['this_week'] }}</h4>
                <small class="text-muted">This Week</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['active'] }}</h4>
                <small class="text-muted">Active</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Rule Configuration -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Rule Configuration</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="30%" class="text-muted">Rule Name:</td>
                        <td><strong>{{ $rule->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Condition Type:</td>
                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $rule->condition_type)) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            @if($rule->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Check Interval:</td>
                        <td>{{ $rule->check_interval }} seconds</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cooldown Period:</td>
                        <td>{{ $rule->cooldown_period }} seconds</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $rule->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Updated:</td>
                        <td>{{ $rule->updated_at->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Condition Parameters -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Condition Parameters</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded mb-0"><code>{{ json_encode($rule->condition_parameters, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Notification Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Channels:</label>
                    <div>
                        @foreach($rule->notification_channels as $channel)
                            <span class="badge bg-secondary">{{ ucfirst($channel) }}</span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="form-label">Recipients:</label>
                    <div>
                        @foreach($rule->recipients as $recipient)
                            <span class="badge bg-info">{{ $recipient }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Alerts -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Recent Alerts Triggered</h6>
                <a href="{{ route('acs.alerts.index', ['rule_id' => $rule->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($rule->alerts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Triggered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rule->alerts->take(10) as $alert)
                                <tr>
                                    <td>
                                        <a href="{{ route('acs.show', $alert->ont) }}">
                                            {{ $alert->ont->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $alert->ont->sn }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">
                                            {{ ucfirst($alert->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $alert->status === 'new' ? 'danger' : ($alert->status === 'acknowledged' ? 'warning' : 'success') }}">
                                            {{ ucfirst($alert->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $alert->triggered_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No alerts triggered yet</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('acs.alert-rules.edit', $rule) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Rule
                    </a>

                    <form action="{{ route('acs.alert-rules.toggle', $rule) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-{{ $rule->is_active ? 'warning' : 'success' }} w-100">
                            <i class="bi bi-toggle-{{ $rule->is_active ? 'on' : 'off' }}"></i>
                            {{ $rule->is_active ? 'Disable Rule' : 'Enable Rule' }}
                        </button>
                    </form>

                    <a href="{{ route('acs.alerts.index', ['rule_id' => $rule->id]) }}" class="btn btn-info">
                        <i class="bi bi-bell"></i> View Alerts
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Triggered:</td>
                        <td class="text-end"><strong>{{ $stats['total_alerts'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Active Alerts:</td>
                        <td class="text-end text-danger"><strong>{{ $stats['active'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Today:</td>
                        <td class="text-end"><strong>{{ $stats['today'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">This Week:</td>
                        <td class="text-end"><strong>{{ $stats['this_week'] }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
