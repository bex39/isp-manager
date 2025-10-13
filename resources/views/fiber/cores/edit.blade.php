@extends('layouts.admin')

@section('title', 'Edit Fiber Core')
@section('page-title', 'Edit Fiber Core')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('cores.update', $core) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Cable Segment <span class="text-danger">*</span></label>
                        <select name="cable_segment_id" class="form-select" required>
                            @foreach($cableSegments as $segment)
                                <option value="{{ $segment->id }}" {{ old('cable_segment_id', $core->cable_segment_id) == $segment->id ? 'selected' : '' }}>
                                    {{ $segment->name }} ({{ $segment->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <input type="number" name="core_number" class="form-control"
                                   value="{{ old('core_number', $core->core_number) }}" required min="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tube Number</label>
                            <input type="number" name="tube_number" class="form-control"
                                   value="{{ old('tube_number', $core->tube_number) }}" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Core Color</label>
                        <select name="core_color" class="form-select">
                            <option value="">-- Select Color --</option>
                            @foreach(\App\Models\FiberCore::getCoreColors() as $color)
                                <option value="{{ $color }}" {{ old('core_color', $core->core_color) == $color ? 'selected' : '' }}>
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="available" {{ old('status', $core->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="used" {{ old('status', $core->status) == 'used' ? 'selected' : '' }}>Used</option>
                            <option value="reserved" {{ old('status', $core->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="damaged" {{ old('status', $core->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <strong>Connection Info:</strong>
                        @if($core->connected_to_type)
                            Connected to {{ class_basename($core->connected_to_type) }} #{{ $core->connected_to_id }}
                        @else
                            Not connected
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loss (dB)</label>
                            <input type="number" name="loss_db" class="form-control"
                                   value="{{ old('loss_db', $core->loss_db) }}" step="0.01">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Length (km)</label>
                            <input type="number" name="length_km" class="form-control"
                                   value="{{ old('length_km', $core->length_km) }}" step="0.001">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $core->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('cores.show', $core) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
