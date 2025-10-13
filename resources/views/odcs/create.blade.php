@extends('layouts.admin')

@section('title', 'Add ODC')
@section('page-title', 'Add New ODC')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">ODC Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('odcs.store') }}" method="POST" id="odcForm">
                    @csrf

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="e.g., ODC-Area-A">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" required placeholder="e.g., ODC-001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ODF Selection -->
                        <div class="col-md-6">
                            <label class="form-label">ODF Source <span class="text-danger">*</span></label>
                            <select name="odf_id" class="form-select @error('odf_id') is-invalid @enderror" required>
                                <option value="">Select ODF</option>
                                @foreach($odfs as $odf)
                                    <option value="{{ $odf->id }}" {{ old('odf_id') == $odf->id ? 'selected' : '' }}>
                                        {{ $odf->name }} ({{ $odf->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('odf_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Fiber source from Central Office</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location Type</label>
                            <select name="location" class="form-select @error('location') is-invalid @enderror">
                                <option value="outdoor" {{ old('location', 'outdoor') == 'outdoor' ? 'selected' : '' }}>Outdoor (Street Cabinet)</option>
                                <option value="indoor" {{ old('location') == 'indoor' ? 'selected' : '' }}>Indoor (Building)</option>
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Port Configuration -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Port Configuration</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Port Count Option <span class="text-danger">*</span></label>
                            <select id="portOption" class="form-select" onchange="toggleCustomPort()">
                                <option value="preset">Standard Port Count</option>
                                <option value="custom">Custom Port Count</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="presetPortDiv">
                            <label class="form-label">Total Ports <span class="text-danger">*</span></label>
                            <select name="total_ports" id="presetPorts" class="form-select @error('total_ports') is-invalid @enderror">
                                <option value="24" {{ old('total_ports') == 24 ? 'selected' : '' }}>24 Ports</option>
                                <option value="48" {{ old('total_ports', 48) == 48 ? 'selected' : '' }}>48 Ports</option>
                                <option value="96" {{ old('total_ports') == 96 ? 'selected' : '' }}>96 Ports</option>
                                <option value="144" {{ old('total_ports') == 144 ? 'selected' : '' }}>144 Ports</option>
                            </select>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="customPortDiv" style="display: none;">
                            <label class="form-label">Custom Port Count <span class="text-danger">*</span></label>
                            <input type="number" name="custom_ports" id="customPorts"
                                   class="form-control @error('total_ports') is-invalid @enderror"
                                   min="1" max="288" step="1"
                                   placeholder="Enter custom port count (1-288)"
                                   value="{{ old('custom_ports') }}">
                            <small class="text-muted">Maximum: 288 ports for ODC</small>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cabinet Type</label>
                            <input type="text" name="cabinet_type" class="form-control @error('cabinet_type') is-invalid @enderror"
                                   value="{{ old('cabinet_type') }}" placeholder="e.g., Wall-mount, Pole-mount">
                            @error('cabinet_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cabinet Size</label>
                            <input type="text" name="cabinet_size" class="form-control @error('cabinet_size') is-invalid @enderror"
                                   value="{{ old('cabinet_size') }}" placeholder="e.g., 600x400x200mm">
                            @error('cabinet_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location Coordinates - FIXED -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Location Coordinates <span class="text-danger">*</span></h6>
                            <p class="small text-muted mb-0">GPS coordinates required for field mapping</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" name="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude') }}"
                                   required
                                   placeholder="-8.6705"
                                   pattern="^-?([0-9]{1,2}|1[0-7][0-9]|180)(\.[0-9]{1,10})?$">
                            <small class="text-muted">Range: -90 to 90 (e.g., -8.6705)</small>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="text" name="longitude"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude') }}"
                                   required
                                   placeholder="115.2126"
                                   pattern="^-?([0-9]{1,2}|1[0-7][0-9]|180)(\.[0-9]{1,10})?$">
                            <small class="text-muted">Range: -180 to 180 (e.g., 115.2126)</small>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2" required placeholder="Street address or landmark">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Info -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Additional Information</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Installation Date</label>
                            <input type="date" name="installation_date"
                                   class="form-control @error('installation_date') is-invalid @enderror"
                                   value="{{ old('installation_date') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }} value="1">
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Status</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes or comments">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create ODC
                        </button>
                        <a href="{{ route('odcs.index') }}" class="btn btn-secondary">
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
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> About ODC</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>ODC (Optical Distribution Cabinet)</strong> is a field cabinet that distributes fiber from
                    ODF in Central Office to multiple ODPs in the coverage area.
                </p>
                <hr>
                <p class="small mb-2"><strong>Port Configuration:</strong></p>
                <ul class="small mb-0">
                    <li><strong>Standard:</strong> 24, 48, 96, 144</li>
                    <li><strong>Custom:</strong> Any count 1-288</li>
                    <li>Outdoor rated IP65/IP67</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>GPS coordinates required for mapping</li>
                    <li>Use unique code for each ODC</li>
                    <li>Outdoor location for street cabinets</li>
                    <li>Standard sizes: 24, 48, 96, 144 ports</li>
                    <li>Record cabinet type for maintenance</li>
                    <li>Cabinet must be weatherproof</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle custom port input
    function toggleCustomPort() {
        const option = document.getElementById('portOption').value;
        const presetDiv = document.getElementById('presetPortDiv');
        const customDiv = document.getElementById('customPortDiv');
        const presetSelect = document.getElementById('presetPorts');
        const customInput = document.getElementById('customPorts');

        if (option === 'custom') {
            presetDiv.style.display = 'none';
            customDiv.style.display = 'block';
            presetSelect.removeAttribute('required');
            presetSelect.removeAttribute('name');
            customInput.setAttribute('required', 'required');
            customInput.setAttribute('name', 'total_ports');
        } else {
            presetDiv.style.display = 'block';
            customDiv.style.display = 'none';
            customInput.removeAttribute('required');
            customInput.removeAttribute('name');
            presetSelect.setAttribute('required', 'required');
            presetSelect.setAttribute('name', 'total_ports');
        }
    }

    // Form validation
    document.getElementById('odcForm').addEventListener('submit', function(e) {
        const option = document.getElementById('portOption').value;

        if (option === 'custom') {
            const customPorts = parseInt(document.getElementById('customPorts').value);

            if (!customPorts || customPorts < 1) {
                e.preventDefault();
                alert('⚠️ Please enter a valid port count (minimum 1)');
                return false;
            }

            if (customPorts > 288) {
                e.preventDefault();
                alert('⚠️ Maximum port count is 288 for ODC');
                return false;
            }

            if (![24, 48, 96, 144].includes(customPorts)) {
                if (!confirm(`Creating ODC with ${customPorts} ports (custom configuration).\n\nContinue?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        }
    });

    // Coordinate validation
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomPort();

        const latInput = document.querySelector('input[name="latitude"]');
        const longInput = document.querySelector('input[name="longitude"]');

        if (latInput) {
            latInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (this.value && (isNaN(value) || value < -90 || value > 90)) {
                    this.setCustomValidity('Latitude must be between -90 and 90');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        if (longInput) {
            longInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (this.value && (isNaN(value) || value < -180 || value > 180)) {
                    this.setCustomValidity('Longitude must be between -180 and 180');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });
</script>
@endpush
