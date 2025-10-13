@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Revenue This Month -->
    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Revenue This Month</p>
                <h4 class="fw-bold mb-0">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</h4>
                <small class="text-{{ $revenueGrowth >= 0 ? 'success' : 'danger' }}">
                    <i class="bi bi-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }}"></i>
                    {{ number_format(abs($revenueGrowth), 1) }}% vs last month
                </small>
            </div>
            <div class="stats-icon blue">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Total Customers</p>
                <h4 class="fw-bold mb-0">{{ number_format($totalCustomers) }}</h4>
                <small class="text-success">
                    <i class="bi bi-plus-circle"></i>
                    {{ $newCustomersThisMonth }} new this month
                </small>
            </div>
            <div class="stats-icon green">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <!-- Overdue Invoices -->
    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Overdue Invoices</p>
                <h4 class="fw-bold mb-0 text-danger">{{ $overdueInvoices }}</h4>
                <small class="text-muted">
                    Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }} total
                </small>
            </div>
            <div class="stats-icon orange">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Active Routers -->
    <div class="col-md-3">
        <div class="stats-card">
            <div>
                <p class="text-muted mb-1">Active Routers</p>
                <h4 class="fw-bold mb-0">{{ $activeRouters }} / {{ $totalRouters }}</h4>
                <small class="text-muted">Network devices</small>
            </div>
            <div class="stats-icon purple">
                <i class="bi bi-router"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Revenue Trend (Last 6 Months)</h6>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <!-- Customer Status -->
    <div class="col-lg-4">
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Customer Status</h6>
            <canvas id="customerStatusChart"></canvas>
            <div class="mt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bi bi-circle-fill text-success"></i> Active</span>
                    <strong>{{ $activeCustomers }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bi bi-circle-fill text-warning"></i> Suspended</span>
                    <strong>{{ $suspendedCustomers }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span><i class="bi bi-circle-fill text-secondary"></i> Terminated</span>
                    <strong>{{ $totalCustomers - $activeCustomers - $suspendedCustomers }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Growth Chart -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Customer Growth (Last 6 Months)</h6>
            <canvas id="customerGrowthChart" height="80"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activities & Top Packages -->
<div class="row g-4 mb-4">
    <!-- Recent Invoices -->
    <div class="col-lg-6">
        <div class="custom-table">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Invoices</h6>
                <a href="{{ route('invoices.index') }}" class="text-decoration-none">View All →</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td>{{ $invoice->getFormattedTotal() }}</td>
                            <td>
                                <span class="{{ $invoice->getStatusBadgeClass() }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No invoices yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-lg-6">
        <div class="custom-table">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Customers</h6>
                <a href="{{ route('customers.index') }}" class="text-decoration-none">View All →</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCustomers as $customer)
                        <tr>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td>{{ $customer->package?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $customer->getStatusBadgeClass() }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>
                            <td>{{ $customer->created_at->format('d M') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No customers yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Packages -->
<div class="row g-4">
    <div class="col-12">
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Top Packages</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Package Name</th>
                            <th>Speed</th>
                            <th>Price</th>
                            <th class="text-center">Subscribers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPackages as $package)
                        <tr>
                            <td><strong>{{ $package->name }}</strong></td>
                            <td>{{ $package->getSpeedLabel() }}</td>
                            <td>{{ $package->getFormattedPrice() }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $package->customers_count }} customers</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No packages yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Network Uptime & Recent Activities -->
<div class="row g-4 mt-4">
    <!-- Network Uptime -->
    <div class="col-lg-6">
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Network Uptime (Last 24h)</h6>
            @forelse($routerUptimeData as $router)
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span>{{ $router['name'] }}</span>
                    <strong class="text-{{ $router['uptime'] >= 99 ? 'success' : ($router['uptime'] >= 95 ? 'warning' : 'danger') }}">
                        {{ $router['uptime'] }}%
                    </strong>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-{{ $router['uptime'] >= 99 ? 'success' : ($router['uptime'] >= 95 ? 'warning' : 'danger') }}"
                         style="width: {{ $router['uptime'] }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-muted text-center">No router data available</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-lg-6">
        <div class="custom-table">
            <h6 class="fw-bold mb-4">Recent Activities</h6>
            <div class="activity-timeline">
                @forelse($recentActivities as $activity)
                <div class="activity-item mb-3">
                    <div class="d-flex">
                        <div class="activity-icon me-3">
                            <i class="bi bi-{{ $activity->action === 'created' ? 'plus-circle' : ($activity->action === 'paid' ? 'check-circle' : 'gear') }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0">
                                <strong>{{ $activity->user?->name ?? 'System' }}</strong>
                                {{ $activity->description }}
                            </p>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No recent activities</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($revenueChart, 'month')) !!},
        datasets: [{
            label: 'Revenue (Rp)',
            data: {!! json_encode(array_column($revenueChart, 'revenue')) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Customer Status Pie Chart
const statusCtx = document.getElementById('customerStatusChart');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Suspended', 'Terminated'],
        datasets: [{
            data: [{{ $activeCustomers }}, {{ $suspendedCustomers }}, {{ $totalCustomers - $activeCustomers - $suspendedCustomers }}],
            backgroundColor: [
                'rgb(34, 197, 94)',
                'rgb(251, 146, 60)',
                'rgb(156, 163, 175)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Customer Growth Chart
const growthCtx = document.getElementById('customerGrowthChart');
new Chart(growthCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($customerGrowthChart, 'month')) !!},
        datasets: [{
            label: 'New Customers',
            data: {!! json_encode(array_column($customerGrowthChart, 'customers')) !!},
            backgroundColor: 'rgb(34, 197, 94)',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
