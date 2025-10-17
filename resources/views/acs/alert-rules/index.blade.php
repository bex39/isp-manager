@extends('layouts.admin')

@section('title', 'Alert Rules')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Alert Rules</h5>
        <p class="text-muted mb-0">Configure automatic alert rules for device monitoring</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('acs.alerts.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-bell"></i> View Alerts
        </a>
        <a href="{{ route('acs.alert-rules.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Create Rule
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Rules</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
                <small class="text-muted">Active</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-secondary">{{ $stats['inactive'] }}</h4>
                <small class="text-muted">Inactive</small>
            </div>
        </div>
    </div>
</div>

<!-- Rules List -->
<div class="row">
    @forelse($rules as $rule)
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">{{ $rule->name }}</h6>
                    <div>
                        @if($rule->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $rule->condition_type)) }}</span>
                </p>

                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td class="text-muted small">Check Interval:</td>
                        <td class="small"><strong>{{ $rule->check_interval }}s</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Cooldown:</td>
                        <td class="small"><strong>{{ $rule->cooldown_period }}s</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Channels:</td>
                        <td class="small">
                            @foreach($rule->notification_channels as $channel)
                                <span class="badge bg-secondary">{{ $channel }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>

                <div class="d-grid gap-2">
                    <a href="{{ route('acs.alert-rules.show', $rule) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('acs.alert-rules.edit', $rule) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('acs.alert-rules.toggle', $rule) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $rule->is_active ? 'warning' : 'success' }}">
                                <i class="bi bi-toggle-{{ $rule->is_active ? 'on' : 'off' }}"></i>
                                {{ $rule->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form action="{{ route('acs.alert-rules.destroy', $rule) }}" method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Delete this rule?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-gear" style="font-size: 4rem; color: #ccc;"></i>
                <h5 class="mt-3">No Alert Rules</h5>
                <p class="text-muted">Create your first alert rule to monitor devices</p>
                <a href="{{ route('acs.alert-rules.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Alert Rule
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($rules->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $rules->links() }}
</div>
@endif
@endsection
