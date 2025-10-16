@extends('layouts.admin')

@section('title', 'Network Topology')

@push('styles')
<link href="https://unpkg.com/vis-network/styles/vis-network.min.css" rel="stylesheet">
<style>
    #network-container {
        width: 100%;
        height: 700px;
        border: 1px solid #ddd;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 20px;
        margin-bottom: 10px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        margin-right: 8px;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-1">Network Topology</h5>
        <p class="text-muted mb-0">Interactive fiber network visualization</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary btn-sm" onclick="network.fit()">
            <i class="bi bi-arrows-fullscreen"></i> Fit View
        </button>
        <button class="btn btn-info btn-sm" onclick="refreshTopology()">
            <i class="bi bi-arrow-repeat"></i> Refresh
        </button>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h4 class="mb-0 text-primary">{{ $stats['olts'] }}</h4>
                <small class="text-muted">OLTs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h4 class="mb-0 text-success">{{ $stats['odps'] }}</h4>
                <small class="text-muted">ODPs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h4 class="mb-0 text-info">{{ $stats['onts'] }}</h4>
                <small class="text-muted">ONTs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h4 class="mb-0 text-warning">{{ $stats['cables'] }}</h4>
                <small class="text-muted">Cables</small>
            </div>
        </div>
    </div>
</div>

<!-- Network Diagram -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div id="network-container"></div>
    </div>
</div>

<!-- Legend -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0">Legend</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="small fw-bold">Equipment Types</h6>
                <div class="legend-item">
                    <div class="legend-color" style="background: #3b82f6;"></div>
                    <span class="small">OLT</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #10b981;"></div>
                    <span class="small">ODF</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f59e0b;"></div>
                    <span class="small">ODC</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f97316;"></div>
                    <span class="small">Joint Box</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #8b5cf6;"></div>
                    <span class="small">Splitter</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ec4899;"></div>
                    <span class="small">ODP</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #6b7280;"></div>
                    <span class="small">ONT</span>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="small fw-bold">Cable Types</h6>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ef4444; width: 40px; height: 4px;"></div>
                    <span class="small">Backbone</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f59e0b; width: 40px; height: 2px;"></div>
                    <span class="small">Distribution</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #22c55e; width: 40px; height: 1px;"></div>
                    <span class="small">Drop Cable</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
let network;

// Initialize network
document.addEventListener('DOMContentLoaded', function() {
    loadTopology();
});

async function loadTopology() {
    try {
        const response = await fetch('{{ route("network.topology.data") }}');
        const data = await response.json();

        const container = document.getElementById('network-container');

        const options = {
            nodes: {
                font: {
                    size: 14,
                    color: '#ffffff',
                },
                borderWidth: 2,
                borderWidthSelected: 4,
            },
            edges: {
                smooth: {
                    type: 'continuous',
                    roundness: 0.5
                },
                font: {
                    size: 10,
                    align: 'middle',
                    background: 'white',
                    strokeWidth: 0,
                },
            },
            physics: {
                enabled: true,
                barnesHut: {
                    gravitationalConstant: -2000,
                    springLength: 150,
                    springConstant: 0.04
                },
                stabilization: {
                    iterations: 1000
                }
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                zoomView: true,
                dragView: true,
            },
            layout: {
                hierarchical: {
                    enabled: false,
                }
            }
        };

        network = new vis.Network(container, data, options);

        // Node click event
        network.on('click', function(params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                handleNodeClick(nodeId);
            }
        });

        console.log('Network topology loaded:', data);

    } catch (error) {
        console.error('Error loading topology:', error);
        alert('Failed to load network topology');
    }
}

function handleNodeClick(nodeId) {
    const [type, id] = nodeId.split('-');

    const routeMap = {
        'olt': '{{ url("olts") }}/',
        'odf': '{{ url("odfs") }}/',
        'odc': '{{ url("odcs") }}/',
        'jointbox': '{{ url("joint-boxes") }}/',
        'splitter': '{{ url("splitters") }}/',
        'odp': '{{ url("odps") }}/',
        'ont': '{{ url("onts") }}/',
    };

    if (routeMap[type]) {
        window.location.href = routeMap[type] + id;
    }
}

function refreshTopology() {
    loadTopology();
}
</script>
@endpush
