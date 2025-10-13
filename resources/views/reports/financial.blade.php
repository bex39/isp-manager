@extends('layouts.admin')

@section('title', 'Financial Report')
@section('page-title', 'Financial Report')

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
                <a href="{{ route('reports.financial.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </a>
                <a href="{{ route('reports.financial.excel', request()->all()) }}" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Revenue (Paid)</p>
                        <h3 class="fw-bold text-success mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Pending Revenue (Unpaid)</p>
                        <h3 class="fw-bold text-danger mb-0">Rp {{ number_format($pendingRevenue, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Trend -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-4">Monthly Revenue Trend (Last 12 Months)</h5>
        <canvas id="monthlyRevenueChart" height="80"></canvas>
    </div>
</div>

<div class="row g-4">
    <!-- Payment Methods Breakdown -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Payment Methods</h5>
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Package Revenue -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Revenue by Package</h5>
                <canvas id="packageRevenueChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Revenue Chart
const monthlyCtx = document.getElementById('monthlyRevenueChart');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: @json($monthlyRevenue->pluck('month')),
        datasets: [{
            label: 'Revenue',
            data: @json($monthlyRevenue->pluck('revenue')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
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

// Payment Method Chart
const paymentCtx = document.getElementById('paymentMethodChart');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: @json($paymentMethods->pluck('payment_method')->map(fn($m) => ucfirst($m))),
        datasets: [{
            data: @json($paymentMethods->pluck('total')),
            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF']
        }]
    }
});

// Package Revenue Chart
const packageCtx = document.getElementById('packageRevenueChart');
new Chart(packageCtx, {
    type: 'bar',
    data: {
        labels: @json($packageRevenue->pluck('name')),
        datasets: [{
            label: 'Revenue',
            data: @json($packageRevenue->pluck('revenue')),
            backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
@endpush
