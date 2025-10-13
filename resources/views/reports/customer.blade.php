@extends('layouts.admin')

@section('title', 'Customer Report')
@section('page-title', 'Customer Analytics Report')

@section('content')
<!-- Filter & Export -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('reports.customer.excel', request()->all()) }}" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Active Customers</p>
                        <h3 class="fw-bold text-success mb-0">{{ $statusSummary['active'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-person-check text-success" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Suspended</p>
                        <h3 class="fw-bold text-warning mb-0">{{ $statusSummary['suspended'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-person-x text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">New in Period</p>
                        <h3 class="fw-bold text-primary mb-0">{{ $newCustomers }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-person-plus text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Churn Rate</p>
                        <h3 class="fw-bold text-danger mb-0">{{ number_format($churnRate, 2) }}%</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-graph-down text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Growth Chart -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-4">Customer Growth (Last 12 Months)</h5>
        <canvas id="customerGrowthChart" height="80"></canvas>
    </div>
</div>

<div class="row g-4">
    <!-- Customers by Package -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Customers by Package</h5>
                <canvas id="packageDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Customers by Router -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Customers by Router</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Router</th>
                                <th class="text-end">Customers</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customersByRouter as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="text-end"><strong>{{ $item->count }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Customer Growth Chart
const growthCtx = document.getElementById('customerGrowthChart');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: @json($customerGrowth->pluck('month')),
        datasets: [{
            label: 'New Customers',
            data: @json($customerGrowth->pluck('count')),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Package Distribution Chart
const packageCtx = document.getElementById('packageDistributionChart');
new Chart(packageCtx, {
    type: 'pie',
    data: {
        labels: @json($customersByPackage->pluck('name')),
        datasets: [{
            data: @json($customersByPackage->pluck('count')),
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
        }]
    }
});
</script>
@endpush
