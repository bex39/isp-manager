@extends('layouts.admin')

@section('title', 'Edit Joint Box')
@section('page-title', 'Edit Joint Box')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('joint-boxes.show', $jointBox) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Joint Box
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Joint Box: {{ $jointBox->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('joint-boxes.update', $jointBox) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $jointBox->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $jointBox->code) }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Type & Capacity -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="inline" {{ old('type', $jointBox->type) == 'inline' ? 'selected' : '' }}>Inline</option>
                                <option value="branch" {{ old('type', $jointBox->type) == 'branch' ? 'selected' : '' }}>Branch</option>
                                <option value="terminal" {{ old('type', $jointBox->type) == 'terminal' ? 'selected' : '' }}>Terminal</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity (splices) <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
                                   value="{{ old('capacity', $jointBox->capacity) }}" min="1" max="288" required>
                            <small class="text-muted">Current splices: {{ $jointBox->splices->count() }}</small>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <textarea name="location" rows="2" class="form-control @error('location') is-invalid @enderror">{{ old('location', $jointBox->location) }}</textarea>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Coordinates -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $jointBox->latitude) }}">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $jointBox->longitude) }}">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Installation Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Installation Type</label>
                            <select name="installation_type" class="form-select @error('installation_type') is-invalid @enderror">
                                <option value="">Select Installation Type</option>
                                <option value="aerial" {{ old('installation_type', $jointBox->installation_type) == 'aerial' ? 'selected' : '' }}>Aerial</option>
                                <option value="underground" {{ old('installation_type', $jointBox->installation_type) == 'underground' ? 'selected' : '' }}>Underground</option>
                                <option value="buried" {{ old('installation_type', $jointBox->installation_type) == 'buried' ? 'selected' : '' }}>Buried</option>
                            </select>
                            @error('installation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date', $jointBox->installation_date ? $jointBox->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $jointBox->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ old('status', $jointBox->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ old('status', $jointBox->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                       id="isActive" {{ old('is_active', $jointBox->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $jointBox->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('joint-boxes.show', $jointBox) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Joint Box
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Important</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>Current Splices:</strong> {{ $jointBox->splices->count() }}
                </p>
                <p class="small mb-0">
                    Ensure capacity is not less than current splice count.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Info</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>Created:</strong><br>
                    {{ $jointBox->created_at->format('d M Y, H:i') }}
                </p>
                <p class="small mb-0">
                    <strong>Last Updated:</strong><br>
                    {{ $jointBox->updated_at->format('d M Y, H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
