@extends('layouts.admin')

@section('title', 'Select Payment Method')
@section('page-title', 'Select Payment Method')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Invoice Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold">Invoice Details</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Invoice:</strong> {{ $invoice->invoice_number }}</p>
                        <p class="mb-1"><strong>Customer:</strong> {{ $invoice->customer->name }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-1"><strong>Amount:</strong></p>
                        <h4 class="text-primary mb-0">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Channels -->
        @foreach($groupedChannels as $groupName => $channels)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="fw-bold mb-0">{{ $groupName }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($channels as $channel)
                    <div class="col-md-6">
                        <form action="{{ route('payments.create', $invoice) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="{{ $channel['code'] }}">
                            <button type="submit" class="btn btn-outline-primary w-100 text-start p-3">
                                <div class="d-flex align-items-center">
                                    @if($channel['icon_url'])
                                        <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" style="height: 30px;" class="me-3">
                                    @endif
                                    <div class="flex-grow-1">
                                        <strong>{{ $channel['name'] }}</strong>
                                        @if($channel['total_fee']['flat'] > 0 || $channel['total_fee']['percent'] > 0)
                                            <br><small class="text-muted">
                                                Fee:
                                                @if($channel['total_fee']['flat'] > 0)
                                                    Rp {{ number_format($channel['total_fee']['flat'], 0, ',', '.') }}
                                                @endif
                                                @if($channel['total_fee']['percent'] > 0)
                                                    {{ $channel['total_fee']['percent'] }}%
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Payment Instructions</h6>
                <ol class="ps-3">
                    <li>Select your preferred payment method</li>
                    <li>You'll receive payment instructions</li>
                    <li>Complete payment within 24 hours</li>
                    <li>Payment will be verified automatically</li>
                </ol>
                <hr>
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Back to Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
