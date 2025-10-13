@extends('layouts.admin')

@section('title', 'Add Splitter')
@section('page-title', 'Add New Splitter')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('splitters.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">ODP <span class="text-danger">*</span></label>
                        <select name="odp_id" class="form-select @error('odp_id') is-invalid @enderror" required>
                            <option value="">-- Select ODP --</option>
                            @foreach($odps as $odp)
                                <option value="{{ $odp->id }}" {{ old('odp_id', request('odp_id')) == $odp->id ? 'selected' : '' }}>
                                    {{ $odp->code }} - {{ $odp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('odp_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Splitter Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required placeholder="e.g., Splitter-1">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Splitter Type</label>
                        <select name="type" class="form-select">
                            <option value="PLC">PLC Splitter</option>
                            <option value="FBT">FBT Splitter</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ratio <span class="text-danger">*</span></label>
                        <select name="ratio" id="ratioSelect" class="form-select @error('ratio') is-invalid @enderror" required>
                            <option value="">-- Select Ratio --</option>
                            <option value="1:2" {{ old('ratio') == '1:2' ? 'selected' : '' }}>1:2</option>
                            <option value="1:4" {{ old('ratio') == '1:4' ? 'selected' : '' }}>1:4</option>
                            <option value="1:8" {{ old('ratio') == '1:8' ? 'selected' : '' }}>1:8</option>
                            <option value="1:16" {{ old('ratio') == '1:16' ? 'selected' : '' }}>1:16</option>
                            <option value="1:32" {{ old('ratio') == '1:32' ? 'selected' : '' }}>1:32</option>
                            <option value="1:64" {{ old('ratio') == '1:64' ? 'selected' : '' }}>1:64</option>
                        </select>
                        @error('ratio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Input Ports <span class="text-danger">*</span></label>
                            <input type="number" name="input_ports" class="form-control" value="1" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Output Ports <span class="text-danger">*</span></label>
                            <input type="number" name="output_ports" id="outputPorts" class="form-control"
                                   value="{{ old('output_ports', 8) }}" required readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Splitter
                        </button>
                        <a href="{{ route('splitters.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('ratioSelect').addEventListener('change', function() {
    const ratio = this.value;
    const outputPorts = document.getElementById('outputPorts');

    const ratioMap = {
        '1:2': 2,
        '1:4': 4,
        '1:8': 8,
        '1:16': 16,
        '1:32': 32,
        '1:64': 64
    };

    outputPorts.value = ratioMap[ratio] || 8;
});
</script>
@endpush
@endsection
