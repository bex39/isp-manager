@extends('layouts.admin')

@section('title', 'Add Joint Box')
@section('page-title', 'Add Joint Box')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('joint-boxes.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joint Box Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" required placeholder="JB-001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joint Box Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="JB Denpasar 1">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="closure" {{ old('type') == 'closure' ? 'selected' : '' }}>Closure (Inline)</option>
                                <option value="manhole" {{ old('type') == 'manhole' ? 'selected' : '' }}>Manhole (Underground)</option>
                                <option value="pole" {{ old('type') == 'pole' ? 'selected' : '' }}>Pole Mount (Aerial)</option>
                                <option value="cabinet" {{ old('type') == 'cabinet' ? 'selected' : '' }}>Cabinet</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Splice Capacity <span class="text-danger">*</span></label>
                            <select name="capacity" class="form-select @error('capacity') is-invalid @enderror" required>
                                <option value="24" {{ old('capacity') == 24 ? 'selected' : '' }}>24 Splices</option>
                                <option value="48" {{ old('capacity') == 48 ? 'selected' : '' }}>48 Splices</option>
                                <option value="96" {{ old('capacity') == 96 ? 'selected' : '' }}>96 Splices</option>
                                <option value="144" {{ old('capacity') == 144 ? 'selected' : '' }}>144 Splices</option>
                                <option value="288" {{ old('capacity') == 288 ? 'selected' : '' }}>288 Splices</option>
                            </select>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude') }}" placeholder="-8.6705">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude') }}" placeholder="115.2126">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Joint Box
                        </button>
                        <a href="{{ route('joint-boxes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
