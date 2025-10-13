@extends('layouts.admin')

@section('title', 'Edit ODP')
@section('page-title', 'Edit ODP')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('odps.update', $odp) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ODP Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $odp->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">ODP Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $odp->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Connected to OLT</label>
                        <select name="olt_id" class="form-select @error('olt_id') is-invalid @enderror">
                            <option value="">-- Select OLT --</option>
                            @foreach($olts as $olt)
                                <option value="{{ $olt->id }}" {{ old('olt_id', $odp->olt_id) == $olt->id ? 'selected' : '' }}>
                                    {{ $olt->name }} ({{ $olt->ip_address }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Ports <span class="text-danger">*</span></label>
                        <select name="total_ports" class="form-select" required>
                            <option value="8" {{ old('total_ports', $odp->total_ports) == 8 ? 'selected' : '' }}>8 Ports</option>
                            <option value="16" {{ old('total_ports', $odp->total_ports) == 16 ? 'selected' : '' }}>16 Ports</option>
                            <option value="24" {{ old('total_ports', $odp->total_ports) == 24 ? 'selected' : '' }}>24 Ports</option>
                            <option value="32" {{ old('total_ports', $odp->total_ports) == 32 ? 'selected' : '' }}>32 Ports</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $odp->latitude) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $odp->longitude) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $odp->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $odp->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('odps.show', $odp) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
