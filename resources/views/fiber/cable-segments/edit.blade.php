@extends('layouts.admin')

@section('title', 'Edit Cable Segment')
@section('page-title', 'Edit Cable Segment')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('cable-segments.update', $cableSegment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold mb-3">Basic Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cable Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code', $cableSegment->code) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cable Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $cableSegment->name) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cable Type <span class="text-danger">*</span></label>
                            <select name="cable_type" class="form-select" required>
                                <option value="backbone" {{ old('cable_type', $cableSegment->cable_type) == 'backbone' ? 'selected' : '' }}>Backbone</option>
                                <option value="distribution" {{ old('cable_type', $cableSegment->cable_type) == 'distribution' ? 'selected' : '' }}>Distribution</option>
                                <option value="drop" {{ old('cable_type', $cableSegment->cable_type) == 'drop' ? 'selected' : '' }}>Drop Cable</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Installation Type</label>
                            <select name="installation_type" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="aerial" {{ old('installation_type', $cableSegment->installation_type) == 'aerial' ? 'selected' : '' }}>Aerial</option>
                                <option value="underground" {{ old('installation_type', $cableSegment->installation_type) == 'underground' ? 'selected' : '' }}>Underground</option>
                                <option value="direct_buried" {{ old('installation_type', $cableSegment->installation_type) == 'direct_buried' ? 'selected' : '' }}>Direct Buried</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $cableSegment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="damaged" {{ old('status', $cableSegment->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="maintenance" {{ old('status', $cableSegment->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <strong>Route:</strong>
                                {{ $cableSegment->startPoint->name ?? 'Unknown' }} ({{ class_basename($cableSegment->start_point_type) }})
                                →
                                {{ $cableSegment->endPoint->name ?? 'Unknown' }} ({{ class_basename($cableSegment->end_point_type) }})
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distance (meters)</label>
                            <input type="number" name="distance" class="form-control"
                                   value="{{ old('distance', $cableSegment->distance) }}" step="0.01">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control"
                                   value="{{ old('installation_date', $cableSegment->installation_date?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $cableSegment->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('cable-segments.show', $cableSegment) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
