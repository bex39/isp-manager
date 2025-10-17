@extends('layouts.admin')

@section('title', 'Bulk Operation Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.bulk.index') }}">Bulk Operations</a></li>
                <li class="breadcrumb-item active">{{ $bulkOperation->operation_name }}</li>
            </ol>
        </nav>
        <h5 class="fw-bold mb-1">{{ $bulkOperation->operation_name }}</h5>
        <p class="text-muted mb-0">
            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $bulkOperation->operation_type)) }}</span>
            <span class="badge bg-{{ $bulkOperation->status === 'completed' ? 'success' : ($bulkOperation->status === 'failed' ? 'danger' : 'warning') }}">
                {{ ucfirst($bulkOperation->status) }}
            </span>
        </p>
    </div>
    <div class="col-md-4 text-end">
        @if($bulkOperation->status === 'failed' || $bulkOperation->failed_count > 0)
            <form action="{{ route('acs.bulk.retry', $bulkOperation) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-arrow-repeat"></i> Retry Failed
                </button>
            </form>
        @endif
        @if(in_array($bulkOperation->status, ['pending', 'processing']))
            <form action="{{ route('acs.bulk.cancel', $bulkOperation) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Cancel this operation?')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            </form>
        @endif
        <a href="{{ route('acs.bulk.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Progress Overview -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $bulkOperation->total_devices }}</h4>
                <small class="text-muted">Total Devices</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $bulkOperation->processed_devices }}</h4>
                <small class="text-muted">Processed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-success">{{ $bulkOperation->success_count }}</h4>
                <small class="text-muted">Successful</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $bulkOperation->failed_count }}</h4>
                <small class="text-muted">Failed</small>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Overall Progress</h6>
        @php
            $percentage = $bulkOperation->total_devices > 0
                ? round(($bulkOperation->processed_devices / $bulkOperation->total_devices) * 100)
                : 0;
            $successRate = $bulkOperation->processed_devices > 0
                ? round(($bulkOperation->success_count / $bulkOperation->processed_devices) * 100)
                : 0;
        @endphp
        <div class="progress mb-2" style="height: 30px;">
            <div class="progress-bar bg-success" style="width: {{ ($bulkOperation->success_count / $bulkOperation->total_devices) * 100 }}%">
                Success: {{ $bulkOperation->success_count }}
            </div>
            <div class="progress-bar bg-danger" style="width: {{ ($bulkOperation->failed_count / $bulkOperation->total_devices) * 100 }}%">
                Failed: {{ $bulkOperation->failed_count }}
            </div>
            <div class="progress-bar bg-secondary" style="width: {{ (($bulkOperation->total_devices - $bulkOperation->processed_devices) / $bulkOperation->total_devices) * 100 }}%">
                Pending: {{ $bulkOperation->total_devices - $bulkOperation->processed_devices }}
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <small class="text-muted">{{ $percentage }}% Complete</small>
            <small class="text-muted">Success Rate: {{ $successRate }}%</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Device Results -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Device Results</h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="filterResults('all')">All</button>
                    <button class="btn btn-outline-success" onclick="filterResults('success')">Success</button>
                    <button class="btn btn-outline-danger" onclick="filterResults('failed')">Failed</button>
                    <button class="btn btn-outline-warning" onclick="filterResults('pending')">Pending</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Device</th>
                                <th>Status</th>
                                <th>Error</th>
                                <th>Processed At</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTable">
                            @foreach($bulkOperation->details as $detail)
                            <tr data-status="{{ $detail->status }}">
                                <td>
                                    <a href="{{ route('acs.show', $detail->ont) }}">
                                        {{ $detail->ont->name }}
                                    </a>
                                    <br><small class="text-muted">{{ $detail->ont->sn }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($detail->status) {
                                            'success' => 'success',
                                            'failed' => 'danger',
                                            'processing' => 'info',
                                            'pending' => 'warning',
                                            'cancelled' => 'secondary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($detail->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($detail->error_message)
                                        <small class="text-danger">{{ Str::limit($detail->error_message, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->processed_at)
                                        <small>{{ $detail->processed_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not processed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Operation Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Operation Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Type:</td>
                        <td><strong>{{ ucfirst(str_replace('_', ' ', $bulkOperation->operation_type)) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="badge bg-{{ $bulkOperation->status === 'completed' ? 'success' : ($bulkOperation->status === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst($bulkOperation->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created By:</td>
                        <td>{{ $bulkOperation->creator ? $bulkOperation->creator->name : 'System' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td><small>{{ $bulkOperation->created_at->format('M d, Y H:i') }}</small></td>
                    </tr>
                    @if($bulkOperation->started_at)
                    <tr>
                        <td class="text-muted">Started:</td>
                        <td><small>{{ $bulkOperation->started_at->format('M d, Y H:i') }}</small></td>
                    </tr>
                    @endif
                    @if($bulkOperation->completed_at)
                    <tr>
                        <td class="text-muted">Completed:</td>
                        <td><small>{{ $bulkOperation->completed_at->format('M d, Y H:i') }}</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Duration:</td>
                        <td><small>{{ $bulkOperation->started_at->diffForHumans($bulkOperation->completed_at, true) }}</small></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Parameters -->
        @if($bulkOperation->parameters)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Parameters</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-2 rounded small mb-0"><code>{{ json_encode($bulkOperation->parameters, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterResults(status) {
    const rows = document.querySelectorAll('#resultsTable tr');

    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Auto-refresh if processing
@if(in_array($bulkOperation->status, ['pending', 'processing']))
setInterval(() => {
    location.reload();
}, 5000); // Refresh every 5 seconds
@endif
</script>
@endpush
