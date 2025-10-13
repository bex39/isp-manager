@extends('layouts.admin')

@section('title', 'Edit Fiber Splice')
@section('page-title', 'Edit Fiber Splice')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Edit Fiber Splice Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('fiber-splices.update', $fiberSplice) }}" method="POST" id="spliceEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Joint Box Selection -->
                        <div class="col-md-6">
                            <label class="form-label">Joint Box <span class="text-danger">*</span></label>
                            <select name="joint_box_id" id="jointBoxSelect" class="form-select @error('joint_box_id') is-invalid @enderror" required>
                                <option value="">Select Joint Box</option>
                                @foreach($jointBoxes as $box)
                                    <option value="{{ $box->id }}" {{ old('joint_box_id', $fiberSplice->joint_box_id) == $box->id ? 'selected' : '' }}>
                                        {{ $box->name }} ({{ $box->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('joint_box_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Splice Type <span class="text-danger">*</span></label>
                            <select name="splice_type" class="form-select @error('splice_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="fusion" {{ old('splice_type', $fiberSplice->splice_type) == 'fusion' ? 'selected' : '' }}>Fusion Splice</option>
                                <option value="mechanical" {{ old('splice_type', $fiberSplice->splice_type) == 'mechanical' ? 'selected' : '' }}>Mechanical Splice</option>
                            </select>
                            @error('splice_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input Cable Segment -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Input Cable (Incoming)</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Input Cable Segment <span class="text-danger">*</span></label>
                            <select name="input_segment_id" id="inputSegmentSelect" class="form-select @error('input_segment_id') is-invalid @enderror" required onchange="loadCores('input')">
                                <option value="">Select Cable Segment</option>
                                @foreach($cableSegments as $segment)
                                    <option value="{{ $segment->id }}" data-cores="{{ $segment->core_count }}" {{ old('input_segment_id', $fiberSplice->input_segment_id) == $segment->id ? 'selected' : '' }}>
                                        {{ $segment->name }} ({{ $segment->core_count }} cores)
                                    </option>
                                @endforeach
                            </select>
                            @error('input_segment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input Core - ENHANCED VERSION -->
                        <div class="col-md-3">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <select name="input_core_number" id="inputCoreNumber" class="form-select @error('input_core_number') is-invalid @enderror" required>
                                <option value="">Select Core</option>
                                @foreach($inputCores as $core)
                                    <option value="{{ $core->core_number }}"
                                            data-color="{{ $core->core_color }}"
                                            {{ old('input_core_number', $fiberSplice->input_core_number) == $core->core_number ? 'selected' : '' }}>
                                        Core {{ $core->core_number }}
                                        @if($core->core_color)
                                            ({{ $core->core_color }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('input_core_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Core Color</label>
                            <input type="text" name="input_core_color" class="form-control @error('input_core_color') is-invalid @enderror"
                                   value="{{ old('input_core_color', $fiberSplice->input_core_color) }}" placeholder="e.g., Blue">
                            @error('input_core_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Output Cable Segment -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Output Cable (Outgoing)</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Output Cable Segment <span class="text-danger">*</span></label>
                            <select name="output_segment_id" id="outputSegmentSelect" class="form-select @error('output_segment_id') is-invalid @enderror" required onchange="loadCores('output')">
                                <option value="">Select Cable Segment</option>
                                @foreach($cableSegments as $segment)
                                    <option value="{{ $segment->id }}" data-cores="{{ $segment->core_count }}" {{ old('output_segment_id', $fiberSplice->output_segment_id) == $segment->id ? 'selected' : '' }}>
                                        {{ $segment->name }} ({{ $segment->core_count }} cores)
                                    </option>
                                @endforeach
                            </select>
                            @error('output_segment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Output Core - ENHANCED VERSION -->
                        <div class="col-md-3">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <select name="output_core_number" id="outputCoreNumber" class="form-select @error('output_core_number') is-invalid @enderror" required>
                                <option value="">Select Core</option>
                                @foreach($outputCores as $core)
                                    <option value="{{ $core->core_number }}"
                                            data-color="{{ $core->core_color }}"
                                            {{ old('output_core_number', $fiberSplice->output_core_number) == $core->core_number ? 'selected' : '' }}>
                                        Core {{ $core->core_number }}
                                        @if($core->core_color)
                                            ({{ $core->core_color }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('output_core_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Core Color</label>
                            <input type="text" name="output_core_color" class="form-control @error('output_core_color') is-invalid @enderror"
                                   value="{{ old('output_core_color', $fiberSplice->output_core_color) }}" placeholder="e.g., Blue">
                            @error('output_core_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Splice Details -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Splice Details</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Splice Loss (dB)</label>
                            <input type="number" step="0.01" name="splice_loss" class="form-control @error('splice_loss') is-invalid @enderror"
                                   value="{{ old('splice_loss', $fiberSplice->splice_loss) }}" placeholder="e.g., 0.05">
                            <small class="text-muted">Typical: 0.05-0.3 dB</small>
                            @error('splice_loss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Splice Date</label>
                            <input type="date" name="splice_date" class="form-control @error('splice_date') is-invalid @enderror"
                                   value="{{ old('splice_date', $fiberSplice->splice_date ? $fiberSplice->splice_date->format('Y-m-d') : '') }}">
                            @error('splice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Technician</label>
                            <input type="text" name="technician" class="form-control @error('technician') is-invalid @enderror"
                                   value="{{ old('technician', $fiberSplice->technician) }}" placeholder="Technician name">
                            @error('technician')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes">{{ old('notes', $fiberSplice->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Splice
                        </button>
                        <a href="{{ route('fiber-splices.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>

                <!-- Danger Zone -->
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger mb-1">Danger Zone</h6>
                        <small class="text-muted">Permanently delete this splice</small>
                    </div>
                    <form action="{{ route('fiber-splices.destroy', $fiberSplice) }}" method="POST" onsubmit="return confirm('⚠️ DELETE SPLICE?\n\nCore {{ $fiberSplice->input_core_number }} → {{ $fiberSplice->output_core_number }}\n\nThis action CANNOT be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete Splice
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Current Status</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="45%" class="text-muted">Joint Box:</td>
                        <td><strong>{{ $fiberSplice->jointBox->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Splice Type:</td>
                        <td>
                            <span class="badge bg-{{ $fiberSplice->splice_type === 'fusion' ? 'success' : 'info' }}">
                                {{ ucfirst($fiberSplice->splice_type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Core Mapping:</td>
                        <td>
                            <strong class="text-primary">
                                {{ $fiberSplice->input_core_number }} → {{ $fiberSplice->output_core_number }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Splice Loss:</td>
                        <td>
                            @if($fiberSplice->splice_loss)
                                <span class="badge bg-{{ $fiberSplice->splice_loss <= 0.1 ? 'success' : ($fiberSplice->splice_loss <= 0.3 ? 'warning' : 'danger') }}">
                                    {{ $fiberSplice->splice_loss }} dB
                                </span>
                            @else
                                <span class="text-muted">Not recorded</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Technician:</td>
                        <td>{{ $fiberSplice->technician ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date:</td>
                        <td>{{ $fiberSplice->splice_date ? $fiberSplice->splice_date->format('d M Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('fiber-splices.show', $fiberSplice) }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <a href="{{ route('joint-boxes.show', $fiberSplice->joint_box_id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-seam"></i> View Joint Box
                    </a>
                    <a href="{{ route('fiber-splices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb"></i> Edit Tips</h6>
                <ul class="small mb-0">
                    <li>Verify core numbers match physical connection</li>
                    <li>Update loss if re-tested</li>
                    <li>Keep technician records accurate</li>
                    <li>Document any maintenance changes</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function loadCores(type) {
        const selectId = type === 'input' ? 'inputSegmentSelect' : 'outputSegmentSelect';
        const coreInputId = type === 'input' ? 'inputCoreNumber' : 'outputCoreNumber';

        const select = document.getElementById(selectId);
        const coreInput = document.getElementById(coreInputId);
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption.value) {
            const coreCount = selectedOption.dataset.cores;
            coreInput.max = coreCount;
            coreInput.placeholder = `1-${coreCount}`;
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        loadCores('input');
        loadCores('output');
    });

    // Form validation
    document.getElementById('spliceEditForm').addEventListener('submit', function(e) {
        const inputSegment = document.getElementById('inputSegmentSelect').value;
        const outputSegment = document.getElementById('outputSegmentSelect').value;

        if (inputSegment === outputSegment) {
            e.preventDefault();
            alert('⚠️ Input and Output segments cannot be the same!\n\nPlease select different cable segments.');
            return false;
        }

        const inputCore = parseInt(document.getElementById('inputCoreNumber').value);
        const outputCore = parseInt(document.getElementById('outputCoreNumber').value);
        const inputMax = parseInt(document.getElementById('inputCoreNumber').max);
        const outputMax = parseInt(document.getElementById('outputCoreNumber').max);

        if (inputCore > inputMax) {
            e.preventDefault();
            alert(`⚠️ Input core number (${inputCore}) exceeds cable capacity (${inputMax})!`);
            return false;
        }

        if (outputCore > outputMax) {
            e.preventDefault();
            alert(`⚠️ Output core number (${outputCore}) exceeds cable capacity (${outputMax})!`);
            return false;
        }
    });
</script>
@endpush
