@extends('layouts.admin')

@section('title', 'Splitter Management')
@section('page-title', 'Fiber Splitters')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold">Splitter Management</h5>
        <p class="text-muted mb-0">Manage fiber optic splitters</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('splitters.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Splitter
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>ODP</th>
                        <th>Ratio</th>
                        <th>Input</th>
                        <th>Output</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($splitters as $splitter)
                        <tr>
                            <td><strong>{{ $splitter->name }}</strong></td>

                            <td>
                                @if ($splitter->odp)
                                    <a href="{{ route('odps.show', $splitter->odp->id) }}">
                                        {{ $splitter->odp->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td><span class="badge bg-info">{{ $splitter->ratio }}</span></td>
                            <td>{{ $splitter->input_ports }}</td>
                            <td>{{ $splitter->output_ports }}</td>
                            <td>
                                <span class="badge bg-success">{{ $splitter->getAvailableOutputs() }}</span>
                            </td>

                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('splitters.show', $splitter) }}" class="btn btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('splitters.edit', $splitter) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('splitters.destroy', $splitter) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('Delete this splitter?')"
                                            {{ $splitter->used_outputs > 0 ? 'disabled' : '' }}
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No splitters found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $splitters->links() }}
        </div>
    </div>
</div>
@endsection
