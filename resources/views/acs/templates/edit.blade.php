@extends('layouts.admin')

@section('title', 'Edit Template')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.templates.index') }}">Templates</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acs.templates.show', $template) }}">{{ $template->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Edit Configuration Template</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('acs.templates.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $template->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $template->code) }}" required>
                        <small class="text-muted">Unique identifier for this template</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="wifi" {{ old('type', $template->type) == 'wifi' ? 'selected' : '' }}>WiFi Configuration</option>
                            <option value="vlan" {{ old('type', $template->type) == 'vlan' ? 'selected' : '' }}>VLAN Configuration</option>
                            <option value="port" {{ old('type', $template->type) == 'port' ? 'selected' : '' }}>Port Configuration</option>
                            <option value="service_profile" {{ old('type', $template->type) == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
                            <option value="custom" {{ old('type', $template->type) == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description', $template->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parameters <span class="text-danger">*</span></label>
                        <textarea name="parameters" id="parametersJson"
                                  class="form-control @error('parameters') is-invalid @enderror"
                                  rows="10" required>{{ old('parameters', json_encode($template->parameters, JSON_PRETTY_PRINT)) }}</textarea>
                        <small class="text-muted">JSON format</small>
                        @error('parameters')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_default"
                                   id="is_default" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as default template for this type
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active"
                                   id="is_active" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Template
                        </button>
                        <a href="{{ route('acs.templates.show', $template) }}" class="btn btn-secondary">
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
                <h6 class="mb-0 fw-bold">Template Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td><small>{{ $template->created_at->format('M d, Y H:i') }}</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td><small>{{ $template->updated_at->format('M d, Y H:i') }}</small></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created By:</td>
                        <td><small>{{ $template->creator ? $template->creator->name : 'System' }}</small></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Delete this template permanently</p>
                <form action="{{ route('acs.templates.destroy', $template) }}" method="POST"
                      onsubmit="return confirm('Are you sure? This cannot be undone!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Delete Template
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
