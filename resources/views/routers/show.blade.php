@extends('layouts.admin')

@section('title', 'Detail Router')
@section('page-title', 'Detail Router')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Router Information -->
        <div class="custom-table mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">{{ $router->name }}</h5>
                    <div class="d-flex gap-2 align-items-center">
                        @if($routerInfo['online'] ?? false)
                            <span class="badge bg-success">Online</span>
                        @else
                            <span class="badge bg-danger">Offline</span>
                        @endif
                        <small class="text-muted">{{ $router->ip_address }}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @can('edit_router')
                    <a href="{{ route('routers.edit', $router) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @endcan
                    <a href="{{ route('routers.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                </div>
            </div>

            @if($routerInfo['online'] ?? false)
                <!-- System Resources (Real-time) -->
                <div class="alert alert-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-check-circle"></i> <strong>Router Online</strong>
                        </div>
                        <small>Identity: {{ $routerInfo['identity'] ?? 'Unknown' }}</small>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">System Resources</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">CPU Load</div>
                                <h4 class="mb-0">{{ $routerInfo['cpu_load'] ?? 0 }}%</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Memory</div>
                                <h4 class="mb-0">
                                    @php
                                        $freeMemory = $routerInfo['free_memory'] ?? 0;
                                        $totalMemory = $routerInfo['total_memory'] ?? 1;
                                        $usedPercent = 100 - (($freeMemory / $totalMemory) * 100);
                                    @endphp
                                    {{ number_format($usedPercent, 0) }}%
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Uptime</div>
                                <div class="small fw-semibold">{{ $routerInfo['uptime'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Version</div>
                                <div class="small fw-semibold">{{ $routerInfo['version'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Free Memory</div>
                                <div class="fw-semibold">
                                    {{ number_format(($routerInfo['free_memory'] ?? 0) / 1024 / 1024, 2) }} MB
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Total Memory</div>
                                <div class="fw-semibold">
                                    {{ number_format(($routerInfo['total_memory'] ?? 0) / 1024 / 1024, 2) }} MB
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Router Offline</strong><br>
                    <small>{{ $routerInfo['error'] ?? 'Tidak bisa terhubung ke router' }}</small>
                </div>
            @endif

            <hr>

            <h6 class="fw-bold mb-3">Konfigurasi</h6>
            <table class="table table-borderless">
                <tr>
                    <td width="200" class="fw-semibold">IP Address</td>
                    <td>: {{ $router->ip_address }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">SSH Port</td>
                    <td>: {{ $router->ssh_port }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">API Port</td>
                    <td>: {{ $router->api_port }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Username</td>
                    <td>: {{ $router->username }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">RouterOS Version</td>
                    <td>: <span class="badge bg-info">Version {{ $router->ros_version }}</span></td>
                </tr>
                <tr>
                    <td class="fw-semibold">Status</td>
                    <td>:
                        @if($router->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                </tr>
            </table>

            @if($router->address || $router->latitude || $router->longitude)
            <hr>
            <h6 class="fw-bold mb-3">Lokasi</h6>
            <table class="table table-borderless">
                @if($router->address)
                <tr>
                    <td width="200" class="fw-semibold">Alamat</td>
                    <td>: {{ $router->address }}</td>
                </tr>
                @endif
                @if($router->latitude && $router->longitude)
                <tr>
                    <td class="fw-semibold">Koordinat</td>
                    <td>: {{ $router->latitude }}, {{ $router->longitude }}</td>
                </tr>
                @endif
                @if($router->coverage_radius)
                <tr>
                    <td class="fw-semibold">Coverage Radius</td>
                    <td>: {{ $router->coverage_radius }} meter</td>
                </tr>
                @endif
            </table>
            @endif
        </div>

        <!-- Connected Customers -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Connected Customers ({{ $router->customers_count }})</h6>

            @if($router->customers_count > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Connection Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($router->customers()->take(10)->get() as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('customers.show', $customer) }}">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td>
                                    <small>{{ $customer->package?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <small>{{ $customer->getConnectionTypeLabel() }}</small>
                                </td>
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

                @if($router->customers_count > 10)
                <div class="text-center mt-2">
                    <small class="text-muted">Dan {{ $router->customers_count - 10 }} customer lainnya...</small>
                </div>
                @endif
            @else
                <p class="text-muted">Belum ada customer yang terhubung ke router ini.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                @can('access_router')
                <form action="{{ route('routers.test', $router) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-success w-100">
                        <i class="bi bi-wifi"></i> Test Connection
                    </button>
                </form>
                <a href="{{ route('routers.ssh-terminal', $router) }}" class="btn btn-dark">
                        <i class="bi bi-terminal"></i> SSH Terminal
                    </a>

                <a href="{{ route('routers.pppoe-users', $router) }}" class="btn btn-outline-info w-100">
                    <i class="bi bi-people"></i> PPPoE Users
                </a>
                @endcan

                @can('reboot_router')
                <form action="{{ route('routers.reboot', $router) }}" method="POST" onsubmit="return confirm('Yakin ingin reboot router ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning w-100">
                        <i class="bi bi-arrow-clockwise"></i> Reboot Router
                    </button>
                </form>
                @endcan

               <!-- <button class="btn btn-outline-primary" disabled>
                    <i class="bi bi-terminal"></i> Web Terminal (Coming Soon)
                </button>-->

                <div class="btn-group" role="group">
    <form action="{{ route('routers.backup-config', $router) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-warning">
            <i class="bi bi-download"></i> Backup (.backup)
        </button>
    </form>

    <form action="{{ route('routers.export-config', $router) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-info">
            <i class="bi bi-file-earmark-code"></i> Export (.rsc)
        </button>
    </form>
</div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="custom-table mb-4">
            <h6 class="fw-bold mb-3">Statistik</h6>
            <div class="text-center py-3 mb-3">
                <h2 class="mb-0">{{ $router->customers_count }}</h2>
                <p class="text-muted mb-0">Connected Customers</p>
            </div>
            @if($routerInfo['online'] ?? false)
            <div class="text-center py-2">
                <div class="text-muted small">Interfaces</div>
                <div class="fw-semibold">{{ $routerInfo['interfaces'] ?? 0 }}</div>
            </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="custom-table">
            <h6 class="fw-bold mb-3">Timeline</h6>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <small class="text-muted">Dibuat</small><br>
                    {{ $router->created_at->format('d M Y H:i') }}
                </li>
                <li class="mb-2">
                    <small class="text-muted">Last Update</small><br>
                    {{ $router->updated_at->format('d M Y H:i') }}
                </li>
                <li class="mb-2">
                    <small class="text-muted">Last Seen</small><br>
                    {{ $router->last_seen ? $router->last_seen->format('d M Y H:i') : 'Never' }}
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection


