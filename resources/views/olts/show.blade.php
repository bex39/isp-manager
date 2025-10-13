@extends('layouts.admin')

@section('title', 'OLT Detail')
@section('page-title', 'OLT Detail')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- OLT Information -->
        <div class="custom-table mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">{{ $olt->name }}</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="{{ $oltStatus['online'] ? 'badge bg-success' : 'badge bg-danger' }}">
                            {{ $oltStatus['online'] ? 'Online' : 'Offline' }}
                        </span>
                        <small class="text-muted">{{ $olt->ip_address }}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @can('edit_olt')
                    <a href="{{ route('olts.edit', $olt) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @endcan
                    <a href="{{ route('olts.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            @if($oltStatus['online'])
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <strong>OLT Online</strong>
                </div>
            @else
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <strong>OLT Offline</strong><br>
                    <small>{{ $oltStatus['error'] ?? 'Cannot connect to OLT' }}</small>
                </div>
            @endif

            <hr>

            <h6 class="fw-bold mb-3">Configuration</h6>
            <table class="table table-borderless">
                <tr>
                    <td width="200" class="fw-semibold">IP Address</td>
                    <td>: <code>{{ $olt->ip_address }}</code></td>
                </tr>
                <tr>
                    <td class="fw-semibold">OLT Type</td>
                    <td>: {{ $olt->getOltTypeLabel() }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Model</td>
                    <td>: {{ $olt->model ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Telnet Port</td>
                    <td>: {{ $olt->telnet_port }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">SSH Port</td>
                    <td>: {{ $olt->ssh_port }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Username</td>
                    <td>: {{ $olt->username }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Total Ports</td>
                    <td>: {{ $olt->total_ports }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Status</td>
                    <td>: <span class="{{ $olt->is_active ? 'badge bg-success' : 'badge bg-secondary' }}">
                        {{ $olt->is_active ? 'Active' : 'Inactive' }}
                    </span></td>
                </tr>
            </table>

            @if($olt->address || $olt->latitude || $olt->longitude)
            <hr>
            <h6 class="fw-bold mb-3">Location</h6>
            <table class="table table-borderless">
                @if($olt->address)
                <tr>
                    <td width="200" class="fw-semibold">Address</td>
                    <td>: {{ $olt->address }}</td>
                </tr>
                @endif
                @if($olt->latitude && $olt->longitude)
                <tr>
                    <td class="fw-semibold">Coordinates</td>
                    <td>: {{ $olt->latitude }}, {{ $olt->longitude }}</td>
                </tr>
                @endif
            </table>
            @endif

            @if($olt->notes)
            <hr>
            <h6 class="fw-bold mb-3">Notes</h6>
            <p>{{ $olt->notes }}</p>
            @endif
        </div>

        <!-- Connected Customers -->
        <div class="custom-table">
            <h6 class="fw-bold mb-3">Connected Customers ({{ $olt->customers_count }})</h6>

            @if($olt->customers_count > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>ONT SN</th>
                                <th>PON Port</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($olt->customers()->take(10)->get() as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('customers.show', $customer) }}">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td><code>{{ $customer->ont_serial_number ?? '-' }}</code></td>
                                <td>{{ $customer->pon_port ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $customer->getStatusBadgeClass() }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($olt->customers_count > 10)
                <div class="text-center mt-2">
                    <small class="text-muted">And {{ $olt->customers_count - 10 }} more customers...</small>
                </div>
                @endif
            @else
                <p class="text-muted">No customers connected to this OLT yet.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                <form action="{{ route('olts.test', $olt) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-success w-100">
                        <i class="bi bi-wifi"></i> Test Connection
                    </button>
                </form>

                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sshModal">
                    <i class="bi bi-terminal"></i> SSH Terminal
                </button>

                <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#ontListModal">
                    <i class="bi bi-list-ul"></i> View ONT List
                </button>

                <button class="btn btn-outline-primary" disabled>
                    <i class="bi bi-download"></i> Backup Config (Coming Soon)
                </button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Statistics</h6>
            <div class="text-center py-3 mb-3">
                <h2 class="mb-0">{{ $olt->customers_count }}</h2>
                <p class="text-muted mb-0">Connected Customers</p>
            </div>
            <div class="text-center py-2">
                <div class="text-muted small">Total Ports</div>
                <div class="fw-semibold">{{ $olt->total_ports }}</div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="custom-table">
            <h6 class="fw-bold mb-3">Timeline</h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <small class="text-muted">Created</small><br>
                    {{ $olt->created_at->format('d M Y H:i') }}
                </li>
                <li class="mb-2">
                    <small class="text-muted">Last Update</small><br>
                    {{ $olt->updated_at->format('d M Y H:i') }}
                </li>
                <li class="mb-2">
                    <small class="text-muted">Last Seen</small><br>
                    {{ $olt->last_seen ? $olt->last_seen->format('d M Y H:i') : 'Never' }}
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- SSH Terminal Modal -->
<div class="modal fade" id="sshModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SSH Terminal - {{ $olt->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Web SSH Terminal - Execute commands directly on OLT
                </div>
                <div id="terminal" style="height: 500px; background: #1e1e1e; color: #00ff00; padding: 15px; font-family: monospace; overflow-y: auto;">
                    <div id="terminalOutput"></div>
                    <div class="input-group mt-2">
                        <span class="input-group-text bg-dark text-success border-0">$</span>
                        <input type="text" id="terminalInput" class="form-control bg-dark text-success border-0" placeholder="Enter command..." autofocus>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="clearTerminal()">Clear</button>
            </div>
        </div>
    </div>
</div>

<!-- ONT List Modal -->
<div class="modal fade" id="ontListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ONT List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ontListForm">
                    <div class="mb-3">
                        <label class="form-label">PON Port</label>
                        <input type="text" name="pon_port" class="form-control" placeholder="e.g., 0/1/0" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Get ONT List
                    </button>
                </form>
                <div id="ontListResult" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// SSH Terminal Simulation
document.getElementById('terminalInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const command = this.value;
        const output = document.getElementById('terminalOutput');

        // Add command to output
        output.innerHTML += `<div class="text-warning">$ ${command}</div>`;

        // Execute command via AJAX (you'll need to create the endpoint)
        fetch('{{ route('olts.ssh-command', $olt) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ command: command })
        })
        .then(response => response.json())
        .then(data => {
            output.innerHTML += `<div>${data.output || 'Command executed'}</div>`;
            output.scrollTop = output.scrollHeight;
        })
        .catch(error => {
            output.innerHTML += `<div class="text-danger">Error: ${error.message}</div>`;
        });

        this.value = '';
    }
});

function clearTerminal() {
    document.getElementById('terminalOutput').innerHTML = '';
}

// ONT List Form
document.getElementById('ontListForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('{{ route('olts.ont-list', $olt) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('ontListResult').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('ontListResult').innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    });
});
</script>
@endpush
