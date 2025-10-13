@extends('layouts.admin')

@section('title', 'Add Joint Box')
@section('page-title', 'Add Joint Box')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('joint-boxes.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Joint Boxes
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Joint Box Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('joint-boxes.store') }}" method="POST">
                    @csrf

                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g., JB-Central-01" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" placeholder="e.g., JB-001">
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
                                <option value="inline" {{ old('type') == 'inline' ? 'selected' : '' }}>Inline</option>
                                <option value="branch" {{ old('type') == 'branch' ? 'selected' : '' }}>Branch</option>
                                <option value="terminal" {{ old('type') == 'terminal' ? 'selected' : '' }}>Terminal</option>
                            </select>
                            <small class="text-muted">
                                Inline: Straight connection | Branch: Multiple outputs | Terminal: End point
                            </small>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity (splices) <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
                                   value="{{ old('capacity', 24) }}" min="1" max="288" required>
                            <small class="text-muted">Common: 12, 24, 48, 96</small>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <textarea name="location" rows="2" class="form-control @error('location') is-invalid @enderror"
                                  placeholder="e.g., Jl. Sudirman No. 123, Near traffic light">{{ old('location') }}</textarea>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Coordinates -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude') }}" placeholder="-8.670458">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude') }}" placeholder="115.212631">
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
                                <option value="aerial" {{ old('installation_type') == 'aerial' ? 'selected' : '' }}>Aerial</option>
                                <option value="underground" {{ old('installation_type') == 'underground' ? 'selected' : '' }}>Underground</option>
                                <option value="buried" {{ old('installation_type') == 'buried' ? 'selected' : '' }}>Buried</option>
                            </select>
                            @error('installation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date') }}">
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
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ old('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                       id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('joint-boxes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Joint Box
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Help</h6>
            </div>
            <div class="card-body">
                <h6>Joint Box Types:</h6>
                <ul class="small">
                    <li><strong>Inline:</strong> For straight fiber connections</li>
                    <li><strong>Branch:</strong> For splitting into multiple paths</li>
                    <li><strong>Terminal:</strong> End point connection</li>
                </ul>

                <h6 class="mt-3">Common Capacities:</h6>
                <ul class="small">
                    <li>12-core: Small installations</li>
                    <li>24-core: Standard use</li>
                    <li>48-core: Medium network</li>
                    <li>96-core: Large network</li>
                    <li>144-288 core: Main distribution</li>
                </ul>

                <h6 class="mt-3">Installation Types:</h6>
                <ul class="small">
                    <li><strong>Aerial:</strong> Mounted on poles</li>
                    <li><strong>Underground:</strong> In ducts/conduits</li>
                    <li><strong>Buried:</strong> Direct burial</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
