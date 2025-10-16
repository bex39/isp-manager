@extends('layouts.admin')

@section('title', 'Edit ODP')
@section('page-title', 'Edit ODP')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('odps.show', $odp) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to ODP
        </a>
        <a href="{{ route('odps.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list"></i> All ODPs
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit ODP: {{ $odp->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('odps.update', $odp) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $odp->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $odp->code) }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Total Ports -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Total Ports <span class="text-danger">*</span></label>
                            <input type="number" name="total_ports" class="form-control @error('total_ports') is-invalid @enderror"
                                   value="{{ old('total_ports', $odp->total_ports) }}"
                                   min="1" max="48" required>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Current used ports: {{ $odp->used_ports }}
                                (Cannot reduce below this number)
                            </small>
                        </div>
                    </div>

                    <!-- Address/Location -->
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Street address or landmark">{{ old('address', $odp->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Coordinates -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $odp->latitude) }}"
                                   placeholder="-8.670458" required>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $odp->longitude) }}"
                                   placeholder="115.212631" required>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if($odp->latitude && $odp->longitude)
                    <div class="mb-3">
                        <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-info">
                            <i class="bi bi-map"></i> View on Google Maps
                        </a>
                    </div>
                    @endif

                    <!-- Installation Date -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date', $odp->installation_date) }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                       id="isActive" {{ old('is_active', $odp->is_active) ? 'checked' : '' }}>
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
                                  placeholder="Additional information...">{{ old('notes', $odp->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('odps.show', $odp) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update ODP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Port Usage -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-diagram-3"></i> Port Usage</h6>
            </div>
            <div class="card-body">
                @php
                    $connectedONTs = \App\Models\ONT::where('odp_id', $odp->id)->count();
                    $usagePercent = $odp->total_ports > 0 ? ($connectedONTs / $odp->total_ports) * 100 : 0;
                @endphp

                <div class="text-center mb-3">
                    <h3 class="mb-0">{{ $odp->used_ports }} / {{ $odp->total_ports }}</h3>
                    <small class="text-muted">Ports Used</small>
                </div>

                <div class="progress mb-2" style="height: 20px;">
                    <div class="progress-bar bg-{{ $usagePercent > 80 ? 'danger' : ($usagePercent > 50 ? 'warning' : 'success') }}"
                         style="width: {{ min($usagePercent, 100) }}%">
                        {{ number_format($usagePercent, 1) }}%
                    </div>
                </div>

                <small class="text-muted">Available: {{ $odp->total_ports - $odp->used_ports }} ports</small>

                @if($usagePercent > 90)
                    <div class="alert alert-danger mt-3 mb-0 small">
                        <i class="bi bi-exclamation-triangle"></i> Almost full!
                    </div>
                @endif
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
                    <p class="mb-0 small">{{ $odp->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 small">{{ $odp->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
