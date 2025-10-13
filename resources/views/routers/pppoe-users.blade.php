@extends('layouts.admin')

@section('title', 'PPPoE Users')
@section('page-title', 'PPPoE Users - ' . $router->name)

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="custom-table mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">PPPoE Users</h5>
                    <small class="text-muted">{{ $router->name }} ({{ $router->ip_address }})</small>
                </div>
                <a href="{{ route('routers.show', $router) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Router
                </a>
            </div>

            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#all-users">
                        All Users ({{ count($secrets) }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#active-sessions">
                        Active Sessions ({{ count($activeSessions) }})
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- All PPPoE Users -->
                <div class="tab-pane fade show active" id="all-users">
                    @if(count($secrets) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Profile</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($secrets as $secret)
                                <tr>
                                    <td>
                                        <strong>{{ $secret['name'] }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $secret['profile'] ?? 'default' }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $secret['service'] ?? 'any' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $isOnline = collect($activeSessions)->contains('name', $secret['name']);
                                        @endphp
                                        @if($isOnline)
                                            <span class="badge bg-success">Online</span>
                                        @else
                                            <span class="badge bg-secondary">Offline</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @if($isOnline)
                                            <button class="btn btn-outline-warning" title="Disconnect" disabled>
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                            @endif
                                            <button class="btn btn-outline-danger" title="Delete" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">No PPPoE Users</h5>
                        <p class="text-muted">Belum ada PPPoE user di router ini.</p>
                    </div>
                    @endif
                </div>

                <!-- Active Sessions -->
                <div class="tab-pane fade" id="active-sessions">
                    @if(count($activeSessions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Address</th>
                                    <th>Caller ID</th>
                                    <th>Uptime</th>
                                    <th>Encoding</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeSessions as $session)
                                <tr>
                                    <td>
                                        <strong>{{ $session['name'] ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <code>{{ $session['address'] ?? '-' }}</code>
                                    </td>
                                    <td>
                                        <small>{{ $session['caller-id'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $session['uptime'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $session['encoding'] ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning" title="Disconnect" disabled>
                                            <i class="bi bi-x-circle"></i> Disconnect
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="bi bi-wifi-off" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">No Active Sessions</h5>
                        <p class="text-muted">Tidak ada user yang sedang online saat ini.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
