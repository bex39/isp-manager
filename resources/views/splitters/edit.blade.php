@extends('layouts.admin')

@section('title', 'Edit Splitter')
@section('page-title', 'Edit Splitter')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('splitters.update', $splitter) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">ODP <span class="text-danger">*</span></label>
                        <select name="odp_id" class="form-select" required>
                            @foreach($odps as $odp)
                                <option value="{{ $odp->id }}" {{ old('odp_id', $splitter->odp_id) == $odp->id ? 'selected' : '' }}>
                                    {{ $odp->code }} - {{ $odp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Splitter Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $splitter->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ratio <span class="text-danger">*</span></label>
                        <select name="ratio" class="form-select" required>
                            <option value="1:2" {{ old('ratio', $splitter->ratio) == '1:2' ? 'selected' : '' }}>1:2</option>
                            <option value="1:4" {{ old('ratio', $splitter->ratio) == '1:4' ? 'selected' : '' }}>1:4</option>
                            <option value="1:8" {{ old('ratio', $splitter->ratio) == '1:8' ? 'selected' : '' }}>1:8</option>
                            <option value="1:16" {{ old('ratio', $splitter->ratio) == '1:16' ? 'selected' : '' }}>1:16</option>
                            <option value="1:32" {{ old('ratio', $splitter->ratio) == '1:32' ? 'selected' : '' }}>1:32</option>
                            <option value="1:64" {{ old('ratio', $splitter->ratio) == '1:64' ? 'selected' : '' }}>1:64</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Output Ports <span class="text-danger">*</span></label>
                        <input type="number" name="output_ports" class="form-control"
                               value="{{ old('output_ports', $splitter->output_ports) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $splitter->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('splitters.show', $splitter) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
