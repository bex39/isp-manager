@extends('layouts.admin')

@section('title', 'Edit Splitter')
@section('page-title', 'Edit Splitter')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('splitters.show', $splitter) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Splitter
        </a>
        <a href="{{ route('splitters.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list"></i> All Splitters
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Splitter: {{ $splitter->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('splitters.update', $splitter) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $splitter->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $splitter->code) }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Splitter Configuration -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splitter Ratio <span class="text-danger">*</span></label>
                            <select name="splitter_ratio" class="form-select @error('splitter_ratio') is-invalid @enderror" required>
                                <option value="">Select Ratio</option>
                                <option value="1:2" {{ old('splitter_ratio', $splitter->splitter_ratio) == '1:2' ? 'selected' : '' }}>1:2</option>
                                <option value="1:4" {{ old('splitter_ratio', $splitter->splitter_ratio) == '1:4' ? 'selected' : '' }}>1:4</option>
                                <option value="1:8" {{ old('splitter_ratio', $splitter->splitter_ratio) == '1:8' ? 'selected' : '' }}>1:8</option>
                                <option value="1:16" {{ old('splitter_ratio', $splitter->splitter_ratio) == '1:16' ? 'selected' : '' }}>1:16</option>
                                <option value="1:32" {{ old('splitter_ratio', $splitter->splitter_ratio) == '1:32' ? 'selected' : '' }}>1:32</option>
                                <option value="1:64" {{ old('splitter_ratio', $splitter->splitter_ratio) == '1:64' ? 'selected' : '' }}>1:64</option>
                            </select>
                            @error('splitter_ratio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">1 input to N outputs</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Insertion Loss (dB)</label>
                            <input type="number" step="0.01" name="insertion_loss"
                                   class="form-control @error('insertion_loss') is-invalid @enderror"
                                   value="{{ old('insertion_loss', $splitter->insertion_loss) }}"
                                   min="0" max="30">
                            @error('insertion_loss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Typical: 3.5dB (1:2), 10.5dB (1:8), 13.5dB (1:16)</small>
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splitter Type</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="plc" {{ old('type', $splitter->type) == 'plc' ? 'selected' : '' }}>PLC (Planar Lightwave Circuit)</option>
                                <option value="fbt" {{ old('type', $splitter->type) == 'fbt' ? 'selected' : '' }}>FBT (Fused Biconical Taper)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Output Ports <span class="text-danger">*</span></label>
                            <input type="number" name="output_ports"
                                   class="form-control @error('output_ports') is-invalid @enderror"
                                   value="{{ old('output_ports', $splitter->output_ports) }}"
                                   min="2" max="64" required>
                            @error('output_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Must match splitter ratio (e.g., 1:8 = 8 ports)</small>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <textarea name="location" rows="2" class="form-control @error('location') is-invalid @enderror">{{ old('location', $splitter->location) }}</textarea>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Coordinates -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $splitter->latitude) }}">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $splitter->longitude) }}">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Installation -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date', $splitter->installation_date ? $splitter->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $splitter->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ old('status', $splitter->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ old('status', $splitter->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                   id="isActive" {{ old('is_active', $splitter->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">
                                Active
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $splitter->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('splitters.show', $splitter) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Splitter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Insertion Loss Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Insertion Loss Guide</h6>
            </div>
            <div class="card-body">
                <small>
                    <strong>Typical Insertion Loss:</strong>
                    <ul class="mb-0 mt-2">
                        <li>1:2 = 3.5 dB</li>
                        <li>1:4 = 7.0 dB</li>
                        <li>1:8 = 10.5 dB</li>
                        <li>1:16 = 13.5 dB</li>
                        <li>1:32 = 16.5 dB</li>
                        <li>1:64 = 20.0 dB</li>
                    </ul>
                </small>
            </div>
        </div>

        <!-- Port Usage -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-diagram-3"></i> Output Ports</h6>
            </div>
            <div class="card-body">
                @php
                    // Count connected ports (example - adjust based on your relations)
                    $connectedPorts = 0; // You may need to implement port tracking
                @endphp

                <div class="text-center">
                    <h3 class="mb-0">{{ $splitter->output_ports }}</h3>
                    <small class="text-muted">Total Output Ports</small>
                </div>
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
                    <p class="mb-0 small">{{ $splitter->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 small">{{ $splitter->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
