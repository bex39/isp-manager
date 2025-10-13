@extends('layouts.admin')

@section('title', 'Splice Details')
@section('page-title', 'Fiber Splice Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <a href="{{ route('fiber-splices.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Splices
        </a>
        <a href="{{ route('joint-boxes.show', $fiberSplice->jointBox) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box"></i> View Joint Box
        </a>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('fiber-splices.edit', $fiberSplice) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('fiber-splices.destroy', $fiberSplice) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete this splice?')">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Splice Connection Diagram -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Splice Connection</h5>
            </div>
            <div class="card-body">
                <div class="splice-diagram">
                    <!-- Input Side -->
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="mb-2">INPUT</h6>
                                    <h5 class="mb-1">{{ $fiberSplice->inputSegment->name }}</h5>
                                    <small>{{ $fiberSplice->inputSegment->code }}</small>
                                    <hr class="my-2 border-light">
                                    <div class="d-flex justify-content-between">
                                        <span>Core:</span>
                                        <strong>#{{ $fiberSplice->input_core_number }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Color:</span>
                                        <strong>{{ $inputCore->core_color ?? '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Status:</span>
                                        <strong>{{ $inputCore->status ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Splice Point -->
                        <div class="col-md-2 text-center d-flex align-items-center justify-content-center">
                            <div>
                                <i class="bi bi-arrow-right" style="font-size: 2rem;"></i>
                                <div class="mt-2">
                                    <span class="badge bg-warning text-dark">SPLICE</span>
                                </div>
                            </div>
                        </div>

                        <!-- Output Side -->
                        <div class="col-md-5">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="mb-2">OUTPUT</h6>
                                    <h5 class="mb-1">{{ $fiberSplice->outputSegment->name }}</h5>
                                    <small>{{ $fiberSplice->outputSegment->code }}</small>
                                    <hr class="my-2 border-light">
                                    <div class="d-flex justify-content-between">
                                        <span>Core:</span>
                                        <strong>#{{ $fiberSplice->output_core_number }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Color:</span>
                                        <strong>{{ $outputCore->core_color ?? '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Status:</span>
                                        <strong>{{ $outputCore->status ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Joint Box Info -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-box"></i> Joint Box
                        </h6>
                        <strong>{{ $fiberSplice->jointBox->name }}</strong>
                        @if($fiberSplice->jointBox->location)
                            <br><small>{{ $fiberSplice->jointBox->location }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Splice Details -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Splice Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Splice Type</label>
                        <h6>
                            <span class="badge bg-{{ $fiberSplice->splice_type === 'fusion' ? 'success' : 'info' }} fs-6">
                                {{ ucfirst($fiberSplice->splice_type) }}
                            </span>
                        </h6>
                        <small class="text-muted">
                            {{ $fiberSplice->splice_type === 'fusion' ? 'Permanent fusion splice' : 'Temporary mechanical splice' }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Splice Loss</label>
                        <h6>
                            @if($fiberSplice->splice_loss)
                                <span class="badge bg-{{ $fiberSplice->splice_loss <= 0.1 ? 'success' : ($fiberSplice->splice_loss <= 0.3 ? 'warning' : 'danger') }} fs-6">
                                    {{ $fiberSplice->splice_loss }} dB
                                </span>
                            @else
                                <span class="text-muted">Not measured</span>
                            @endif
                        </h6>
                        <small class="text-muted">
                            @if($fiberSplice->splice_loss)
                                @if($fiberSplice->splice_loss <= 0.1)
                                    Excellent (≤ 0.1 dB)
                                @elseif($fiberSplice->splice_loss <= 0.3)
                                    Good (≤ 0.3 dB)
                                @else
                                    Poor (> 0.3 dB)
                                @endif
                            @endif
                        </small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Splice Date</label>
                        <p class="mb-0">{{ $fiberSplice->splice_date ? $fiberSplice->splice_date->format('d M Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Technician</label>
                        <p class="mb-0">{{ $fiberSplice->technician_name ?? $fiberSplice->technician ?? '-' }}</p>
                    </div>
                </div>

                @if($fiberSplice->notes)
                <div class="row">
                    <div class="col-md-12">
                        <label class="text-muted small">Notes</label>
                        <p class="text-muted">{{ $fiberSplice->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Loss Analysis -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-{{ $fiberSplice->splice_loss && $fiberSplice->splice_loss <= 0.1 ? 'success' : ($fiberSplice->splice_loss && $fiberSplice->splice_loss <= 0.3 ? 'warning' : 'danger') }} text-white">
                <h6 class="mb-0"><i class="bi bi-graph-down"></i> Loss Analysis</h6>
            </div>
            <div class="card-body">
                @if($fiberSplice->splice_loss)
                    <div class="text-center mb-3">
                        <h2 class="mb-0">{{ $fiberSplice->splice_loss }} dB</h2>
                        <small class="text-muted">Splice Loss</small>
                    </div>

                    <div class="progress mb-2" style="height: 25px;">
                        @php
                            $lossPercent = min(($fiberSplice->splice_loss / 0.5) * 100, 100);
                        @endphp
                        <div class="progress-bar bg-{{ $fiberSplice->splice_loss <= 0.1 ? 'success' : ($fiberSplice->splice_loss <= 0.3 ? 'warning' : 'danger') }}"
                             style="width: {{ $lossPercent }}%">
                            {{ number_format($lossPercent, 0) }}%
                        </div>
                    </div>

                    <small class="text-muted">
                        Standard: ≤ 0.1 dB (Fusion) | ≤ 0.3 dB (Mechanical)
                    </small>
                @else
                    <p class="text-muted text-center mb-0">No loss measurement recorded</p>
                @endif
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> History</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Created</small>
                    <p class="mb-0 small">{{ $fiberSplice->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 small">{{ $fiberSplice->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
