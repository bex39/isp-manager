@extends('layouts.admin')

@section('title', 'Configuration Templates')

@section('content')
<!-- Header -->
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Configuration Templates</h5>
        <p class="text-muted mb-0">Manage device configuration templates</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('acs.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Devices
        </a>
        <a href="{{ route('acs.templates.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Create Template
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Templates</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-success">{{ $stats['wifi'] }}</h4>
                <small class="text-muted">WiFi Templates</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-info">{{ $stats['vlan'] }}</h4>
                <small class="text-muted">VLAN Templates</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-warning">{{ $stats['custom'] }}</h4>
                <small class="text-muted">Custom Templates</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('acs.templates.index') }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search templates..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="wifi" {{ request('type') == 'wifi' ? 'selected' : '' }}>WiFi</option>
                        <option value="vlan" {{ request('type') == 'vlan' ? 'selected' : '' }}>VLAN</option>
                        <option value="port" {{ request('type') == 'port' ? 'selected' : '' }}>Port</option>
                        <option value="service_profile" {{ request('type') == 'service_profile' ? 'selected' : '' }}>Service Profile</option>
                        <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Templates List -->
<div class="row">
    @forelse($templates as $template)
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">{{ $template->name }}</h6>
                    <div>
                        @if($template->is_default)
                            <span class="badge bg-primary">Default</span>
                        @endif
                        @if(!$template->is_active)
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>

                <p class="text-muted mb-2">
                    <small><code>{{ $template->code }}</code></small>
                </p>

                <span class="badge bg-info mb-2">{{ ucfirst($template->type) }}</span>

                @if($template->description)
                    <p class="text-muted small mb-3">{{ Str::limit($template->description, 80) }}</p>
                @endif

                <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                    <span>
                        <i class="bi bi-person"></i>
                        {{ $template->creator ? $template->creator->name : 'System' }}
                    </span>
                    <span>
                        <i class="bi bi-calendar"></i>
                        {{ $template->created_at->format('M d, Y') }}
                    </span>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('acs.templates.show', $template) }}"
                       class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('acs.templates.edit', $template) }}"
                           class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <button class="btn btn-outline-success"
                                onclick="applyTemplate({{ $template->id }})">
                            <i class="bi bi-play"></i> Apply
                        </button>
                        <form action="{{ route('acs.templates.duplicate', $template) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-files"></i> Duplicate
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-text" style="font-size: 4rem; color: #ccc;"></i>
                <h5 class="mt-3">No Templates Found</h5>
                <p class="text-muted">Create your first configuration template</p>
                <a href="{{ route('acs.templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Template
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($templates->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $templates->appends(request()->query())->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
function applyTemplate(templateId) {
    const deviceId = prompt('Enter Device ID to apply this template:');

    if (!deviceId) return;

    fetch(`/acs/templates/${templateId}/apply`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ont_id: deviceId })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? '✅ Template queued for application!' : '❌ Error: ' + data.message);
    });
}
</script>
@endpush
