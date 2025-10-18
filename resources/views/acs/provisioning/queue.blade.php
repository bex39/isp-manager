@extends('layouts.admin')

@section('title', 'Provisioning Queue')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Provisioning Queue</h5>
        <p class="text-muted mb-0">Monitor and manage device provisioning jobs</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-success btn-sm" onclick="processQueue()">
            <i class="bi bi-play-circle"></i> Process Queue
        </button>
        <button class="btn btn-danger btn-sm" onclick="clearFailed()">
            <i class="bi bi-trash"></i> Clear Failed
        </button>
        <a href="{{ route('acs.index') }}" class="btn btn-secondary btn-sm">
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
                <small class="text-muted">Total Jobs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-warning">{{ $stats['pending'] }}</h4>
                <small class="text-muted">Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $stats['processing'] }}</h4>
                <small class="text-muted">Processing</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['failed'] }}</h4>
                <small class="text-muted">Failed</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('acs.provisioning.queue') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="olt_id" class="form-select">
                        <option value="">All OLTs</option>
                        @foreach($olts as $olt)
                            <option value="{{ $olt->id }}" {{ request('olt_id') == $olt->id ? 'selected' : '' }}>
                                {{ $olt->name }}
                            </option>
                        @endforeach
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

<!-- Queue List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Device</th>
                        <th>OLT</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Retry</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queue as $job)
                    <tr>
                        <td>
                            <a href="{{ route('acs.show', $job->ont) }}">
                                {{ $job->ont->name }}
                            </a>
                            <br><small class="text-muted">SN: {{ $job->sn }}</small>
                        </td>
                        <td>
                            <small>{{ $job->olt ? $job->olt->name : '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst(str_replace('_', ' ', $job->provision_type)) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $priorityClass = match($job->priority) {
                                    'high' => 'danger',
                                    'normal' => 'primary',
                                    'low' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $priorityClass }}">
                                {{ ucfirst($job->priority) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($job->status) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($job->status) }}
                            </span>
                            @if($job->error_message)
                                <br><small class="text-danger">{{ Str::limit($job->error_message, 30) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $job->retry_count }}</span>
                        </td>
                        <td>
                            <small>{{ $job->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($job->status === 'failed')
                                    <form action="{{ route('acs.provisioning.retry', $job) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Retry">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($job->status, ['pending', 'failed']))
                                    <form action="{{ route('acs.provisioning.cancel', $job) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Cancel this job?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Cancel">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No jobs in queue</p>
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
                    Showing {{ $queue->firstItem() ?? 0 }} to {{ $queue->lastItem() ?? 0 }}
                    of {{ $queue->total() }} jobs
                </small>
            </div>
            <div>
                {{ $queue->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function processQueue() {
    if (!confirm('Start processing provisioning queue?')) return;

    fetch('/acs/provisioning/process', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Queue processing started!');
        location.reload();
    });
}

function clearFailed() {
    if (!confirm('Clear all failed jobs?')) return;

    fetch('/acs/provisioning/clear-failed', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Failed jobs cleared!');
        location.reload();
    });
}

// Auto-refresh if processing
@if($stats['processing'] > 0)
setInterval(() => {
    location.reload();
}, 10000); // Refresh every 10 seconds
@endif
</script>
@endpush
