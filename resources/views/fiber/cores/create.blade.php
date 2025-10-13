@extends('layouts.admin')

@section('title', 'Add Fiber Core')
@section('page-title', 'Add Fiber Core')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('cores.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Cable Segment <span class="text-danger">*</span></label>
                        <select name="cable_segment_id" class="form-select @error('cable_segment_id') is-invalid @enderror" required>
                            <option value="">-- Select Cable Segment --</option>
                            @foreach($cableSegments as $segment)
                                <option value="{{ $segment->id }}"
                                    {{ old('cable_segment_id', $selectedSegment?->id) == $segment->id ? 'selected' : '' }}>
                                    {{ $segment->name }} ({{ $segment->code }}) - {{ $segment->core_count }} cores
                                </option>
                            @endforeach
                        </select>
                        @error('cable_segment_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <input type="number" name="core_number" class="form-control @error('core_number') is-invalid @enderror"
                                   value="{{ old('core_number') }}" required min="1" placeholder="1">
                            @error('core_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tube Number</label>
                            <input type="number" name="tube_number" class="form-control @error('tube_number') is-invalid @enderror"
                                   value="{{ old('tube_number') }}" min="1" placeholder="1">
                            @error('tube_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Core Color</label>
                        <select name="core_color" class="form-select @error('core_color') is-invalid @enderror">
                            <option value="">-- Select Color --</option>
                            @foreach(\App\Models\FiberCore::getCoreColors() as $color)
                                <option value="{{ $color }}" {{ old('core_color') == $color ? 'selected' : '' }}>
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                        @error('core_color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="used" {{ old('status') == 'used' ? 'selected' : '' }}>Used</option>
                            <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="damaged" {{ old('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loss (dB)</label>
                            <input type="number" name="loss_db" class="form-control @error('loss_db') is-invalid @enderror"
                                   value="{{ old('loss_db') }}" step="0.01" placeholder="0.25">
                            @error('loss_db')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Length (km)</label>
                            <input type="number" name="length_km" class="form-control @error('length_km') is-invalid @enderror"
                                   value="{{ old('length_km') }}" step="0.001" placeholder="2.500">
                            @error('length_km')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Core
                        </button>
                        <a href="{{ route('cores.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
