@extends('layouts.admin')

@section('title', 'Fiber Splices - ' . $jointBox->name)
@section('page-title', 'Fiber Splices Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <a href="{{ route('joint-boxes.show', $jointBox) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Joint Box
        </a>
        <h5 class="fw-bold mb-1 mt-2">{{ $jointBox->name }}</h5>
        <p class="text-muted mb-0">
            Capacity: <strong>{{ $jointBox->splices->count() }} / {{ $jointBox->capacity }}</strong> splices used
        </p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSpliceModal">
            <i class="bi bi-plus-circle"></i> Add Splice
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Splices</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-bezier2 text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Available Capacity</p>
                        <h4 class="mb-0 text-success">{{ $jointBox->capacity - $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Avg Loss</p>
                        <h4 class="mb-0 text-info">{{ $stats['avg_loss'] }} dB</h4>
                    </div>
                    <div>
                        <i class="bi bi-lightning text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Utilization</p>
                        <h4 class="mb-0 text-warning">{{ $stats['utilization'] }}%</h4>
                    </div>
                    <div>
                        <i class="bi bi-pie-chart text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('joint-boxes.splices', $jointBox) }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="splice_type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="fusion" {{ request('splice_type') == 'fusion' ? 'selected' : '' }}>Fusion</option>
                    <option value="mechanical" {{ request('splice_type') == 'mechanical' ? 'selected' : '' }}>Mechanical</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort_by" class="form-select" onchange="this.form.submit()">
                    <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>Date</option>
                    <option value="loss" {{ request('sort_by') == 'loss' ? 'selected' : '' }}>Loss</option>
                    <option value="input" {{ request('sort_by') == 'input' ? 'selected' : '' }}>Input Segment</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Splices Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Input Segment</th>
                        <th>Core #</th>
                        <th></th>
                        <th>Output Segment</th>
                        <th>Core #</th>
                        <th>Loss</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($splices as $splice)
                    <tr>
                        <td>
                            @if($splice->inputSegment)
                                <a href="{{ route('cable-segments.show', $splice->inputSegment) }}" class="text-decoration-none">
                                    <strong>{{ $splice->inputSegment->name }}</strong>
                                </a>
                                <br><small class="text-muted">{{ $splice->inputSegment->code }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">Core {{ $splice->input_core_number }}</span>
                        </td>
                        <td class="text-center">
                            <i class="bi bi-arrow-right text-muted"></i>
                        </td>
                        <td>
                            @if($splice->outputSegment)
                                <a href="{{ route('cable-segments.show', $splice->outputSegment) }}" class="text-decoration-none">
                                    <strong>{{ $splice->outputSegment->name }}</strong>
                                </a>
                                <br><small class="text-muted">{{ $splice->outputSegment->code }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">Core {{ $splice->output_core_number }}</span>
                        </td>
                        <td>
                            @if($splice->splice_loss)
                                <span class="badge bg-{{ $splice->splice_loss <= 0.1 ? 'success' : ($splice->splice_loss <= 0.3 ? 'warning' : 'danger') }}">
                                    {{ $splice->splice_loss }} dB
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($splice->splice_type)
                                <span class="badge bg-secondary">{{ ucfirst($splice->splice_type) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $splice->splice_date ? \Carbon\Carbon::parse($splice->splice_date)->format('d M Y') : '-' }}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-warning"
                                        onclick="editSplice({{ $splice->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('fiber-splices.destroy', $splice) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete this splice?')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-bezier2" style="font-size: 3rem;"></i>
                            <p class="mt-2">No splices found.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSpliceModal">
                                <i class="bi bi-plus-circle"></i> Add First Splice
                            </button>
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
                    Showing {{ $splices->firstItem() ?? 0 }} to {{ $splices->lastItem() ?? 0 }} of {{ $splices->total() }} splices
                </small>
            </div>
            <div>
                {{ $splices->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Splice Modal -->
<div class="modal fade" id="createSpliceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('fiber-splices.store') }}" method="POST">
                @csrf
                <input type="hidden" name="joint_box_id" value="{{ $jointBox->id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Add New Splice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Input Segment -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Input Cable Segment <span class="text-danger">*</span></label>
                            <select name="input_segment_id" id="inputSegment" class="form-select" required onchange="loadCores('input')">
                                <option value="">Select Input Segment</option>
                                @foreach($cableSegments as $segment)
                                    <option value="{{ $segment->id }}">{{ $segment->name }} ({{ $segment->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <select name="input_core_number" id="inputCoreNumber" class="form-select" required>
                                <option value="">Select core first</option>
                            </select>
                        </div>
                    </div>

                    <!-- Output Segment -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Output Cable Segment <span class="text-danger">*</span></label>
                            <select name="output_segment_id" id="outputSegment" class="form-select" required onchange="loadCores('output')">
                                <option value="">Select Output Segment</option>
                                @foreach($cableSegments as $segment)
                                    <option value="{{ $segment->id }}">{{ $segment->name }} ({{ $segment->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <select name="output_core_number" id="outputCoreNumber" class="form-select" required>
                                <option value="">Select core first</option>
                            </select>
                        </div>
                    </div>

                    <!-- Splice Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splice Type <span class="text-danger">*</span></label>
                            <select name="splice_type" class="form-select" required>
                                <option value="fusion">Fusion Splice</option>
                                <option value="mechanical">Mechanical Splice</option>
                            </select>
                            <small class="text-muted">Fusion: Permanent | Mechanical: Temporary</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Splice Date</label>
                            <input type="date" name="splice_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splice Loss (dB)</label>
                            <input type="number" step="0.01" name="splice_loss" class="form-control" placeholder="e.g., 0.15">
                            <small class="text-muted">Good: ≤0.1 dB | Acceptable: ≤0.3 dB</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Technician</label>
                            <input type="text" name="technician" class="form-control" placeholder="Technician name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create Splice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Splice Modal -->
<div class="modal fade" id="editSpliceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editSpliceForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Splice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splice Type</label>
                            <select name="splice_type" id="edit_splice_type" class="form-select">
                                <option value="fusion">Fusion Splice</option>
                                <option value="mechanical">Mechanical Splice</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Splice Date</label>
                            <input type="date" name="splice_date" id="edit_splice_date" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splice Loss (dB)</label>
                            <input type="number" step="0.01" name="splice_loss" id="edit_splice_loss" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Technician</label>
                            <input type="text" name="technician" id="edit_technician_name" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="edit_notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Splice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load available cores for selected segment
async function loadCores(type) {
    const segmentSelect = document.getElementById(type + 'Segment');
    const coreSelect = document.getElementById(type + 'CoreNumber');
    const segmentId = segmentSelect.value;

    console.log(`Loading cores for ${type}, segment ID: ${segmentId}`); // Debug log

    coreSelect.innerHTML = '<option value="">Loading...</option>';
    coreSelect.disabled = true;

    if (!segmentId) {
        coreSelect.innerHTML = '<option value="">Select segment first</option>';
        return;
    }

    try {
        // Use /api/ prefix explicitly
        const response = await fetch(`/api/cable-segments/${segmentId}/available-cores`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        console.log('Response status:', response.status); // Debug log

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const cores = await response.json();
        console.log('Loaded cores:', cores); // Debug log

        let options = '<option value="">Select Core</option>';

        if (cores.length === 0) {
            options = '<option value="">No available cores</option>';
        } else {
            cores.forEach(core => {
                const color = core.core_color ? ` (${core.core_color})` : '';
                options += `<option value="${core.core_number}">Core ${core.core_number}${color}</option>`;
            });
        }

        coreSelect.innerHTML = options;
        coreSelect.disabled = false;

    } catch (error) {
        console.error('Error loading cores:', error);
        coreSelect.innerHTML = '<option value="">Error loading cores</option>';
        coreSelect.disabled = false;
        alert(`⚠️ Error loading cores: ${error.message}`);
    }
}

// Edit splice
async function editSplice(spliceId) {
    console.log('Editing splice:', spliceId);

    try {
        const response = await fetch(`/api/fiber-splices/${spliceId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const splice = await response.json();
        console.log('Loaded splice:', splice);

        // Fill form fields
        document.getElementById('edit_splice_type').value = splice.splice_type || 'fusion';

        // ✅ FIX: Parse date properly
        let dateValue = '';
        if (splice.splice_date) {
            // Handle both "2025-10-13" and "2025-10-13T00:00:00.000000Z" formats
            const date = new Date(splice.splice_date);
            if (!isNaN(date.getTime())) {
                // Format as yyyy-MM-dd
                dateValue = date.toISOString().split('T')[0];
            }
        }
        document.getElementById('edit_splice_date').value = dateValue;
        document.getElementById('edit_splice_loss').value = splice.splice_loss || '';
        document.getElementById('edit_technician_name').value = splice.technician || '';
        document.getElementById('edit_notes').value = splice.notes || '';

        // Set form action
        document.getElementById('editSpliceForm').action = `/fiber-splices/${spliceId}`;

        // ✅ FIX: Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
            alert('Error: Bootstrap library not loaded. Please refresh the page.');
            return;
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editSpliceModal'));
        modal.show();

        console.log('Modal opened successfully');

    } catch (error) {
        console.error('Error loading splice:', error);
        alert(`Error loading splice data: ${error.message}`);
    }
}

</script>
@endpush
