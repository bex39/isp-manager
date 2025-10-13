@extends('layouts.admin')

@section('title', 'Add Fiber Splice')
@section('page-title', 'Add New Fiber Splice')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">Fiber Splice Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('fiber-splices.store') }}" method="POST" id="spliceForm">
                    @csrf

                    <div class="row g-3">
                        <!-- Joint Box Selection -->
                        <div class="col-md-6">
                            <label class="form-label">Joint Box <span class="text-danger">*</span></label>
                            <select name="joint_box_id" id="jointBoxSelect" class="form-select @error('joint_box_id') is-invalid @enderror" required>
                                <option value="">Select Joint Box</option>
                                @foreach($jointBoxes as $box)
                                    <option value="{{ $box->id }}" {{ old('joint_box_id') == $box->id ? 'selected' : '' }}>
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
                                <option value="fusion" {{ old('splice_type') == 'fusion' ? 'selected' : '' }}>Fusion Splice</option>
                                <option value="mechanical" {{ old('splice_type') == 'mechanical' ? 'selected' : '' }}>Mechanical Splice</option>
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
                                    <option value="{{ $segment->id }}" data-cores="{{ $segment->core_count }}" {{ old('input_segment_id') == $segment->id ? 'selected' : '' }}>
                                        {{ $segment->name }} ({{ $segment->core_count }} cores)
                                    </option>
                                @endforeach
                            </select>
                            @error('input_segment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <input type="number" name="input_core_number" id="inputCoreNumber" class="form-control @error('input_core_number') is-invalid @enderror"
                                   value="{{ old('input_core_number') }}" required min="1" placeholder="e.g., 1">
                            @error('input_core_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Core Color</label>
                            <input type="text" name="input_core_color" class="form-control @error('input_core_color') is-invalid @enderror"
                                   value="{{ old('input_core_color') }}" placeholder="e.g., Blue">
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
                                    <option value="{{ $segment->id }}" data-cores="{{ $segment->core_count }}" {{ old('output_segment_id') == $segment->id ? 'selected' : '' }}>
                                        {{ $segment->name }} ({{ $segment->core_count }} cores)
                                    </option>
                                @endforeach
                            </select>
                            @error('output_segment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Core Number <span class="text-danger">*</span></label>
                            <input type="number" name="output_core_number" id="outputCoreNumber" class="form-control @error('output_core_number') is-invalid @enderror"
                                   value="{{ old('output_core_number') }}" required min="1" placeholder="e.g., 1">
                            @error('output_core_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Core Color</label>
                            <input type="text" name="output_core_color" class="form-control @error('output_core_color') is-invalid @enderror"
                                   value="{{ old('output_core_color') }}" placeholder="e.g., Blue">
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
                                   value="{{ old('splice_loss') }}" placeholder="e.g., 0.05">
                            <small class="text-muted">Typical: 0.05-0.3 dB</small>
                            @error('splice_loss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Splice Date</label>
                            <input type="date" name="splice_date" class="form-control @error('splice_date') is-invalid @enderror"
                                   value="{{ old('splice_date', date('Y-m-d')) }}">
                            @error('splice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Technician</label>
                            <input type="text" name="technician" class="form-control @error('technician') is-invalid @enderror"
                                   value="{{ old('technician') }}" placeholder="Technician name">
                            @error('technician')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Additional notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Splice
                        </button>
                        <a href="{{ route('fiber-splices.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> About Fiber Splice</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>Fiber Splice</strong> connects two fiber cores at a joint box, allowing signal to pass through multiple cable segments.
                </p>
                <hr>
                <p class="small mb-2"><strong>Splice Types:</strong></p>
                <ul class="small mb-2">
                    <li><strong>Fusion:</strong> Permanent, low loss (0.05-0.1 dB)</li>
                    <li><strong>Mechanical:</strong> Temporary, higher loss (0.2-0.3 dB)</li>
                </ul>
                <hr>
                <p class="small mb-0"><strong>Loss Standards:</strong></p>
                <ul class="small mb-0">
                    <li>Excellent: &lt; 0.1 dB</li>
                    <li>Good: 0.1 - 0.3 dB</li>
                    <li>Poor: &gt; 0.3 dB</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Core numbers must match fiber color codes</li>
                    <li>Record splice loss for network performance</li>
                    <li>Use fusion splice for permanent connections</li>
                    <li>Clean fiber ends before splicing</li>
                    <li>Document technician and date</li>
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

    // Form validation
    document.getElementById('spliceForm').addEventListener('submit', function(e) {
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
