@extends('layouts.admin')

@section('title', 'Edit OLT')
@section('page-title', 'Edit OLT')

@section('content')
<form action="{{ route('olts.update', $olt) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">OLT Information</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $olt->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                               value="{{ old('ip_address', $olt->ip_address) }}" required>
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">OLT Type <span class="text-danger">*</span></label>
                        <select name="olt_type" class="form-select @error('olt_type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            <option value="huawei" {{ old('olt_type', $olt->olt_type) == 'huawei' ? 'selected' : '' }}>Huawei</option>
                            <option value="zte" {{ old('olt_type', $olt->olt_type) == 'zte' ? 'selected' : '' }}>ZTE</option>
                            <option value="fiberhome" {{ old('olt_type', $olt->olt_type) == 'fiberhome' ? 'selected' : '' }}>FiberHome</option>
                            <option value="bdcom" {{ old('olt_type', $olt->olt_type) == 'bdcom' ? 'selected' : '' }}>BDCOM</option>
                            <option value="other" {{ old('olt_type', $olt->olt_type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('olt_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" value="{{ old('model', $olt->model) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Telnet Port <span class="text-danger">*</span></label>
                        <input type="number" name="telnet_port" class="form-control" value="{{ old('telnet_port', $olt->telnet_port) }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">SSH Port <span class="text-danger">*</span></label>
                        <input type="number" name="ssh_port" class="form-control" value="{{ old('ssh_port', $olt->ssh_port) }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Ports <span class="text-danger">*</span></label>
                        <input type="number" name="total_ports" class="form-control" value="{{ old('total_ports', $olt->total_ports) }}" min="1" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $olt->username) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" value="{{ old('password', $olt->password) }}" required>
                    </div>
                </div>
            </div>

            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Location (Optional)</h5>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $olt->address) }}</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="0.00000001" name="latitude" class="form-control"
                        value="{{ old('latitude', $olt->latitude ?? '') }}"
                        placeholder="-8.67050000">
                    <small class="text-muted">Range: -90 to 90</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="0.00000001" name="longitude" class="form-control"
                        value="{{ old('longitude', $olt->longitude ?? '') }}"
                        placeholder="115.21260000">
                    <small class="text-muted">Range: -180 to 180</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $olt->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="custom-table mb-4">
                <h6 class="fw-bold mb-3">Status</h6>
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $olt->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Update OLT
                </button>
                <a href="{{ route('olts.show', $olt) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
