@extends('layouts.admin')

@section('title', 'Fiber Core #' . $core->core_number)
@section('page-title', 'Fiber Core Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">Core #{{ $core->core_number }} - {{ $core->cableSegment->name }}</h4>
        <p class="text-muted mb-0">Cable: {{ $core->cableSegment->code }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('cores.index', ['cable_segment_id' => $core->cable_segment_id]) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('cores.edit', $core) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Core Info -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Core Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Cable Segment</strong></td>
                        <td>
                            <a href="{{ route('cable-segments.show', $core->cableSegment) }}">
                                {{ $core->cableSegment->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Core Number</strong></td>
                        <td><span class="badge bg-primary">#{{ $core->core_number }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Core Color</strong></td>
                        <td>
                            <span class="badge" style="background-color: {{ strtolower($core->core_color) }}; color: white;">
                                {{ $core->core_color }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tube Number</strong></td>
                        <td>{{ $core->tube_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            @if($core->status === 'available')
                                <span class="badge bg-success">Available</span>
                            @elseif($core->status === 'used')
                                <span class="badge bg-primary">Used</span>
                            @elseif($core->status === 'reserved')
                                <span class="badge bg-warning">Reserved</span>
                            @else
                                <span class="badge bg-danger">Damaged</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Loss (dB)</strong></td>
                        <td>{{ $core->loss_db ? number_format($core->loss_db, 2) . ' dB' : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Length (km)</strong></td>
                        <td>{{ $core->length_km ? number_format($core->length_km, 3) . ' km' : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Connection Info</h6>
            </div>
            <div class="card-body">
                @if($core->connected_to_type)
                    <div class="alert alert-info">
                        <strong>Connected To:</strong><br>
                        {{ class_basename($core->connected_to_type) }} #{{ $core->connected_to_id }}
                    </div>

                    <form action="{{ route('cores.release', $core) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Release this core?')">
                            <i class="bi bi-plug"></i> Release Core
                        </button>
                    </form>
                @else
                    <p class="text-muted">This core is not connected to any device.</p>
                    <hr>
                    <h6>Assign Core</h6>
                    <form action="{{ route('cores.assign', $core) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Device Type</label>
                            <select name="connected_to_type" class="form-select" required>
                                <option value="">-- Select Device Type --</option>
                                <option value="App\Models\Splitter">Splitter</option>
                                <option value="App\Models\ONT">ONT</option>
                                <option value="App\Models\ODP">ODP</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Device ID</label>
                            <input type="number" name="connected_to_id" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-link"></i> Assign Core
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if($core->notes)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold">Notes</h6>
        <p class="mb-0">{{ $core->notes }}</p>
    </div>
</div>
@endif
@endsection
