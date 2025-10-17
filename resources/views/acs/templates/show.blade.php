@extends('layouts.admin')

@section('title', 'Template Details - ' . $template->name)

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('acs.templates.index') }}">Templates</a></li>
                <li class="breadcrumb-item active">{{ $template->name }}</li>
            </ol>
        </nav>
        <h5 class="fw-bold mb-1">{{ $template->name }}</h5>
        <p class="text-muted mb-0"><code>{{ $template->code }}</code></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('acs.templates.edit', $template) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('acs.templates.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total_applications'] }}</h4>
                <small class="text-muted">Total Applications</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-success">{{ $stats['successful'] }}</h4>
                <small class="text-muted">Successful</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-danger">{{ $stats['failed'] }}</h4>
                <small class="text-muted">Failed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $stats['devices_using'] }}</h4>
                <small class="text-muted">Devices Using</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Template Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Template Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="30%" class="text-muted">Name:</td>
                        <td><strong>{{ $template->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Code:</td>
                        <td><code>{{ $template->code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type:</td>
                        <td><span class="badge bg-info">{{ ucfirst($template->type) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                            @if($template->is_default)
                                <span class="badge bg-primary">Default</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Description:</td>
                        <td>{{ $template->description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created By:</td>
                        <td>{{ $template->creator ? $template->creator->name : 'System' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $template->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Updated:</td>
                        <td>{{ $template->updated_at->diffForHumans() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Parameters -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Parameters</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ json_encode($template->parameters, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Recent Applications</h6>
            </div>
            <div class="card-body">
                @if($recentApplications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>User</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentApplications as $app)
                                <tr>
                                    <td>
                                        <a href="{{ route('acs.show', $app->ont) }}">
                                            {{ $app->ont->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $app->ont->sn }}</small>
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $app->action)) }}</td>
                                    <td>
                                        @if($app->status === 'success')
                                            <span class="badge bg-success">Success</span>
                                        @elseif($app->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($app->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $app->executor ? $app->executor->name : 'System' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $app->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No applications yet</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('acs.templates.edit', $template) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Template
                    </a>

                    <form action="{{ route('acs.templates.duplicate', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-files"></i> Duplicate Template
                        </button>
                    </form>

                    @if(!$template->is_default)
                    <form action="{{ route('acs.templates.set-default', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-star"></i> Set as Default
                        </button>
                    </form>
                    @endif

                    <button class="btn btn-outline-primary" onclick="showApplyModal()">
                        <i class="bi bi-play"></i> Apply to Device
                    </button>
                </div>
            </div>
        </div>

        <!-- Usage Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Usage Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Success Rate:</td>
                        <td class="text-end">
                            <strong>
                                @php
                                    $rate = $stats['total_applications'] > 0
                                        ? round(($stats['successful'] / $stats['total_applications']) * 100, 1)
                                        : 0;
                                @endphp
                                {{ $rate }}%
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Applications:</td>
                        <td class="text-end"><strong>{{ $stats['total_applications'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Devices Using:</td>
                        <td class="text-end"><strong>{{ $stats['devices_using'] }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Apply Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Template to Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('acs.templates.apply', $template) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Device ID</label>
                        <input type="number" name="ont_id" class="form-control" required>
                        <small class="text-muted">Enter the ONT device ID</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showApplyModal() {
    const modal = new bootstrap.Modal(document.getElementById('applyModal'));
    modal.show();
}
</script>
@endpush
