@extends('layouts.admin')

@section('title', 'Edit Cable Segment')
@section('page-title', 'Edit Cable Segment')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('cable-segments.show', $cableSegment) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Cable Details
        </a>

        <a href="{{ route('cable-segments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list"></i> All Cables
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Cable Segment: {{ $cableSegment->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cable-segments.update', $cableSegment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cable Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $cableSegment->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cable Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $cableSegment->code) }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Cable Type & Core Count -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cable Type <span class="text-danger">*</span></label>
                            <select name="cable_type" class="form-select @error('cable_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="backbone" {{ old('cable_type', $cableSegment->cable_type) == 'backbone' ? 'selected' : '' }}>
                                    Backbone (Long distance, high capacity)
                                </option>
                                <option value="distribution" {{ old('cable_type', $cableSegment->cable_type) == 'distribution' ? 'selected' : '' }}>
                                    Distribution (Medium distance)
                                </option>
                                <option value="drop" {{ old('cable_type', $cableSegment->cable_type) == 'drop' ? 'selected' : '' }}>
                                    Drop Cable (Last mile to customer)
                                </option>
                            </select>
                            @error('cable_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Core Count <span class="text-danger">*</span></label>
                            <input type="number" name="core_count" class="form-control @error('core_count') is-invalid @enderror"
                                   value="{{ old('core_count', $cableSegment->core_count) }}"
                                   min="2" max="288" required>
                            @error('core_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Current cores: {{ $cableSegment->cores->count() }}
                                @if($cableSegment->core_count != $cableSegment->cores->count())
                                    <span class="text-warning">(⚠️ Mismatch)</span>
                                @endif
                            </small>
                        </div>
                    </div>

                    <!-- Connection Points -->
                    <h6 class="mb-3 mt-4">Connection Points</h6>

                    <!-- Start Point -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Point Type <span class="text-danger">*</span></label>
                            <select name="start_point_type" id="startPointType"
                                    class="form-select @error('start_point_type') is-invalid @enderror"
                                    onchange="loadEquipment('start')" required>
                                <option value="">Select Type</option>
                                <option value="olt" {{ old('start_point_type', $cableSegment->start_point_type) == 'olt' ? 'selected' : '' }}>OLT</option>
                                <option value="odf" {{ old('start_point_type', $cableSegment->start_point_type) == 'odf' ? 'selected' : '' }}>ODF</option>
                                <option value="odc" {{ old('start_point_type', $cableSegment->start_point_type) == 'odc' ? 'selected' : '' }}>ODC</option>
                                <option value="joint_box" {{ old('start_point_type', $cableSegment->start_point_type) == 'joint_box' ? 'selected' : '' }}>Joint Box</option>
                                <option value="splitter" {{ old('start_point_type', $cableSegment->start_point_type) == 'splitter' ? 'selected' : '' }}>Splitter</option>
                                <option value="odp" {{ old('start_point_type', $cableSegment->start_point_type) == 'odp' ? 'selected' : '' }}>ODP</option>
                            </select>
                            @error('start_point_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Point <span class="text-danger">*</span></label>
                            <select name="start_point_id" id="startPointId"
                                    class="form-select @error('start_point_id') is-invalid @enderror" required>
                                <option value="">Select equipment first</option>
                                @if($cableSegment->startPoint)
                                    <option value="{{ $cableSegment->start_point_id }}" selected>
                                        {{ $cableSegment->startPoint->name }}
                                    </option>
                                @endif
                            </select>
                            @error('start_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- End Point -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">End Point Type <span class="text-danger">*</span></label>
                            <select name="end_point_type" id="endPointType"
                                    class="form-select @error('end_point_type') is-invalid @enderror"
                                    onchange="loadEquipment('end')" required>
                                <option value="">Select Type</option>
                                <option value="olt" {{ old('end_point_type', $cableSegment->end_point_type) == 'olt' ? 'selected' : '' }}>OLT</option>
                                <option value="odf" {{ old('end_point_type', $cableSegment->end_point_type) == 'odf' ? 'selected' : '' }}>ODF</option>
                                <option value="odc" {{ old('end_point_type', $cableSegment->end_point_type) == 'odc' ? 'selected' : '' }}>ODC</option>
                                <option value="joint_box" {{ old('end_point_type', $cableSegment->end_point_type) == 'joint_box' ? 'selected' : '' }}>Joint Box</option>
                                <option value="splitter" {{ old('end_point_type', $cableSegment->end_point_type) == 'splitter' ? 'selected' : '' }}>Splitter</option>
                                <option value="odp" {{ old('end_point_type', $cableSegment->end_point_type) == 'odp' ? 'selected' : '' }}>ODP</option>
                                <option value="ont" {{ old('end_point_type', $cableSegment->end_point_type) == 'ont' ? 'selected' : '' }}>ONT</option>
                            </select>
                            @error('end_point_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Point <span class="text-danger">*</span></label>
                            <select name="end_point_id" id="endPointId"
                                    class="form-select @error('end_point_id') is-invalid @enderror" required>
                                <option value="">Select equipment first</option>
                                @if($cableSegment->endPoint)
                                    <option value="{{ $cableSegment->end_point_id }}" selected>
                                        {{ $cableSegment->endPoint->name }}
                                    </option>
                                @endif
                            </select>
                            @error('end_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Cable Details -->
                    <h6 class="mb-3 mt-4">Cable Details</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Distance (meters)</label>
                            <input type="number" step="0.01" name="distance"
                                   class="form-control @error('distance') is-invalid @enderror"
                                   value="{{ old('distance', $cableSegment->distance) }}"
                                   min="0">
                            @error('distance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Cable length in meters</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Installation Type</label>
                            <select name="installation_type" class="form-select @error('installation_type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="aerial" {{ old('installation_type', $cableSegment->installation_type) == 'aerial' ? 'selected' : '' }}>Aerial</option>
                                <option value="underground" {{ old('installation_type', $cableSegment->installation_type) == 'underground' ? 'selected' : '' }}>Underground</option>
                                <option value="buried" {{ old('installation_type', $cableSegment->installation_type) == 'buried' ? 'selected' : '' }}>Buried</option>
                            </select>
                            @error('installation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date"
                                   class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date', $cableSegment->installation_date ? $cableSegment->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $cableSegment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ old('status', $cableSegment->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ old('status', $cableSegment->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="reserved" {{ old('status', $cableSegment->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Additional notes about this cable segment...">{{ old('notes', $cableSegment->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('cable-segments.show', $cableSegment) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Cable Segment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Core Count Warning -->
        @if($cableSegment->core_count != $cableSegment->cores->count())
        <div class="card border-0 shadow-sm mb-3 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Core Count Mismatch</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Defined:</strong> {{ $cableSegment->core_count }} cores<br>
                    <strong>Actual:</strong> {{ $cableSegment->cores->count() }} cores
                </p>
                <small class="text-muted">
                    If you change core count, consider updating the actual fiber cores.
                </small>
                <a href="{{ route('cable-segments.cores', $cableSegment) }}" class="btn btn-sm btn-warning w-100 mt-2">
                    <i class="bi bi-bezier2"></i> Manage Cores
                </a>
            </div>
        </div>
        @endif

        <!-- Usage Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Usage Statistics</h6>
            </div>
            <div class="card-body">
                @php
                    $usedCores = $cableSegment->cores->where('status', 'used')->count();
                    $availableCores = $cableSegment->cores->where('status', 'available')->count();
                    $totalCores = $cableSegment->cores->count();
                    $usagePercent = $totalCores > 0 ? ($usedCores / $totalCores) * 100 : 0;
                @endphp

                <div class="mb-2">
                    <small class="text-muted">Core Usage</small>
                    <div class="d-flex justify-content-between">
                        <span>{{ $usedCores }} / {{ $totalCores }}</span>
                        <span>{{ number_format($usagePercent, 1) }}%</span>
                    </div>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-{{ $usagePercent > 80 ? 'danger' : ($usagePercent > 50 ? 'warning' : 'success') }}"
                             style="width: {{ $usagePercent }}%"></div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-1">
                    <span class="small">Used:</span>
                    <span class="badge bg-danger">{{ $usedCores }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small">Available:</span>
                    <span class="badge bg-success">{{ $availableCores }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small">Reserved:</span>
                    <span class="badge bg-warning">{{ $cableSegment->cores->where('status', 'reserved')->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Important</h6>
            </div>
            <div class="card-body">
                <small>
                    <strong>⚠️ Changing connection points:</strong><br>
                    If you change start/end points, ensure no active splices are connected to this cable segment.
                </small>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> History</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Created</small>
                    <p class="mb-0 small">{{ $cableSegment->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 small">{{ $cableSegment->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load equipment based on type selection
async function loadEquipment(point) {
    const typeSelect = document.getElementById(point + 'PointType');
    const idSelect = document.getElementById(point + 'PointId');
    const equipmentType = typeSelect.value;

    console.log(`Loading ${point} equipment for type: ${equipmentType}`);

    idSelect.innerHTML = '<option value="">Loading...</option>';
    idSelect.disabled = true;

    if (!equipmentType) {
        idSelect.innerHTML = '<option value="">Select equipment type first</option>';
        return;
    }

    try {
        const response = await fetch(`/api/equipment/${equipmentType}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const equipment = await response.json();
        console.log('Loaded equipment:', equipment);

        let options = '<option value="">Select Equipment</option>';

        if (equipment.length === 0) {
            options = '<option value="">No equipment available</option>';
        } else {
            equipment.forEach(item => {
                options += `<option value="${item.id}">${item.display_name || item.name}</option>`;
            });
        }

        idSelect.innerHTML = options;
        idSelect.disabled = false;

    } catch (error) {
        console.error('Error loading equipment:', error);
        idSelect.innerHTML = '<option value="">Error loading equipment</option>';
        idSelect.disabled = false;
        alert(`Error loading equipment: ${error.message}`);
    }
}

// Load equipment on page load if type is already selected
document.addEventListener('DOMContentLoaded', function() {
    const startType = document.getElementById('startPointType').value;
    const endType = document.getElementById('endPointType').value;

    if (startType) {
        loadEquipment('start');
    }

    if (endType) {
        loadEquipment('end');
    }
});
</script>
@endpush
