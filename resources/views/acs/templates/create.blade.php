@extends('layouts.admin')

@section('title', 'Create Template')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.templates.index') }}">Templates</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Create Configuration Template</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('acs.templates.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}" placeholder="e.g., wifi_basic, vlan_100" required>
                        <small class="text-muted">Unique identifier for this template</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror"
                                id="templateType" onchange="updateParameters()" required>
                            <option value="">Select Type</option>
                            <option value="wifi" {{ old('type') == 'wifi' ? 'selected' : '' }}>WiFi Configuration</option>
                            <option value="vlan" {{ old('type') == 'vlan' ? 'selected' : '' }}>VLAN Configuration</option>
                            <option value="port" {{ old('type') == 'port' ? 'selected' : '' }}>Port Configuration</option>
                            <option value="service_profile" {{ old('type') == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
                            <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parameters <span class="text-danger">*</span></label>
                        <div id="parametersContainer">
                            <textarea name="parameters" id="parametersJson"
                                      class="form-control @error('parameters') is-invalid @enderror"
                                      rows="10" required>{{ old('parameters', '{}') }}</textarea>
                        </div>
                        <small class="text-muted">JSON format. Click "Load Template" for examples.</small>
                        @error('parameters')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_default"
                                   id="is_default" {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as default template for this type
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active"
                                   id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Template
                        </button>
                        <a href="{{ route('acs.templates.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Parameter Templates</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Click to load parameter template:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="loadTemplate('wifi')">
                        WiFi Template
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="loadTemplate('vlan')">
                        VLAN Template
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="loadTemplate('port')">
                        Port Template
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="loadTemplate('service_profile')">
                        Service Profile
                    </button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Help</h6>
            </div>
            <div class="card-body">
                <p class="small"><strong>WiFi Template:</strong> Configure WiFi SSID and password</p>
                <p class="small"><strong>VLAN Template:</strong> Set VLAN ID and tagging</p>
                <p class="small"><strong>Port Template:</strong> Configure port settings</p>
                <p class="small"><strong>Service Profile:</strong> Define service parameters</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const templates = {
    wifi: {
        ssid: "MyWiFi",
        password: "password123",
        encryption: "WPA2-PSK",
        channel: "auto",
        bandwidth: "20MHz"
    },
    vlan: {
        vlan_id: 100,
        vlan_mode: "tagged",
        priority: 0
    },
    port: {
        port_number: 1,
        admin_status: "up",
        speed: "auto",
        duplex: "auto"
    },
    service_profile: {
        profile_name: "default",
        upstream_bandwidth: 100,
        downstream_bandwidth: 100,
        qos_priority: 0
    }
};

function loadTemplate(type) {
    const template = templates[type];
    if (template) {
        document.getElementById('parametersJson').value = JSON.stringify(template, null, 2);
        document.getElementById('templateType').value = type;
    }
}

function updateParameters() {
    const type = document.getElementById('templateType').value;
    if (type && templates[type]) {
        if (confirm('Load default parameters for this type?')) {
            loadTemplate(type);
        }
    }
}
</script>
@endpush
