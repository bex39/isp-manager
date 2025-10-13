@extends('layouts.admin')

@section('title', 'Edit Joint Box')
@section('page-title', 'Edit Joint Box')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('joint-boxes.update', $jointBox) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joint Box Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code', $jointBox->code) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joint Box Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $jointBox->name) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="closure" {{ old('type', $jointBox->type) == 'closure' ? 'selected' : '' }}>Closure</option>
                                <option value="manhole" {{ old('type', $jointBox->type) == 'manhole' ? 'selected' : '' }}>Manhole</option>
                                <option value="pole" {{ old('type', $jointBox->type) == 'pole' ? 'selected' : '' }}>Pole Mount</option>
                                <option value="cabinet" {{ old('type', $jointBox->type) == 'cabinet' ? 'selected' : '' }}>Cabinet</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Splice Capacity <span class="text-danger">*</span></label>
                            <select name="capacity" class="form-select" required>
                                <option value="24" {{ old('capacity', $jointBox->capacity) == 24 ? 'selected' : '' }}>24 Splices</option>
                                <option value="48" {{ old('capacity', $jointBox->capacity) == 48 ? 'selected' : '' }}>48 Splices</option>
                                <option value="96" {{ old('capacity', $jointBox->capacity) == 96 ? 'selected' : '' }}>96 Splices</option>
                                <option value="144" {{ old('capacity', $jointBox->capacity) == 144 ? 'selected' : '' }}>144 Splices</option>
                                <option value="288" {{ old('capacity', $jointBox->capacity) == 288 ? 'selected' : '' }}>288 Splices</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control"
                                   value="{{ old('latitude', $jointBox->latitude) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control"
                                   value="{{ old('longitude', $jointBox->longitude) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $jointBox->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $jointBox->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('joint-boxes.show', $jointBox) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
