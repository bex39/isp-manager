@extends('layouts.admin')

@section('title', 'Edit ODC')
@section('page-title', 'Edit ODC: ' . $odc->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Edit ODC Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('odcs.update', $odc) }}" method="POST" id="odcEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $odc->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $odc->code) }}" required>
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
                                    <option value="{{ $odf->id }}" {{ old('odf_id', $odc->odf_id) == $odf->id ? 'selected' : '' }}>
                                        {{ $odf->name }} ({{ $odf->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('odf_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location Type</label>
                            <select name="location" class="form-select @error('location') is-invalid @enderror">
                                <option value="outdoor" {{ old('location', $odc->location) == 'outdoor' ? 'selected' : '' }}>Outdoor (Street Cabinet)</option>
                                <option value="indoor" {{ old('location', $odc->location) == 'indoor' ? 'selected' : '' }}>Indoor (Building)</option>
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
                                <option value="custom" {{ !in_array($odc->total_ports, [24, 48, 96, 144]) ? 'selected' : '' }}>Custom Port Count</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="presetPortDiv">
                            <label class="form-label">Total Ports <span class="text-danger">*</span></label>
                            <select name="total_ports" id="presetPorts" class="form-select @error('total_ports') is-invalid @enderror">
                                <option value="24" {{ old('total_ports', $odc->total_ports) == 24 ? 'selected' : '' }}>24 Ports</option>
                                <option value="48" {{ old('total_ports', $odc->total_ports) == 48 ? 'selected' : '' }}>48 Ports</option>
                                <option value="96" {{ old('total_ports', $odc->total_ports) == 96 ? 'selected' : '' }}>96 Ports</option>
                                <option value="144" {{ old('total_ports', $odc->total_ports) == 144 ? 'selected' : '' }}>144 Ports</option>
                            </select>
                            <small class="text-muted">Currently used: <strong class="text-warning">{{ $odc->used_ports }}</strong> ports</small>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="customPortDiv" style="display: none;">
                            <label class="form-label">Custom Port Count <span class="text-danger">*</span></label>
                            <input type="number" name="custom_ports" id="customPorts"
                                   class="form-control @error('total_ports') is-invalid @enderror"
                                   min="{{ $odc->used_ports }}" max="288" step="1"
                                   placeholder="Enter custom port count ({{ $odc->used_ports }}-288)"
                                   value="{{ old('custom_ports', in_array($odc->total_ports, [24, 48, 96, 144]) ? '' : $odc->total_ports) }}">
                            <small class="text-muted">
                                Minimum: <strong class="text-danger">{{ $odc->used_ports }}</strong> (currently used) |
                                Maximum: <strong>288</strong>
                            </small>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warning Alert -->
                        <div class="col-12">
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>
                                    <strong>Important:</strong> Cannot reduce total ports below currently used ports ({{ $odc->used_ports }}).
                                    Free up ports before reducing capacity.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cabinet Type</label>
                            <input type="text" name="cabinet_type" class="form-control @error('cabinet_type') is-invalid @enderror"
                                   value="{{ old('cabinet_type', $odc->cabinet_type) }}" placeholder="e.g., Wall-mount, Pole-mount">
                            @error('cabinet_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cabinet Size</label>
                            <input type="text" name="cabinet_size" class="form-control @error('cabinet_size') is-invalid @enderror"
                                   value="{{ old('cabinet_size', $odc->cabinet_size) }}" placeholder="e.g., 600x400x200mm">
                            @error('cabinet_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location Coordinates - FIXED -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Location Coordinates <span class="text-danger">*</span></h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" name="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $odc->latitude) }}"
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
                                   value="{{ old('longitude', $odc->longitude) }}"
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
                                      rows="2" required placeholder="Street address or landmark">{{ old('address', $odc->address) }}</textarea>
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
                                   value="{{ old('installation_date', $odc->installation_date ? $odc->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', $odc->is_active) ? 'checked' : '' }} value="1">
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Status</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes or comments">{{ old('notes', $odc->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update ODC
                        </button>
                        <a href="{{ route('odcs.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>

                <!-- Danger Zone -->
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger mb-1">Danger Zone</h6>
                        <small class="text-muted">Permanently delete this ODC</small>
                    </div>
                    <form action="{{ route('odcs.destroy', $odc) }}" method="POST" onsubmit="return confirm('⚠️ DELETE ODC: {{ $odc->name }}?\n\nThis will remove:\n- All port mappings\n- Connected splitters\n- Cable connections\n\nThis action CANNOT be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete ODC
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Current Status</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="45%" class="text-muted">Status:</td>
                        <td>
                            @if($odc->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Ports:</td>
                        <td><strong>{{ $odc->total_ports }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Used Ports:</td>
                        <td><strong class="text-warning">{{ $odc->used_ports }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Available:</td>
                        <td><span class="badge bg-success">{{ $odc->getAvailablePorts() }} ports</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Usage:</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $odc->getUsageBadgeClass() }}"
                                     style="width: {{ $odc->getUsagePercentage() }}%">
                                    {{ $odc->getUsagePercentage() }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Splitters:</td>
                        <td><strong>{{ $odc->splitters->count() }}</strong></td>
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
                    <a href="{{ route('odcs.ports', $odc) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-diagram-3"></i> View Port Map
                    </a>
                    <a href="{{ route('odcs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb"></i> Edit Tips</h6>
                <ul class="small mb-0">
                    <li>Cannot reduce ports below {{ $odc->used_ports }} (currently used)</li>
                    <li>Changing ODF affects cable routing</li>
                    <li>Code must remain unique</li>
                    <li>Inactive ODC won't appear in selections</li>
                    <li>GPS coordinates required for mapping</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const usedPorts = {{ $odc->used_ports }};

    // Toggle custom port
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
    document.getElementById('odcEditForm').addEventListener('submit', function(e) {
        const option = document.getElementById('portOption').value;
        let totalPorts;

        if (option === 'custom') {
            totalPorts = parseInt(document.getElementById('customPorts').value);

            if (!totalPorts || totalPorts < usedPorts) {
                e.preventDefault();
                alert(`⚠️ Cannot set total ports below currently used ports!\n\nMinimum: ${usedPorts} ports\nYou entered: ${totalPorts || 0} ports`);
                return false;
            }

            if (totalPorts > 288) {
                e.preventDefault();
                alert('⚠️ Maximum port count is 288 for ODC');
                return false;
            }

            if (![24, 48, 96, 144].includes(totalPorts)) {
                if (!confirm(`Changing to ${totalPorts} ports (custom configuration).\n\nCurrent: {{ $odc->total_ports }} ports\nUsed: ${usedPorts} ports\n\nContinue?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        } else {
            totalPorts = parseInt(document.getElementById('presetPorts').value);

            if (totalPorts < usedPorts) {
                e.preventDefault();
                alert(`⚠️ Cannot reduce ports!\n\nCurrently ${usedPorts} ports are in use.`);
                return false;
            }
        }
    });

    // Real-time validation
    document.getElementById('customPorts').addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value < usedPorts) {
            this.setCustomValidity(`Minimum ${usedPorts} ports (currently in use)`);
        } else if (value > 288) {
            this.setCustomValidity('Maximum 288 ports');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('presetPorts').addEventListener('change', function() {
        const value = parseInt(this.value);
        if (value < usedPorts) {
            alert(`⚠️ Cannot select ${value} ports!\n\nCurrently ${usedPorts} ports are in use.`);
            this.value = {{ $odc->total_ports }};
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
