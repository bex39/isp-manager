@extends('layouts.admin')

@section('title', 'Bulk Operations')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Bulk Operations</h5>
        <p class="text-muted mb-0">Monitor and manage bulk device operations</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('acs.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Devices
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Operations</small>
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
                <h4 class="mb-0 text-success">{{ $stats['completed'] }}</h4>
                <small class="text-muted">Completed</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('acs.bulk.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="reboot" {{ request('type') == 'reboot' ? 'selected' : '' }}>Reboot</option>
                        <option value="provision" {{ request('type') == 'provision' ? 'selected' : '' }}>Provision</option>
                        <option value="wifi_update" {{ request('type') == 'wifi_update' ? 'selected' : '' }}>WiFi Update</option>
                        <option value="configure" {{ request('type') == 'configure' ? 'selected' : '' }}>Configure</option>
                        <option value="apply_template" {{ request('type') == 'apply_template' ? 'selected' : '' }}>Apply Template</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Operations List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Operation</th>
                        <th>Type</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $operation)
                    <tr>
                        <td>
                            <strong>{{ $operation->operation_name }}</strong>
                            <br><small class="text-muted">{{ $operation->total_devices }} device(s)</small>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst(str_replace('_', ' ', $operation->operation_type)) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                    @php
                                        $percentage = $operation->total_devices > 0
                                            ? round(($operation->processed_devices / $operation->total_devices) * 100)
                                            : 0;
                                    @endphp
                                    <div class="progress-bar" style="width: {{ $percentage }}%">
                                        {{ $percentage }}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ $operation->processed_devices }}/{{ $operation->total_devices }}
                                </small>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = match($operation->status) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($operation->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $operation->creator ? $operation->creator->name : 'System' }}</small>
                        </td>
                        <td>
                            <small>{{ $operation->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('acs.bulk.show', $operation) }}"
                                   class="btn btn-outline-info"
                                   title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($operation->status === 'failed' || $operation->failed_count > 0)
                                    <form action="{{ route('acs.bulk.retry', $operation) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Retry Failed">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($operation->status, ['pending', 'processing']))
                                    <form action="{{ route('acs.bulk.cancel', $operation) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Cancel this operation?')">
                                        @csrf
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
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No bulk operations found</p>
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
                    Showing {{ $operations->firstItem() ?? 0 }} to {{ $operations->lastItem() ?? 0 }}
                    of {{ $operations->total() }} operations
                </small>
            </div>
            <div>
                {{ $operations->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh for processing operations
setInterval(() => {
    const hasProcessing = {{ $operations->where('status', 'processing')->count() > 0 ? 'true' : 'false' }};
    if (hasProcessing) {
        location.reload();
    }
}, 10000); // Refresh every 10 seconds
</script>
@endpush
