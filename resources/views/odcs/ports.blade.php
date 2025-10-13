@extends('layouts.admin')

@section('title', 'ODC Ports: ' . $odc->name)
@section('page-title', 'ODC Port Map')

@push('styles')
<style>
    /* Dynamic grid based on ports per row */
    .port-grid {
        display: grid;
        gap: 8px;
        margin-bottom: 2rem;
    }

    /* Default: 12 ports per row (Desktop) */
    .port-grid.cols-12 { grid-template-columns: repeat(12, 1fr); }
    .port-grid.cols-8 { grid-template-columns: repeat(8, 1fr); }
    .port-grid.cols-6 { grid-template-columns: repeat(6, 1fr); }
    .port-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }

    /* Responsive breakpoints */
    @media (max-width: 1400px) {
        .port-grid.cols-12 { grid-template-columns: repeat(10, 1fr); }
        .port-grid.cols-8 { grid-template-columns: repeat(8, 1fr); }
    }

    @media (max-width: 1200px) {
        .port-grid.cols-12 { grid-template-columns: repeat(8, 1fr); }
        .port-grid.cols-8 { grid-template-columns: repeat(6, 1fr); }
    }

    @media (max-width: 992px) {
        .port-grid.cols-12 { grid-template-columns: repeat(6, 1fr); }
        .port-grid.cols-8 { grid-template-columns: repeat(6, 1fr); }
        .port-grid.cols-6 { grid-template-columns: repeat(4, 1fr); }
    }

    @media (max-width: 768px) {
        .port-grid { grid-template-columns: repeat(4, 1fr) !important; }
    }

    @media (max-width: 576px) {
        .port-grid { grid-template-columns: repeat(3, 1fr) !important; }
    }

    /* Port item styling */
    .port-item {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 6px;
        text-align: center;
        position: relative;
        background: #fff;
    }

    .port-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        z-index: 10;
    }

    /* Port status colors */
    .port-item.available {
        background: #d1e7dd;
        border-color: #0f5132;
    }

    .port-item.used {
        background: #fff3cd;
        border-color: #997404;
    }

    .port-item.reserved {
        background: #cfe2ff;
        border-color: #084298;
    }

    .port-item.damaged {
        background: #f8d7da;
        border-color: #842029;
    }

    .port-number {
        font-size: 0.875rem;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .port-status-icon {
        font-size: 1.1rem;
    }

    /* Legend styling */
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 1rem;
        margin-bottom: 0.5rem;
    }

    .legend-box {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 0.5rem;
        border: 2px solid;
    }

    /* Row dividers */
    .port-row-divider {
        grid-column: 1 / -1;
        height: 1px;
        background: #dee2e6;
        margin: 8px 0;
    }

    /* Layout selector */
    .layout-selector {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .layout-btn {
        padding: 6px 12px;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .layout-btn.active {
        background: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    .layout-btn:hover:not(.active) {
        border-color: #0d6efd;
        color: #0d6efd;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h4 class="fw-bold">{{ $odc->name }} - Port Map</h4>
        <p class="text-muted mb-0">
            <code>{{ $odc->code }}</code> |
            Total: {{ $odc->total_ports }} ports |
            Used: <span class="badge bg-warning">{{ $odc->used_ports }}</span> |
            Available: <span class="badge bg-success">{{ $odc->getAvailablePorts() }}</span>
        </p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('odcs.show', $odc) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('odcs.edit', $odc) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<!-- Port Usage Summary -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="mb-2">
                    <strong>Port Utilization:</strong> {{ $odc->getUsagePercentage() }}%
                </div>
                <div class="progress" style="height: 30px;">
                    <div class="progress-bar {{ $odc->getUsagePercentage() >= 80 ? 'bg-danger' : ($odc->getUsagePercentage() >= 60 ? 'bg-warning' : 'bg-success') }}"
                         style="width: {{ $odc->getUsagePercentage() }}%">
                        {{ $odc->used_ports }}/{{ $odc->total_ports }} ports used
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <div class="legend-item">
                        <div class="legend-box" style="background: #d1e7dd; border-color: #0f5132;"></div>
                        <small><strong>Available</strong></small>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: #fff3cd; border-color: #997404;"></div>
                        <small><strong>Used</strong></small>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: #cfe2ff; border-color: #084298;"></div>
                        <small><strong>Reserved</strong></small>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: #f8d7da; border-color: #842029;"></div>
                        <small><strong>Damaged</strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Layout Selector & Port Grid -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-grid-3x3"></i> Visual Port Map ({{ $odc->total_ports }} Ports)
                </h6>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                <div class="layout-selector d-inline-flex">
                    <small class="text-muted me-2">Ports per row:</small>
                    <button class="layout-btn active" onclick="changeLayout(12)" data-layout="12">
                        12
                    </button>
                    <button class="layout-btn" onclick="changeLayout(8)" data-layout="8">
                        8
                    </button>
                    <button class="layout-btn" onclick="changeLayout(6)" data-layout="6">
                        6
                    </button>
                    <button class="layout-btn" onclick="changeLayout(4)" data-layout="4">
                        4
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="port-grid cols-12" id="portGrid">
            @foreach($ports as $index => $port)
                @if($index > 0 && $index % 12 == 0)
                    <div class="port-row-divider"></div>
                @endif

                <div class="port-item {{ $port['status'] }}"
                     data-port="{{ $port['number'] }}"
                     data-bs-toggle="tooltip"
                     data-bs-placement="top"
                     data-bs-html="true"
                     title="<div style='text-align:left;'>
                            <strong>Port {{ $port['number'] }}</strong><br>
                            <span class='badge bg-{{ $port['status'] === 'used' ? 'warning' : ($port['status'] === 'reserved' ? 'primary' : ($port['status'] === 'damaged' ? 'danger' : 'success')) }}'>{{ ucfirst($port['status']) }}</span><br>
                            @if($port['splitter'])
                                <hr style='margin:4px 0;'>
                                <small>Splitter: {{ $port['splitter']['name'] }}</small><br>
                                <small>Ratio: {{ $port['splitter']['ratio'] }}</small>
                            @endif
                            @if($port['cable'])
                                <hr style='margin:4px 0;'>
                                <small>Cable: {{ $port['cable']['name'] }}</small><br>
                                @if($port['cable']['end_point'])
                                    <small>To: {{ $port['cable']['end_point']['name'] }}</small>
                                @endif
                            @endif
                            </div>">
                    <div class="port-number">{{ $port['number'] }}</div>
                    <div class="port-status-icon">
                        @if($port['status'] === 'used')
                            <i class="bi bi-plug-fill text-warning"></i>
                        @elseif($port['status'] === 'reserved')
                            <i class="bi bi-lock-fill text-primary"></i>
                        @elseif($port['status'] === 'damaged')
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        @else
                            <i class="bi bi-circle text-success"></i>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="alert alert-info mb-0 mt-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle me-2"></i>
                <small>
                    <strong>Tip:</strong> Hover over any port to see connection details.
                    Current layout: <strong><span id="currentLayout">12</span> ports per row</strong>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Port Connection Details Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Port Connection Details</h6>
            <span class="badge bg-info">{{ collect($ports)->where('status', 'used')->count() }} connected ports</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="8%">Port #</th>
                        <th width="10%">Status</th>
                        <th width="20%">Connected To</th>
                        <th width="15%">Type</th>
                        <th width="20%">Cable/Equipment</th>
                        <th width="15%">Details</th>
                        <th width="12%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(collect($ports)->where('status', 'used') as $port)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $port['number'] }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-warning">Used</span>
                            </td>
                            <td>
                                @if($port['splitter'])
                                    <a href="{{ route('splitters.show', $port['splitter']['id']) }}" class="text-decoration-none">
                                        <strong>{{ $port['splitter']['name'] }}</strong>
                                    </a>
                                @elseif($port['cable'])
                                    <strong>Cable Connection</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($port['splitter'])
                                    <span class="badge bg-secondary">Splitter</span>
                                    <br><small class="text-muted">{{ $port['splitter']['ratio'] }}</small>
                                @elseif($port['cable'])
                                    <span class="badge bg-info">Cable</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($port['cable'])
                                    <a href="{{ route('cable-segments.show', $port['cable']['id']) }}" class="text-decoration-none">
                                        {{ $port['cable']['name'] }}
                                    </a>
                                    <br><small class="text-muted">{{ $port['cable']['code'] }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($port['cable'] && $port['cable']['end_point'])
                                    <span class="badge bg-dark">{{ strtoupper($port['cable']['end_point']['type']) }}</span>
                                    <br><small>{{ $port['cable']['end_point']['name'] }}</small>
                                @elseif($port['splitter'])
                                    <span class="badge bg-success">{{ $port['splitter']['output_ports'] }} outputs</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($port['splitter'])
                                    <a href="{{ route('splitters.show', $port['splitter']['id']) }}"
                                       class="btn btn-outline-primary btn-sm" title="View Splitter">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @elseif($port['cable'])
                                    <a href="{{ route('cable-segments.show', $port['cable']['id']) }}"
                                       class="btn btn-outline-info btn-sm" title="View Cable">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                <p class="mt-2 mb-0">No ports are currently in use</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Available Ports Summary -->
@if(collect($ports)->where('status', 'available')->count() > 0)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-check-circle"></i> Available Ports
            <span class="badge bg-success">{{ collect($ports)->where('status', 'available')->count() }}</span>
        </h6>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @foreach(collect($ports)->where('status', 'available')->take(60) as $port)
                <span class="badge bg-success">{{ $port['number'] }}</span>
            @endforeach
            @if(collect($ports)->where('status', 'available')->count() > 60)
                <span class="badge bg-secondary">+ {{ collect($ports)->where('status', 'available')->count() - 60 }} more</span>
            @endif
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    let currentLayoutCols = 12;

    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        initializeTooltips();

        // Load saved layout preference
        const savedLayout = localStorage.getItem('odc_ports_layout');
        if (savedLayout) {
            changeLayout(parseInt(savedLayout));
        }
    });

    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                trigger: 'hover'
            });
        });
    }

    function changeLayout(cols) {
        const portGrid = document.getElementById('portGrid');
        const currentLayoutSpan = document.getElementById('currentLayout');

        // Remove all column classes
        portGrid.classList.remove('cols-12', 'cols-8', 'cols-6', 'cols-4');

        // Add selected column class
        portGrid.classList.add('cols-' + cols);

        // Update active button
        document.querySelectorAll('.layout-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector('[data-layout="' + cols + '"]').classList.add('active');

        // Update display text
        currentLayoutSpan.textContent = cols;
        currentLayoutCols = cols;

        // Save preference
        localStorage.setItem('odc_ports_layout', cols);

        // Update row dividers
        updateRowDividers(cols);
    }

    function updateRowDividers(cols) {
        // Remove existing dividers
        document.querySelectorAll('.port-row-divider').forEach(div => div.remove());

        // Add new dividers
        const portItems = document.querySelectorAll('.port-item');
        portItems.forEach((item, index) => {
            if (index > 0 && index % cols === 0) {
                const divider = document.createElement('div');
                divider.className = 'port-row-divider';
                item.parentNode.insertBefore(divider, item);
            }
        });
    }

    // Port click event (optional)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.port-item').forEach(function(portItem) {
            portItem.addEventListener('click', function() {
                const portNumber = this.dataset.port;
                const portStatus = this.classList.contains('used') ? 'Used' :
                                 this.classList.contains('reserved') ? 'Reserved' :
                                 this.classList.contains('damaged') ? 'Damaged' : 'Available';

                console.log('Port ' + portNumber + ' clicked - Status: ' + portStatus);
            });
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === '1') changeLayout(12);
        if (e.key === '2') changeLayout(8);
        if (e.key === '3') changeLayout(6);
        if (e.key === '4') changeLayout(4);
    });
</script>
@endpush
