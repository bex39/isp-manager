@extends('layouts.admin')

@section('title', 'Add Cable Segment')
@section('page-title', 'Add New Cable Segment')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Cable Segment Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cable-segments.store') }}" method="POST" id="cableForm">
                    @csrf

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">Cable Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="e.g., FB-ODF-ODC-01">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cable Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" required placeholder="e.g., CAB-001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cable Type & Core Count -->
                        <div class="col-md-6">
                            <label class="form-label">Cable Type <span class="text-danger">*</span></label>
                            <select name="cable_type" class="form-select @error('cable_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="backbone" {{ old('cable_type') == 'backbone' ? 'selected' : '' }}>Backbone (ODF-ODC)</option>
                                <option value="distribution" {{ old('cable_type') == 'distribution' ? 'selected' : '' }}>Distribution (ODC-ODP)</option>
                                <option value="drop" {{ old('cable_type') == 'drop' ? 'selected' : '' }}>Drop Cable (ODP-ONT)</option>
                            </select>
                            @error('cable_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Core Count <span class="text-danger">*</span></label>
                            <select name="core_count" class="form-select @error('core_count') is-invalid @enderror" required>
                                <option value="">Select Core Count</option>
                                <option value="2" {{ old('core_count') == 2 ? 'selected' : '' }}>2 Cores (Drop)</option>
                                <option value="4" {{ old('core_count') == 4 ? 'selected' : '' }}>4 Cores</option>
                                <option value="6" {{ old('core_count') == 6 ? 'selected' : '' }}>6 Cores</option>
                                <option value="12" {{ old('core_count') == 12 ? 'selected' : '' }}>12 Cores</option>
                                <option value="24" {{ old('core_count') == 24 ? 'selected' : '' }}>24 Cores</option>
                                <option value="48" {{ old('core_count') == 48 ? 'selected' : '' }}>48 Cores</option>
                                <option value="96" {{ old('core_count') == 96 ? 'selected' : '' }}>96 Cores</option>
                                <option value="144" {{ old('core_count') == 144 ? 'selected' : '' }}>144 Cores</option>
                                <option value="288" {{ old('core_count') == 288 ? 'selected' : '' }}>288 Cores</option>
                            </select>
                            @error('core_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cable Brand & Model -->
                        <div class="col-md-6">
                            <label class="form-label">Cable Brand</label>
                            <input type="text" name="cable_brand" class="form-control @error('cable_brand') is-invalid @enderror"
                                   value="{{ old('cable_brand') }}" placeholder="e.g., Corning, Furukawa">
                            @error('cable_brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cable Model</label>
                            <input type="text" name="cable_model" class="form-control @error('cable_model') is-invalid @enderror"
                                   value="{{ old('cable_model') }}" placeholder="e.g., SM G.652D">
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
                                <option value="olt" {{ old('start_point_type') == 'olt' ? 'selected' : '' }}>OLT</option>
                                <option value="odf" {{ old('start_point_type') == 'odf' ? 'selected' : '' }}>ODF</option>
                                <option value="odc" {{ old('start_point_type') == 'odc' ? 'selected' : '' }}>ODC</option>
                                <option value="joint_box" {{ old('start_point_type') == 'joint_box' ? 'selected' : '' }}>Joint Box</option>
                                <option value="splitter" {{ old('start_point_type') == 'splitter' ? 'selected' : '' }}>Splitter</option>
                                <option value="odp" {{ old('start_point_type') == 'odp' ? 'selected' : '' }}>ODP</option>
                            </select>
                            @error('start_point_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <select name="start_point_id" id="startPointId" class="form-select @error('start_point_id') is-invalid @enderror" required>
                                <option value="">Select equipment first</option>
                            </select>
                            @error('start_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Port/Connector</label>
                            <input type="text" name="start_port" class="form-control @error('start_port') is-invalid @enderror"
                                   value="{{ old('start_port') }}" placeholder="e.g., Port 1">
                            @error('start_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Connector Type</label>
                            <select name="start_connector_type" class="form-select @error('start_connector_type') is-invalid @enderror">
                                <option value="">Select Connector</option>
                                <option value="SC" {{ old('start_connector_type') == 'SC' ? 'selected' : '' }}>SC (Subscriber Connector)</option>
                                <option value="LC" {{ old('start_connector_type') == 'LC' ? 'selected' : '' }}>LC (Lucent Connector)</option>
                                <option value="FC" {{ old('start_connector_type') == 'FC' ? 'selected' : '' }}>FC (Ferrule Connector)</option>
                                <option value="ST" {{ old('start_connector_type') == 'ST' ? 'selected' : '' }}>ST (Straight Tip)</option>
                                <option value="E2000" {{ old('start_connector_type') == 'E2000' ? 'selected' : '' }}>E2000</option>
                                <option value="MPO" {{ old('start_connector_type') == 'MPO' ? 'selected' : '' }}>MPO (Multi-fiber)</option>
                            </select>
                            @error('start_connector_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start GPS (Optional)</label>
                            <input type="text" name="start_coordinates" class="form-control"
                                   value="{{ old('start_coordinates') }}" placeholder="Lat, Long (auto-filled if available)">
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
                                <option value="odf" {{ old('end_point_type') == 'odf' ? 'selected' : '' }}>ODF</option>
                                <option value="odc" {{ old('end_point_type') == 'odc' ? 'selected' : '' }}>ODC</option>
                                <option value="joint_box" {{ old('end_point_type') == 'joint_box' ? 'selected' : '' }}>Joint Box</option>
                                <option value="splitter" {{ old('end_point_type') == 'splitter' ? 'selected' : '' }}>Splitter</option>
                                <option value="odp" {{ old('end_point_type') == 'odp' ? 'selected' : '' }}>ODP</option>
                                <option value="ont" {{ old('end_point_type') == 'ont' ? 'selected' : '' }}>ONT</option>
                            </select>
                            @error('end_point_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <select name="end_point_id" id="endPointId" class="form-select @error('end_point_id') is-invalid @enderror" required>
                                <option value="">Select equipment first</option>
                            </select>
                            @error('end_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Port/Connector</label>
                            <input type="text" name="end_port" class="form-control @error('end_port') is-invalid @enderror"
                                   value="{{ old('end_port') }}" placeholder="e.g., Port 1">
                            @error('end_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Connector Type</label>
                            <select name="end_connector_type" class="form-select @error('end_connector_type') is-invalid @enderror">
                                <option value="">Select Connector</option>
                                <option value="SC" {{ old('end_connector_type') == 'SC' ? 'selected' : '' }}>SC (Subscriber Connector)</option>
                                <option value="LC" {{ old('end_connector_type') == 'LC' ? 'selected' : '' }}>LC (Lucent Connector)</option>
                                <option value="FC" {{ old('end_connector_type') == 'FC' ? 'selected' : '' }}>FC (Ferrule Connector)</option>
                                <option value="ST" {{ old('end_connector_type') == 'ST' ? 'selected' : '' }}>ST (Straight Tip)</option>
                                <option value="E2000" {{ old('end_connector_type') == 'E2000' ? 'selected' : '' }}>E2000</option>
                                <option value="MPO" {{ old('end_connector_type') == 'MPO' ? 'selected' : '' }}>MPO (Multi-fiber)</option>
                            </select>
                            @error('end_connector_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End GPS (Optional)</label>
                            <input type="text" name="end_coordinates" class="form-control"
                                   value="{{ old('end_coordinates') }}" placeholder="Lat, Long (auto-filled if available)">
                        </div>

                        <!-- Installation Details -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Installation Details</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Distance (meters)</label>
                            <input type="number" step="0.01" name="distance" class="form-control @error('distance') is-invalid @enderror"
                                   value="{{ old('distance') }}" placeholder="e.g., 1500">
                            <small class="text-muted">Will be calculated from GPS if available</small>
                            @error('distance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Installation Type</label>
                            <select name="installation_type" class="form-select @error('installation_type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="aerial" {{ old('installation_type') == 'aerial' ? 'selected' : '' }}>Aerial (Pole)</option>
                                <option value="underground" {{ old('installation_type') == 'underground' ? 'selected' : '' }}>Underground (Buried)</option>
                                <option value="duct" {{ old('installation_type') == 'duct' ? 'selected' : '' }}>Duct (Conduit)</option>
                            </select>
                            @error('installation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Cable Segment
                        </button>
                        <a href="{{ route('cable-segments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Cable Types</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Backbone Cable:</strong></p>
                <p class="small text-muted mb-3">ODF → ODC (Central Office to Field)</p>

                <p class="small mb-2"><strong>Distribution Cable:</strong></p>
                <p class="small text-muted mb-3">ODC → ODP (Field Cabinet to Distribution Point)</p>

                <p class="small mb-2"><strong>Drop Cable:</strong></p>
                <p class="small text-muted mb-0">ODP → ONT (Last Mile to Customer)</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Select appropriate cable type for connection</li>
                    <li>Core count depends on requirement</li>
                    <li>SC/LC connectors most common</li>
                    <li>Aerial for pole-mounted installation</li>
                    <li>Underground requires protection</li>
                    <li>Record accurate distance for loss calculation</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Load equipment based on type selection
    // Load equipment based on type selection - ENHANCED VERSION
async function loadEquipment(point) {
    const typeSelect = document.getElementById(point + 'PointType');
    const idSelect = document.getElementById(point + 'PointId');
    const type = typeSelect.value;

    console.log(`Loading ${point} equipment type: ${type}`);

    idSelect.innerHTML = '<option value="">Loading...</option>';
    idSelect.disabled = true;

    if (!type) {
        idSelect.innerHTML = '<option value="">Select equipment type first</option>';
        idSelect.disabled = false;
        return;
    }

    try {
        const response = await fetch(`/api/equipment/${type}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log(`Loaded ${data.length} items for ${type}:`, data);

        let options = '<option value="">Select Equipment</option>';

        if (data.length === 0) {
            options = '<option value="">No equipment found</option>';
        } else {
            // Sort: Active first, then inactive
            data.sort((a, b) => {
                if (a.is_active === b.is_active) return a.name.localeCompare(b.name);
                return b.is_active ? 1 : -1;
            });

            data.forEach(item => {
                // Use display_name from API (includes status)
                const displayName = item.display_name ||
                    (item.name + (item.code ? ' (' + item.code + ')' : ''));

                // Add data attribute for status
                options += `<option value="${item.id}" data-active="${item.is_active}">${displayName}</option>`;
            });
        }

        idSelect.innerHTML = options;
        idSelect.disabled = false;

        // Add visual styling for offline items
        styleOfflineOptions(idSelect);

    } catch (error) {
        console.error('Error loading equipment:', error);
        idSelect.innerHTML = '<option value="">Error loading data</option>';
        idSelect.disabled = false;
        alert(`⚠️ Error loading ${type.toUpperCase()} equipment:\n${error.message}`);
    }
}

// Style offline equipment options
function styleOfflineOptions(selectElement) {
    // Apply CSS to inactive options
    Array.from(selectElement.options).forEach(option => {
        if (option.dataset.active === 'false' || option.dataset.active === '0') {
            option.style.color = '#999';
            option.style.fontStyle = 'italic';
        }
    });
}

    // Form validation
    document.getElementById('cableForm').addEventListener('submit', function(e) {
        const startType = document.getElementById('startPointType').value;
        const endType = document.getElementById('endPointType').value;
        const startId = document.getElementById('startPointId').value;
        const endId = document.getElementById('endPointId').value;

        if (startType === endType && startId === endId) {
            e.preventDefault();
            alert('⚠️ Start and End points cannot be the same equipment!');
            return false;
        }
    });

    @push('styles')
<style>
    /* Style for offline equipment in dropdown */
    select option[data-active="false"],
    select option[data-active="0"] {
        color: #999 !important;
        font-style: italic;
    }

    /* Optional: Add warning icon */
    select option[data-active="false"]::before,
    select option[data-active="0"]::before {
        content: "⚠️ ";
    }
</style>
@endpush

</script>
@endpush
