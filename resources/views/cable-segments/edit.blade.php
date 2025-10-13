@extends('layouts.admin')

@section('title', 'Edit Cable Segment')
@section('page-title', 'Edit Cable Segment: ' . $cableSegment->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Edit Cable Segment Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cable-segments.update', $cableSegment) }}" method="POST" id="cableEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">Cable Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $cableSegment->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cable Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $cableSegment->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cable Type & Core Count -->
                        <div class="col-md-6">
                            <label class="form-label">Cable Type <span class="text-danger">*</span></label>
                            <select name="cable_type" class="form-select @error('cable_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="backbone" {{ old('cable_type', $cableSegment->cable_type) == 'backbone' ? 'selected' : '' }}>Backbone (ODF-ODC)</option>
                                <option value="distribution" {{ old('cable_type', $cableSegment->cable_type) == 'distribution' ? 'selected' : '' }}>Distribution (ODC-ODP)</option>
                                <option value="drop" {{ old('cable_type', $cableSegment->cable_type) == 'drop' ? 'selected' : '' }}>Drop Cable (ODP-ONT)</option>
                            </select>
                            @error('cable_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Core Count <span class="text-danger">*</span></label>
                            <select name="core_count" class="form-select @error('core_count') is-invalid @enderror" required>
                                <option value="">Select Core Count</option>
                                <option value="2" {{ old('core_count', $cableSegment->core_count) == 2 ? 'selected' : '' }}>2 Cores (Drop)</option>
                                <option value="4" {{ old('core_count', $cableSegment->core_count) == 4 ? 'selected' : '' }}>4 Cores</option>
                                <option value="6" {{ old('core_count', $cableSegment->core_count) == 6 ? 'selected' : '' }}>6 Cores</option>
                                <option value="12" {{ old('core_count', $cableSegment->core_count) == 12 ? 'selected' : '' }}>12 Cores</option>
                                <option value="24" {{ old('core_count', $cableSegment->core_count) == 24 ? 'selected' : '' }}>24 Cores</option>
                                <option value="48" {{ old('core_count', $cableSegment->core_count) == 48 ? 'selected' : '' }}>48 Cores</option>
                                <option value="96" {{ old('core_count', $cableSegment->core_count) == 96 ? 'selected' : '' }}>96 Cores</option>
                                <option value="144" {{ old('core_count', $cableSegment->core_count) == 144 ? 'selected' : '' }}>144 Cores</option>
                                <option value="288" {{ old('core_count', $cableSegment->core_count) == 288 ? 'selected' : '' }}>288 Cores</option>
                            </select>
                            <small class="text-muted">Current cores created: {{ $cableSegment->cores->count() }}</small>
                            @error('core_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warning Alert -->
                        @if($cableSegment->cores->count() > 0)
                        <div class="col-12">
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>
                                    <strong>Warning:</strong> {{ $cableSegment->cores->count() }} fiber cores already created.
                                    Cannot reduce core count below this number.
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Cable Brand & Model -->
                        <div class="col-md-6">
                            <label class="form-label">Cable Brand</label>
                            <input type="text" name="cable_brand" class="form-control @error('cable_brand') is-invalid @enderror"
                                   value="{{ old('cable_brand', $cableSegment->cable_brand) }}">
                            @error('cable_brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cable Model</label>
                            <input type="text" name="cable_model" class="form-control @error('cable_model') is-invalid @enderror"
                                   value="{{ old('cable_model', $cableSegment->cable_model) }}">
                            @error('cable_model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- START POINT -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-primary">Start Point (From)</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Equipment Type <span class="text-danger">*</span></label>
                            <select name="start_point_type" id="startPointType" class="form-select @error('start_point_type') is-invalid @enderror" required onchange="loadEquipment('start')">
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

                        <div class="col-md-4">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <select name="start_point_id" id="startPointId" class="form-select @error('start_point_id') is-invalid @enderror" required>
                                <option value="{{ $cableSegment->start_point_id }}">{{ $cableSegment->startPoint->name ?? 'Select Equipment' }}</option>
                            </select>
                            @error('start_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Port/Connector</label>
                            <input type="text" name="start_port" class="form-control @error('start_port') is-invalid @enderror"
                                   value="{{ old('start_port', $cableSegment->start_port) }}">
                            @error('start_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Connector Type</label>
                            <select name="start_connector_type" class="form-select @error('start_connector_type') is-invalid @enderror">
                                <option value="">Select Connector</option>
                                <option value="SC" {{ old('start_connector_type', $cableSegment->start_connector_type) == 'SC' ? 'selected' : '' }}>SC (Subscriber Connector)</option>
                                <option value="LC" {{ old('start_connector_type', $cableSegment->start_connector_type) == 'LC' ? 'selected' : '' }}>LC (Lucent Connector)</option>
                                <option value="FC" {{ old('start_connector_type', $cableSegment->start_connector_type) == 'FC' ? 'selected' : '' }}>FC (Ferrule Connector)</option>
                                <option value="ST" {{ old('start_connector_type', $cableSegment->start_connector_type) == 'ST' ? 'selected' : '' }}>ST (Straight Tip)</option>
                                <option value="E2000" {{ old('start_connector_type', $cableSegment->start_connector_type) == 'E2000' ? 'selected' : '' }}>E2000</option>
                                <option value="MPO" {{ old('start_connector_type', $cableSegment->start_connector_type) == 'MPO' ? 'selected' : '' }}>MPO (Multi-fiber)</option>
                            </select>
                            @error('start_connector_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start GPS</label>
                            <input type="text" name="start_coordinates" class="form-control"
                                   value="{{ $cableSegment->start_latitude && $cableSegment->start_longitude ? $cableSegment->start_latitude . ', ' . $cableSegment->start_longitude : '' }}"
                                   placeholder="Lat, Long">
                        </div>

                        <!-- END POINT -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-success">End Point (To)</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Equipment Type <span class="text-danger">*</span></label>
                            <select name="end_point_type" id="endPointType" class="form-select @error('end_point_type') is-invalid @enderror" required onchange="loadEquipment('end')">
                                <option value="">Select Type</option>
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

                        <div class="col-md-4">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <select name="end_point_id" id="endPointId" class="form-select @error('end_point_id') is-invalid @enderror" required>
                                <option value="{{ $cableSegment->end_point_id }}">{{ $cableSegment->endPoint->name ?? 'Select Equipment' }}</option>
                            </select>
                            @error('end_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Port/Connector</label>
                            <input type="text" name="end_port" class="form-control @error('end_port') is-invalid @enderror"
                                   value="{{ old('end_port', $cableSegment->end_port) }}">
                            @error('end_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Connector Type</label>
                            <select name="end_connector_type" class="form-select @error('end_connector_type') is-invalid @enderror">
                                <option value="">Select Connector</option>
                                <option value="SC" {{ old('end_connector_type', $cableSegment->end_connector_type) == 'SC' ? 'selected' : '' }}>SC (Subscriber Connector)</option>
                                <option value="LC" {{ old('end_connector_type', $cableSegment->end_connector_type) == 'LC' ? 'selected' : '' }}>LC (Lucent Connector)</option>
                                <option value="FC" {{ old('end_connector_type', $cableSegment->end_connector_type) == 'FC' ? 'selected' : '' }}>FC (Ferrule Connector)</option>
                                <option value="ST" {{ old('end_connector_type', $cableSegment->end_connector_type) == 'ST' ? 'selected' : '' }}>ST (Straight Tip)</option>
                                <option value="E2000" {{ old('end_connector_type', $cableSegment->end_connector_type) == 'E2000' ? 'selected' : '' }}>E2000</option>
                                <option value="MPO" {{ old('end_connector_type', $cableSegment->end_connector_type) == 'MPO' ? 'selected' : '' }}>MPO (Multi-fiber)</option>
                            </select>
                            @error('end_connector_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End GPS</label>
                            <input type="text" name="end_coordinates" class="form-control"
                                   value="{{ $cableSegment->end_latitude && $cableSegment->end_longitude ? $cableSegment->end_latitude . ', ' . $cableSegment->end_longitude : '' }}"
                                   placeholder="Lat, Long">
                        </div>

                        <!-- Installation Details -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Installation Details</h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Distance (meters)</label>
                            <input type="number" step="0.01" name="distance" class="form-control @error('distance') is-invalid @enderror"
                                   value="{{ old('distance', $cableSegment->distance) }}">
                            @error('distance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Installation Type</label>
                            <select name="installation_type" class="form-select @error('installation_type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="aerial" {{ old('installation_type', $cableSegment->installation_type) == 'aerial' ? 'selected' : '' }}>Aerial (Pole)</option>
                                <option value="underground" {{ old('installation_type', $cableSegment->installation_type) == 'underground' ? 'selected' : '' }}>Underground (Buried)</option>
                                <option value="duct" {{ old('installation_type', $cableSegment->installation_type) == 'duct' ? 'selected' : '' }}>Duct (Conduit)</option>
                            </select>
                            @error('installation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date', $cableSegment->installation_date ? $cableSegment->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $cableSegment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="damaged" {{ old('status', $cableSegment->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="maintenance" {{ old('status', $cableSegment->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3">{{ old('notes', $cableSegment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Cable Segment
                        </button>
                        <a href="{{ route('cable-segments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>

                <!-- Danger Zone -->
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger mb-1">Danger Zone</h6>
                        <small class="text-muted">Permanently delete this cable segment</small>
                    </div>
                    <form action="{{ route('cable-segments.destroy', $cableSegment) }}" method="POST" onsubmit="return confirm('⚠️ DELETE CABLE SEGMENT: {{ $cableSegment->name }}?\n\nThis will remove:\n- All fiber cores ({{ $cableSegment->cores->count() }})\n- All connections\n- Test results\n\nThis action CANNOT be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete Cable Segment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Current Status</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="45%" class="text-muted">Cable Type:</td>
                        <td>
                            <span class="badge bg-{{ $cableSegment->cable_type === 'backbone' ? 'danger' : ($cableSegment->cable_type === 'distribution' ? 'warning' : 'info') }}">
                                {{ ucfirst($cableSegment->cable_type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Cores:</td>
                        <td><strong>{{ $cableSegment->core_count }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cores Created:</td>
                        <td><strong class="text-info">{{ $cableSegment->cores->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cores Used:</td>
                        <td><strong class="text-warning">{{ $cableSegment->cores->where('status', 'used')->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Distance:</td>
                        <td>{{ $cableSegment->distance ? number_format($cableSegment->distance / 1000, 2) . ' km' : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="badge bg-{{ $cableSegment->status === 'active' ? 'success' : ($cableSegment->status === 'damaged' ? 'danger' : 'warning') }}">
                                {{ ucfirst($cableSegment->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('cable-segments.show', $cableSegment) }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <a href="{{ route('cable-segments.cores', $cableSegment) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-diagram-2"></i> Manage Cores
                    </a>
                    <a href="{{ route('cable-segments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list"></i> Back to List
                    </a>
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
        const type = typeSelect.value;

        if (!type) {
            idSelect.innerHTML = '<option value="">Select equipment type first</option>';
            return;
        }

        idSelect.innerHTML = '<option value="">Loading...</option>';

        try {
            const response = await fetch(`/api/equipment/${type}`);
            const data = await response.json();

            let options = '<option value="">Select Equipment</option>';
            data.forEach(item => {
                const selected = item.id == idSelect.options[0].value ? 'selected' : '';
                options += `<option value="${item.id}" ${selected}>${item.name} ${item.code ? '(' + item.code + ')' : ''}</option>`;
            });

            idSelect.innerHTML = options;
        } catch (error) {
            console.error('Error loading equipment:', error);
            idSelect.innerHTML = '<option value="">Error loading data</option>';
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        loadEquipment('start');
        loadEquipment('end');
    });

    // Form validation
    document.getElementById('cableEditForm').addEventListener('submit', function(e) {
        const coresCreated = {{ $cableSegment->cores->count() }};
        const newCoreCount = parseInt(document.querySelector('select[name="core_count"]').value);

        if (newCoreCount < coresCreated) {
            e.preventDefault();
            alert(`⚠️ Cannot reduce core count below ${coresCreated}!\n\n${coresCreated} cores have already been created.`);
            return false;
        }
    });
</script>
@endpush
