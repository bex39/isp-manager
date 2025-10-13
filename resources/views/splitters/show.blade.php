@extends('layouts.admin')

@section('title', 'Splitter: ' . $splitter->name)
@section('page-title', 'Splitter Details')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold">{{ $splitter->name }}</h4>
        <p class="text-muted mb-0">Ratio: {{ $splitter->ratio }}</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('splitters.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('splitters.edit', $splitter) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-info mb-0">{{ $splitter->ratio }}</h2>
                <p class="text-muted mb-0 small">Split Ratio</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-primary mb-0">{{ $splitter->output_ports }}</h2>
                <p class="text-muted mb-0 small">Total Outputs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-success mb-0">{{ $splitter->getAvailableOutputs() }}</h2>
                <p class="text-muted mb-0 small">Available</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h2 class="text-danger mb-0">{{ $splitter->used_outputs }}</h2>
                <p class="text-muted mb-0 small">Used</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="fw-bold mb-0">Splitter Information</h6>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <td width="30%"><strong>Name</strong></td>
                <td>{{ $splitter->name }}</td>
            </tr>
            <tr>
                <td><strong>Connected to ODP</strong></td>
                <td>
                    <a href="{{ route('odps.show', $splitter->odp) }}">
                        {{ $splitter->odp->code }} - {{ $splitter->odp->name }}
                    </a>
                </td>
            </tr>
            <tr>
                <td><strong>Split Ratio</strong></td>
                <td><span class="badge bg-info">{{ $splitter->ratio }}</span></td>
            </tr>
            <tr>
                <td><strong>Input Ports</strong></td>
                <td>{{ $splitter->input_ports }}</td>
            </tr>
            <tr>
                <td><strong>Output Ports</strong></td>
                <td>{{ $splitter->output_ports }}</td>
            </tr>
            <tr>
                <td><strong>Notes</strong></td>
                <td>{{ $splitter->notes ?? '-' }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection
