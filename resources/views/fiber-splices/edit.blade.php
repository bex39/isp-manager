@extends('layouts.admin')

@section('title', 'Edit Fiber Splice')
@section('page-title', 'Edit Fiber Splice')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('fiber-splices.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Splices
        </a>
        <a href="{{ route('joint-boxes.show', $fiberSplice->jointBox) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box"></i> View Joint Box
        </a>
        <a href="{{ route('fiber-splices.show', $fiberSplice) }}" class="btn btn-outline-info btn-sm">
            <i class="bi bi-eye"></i> View Details
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Fiber Splice</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('fiber-splices.update', $fiberSplice) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Joint Box (Read-only display) -->
                    <div class="mb-3">
                        <label class="form-label">Joint Box</label>
                        <input type="text" class="form-control" value="{{ $fiberSplice->jointBox->name }}" disabled>
                        <small class="text-muted">Cannot change joint box after creation</small>
                    </div>

                    <!-- Connection Display (Read-only) -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">Current Splice Connection</h6>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="border border-primary rounded p-3 bg-white">
                                        <small class="text-muted d-block mb-1">INPUT</small>
                                        <strong>{{ $fiberSplice->inputSegment->name }}</strong>
                                        <div class="mt-2">
                                            <span class="badge bg-primary">Core #{{ $fiberSplice->input_core_number }}</span>
                                            @if($inputCore && $inputCore->core_color)
                                                <span class="badge bg-secondary">{{ $inputCore->core_color }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center d-flex align-items-center justify-content-center">
                                    <i class="bi bi-arrow-left-right fs-2 text-muted"></i>
                                </div>
                                <div class="col-md-5">
                                    <div class="border border-success rounded p-3 bg-white">
                                        <small class="text-muted d-block mb-1">OUTPUT</small>
                                        <strong>{{ $fiberSplice->outputSegment->name }}</strong>
                                        <div class="mt-2">
                                            <span class="badge bg-success">Core #{{ $fiberSplice->output_core_number }}</span>
                                            @if($outputCore && $outputCore->core_color)
                                                <span class="badge bg-secondary">{{ $outputCore->core_color }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    Cable connections cannot be changed. To change connections, delete this splice and create a new one.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Editable Fields -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splice Type <span class="text-danger">*</span></label>
                            <select name="splice_type" class="form-select @error('splice_type') is-invalid @enderror" required>
                                <option value="fusion" {{ old('splice_type', $fiberSplice->splice_type) == 'fusion' ? 'selected' : '' }}>
                                    Fusion Splice
                                </option>
                                <option value="mechanical" {{ old('splice_type', $fiberSplice->splice_type) == 'mechanical' ? 'selected' : '' }}>
                                    Mechanical Splice
                                </option>
                            </select>
                            @error('splice_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-lightbulb"></i>
                                Fusion: Permanent (≤0.1dB), Mechanical: Temporary (≤0.3dB)
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Splice Loss (dB)</label>
                            <input type="number" step="0.01" name="splice_loss"
                                   class="form-control @error('splice_loss') is-invalid @enderror"
                                   value="{{ old('splice_loss', $fiberSplice->splice_loss) }}"
                                   min="0" max="5">
                            @error('splice_loss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Standard: ≤0.1 dB (Fusion), ≤0.3 dB (Mechanical)</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Splice Date</label>
                            <input type="date" name="splice_date"
                                   class="form-control @error('splice_date') is-invalid @enderror"
                                   value="{{ old('splice_date', $fiberSplice->splice_date ? $fiberSplice->splice_date->format('Y-m-d') : '') }}">
                            @error('splice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Technician</label>
                            <input type="text" name="technician"
                                   class="form-control @error('technician') is-invalid @enderror"
                                   value="{{ old('technician', $fiberSplice->technician) }}"
                                   placeholder="Technician name">
                            @error('technician')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Additional notes about this splice...">{{ old('notes', $fiberSplice->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('fiber-splices.show', $fiberSplice) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Splice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Side Info -->
    <div class="col-md-4">
        <!-- Current Loss Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-{{ $fiberSplice->splice_loss && $fiberSplice->splice_loss <= 0.1 ? 'success' : ($fiberSplice->splice_loss && $fiberSplice->splice_loss <= 0.3 ? 'warning' : 'secondary') }} text-white">
                <h6 class="mb-0"><i class="bi bi-graph-down"></i> Current Loss</h6>
            </div>
            <div class="card-body text-center">
                @if($fiberSplice->splice_loss)
                    <h2 class="mb-0 text-{{ $fiberSplice->splice_loss <= 0.1 ? 'success' : ($fiberSplice->splice_loss <= 0.3 ? 'warning' : 'danger') }}">
                        {{ $fiberSplice->splice_loss }} dB
                    </h2>
                    <small class="text-muted">
                        @if($fiberSplice->splice_loss <= 0.1)
                            Excellent Quality
                        @elseif($fiberSplice->splice_loss <= 0.3)
                            Good Quality
                        @else
                            Poor Quality
                        @endif
                    </small>
                @else
                    <p class="text-muted mb-0">Not measured</p>
                @endif
            </div>
        </div>

        <!-- Loss Guidelines -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Loss Guidelines</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Fusion Splice:</span>
                        <span class="badge bg-success">≤ 0.1 dB</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Mechanical Splice:</span>
                        <span class="badge bg-warning">≤ 0.3 dB</span>
                    </div>
                </div>
                <hr>
                <small class="text-muted">
                    <i class="bi bi-lightbulb"></i>
                    Keep splice loss as low as possible for optimal signal quality.
                </small>
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
