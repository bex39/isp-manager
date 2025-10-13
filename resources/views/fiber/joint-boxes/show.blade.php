@extends('layouts.admin')

@section('title', 'Joint Box: ' . $jointBox->name)
@section('page-title', 'Joint Box Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $jointBox->name }}</h4>
        <p class="text-muted mb-0">Code: {{ $jointBox->code }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('joint-boxes.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('joint-boxes.edit', $jointBox) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-primary mb-0">{{ $jointBox->capacity }}</h2>
                <p class="text-muted mb-0 small">Total Capacity</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-danger mb-0">{{ $jointBox->used_capacity }}</h2>
                <p class="text-muted mb-0 small">Used</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-success mb-0">{{ $jointBox->getAvailableCapacity() }}</h2>
                <p class="text-muted mb-0 small">Available</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-info mb-0">{{ $jointBox->splices->count() }}</h2>
                <p class="text-muted mb-0 small">Splices</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Joint Box Info -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0">Joint Box Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Code</strong></td>
                        <td><code>{{ $jointBox->code }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td>{{ $jointBox->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Type</strong></td>
                        <td>
                            @if($jointBox->type === 'closure')
                                <span class="badge bg-primary">Closure</span>
                            @elseif($jointBox->type === 'manhole')
                                <span class="badge bg-secondary">Manhole</span>
                            @elseif($jointBox->type === 'pole')
                                <span class="badge bg-info">Pole</span>
                            @else
                                <span class="badge bg-dark">Cabinet</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Address</strong></td>
                        <td>{{ $jointBox->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Coordinates</strong></td>
                        <td>
                            @if($jointBox->latitude && $jointBox->longitude)
                                {{ $jointBox->latitude }}, {{ $jointBox->longitude }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Capacity Usage</strong></td>
                        <td>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar
                                    @if($jointBox->getUsagePercentage() >= 80) bg-danger
                                    @elseif($jointBox->getUsagePercentage() >= 60) bg-warning
                                    @else bg-success
                                    @endif"
                                    style="width: {{ $jointBox->getUsagePercentage() }}%">
                                    {{ number_format($jointBox->getUsagePercentage(), 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <span class="badge {{ $jointBox->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $jointBox->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Splices List -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Splices</h6>
                <a href="#" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Splice
                </a>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @if($jointBox->splices->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($jointBox->splices as $splice)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong>{{ $splice->inputSegment->name }}</strong> Core #{{ $splice->input_core_number }}
                                    <br>
                                    <i class="bi bi-arrow-down text-muted"></i>
                                    <br>
                                    <strong>{{ $splice->outputSegment->name }}</strong> Core #{{ $splice->output_core_number }}
                                    <br>
                                    <small class="text-muted">
                                        Type: {{ $splice->splice_type }} | Loss: {{ $splice->splice_loss }} dB
                                    </small>
                                </div>
                                <span class="badge bg-info">{{ $splice->splice_date?->format('Y-m-d') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No splices configured</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($jointBox->notes)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold">Notes</h6>
        <p class="mb-0">{{ $jointBox->notes }}</p>
    </div>
</div>
@endif
@endsection
