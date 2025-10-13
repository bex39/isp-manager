@extends('layouts.admin')

@section('title', 'Edit ODF')
@section('page-title', 'Edit ODF: ' . $odf->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Edit ODF Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('odfs.update', $odf) }}" method="POST" id="odfEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $odf->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $odf->code) }}" required>
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
                                    <option value="{{ $olt->id }}" {{ old('olt_id', $odf->olt_id) == $olt->id ? 'selected' : '' }}>
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
                                <option value="indoor" {{ old('location', $odf->location) == 'indoor' ? 'selected' : '' }}>Indoor</option>
                                <option value="outdoor" {{ old('location', $odf->location) == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ✅ UPDATED: Port Configuration with Custom Input -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Port Configuration</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Port Count Option <span class="text-danger">*</span></label>
                            <select id="portOption" class="form-select" onchange="toggleCustomPort()">
                                <option value="preset">Standard Port Count</option>
                                <option value="custom" {{ !in_array($odf->total_ports, [24, 48, 96, 144, 288]) ? 'selected' : '' }}>Custom Port Count</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="presetPortDiv">
                            <label class="form-label">Total Ports <span class="text-danger">*</span></label>
                            <select name="total_ports" id="presetPorts" class="form-select @error('total_ports') is-invalid @enderror">
                                <option value="24" {{ old('total_ports', $odf->total_ports) == 24 ? 'selected' : '' }}>24 Ports</option>
                                <option value="48" {{ old('total_ports', $odf->total_ports) == 48 ? 'selected' : '' }}>48 Ports</option>
                                <option value="96" {{ old('total_ports', $odf->total_ports) == 96 ? 'selected' : '' }}>96 Ports</option>
                                <option value="144" {{ old('total_ports', $odf->total_ports) == 144 ? 'selected' : '' }}>144 Ports</option>
                                <option value="288" {{ old('total_ports', $odf->total_ports) == 288 ? 'selected' : '' }}>288 Ports</option>
                            </select>
                            <small class="text-muted">Currently used: <strong class="text-warning">{{ $odf->used_ports }}</strong> ports</small>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="customPortDiv" style="display: none;">
                            <label class="form-label">Custom Port Count <span class="text-danger">*</span></label>
                            <input type="number" name="custom_ports" id="customPorts"
                                   class="form-control @error('total_ports') is-invalid @enderror"
                                   min="{{ $odf->used_ports }}" max="576" step="1"
                                   placeholder="Enter custom port count ({{ $odf->used_ports }}-576)"
                                   value="{{ old('custom_ports', in_array($odf->total_ports, [24, 48, 96, 144, 288]) ? '' : $odf->total_ports) }}">
                            <small class="text-muted">
                                Minimum: <strong class="text-danger">{{ $odf->used_ports }}</strong> (currently used) |
                                Maximum: <strong>576</strong>
                            </small>
                            @error('total_ports')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warning Alert for Port Reduction -->
                        <div class="col-12">
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>
                                    <strong>Important:</strong> Cannot reduce total ports below currently used ports ({{ $odf->used_ports }}).
                                    Free up ports before reducing capacity.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rack Number</label>
                            <input type="text" name="rack_number" class="form-control @error('rack_number') is-invalid @enderror"
                                   value="{{ old('rack_number', $odf->rack_number) }}" placeholder="e.g., Rack A">
                            @error('rack_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position', $odf->position) }}" placeholder="e.g., U1-U2">
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location Coordinates -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Location Coordinates</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.000001" name="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $odf->latitude) }}" placeholder="-8.6705">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.000001" name="longitude"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $odf->longitude) }}" placeholder="115.2126">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2" placeholder="Full address">{{ old('address', $odf->address) }}</textarea>
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
                                   value="{{ old('installation_date', $odf->installation_date ? $odf->installation_date->format('Y-m-d') : '') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', $odf->is_active) ? 'checked' : '' }} value="1">
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Status</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes or comments">{{ old('notes', $odf->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update ODF
                        </button>
                        <a href="{{ route('odfs.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>

                <!-- Danger Zone -->
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger mb-1">Danger Zone</h6>
                        <small class="text-muted">Permanently delete this ODF</small>
                    </div>
                    <form action="{{ route('odfs.destroy', $odf) }}" method="POST" onsubmit="return confirm('⚠️ DELETE ODF: {{ $odf->name }}?\n\nThis will remove:\n- All port mappings\n- Cable connections\n- Related data\n\nThis action CANNOT be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete ODF
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
                            @if($odf->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Ports:</td>
                        <td><strong>{{ $odf->total_ports }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Used Ports:</td>
                        <td><strong class="text-warning">{{ $odf->used_ports }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Available:</td>
                        <td><span class="badge bg-success">{{ $odf->getAvailablePorts() }} ports</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Usage:</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $odf->getUsageBadgeClass() }}"
                                     style="width: {{ $odf->getUsagePercentage() }}%">
                                    {{ $odf->getUsagePercentage() }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Connected ODCs:</td>
                        <td><strong>{{ $odf->odcs->count() }}</strong></td>
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
                    <a href="{{ route('odfs.ports', $odf) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-diagram-3"></i> View Port Map
                    </a>
                    <a href="{{ route('odfs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb"></i> Edit Tips</h6>
                <ul class="small mb-0">
                    <li>Cannot reduce ports below {{ $odf->used_ports }} (currently used)</li>
                    <li>Changing OLT affects cable routing</li>
                    <li>Code must remain unique</li>
                    <li>Inactive ODF won't appear in selections</li>
                    <li>Use custom ports for special configurations</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const usedPorts = {{ $odf->used_ports }};

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

    // Form validation
    document.getElementById('odfEditForm').addEventListener('submit', function(e) {
        const option = document.getElementById('portOption').value;
        let totalPorts;

        if (option === 'custom') {
            totalPorts = parseInt(document.getElementById('customPorts').value);

            if (!totalPorts || totalPorts < usedPorts) {
                e.preventDefault();
                alert(`⚠️ Cannot set total ports below currently used ports!\n\nMinimum: ${usedPorts} ports\nYou entered: ${totalPorts || 0} ports`);
                return false;
            }

            if (totalPorts > 576) {
                e.preventDefault();
                alert('⚠️ Maximum port count is 576');
                return false;
            }

            // Confirmation for custom port counts
            if (![24, 48, 96, 144, 288, 576].includes(totalPorts)) {
                if (!confirm(`Changing to ${totalPorts} ports (custom configuration).\n\nCurrent: {{ $odf->total_ports }} ports\nUsed: ${usedPorts} ports\n\nContinue?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        } else {
            totalPorts = parseInt(document.getElementById('presetPorts').value);

            if (totalPorts < usedPorts) {
                e.preventDefault();
                alert(`⚠️ Cannot reduce ports!\n\nCurrently ${usedPorts} ports are in use.\nPlease free up ports before reducing capacity.`);
                return false;
            }
        }
    });

    // Real-time validation for custom input
    document.getElementById('customPorts').addEventListener('input', function() {
        const value = parseInt(this.value);
        const min = usedPorts;
        const max = 576;

        if (value < min) {
            this.setCustomValidity(`Minimum ${min} ports (currently in use)`);
        } else if (value > max) {
            this.setCustomValidity(`Maximum ${max} ports`);
        } else {
            this.setCustomValidity('');
        }
    });

    // Preset port validation
    document.getElementById('presetPorts').addEventListener('change', function() {
        const value = parseInt(this.value);

        if (value < usedPorts) {
            alert(`⚠️ Cannot select ${value} ports!\n\nCurrently ${usedPorts} ports are in use.`);
            this.value = {{ $odf->total_ports }};
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomPort();
    });
</script>
@endpush
