@extends('layouts.admin')

@section('title', 'Add ODF')
@section('page-title', 'Add New ODF')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">ODF Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('odfs.store') }}" method="POST" id="odfForm">
                    @csrf

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="e.g., ODF-CO-A">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" required placeholder="e.g., ODF-001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- OLT Selection -->
                        <div class="col-md-6">
                            <label class="form-label">OLT <span class="text-danger">*</span></label>
                            <select name="olt_id" class="form-select @error('olt_id') is-invalid @enderror" required>
                                <option value="">Select OLT</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                                        {{ $olt->name }} ({{ $olt->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                            @error('olt_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-select @error('location') is-invalid @enderror">
                                <option value="indoor" {{ old('location', 'indoor') == 'indoor' ? 'selected' : '' }}>Indoor</option>
                                <option value="outdoor" {{ old('location') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ✅ NEW: Port Configuration with Custom Input -->
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
                                <option value="288" {{ old('total_ports') == 288 ? 'selected' : '' }}>288 Ports</option>
                            </select>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="customPortDiv" style="display: none;">
                            <label class="form-label">Custom Port Count <span class="text-danger">*</span></label>
                            <input type="number" name="custom_ports" id="customPorts"
                                   class="form-control @error('total_ports') is-invalid @enderror"
                                   min="1" max="576" step="1"
                                   placeholder="Enter custom port count (1-576)"
                                   value="{{ old('custom_ports') }}">
                            <small class="text-muted">Maximum: 576 ports</small>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rack Number</label>
                            <input type="text" name="rack_number" class="form-control @error('rack_number') is-invalid @enderror"
                                   value="{{ old('rack_number') }}" placeholder="e.g., Rack A">
                            @error('rack_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position') }}" placeholder="e.g., U1-U2">
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <!-- Location Coordinates -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Location Coordinates (Optional)</h6>
                            <p class="small text-muted mb-0">GPS coordinates for mapping. Leave empty if unknown.</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude') }}"
                                   placeholder="-8.6705"
                                   pattern="^-?([0-9]{1,2}|1[0-7][0-9]|180)(\.[0-9]{1,10})?$">
                            <small class="text-muted">Range: -90 to 90 (e.g., -8.6705)</small>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude') }}"
                                   placeholder="115.2126"
                                   pattern="^-?([0-9]{1,2}|1[0-7][0-9]|180)(\.[0-9]{1,10})?$">
                            <small class="text-muted">Range: -180 to 180 (e.g., 115.2126)</small>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2" placeholder="Full address (optional)">{{ old('address') }}</textarea>
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
                            <i class="bi bi-save"></i> Create ODF
                        </button>
                        <a href="{{ route('odfs.index') }}" class="btn btn-secondary">
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
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> About ODF</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>ODF (Optical Distribution Frame)</strong> is a patch panel used in Central Office to connect
                    incoming fiber cables from OLT to outgoing cables to field equipment (ODC, ODP).
                </p>
                <hr>
                <p class="small mb-2"><strong>Port Configuration:</strong></p>
                <ul class="small mb-0">
                    <li><strong>Standard:</strong> 24, 48, 96, 144, 288</li>
                    <li><strong>Custom:</strong> Any count 1-576</li>
                    <li>Based on rack unit capacity</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Use unique code for each ODF</li>
                    <li>Standard ODF sizes: 24, 48, 96, 144</li>
                    <li>Custom ports for special configurations</li>
                    <li>Indoor location for Central Office</li>
                    <li>Record rack position for easy maintenance</li>
                    <li>1 RU = typically 24 ports</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-calculator"></i> Port Calculator</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label small">Rack Units (RU):</label>
                    <input type="number" id="rackUnits" class="form-control form-control-sm"
                           min="1" max="24" value="2" onchange="calculatePorts()">
                </div>
                <div class="alert alert-info mb-0 small">
                    <strong>Estimated Ports:</strong> <span id="estimatedPorts">48</span>
                    <br><small class="text-muted">Typical: 24 ports per RU</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle between preset and custom port input
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

    // Port calculator
    function calculatePorts() {
        const rackUnits = parseInt(document.getElementById('rackUnits').value) || 2;
        const portsPerRU = 24;
        const estimated = rackUnits * portsPerRU;
        document.getElementById('estimatedPorts').textContent = estimated;
    }

    // Form validation
    document.getElementById('odfForm').addEventListener('submit', function(e) {
        const option = document.getElementById('portOption').value;

        if (option === 'custom') {
            const customPorts = parseInt(document.getElementById('customPorts').value);

            if (!customPorts || customPorts < 1) {
                e.preventDefault();
                alert('⚠️ Please enter a valid port count (minimum 1)');
                return false;
            }

            if (customPorts > 576) {
                e.preventDefault();
                alert('⚠️ Maximum port count is 576');
                return false;
            }

            // Confirmation for unusual port counts
            if (![24, 48, 96, 144, 288, 576].includes(customPorts)) {
                if (!confirm(`You're creating an ODF with ${customPorts} ports.\n\nThis is a custom configuration.\n\nContinue?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomPort();
        calculatePorts();
    });
</script>
@endpush
