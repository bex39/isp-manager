@extends('layouts.admin')

<!-- views/fiber/cores/index.blade.php -->

@section('title', 'Fiber Cores')
@section('page-title', 'Fiber Core Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Fiber Core Management</h5>
        <p class="text-muted mb-0">Manage individual fiber cores in cable segments</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Core
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('cores.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cable Segment</label>
                <select name="cable_segment_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Segments</option>
                    @foreach($cableSegments as $segment)
                        <option value="{{ $segment->id }}" {{ request('cable_segment_id') == $segment->id ? 'selected' : '' }}>
                            {{ $segment->name }} ({{ $segment->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Used</option>
                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Connection</label>
                <select name="connected" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="yes" {{ request('connected') == 'yes' ? 'selected' : '' }}>Connected</option>
                    <option value="no" {{ request('connected') == 'no' ? 'selected' : '' }}>Not Connected</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Per Page</label>
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="20" {{ request('per_page', 50) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ request('per_page', 50) == 200 ? 'selected' : '' }}>200</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('cores.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card border-0 shadow-sm mb-3" id="bulkActions" style="display: none;">
    <div class="card-body">
        <form action="{{ route('cores.bulk-delete') }}" method="POST" id="bulkDeleteForm" onsubmit="return confirm('Delete selected cores?')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="core_ids" id="selectedCoreIds">
            <div class="d-flex align-items-center gap-3">
                <span><strong><span id="selectedCount">0</span></strong> core(s) selected</span>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAll()">
                    <i class="bi bi-x"></i> Deselect All
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Fiber Cores Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Cable Segment</th>
                        <th>From → To</th>
                        <th>Core #</th>
                        <th>Color</th>
                        <th>Tube</th>
                        <th>Status</th>
                        <th>Connected To</th>
                        <th>Loss (dB)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cores as $core)
                    <tr>
                        <td>
                            <input type="checkbox" class="core-checkbox" value="{{ $core->id }}" onchange="updateBulkActions()">
                        </td>
                        <td>
                            <a href="{{ route('cable-segments.show', $core->cableSegment) }}">
                                {{ $core->cableSegment->name }}
                            </a>
                            <br><small class="text-muted">{{ $core->cableSegment->code }}</small>
                        </td>
                        {{-- ✅ ADD THIS COLUMN --}}
                        <td>
                            @if($core->cableSegment)
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <span class="badge bg-primary" style="font-size: 0.65rem;">
                                            {{ strtoupper($core->cableSegment->start_point_type) }}
                                        </span>
                                        <small class="d-block">
                                            {{ $core->cableSegment->startPoint?->name ?? 'ID: ' . $core->cableSegment->start_point_id }}
                                        </small>
                                    </div>
                                    <div class="text-center" style="font-size: 0.7rem;">
                                        <i class="bi bi-arrow-down"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-success" style="font-size: 0.65rem;">
                                            {{ strtoupper($core->cableSegment->end_point_type) }}
                                        </span>
                                        <small class="d-block">
                                            {{ $core->cableSegment->endPoint?->name ?? 'ID: ' . $core->cableSegment->end_point_id }}
                                        </small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><strong>{{ $core->core_number }}</strong></td>
                        <td>
                            @if($core->core_color)
                                @php $badge = $core->getColorBadge(); @endphp
                                <span class="{{ $badge['class'] }}" @if($badge['style']) style="{{ $badge['style'] }}" @endif>
                                    {{ $core->core_color }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $core->tube_number ?? '-' }}</td>
                        <td>
                            @if($core->status === 'available')
                                <span class="badge bg-success">Available</span>
                            @elseif($core->status === 'used')
                                <span class="badge bg-primary">Used</span>
                            @elseif($core->status === 'reserved')
                                <span class="badge bg-warning">Reserved</span>
                            @else
                                <span class="badge bg-danger">Damaged</span>
                            @endif
                        </td>
                        <td>
                            @if($core->connected_to_type)
                                <small>{{ class_basename($core->connected_to_type) }} #{{ $core->connected_to_id }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $core->loss_db ? number_format($core->loss_db, 2) : '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cores.show', $core) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('cores.edit', $core) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('cores.destroy', $core) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete core?')"
                                            {{ $core->status === 'used' ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No fiber cores found</td>  {{-- ✅ UPDATE colspan dari 9 ke 10 --}}
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
    <div class="mb-2 mb-md-0">
        <small class="text-muted">
            Showing {{ $cores->firstItem() ?? 0 }} to {{ $cores->lastItem() ?? 0 }} of {{ $cores->total() }} cores
        </small>
    </div>
    <div>
        {{ $cores->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

@push('scripts')
<script>
const selectedCores = new Set();

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.core-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedCores.add(cb.value);
        } else {
            selectedCores.delete(cb.value);
        }
    });
    updateBulkActions();
}

function updateBulkActions() {
    selectedCores.clear();
    document.querySelectorAll('.core-checkbox:checked').forEach(cb => {
        selectedCores.add(cb.value);
    });

    const count = selectedCores.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActions').style.display = count > 0 ? 'block' : 'none';
    document.getElementById('selectedCoreIds').value = Array.from(selectedCores).join(',');

    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.core-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.core-checkbox:checked');
    document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
}

function deselectAll() {
    document.querySelectorAll('.core-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}
</script>
@endpush
@endsection
