@extends('layouts.admin')

@section('title', 'Fiber Cores: ' . $cableSegment->name)
@section('page-title', 'Fiber Cores Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $cableSegment->name }} - Fiber Cores</h4>
        <p class="text-muted mb-0">
            Cable Code: <code>{{ $cableSegment->code }}</code> |
            Total Cores: <strong>{{ $cableSegment->core_count }}</strong> |
            Created: <strong class="text-info">{{ $cableSegment->cores->count() }}</strong>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cable-segments.show', $cableSegment) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Cable
        </a>
        @if($cableSegment->cores->count() < $cableSegment->core_count)
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCoresModal">
                <i class="bi bi-plus-circle"></i> Add Cores
            </button>
        @endif
    </div>
</div>

<!-- Core Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ $stats['available'] }}</h3>
                <small class="text-muted">Available</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-1 text-warning">{{ $stats['used'] }}</h3>
                <small class="text-muted">Used</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-1 text-primary">{{ $stats['reserved'] }}</h3>
                <small class="text-muted">Reserved</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-1 text-danger">{{ $stats['damaged'] }}</h3>
                <small class="text-muted">Damaged</small>
            </div>
        </div>
    </div>
</div>

<!-- Utilization Progress -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <h6 class="fw-bold mb-0">Core Utilization</h6>
            <span>
                <strong>{{ $cableSegment->cores->count() }}</strong> / {{ $cableSegment->core_count }} cores created
            </span>
        </div>
        <div class="progress" style="height: 30px;">
            <div class="progress-bar bg-success" style="width: {{ $cableSegment->cores->count() > 0 ? ($stats['available'] / $cableSegment->cores->count() * 100) : 0 }}%"
                 data-bs-toggle="tooltip" title="{{ $stats['available'] }} Available">
                @if($stats['available'] > 0)
                    {{ $stats['available'] }}
                @endif
            </div>
            <div class="progress-bar bg-warning" style="width: {{ $cableSegment->cores->count() > 0 ? ($stats['used'] / $cableSegment->cores->count() * 100) : 0 }}%"
                 data-bs-toggle="tooltip" title="{{ $stats['used'] }} Used">
                @if($stats['used'] > 0)
                    {{ $stats['used'] }}
                @endif
            </div>
            <div class="progress-bar bg-primary" style="width: {{ $cableSegment->cores->count() > 0 ? ($stats['reserved'] / $cableSegment->cores->count() * 100) : 0 }}%"
                 data-bs-toggle="tooltip" title="{{ $stats['reserved'] }} Reserved">
                @if($stats['reserved'] > 0)
                    {{ $stats['reserved'] }}
                @endif
            </div>
            <div class="progress-bar bg-danger" style="width: {{ $cableSegment->cores->count() > 0 ? ($stats['damaged'] / $cableSegment->cores->count() * 100) : 0 }}%"
                 data-bs-toggle="tooltip" title="{{ $stats['damaged'] }} Damaged">
                @if($stats['damaged'] > 0)
                    {{ $stats['damaged'] }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('cable-segments.cores', $cableSegment) }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search core..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Used</option>
                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="color" class="form-select" onchange="this.form.submit()">
                    <option value="">All Colors</option>
                    @foreach($colors as $color)
                        <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="col-md-3 text-end">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="viewMode('grid')">
                        <i class="bi bi-grid-3x3-gap"></i> Grid
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="viewMode('table')">
                        <i class="bi bi-list"></i> Table
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Cores List - Table View -->
<div class="card border-0 shadow-sm" id="tableView">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="80">Core #</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th>Loss (dB)</th>
                        <th>Length (km)</th>
                        <th>Connected To</th>
                        <th>Notes</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cores as $core)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $core->core_number }}</strong>
                        </td>
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
                        <td>
                            <span class="badge bg-{{ $core->getStatusBadgeClass() }}">
                                {{ ucfirst($core->status) }}
                            </span>
                        </td>
                        <td>
                            @if($core->loss_db)
                                <span class="badge bg-{{ $core->loss_db <= 0.5 ? 'success' : ($core->loss_db <= 1.0 ? 'warning' : 'danger') }}">
                                    {{ $core->loss_db }} dB
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $core->length_km ? number_format($core->length_km, 3) . ' km' : '-' }}
                        </td>
                        <td>
                            @if($core->connectedTo)
                                <small>
                                    <span class="badge bg-info">{{ class_basename($core->connected_to_type) }}</span>
                                    {{ $core->connectedTo->name ?? 'ID: ' . $core->connected_to_id }}
                                </small>
                            @else
                                <span class="text-muted">Not connected</span>
                            @endif
                        </td>
                        <td>
                            @if($core->notes)
                                <small class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $core->notes }}">
                                    {{ $core->notes }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cores.edit', $core) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('cores.destroy', $core) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete core #{{ $core->core_number }}?')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-diagram-2" style="font-size: 3rem;"></i>
                            <p class="mt-2">No fiber cores found.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCoresModal">
                                <i class="bi bi-plus-circle"></i> Create Cores
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($cores->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Showing {{ $cores->firstItem() ?? 0 }} to {{ $cores->lastItem() ?? 0 }} of {{ $cores->total() }} cores
                </small>
            </div>
            <div>
                {{ $cores->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Cores List - Grid View (Hidden by default) -->
<div class="row g-3 d-none" id="gridView">
    @foreach($cores as $core)
    <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100 text-center core-card"
             data-status="{{ $core->status }}"
             onclick="showCoreDetail({{ $core->id }})">
            <div class="card-body p-2">
                <h5 class="mb-1 text-primary">{{ $core->core_number }}</h5>
                @if($core->core_color)
                    @php $badge = $core->getColorBadge(); @endphp
                    <span class="{{ $badge['class'] }}" @if($badge['style']) style="{{ $badge['style'] }}; font-size: 0.7rem;" @endif>
                        {{ $core->core_color }}
                    </span>
                @endif
                <div class="mt-2">
                    <span class="badge bg-{{ $core->getStatusBadgeClass() }}" style="font-size: 0.7rem;">
                        {{ ucfirst($core->status) }}
                    </span>
                </div>
                @if($core->loss_db)
                    <small class="text-muted d-block mt-1">{{ $core->loss_db }} dB</small>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Cores Modal -->
<div class="modal fade" id="createCoresModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cores.bulk-create') }}" method="POST">
                @csrf
                <input type="hidden" name="cable_segment_id" value="{{ $cableSegment->id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Create Multiple Cores</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            This will create cores with automatic color assignment (TIA-598 standard: 12 colors per tube)
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Core Number <span class="text-danger">*</span></label>
                        <input type="number" name="start_core" class="form-control @error('start_core') is-invalid @enderror"
                               value="{{ old('start_core', $cableSegment->cores->count() + 1) }}"
                               min="1"
                               max="{{ $cableSegment->core_count }}"
                               required>
                        @error('start_core')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Start from core number...</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Core Number <span class="text-danger">*</span></label>
                        <input type="number" name="end_core" class="form-control @error('end_core') is-invalid @enderror"
                               value="{{ old('end_core', $cableSegment->core_count) }}"
                               min="1"
                               max="{{ $cableSegment->core_count }}"
                               required>
                        @error('end_core')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">End at core number... (Max: {{ $cableSegment->core_count }})</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="auto_color" value="1" class="form-check-input" id="autoColor" checked>
                        <label class="form-check-label" for="autoColor">
                            Auto-assign colors (TIA-598 standard)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create Cores
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Core Modal -->
<div class="modal fade" id="editCoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Fiber Core</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCoreForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Core Number</label>
                        <input type="number" name="core_number" id="edit_core_number" class="form-control" required readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Core Color</label>
                        <select name="core_color" id="edit_core_color" class="form-select">
                            <option value="">Select Color</option>
                            <option value="Blue">Blue</option>
                            <option value="Orange">Orange</option>
                            <option value="Green">Green</option>
                            <option value="Brown">Brown</option>
                            <option value="Slate">Slate</option>
                            <option value="White">White</option>
                            <option value="Red">Red</option>
                            <option value="Black">Black</option>
                            <option value="Yellow">Yellow</option>
                            <option value="Violet">Violet</option>
                            <option value="Rose">Rose</option>
                            <option value="Aqua">Aqua</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="used">Used</option>
                            <option value="reserved">Reserved</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loss (dB)</label>
                        <input type="number" step="0.01" name="loss_db" id="edit_loss_db" class="form-control" placeholder="e.g., 0.25">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Length (km)</label>
                        <input type="number" step="0.001" name="length_km" id="edit_length_km" class="form-control" placeholder="e.g., 1.500">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Core
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // View Mode Toggle
    function viewMode(mode) {
        const tableView = document.getElementById('tableView');
        const gridView = document.getElementById('gridView');

        if (mode === 'grid') {
            tableView.classList.add('d-none');
            gridView.classList.remove('d-none');
        } else {
            tableView.classList.remove('d-none');
            gridView.classList.add('d-none');
        }
    }

    // Edit Core
    // Edit Core - Enhanced version
async function editCore(coreId) {
    // Show loading state
    const modal = document.getElementById('editCoreModal');
    const modalBody = modal.querySelector('.modal-body');
    const originalContent = modalBody.innerHTML;

    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading core data...</p>
        </div>
    `;

    new bootstrap.Modal(modal).show();

    try {
        const response = await fetch(`/api/cores/${coreId}`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const core = await response.json();

        // Restore original content
        modalBody.innerHTML = originalContent;

        // Fill form
        document.getElementById('edit_core_number').value = core.core_number || '';
        document.getElementById('edit_core_color').value = core.core_color || '';
        document.getElementById('edit_status').value = core.status || 'available';
        document.getElementById('edit_loss_db').value = core.loss_db || '';
        document.getElementById('edit_length_km').value = core.length_km || '';
        document.getElementById('edit_notes').value = core.notes || '';

        document.getElementById('editCoreForm').action = `/cores/${coreId}`;

    } catch (error) {
        console.error('Error loading core:', error);
        modalBody.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="bi bi-exclamation-triangle"></i> Error Loading Data</h6>
                <p class="mb-0">${error.message}</p>
                <button type="button" class="btn btn-sm btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
            </div>
        `;
    }
}

    // Show Core Detail (for grid view)
    function showCoreDetail(coreId) {
        editCore(coreId);
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<style>
    .core-card {
        cursor: pointer;
        transition: all 0.3s;
    }
    .core-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush
