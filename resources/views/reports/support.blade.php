@extends('layouts.admin')

@section('title', 'Support Report')
@section('page-title', 'Support Ticket Analytics')

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
                <a href="{{ route('reports.support.excel', request()->all()) }}" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Card -->
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="fw-bold text-primary">{{ array_sum($ticketStatus->toArray()) }}</h4>
                        <p class="text-muted mb-0">Total Tickets</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="fw-bold text-success">{{ $ticketStatus['resolved'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">Resolved</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="fw-bold text-warning">{{ ($ticketStatus['open'] ?? 0) + ($ticketStatus['in_progress'] ?? 0) }}</h4>
                        <p class="text-muted mb-0">Open/In Progress</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="fw-bold text-info">{{ number_format($avgResolutionTime, 1) }} hrs</h4>
                        <p class="text-muted mb-0">Avg Resolution Time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Ticket Status -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Ticket Status</h5>
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Ticket Priority -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Ticket Priority</h5>
                <canvas id="priorityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Ticket Category -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Ticket Category</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tickets by Assigned User -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="fw-bold mb-4">Tickets by Assigned User</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th class="text-center">Total Tickets</th>
                        <th class="text-end">Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ticketsByUser as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="text-center"><strong>{{ $item->count }}</strong></td>
                        <td class="text-end">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ ($item->count / $ticketsByUser->max('count')) * 100 }}%">
                                    {{ $item->count }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Chart
const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($ticketStatus->keys()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))),
        datasets: [{
            data: @json($ticketStatus->values()),
            backgroundColor: ['#36A2EB', '#FFCE56', '#4BC0C0', '#FF6384', '#9966FF']
        }]
    }
});

// Priority Chart
const priorityCtx = document.getElementById('priorityChart');
new Chart(priorityCtx, {
    type: 'doughnut',
    data: {
        labels: @json($ticketPriority->keys()->map(fn($p) => ucfirst($p))),
        datasets: [{
            data: @json($ticketPriority->values()),
            backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
        }]
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: @json($ticketCategory->keys()->map(fn($c) => ucfirst($c))),
        datasets: [{
            label: 'Tickets',
            data: @json($ticketCategory->values()),
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
        }]
    },
    options: {
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush
