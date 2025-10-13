@extends('layouts.admin')

@section('title', 'Tambah Router')
@section('page-title', 'Tambah Router Baru')

@section('content')
<form action="{{ route('routers.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <!-- Router Information -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Informasi Router</h5>

                <div class="mb-3">
                    <label class="form-label">Nama Router <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Router Pusat 1" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                               value="{{ old('ip_address') }}" placeholder="192.168.1.1" required>
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">RouterOS Version <span class="text-danger">*</span></label>
                        <select name="ros_version" class="form-select @error('ros_version') is-invalid @enderror" required>
                            <option value="7" {{ old('ros_version', '7') == '7' ? 'selected' : '' }}>RouterOS 7</option>
                            <option value="6" {{ old('ros_version') == '6' ? 'selected' : '' }}>RouterOS 6</option>
                        </select>
                        @error('ros_version')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SSH Port</label>
                        <input type="number" name="ssh_port" class="form-control"
                               value="{{ old('ssh_port', 22) }}" min="1" max="65535">
                        <small class="text-muted">Default: 22</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">API Port</label>
                        <input type="number" name="api_port" class="form-control"
                               value="{{ old('api_port', 8728) }}" min="1" max="65535">
                        <small class="text-muted">Default: 8728</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', 'admin') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Informasi Lokasi</h5>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Alamat lokasi router...">{{ old('address') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control"
                               value="{{ old('latitude') }}" placeholder="-8.670458">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control"
                               value="{{ old('longitude') }}" placeholder="115.212629">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Coverage Radius (meter)</label>
                    <input type="number" name="coverage_radius" class="form-control"
                           value="{{ old('coverage_radius', 500) }}" min="1">
                    <small class="text-muted">Radius jangkauan sinyal router</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Status</h5>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                           value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Router Aktif
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Simpan Router
                </button>
                <a href="{{ route('routers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
