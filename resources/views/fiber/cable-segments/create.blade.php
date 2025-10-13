@extends('layouts.admin')

@section('title', 'Add Cable Segment')
@section('page-title', 'Add Cable Segment')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('cable-segments.store') }}" method="POST">
                    @csrf

                    <h6 class="fw-bold mb-3">Basic Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cable Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" required placeholder="CBL-001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cable Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="Backbone OLT-JB1">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cable Type <span class="text-danger">*</span></label>
                            <select name="cable_type" class="form-select @error('cable_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="backbone" {{ old('cable_type') == 'backbone' ? 'selected' : '' }}>Backbone (96-144 core)</option>
                                <option value="distribution" {{ old('cable_type') == 'distribution' ? 'selected' : '' }}>Distribution (24-48 core)</option>
                                <option value="drop" {{ old('cable_type') == 'drop' ? 'selected' : '' }}>Drop Cable (2-4 core)</option>
                            </select>
                            @error('cable_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Core Count <span class="text-danger">*</span></label>
                            <select name="core_count" class="form-select @error('core_count') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                <option value="2" {{ old('core_count') == 2 ? 'selected' : '' }}>2 Core</option>
                                <option value="4" {{ old('core_count') == 4 ? 'selected' : '' }}>4 Core</option>
                                <option value="8" {{ old('core_count') == 8 ? 'selected' : '' }}>8 Core</option>
                                <option value="12" {{ old('core_count') == 12 ? 'selected' : '' }}>12 Core</option>
                                <option value="24" {{ old('core_count') == 24 ? 'selected' : '' }}>24 Core</option>
                                <option value="48" {{ old('core_count') == 48 ? 'selected' : '' }}>48 Core</option>
                                <option value="96" {{ old('core_count') == 96 ? 'selected' : '' }}>96 Core</option>
                                <option value="144" {{ old('core_count') == 144 ? 'selected' : '' }}>144 Core</option>
                            </select>
                            @error('core_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Installation Type</label>
                            <select name="installation_type" class="form-select @error('installation_type') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                <option value="aerial" {{ old('installation_type') == 'aerial' ? 'selected' : '' }}>Aerial (Pole)</option>
                                <option value="underground" {{ old('installation_type') == 'underground' ? 'selected' : '' }}>Underground (Duct)</option>
                                <option value="direct_buried" {{ old('installation_type') == 'direct_buried' ? 'selected' : '' }}>Direct Buried</option>
                            </select>
                            @error('installation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cable Brand</label>
                            <input type="text" name="cable_brand" class="form-control @error('cable_brand') is-invalid @enderror"
                                   value="{{ old('cable_brand') }}" placeholder="Corning, Furukawa, etc">
                            @error('cable_brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cable Model</label>
                            <input type="text" name="cable_model" class="form-control @error('cable_model') is-invalid @enderror"
                                   value="{{ old('cable_model') }}" placeholder="ADSS, GYFTY, etc">
                            @error('cable_model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3">Route Configuration</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Point Type <span class="text-danger">*</span></label>
                            <select name="start_point_type" id="startPointType" class="form-select @error('start_point_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="App\Models\OLT" {{ old('start_point_type') == 'App\Models\OLT' ? 'selected' : '' }}>OLT</option>
                                <option value="App\Models\JointBox" {{ old('start_point_type') == 'App\Models\JointBox' ? 'selected' : '' }}>Joint Box</option>
                                <option value="App\Models\ODP" {{ old('start_point_type') == 'App\Models\ODP' ? 'selected' : '' }}>ODP</option>
                            </select>
                            @error('start_point_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Point <span class="text-danger">*</span></label>
                            <select name="start_point_id" id="startPointId" class="form-select @error('start_point_id') is-invalid @enderror" required>
                                <option value="">-- Select Start Point --</option>
                            </select>
                            @error('start_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Point Type <span class="text-danger">*</span></label>
                            <select name="end_point_type" id="endPointType" class="form-select @error('end_point_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="App\Models\JointBox" {{ old('end_point_type') == 'App\Models\JointBox' ? 'selected' : '' }}>Joint Box</option>
                                <option value="App\Models\ODP" {{ old('end_point_type') == 'App\Models\ODP' ? 'selected' : '' }}>ODP</option>
                                <option value="App\Models\ONT" {{ old('end_point_type') == 'App\Models\ONT' ? 'selected' : '' }}>ONT</option>
                            </select>
                            @error('end_point_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Point <span class="text-danger">*</span></label>
                            <select name="end_point_id" id="endPointId" class="form-select @error('end_point_id') is-invalid @enderror" required>
                                <option value="">-- Select End Point --</option>
                            </select>
                            @error('end_point_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distance (meters)</label>
                            <input type="number" name="distance" class="form-control @error('distance') is-invalid @enderror"
                                   value="{{ old('distance') }}" step="0.01" placeholder="500">
                            @error('distance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
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
</div>

@push('scripts')
<script>
// Dynamic dropdown for start/end points
const pointsData = {
    'App\\Models\\OLT': @json($olts),
    'App\\Models\\JointBox': @json($jointBoxes),
    'App\\Models\\ODP': @json($odps)
};

document.getElementById('startPointType').addEventListener('change', function() {
    updatePointDropdown('startPointId', this.value);
});

document.getElementById('endPointType').addEventListener('change', function() {
    updatePointDropdown('endPointId', this.value);
});

function updatePointDropdown(selectId, type) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">-- Select --</option>';

    if (type && pointsData[type]) {
        pointsData[type].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name + (item.code ? ` (${item.code})` : '');
            select.appendChild(option);
        });
    }
}
</script>
@endpush
@endsection
