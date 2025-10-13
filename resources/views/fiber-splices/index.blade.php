@extends('layouts.admin')

@section('title', 'Fiber Splices')
@section('page-title', 'Fiber Splice Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Fiber Splice Management</h5>
        <p class="text-muted mb-0">Manage all fiber splices across joint boxes</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('fiber-splices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Splice
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Splices</p>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-bezier2 text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Fusion Splices</p>
                        <h4 class="mb-0 text-success">{{ $stats['fusion'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-lightning text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Mechanical</p>
                        <h4 class="mb-0 text-info">{{ $stats['mechanical'] }}</h4>
                    </div>
                    <div>
                        <i class="bi bi-gear text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Avg Loss</p>
                        <h4 class="mb-0 text-warning">{{ $stats['avg_loss'] }} dB</h4>
                    </div>
                    <div>
                        <i class="bi bi-graph-down text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form action="{{ route('fiber-splices.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="joint_box_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Joint Boxes</option>
                    @foreach($jointBoxes as $jb)
                        <option value="{{ $jb->id }}" {{ request('joint_box_id') == $jb->id ? 'selected' : '' }}>
                            {{ $jb->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="splice_type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="fusion" {{ request('splice_type') == 'fusion' ? 'selected' : '' }}>Fusion</option>
                    <option value="mechanical" {{ request('splice_type') == 'mechanical' ? 'selected' : '' }}>Mechanical</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="technician" class="form-select" onchange="this.form.submit()">
                    <option value="">All Technicians</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech }}" {{ request('technician') == $tech ? 'selected' : '' }}>
                            {{ $tech }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Splices Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Joint Box</th>
                        <th>Input Segment</th>
                        <th>Core</th>
                        <th></th>
                        <th>Output Segment</th>
                        <th>Core</th>
                        <th>Type</th>
                        <th>Loss</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($splices as $splice)
                    <tr>
                        <td>
                            <a href="{{ route('joint-boxes.show', $splice->jointBox) }}" class="text-decoration-none">
                                <strong>{{ $splice->jointBox->name }}</strong>
                            </a>
                        </td>
                        <td>
                            @if($splice->inputSegment)
                                <a href="{{ route('cable-segments.show', $splice->inputSegment) }}" class="text-decoration-none">
                                    {{ $splice->inputSegment->name }}
                                </a>
                                <br><small class="text-muted">{{ $splice->inputSegment->code }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $splice->input_core_number }}</span>
                        </td>
                        <td class="text-center">
                            <i class="bi bi-arrow-right text-muted"></i>
                        </td>
                        <td>
                            @if($splice->outputSegment)
                                <a href="{{ route('cable-segments.show', $splice->outputSegment) }}" class="text-decoration-none">
                                    {{ $splice->outputSegment->name }}
                                </a>
                                <br><small class="text-muted">{{ $splice->outputSegment->code }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $splice->output_core_number }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $splice->splice_type === 'fusion' ? 'success' : 'info' }}">
                                {{ ucfirst($splice->splice_type) }}
                            </span>
                        </td>
                        <td>
                            @if($splice->splice_loss)
                                <span class="badge bg-{{ $splice->splice_loss <= 0.1 ? 'success' : ($splice->splice_loss <= 0.3 ? 'warning' : 'danger') }}">
                                    {{ $splice->splice_loss }} dB
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $splice->splice_date ? $splice->splice_date->format('d M Y') : '-' }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('fiber-splices.show', $splice) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('fiber-splices.edit', $splice) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('fiber-splices.destroy', $splice) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Delete this splice?')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-bezier2" style="font-size: 3rem;"></i>
                            <p class="mt-2">No fiber splices found.</p>
                            <a href="{{ route('fiber-splices.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add First Splice
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Showing {{ $splices->firstItem() ?? 0 }} to {{ $splices->lastItem() ?? 0 }} of {{ $splices->total() }} splices
                </small>
            </div>
            <div>
                {{ $splices->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
