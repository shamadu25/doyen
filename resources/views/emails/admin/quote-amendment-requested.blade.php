@extends('emails.layout')

@section('title', 'Quote Amendment Requested — ' . $quote->quote_number)

@section('content')
    <div class="greeting">
        Quote Amendment Requested — {{ $quote->quote_number }}
    </div>

    <div class="alert" style="background:#eff6ff;border-left:4px solid #2563eb;color:#1e3a8a;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>Customer requested a quote amendment</strong><br>
        {{ $quote->customer->full_name }} would like changes made before approving this quote.
    </div>

    <div class="info-box">
        <h3>📋 Quote Details</h3>
        <div class="info-row">
            <span class="info-label">Quote Number:</span>
            <span class="info-value"><strong>{{ $quote->quote_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested From:</span>
            <span class="info-value">{{ ucfirst($source) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-value">{{ $quote->customer->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $quote->customer->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $quote->customer->phone ?? 'N/A' }}</span>
        </div>
        @if($quote->vehicle)
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $quote->vehicle->registration_number }} — {{ $quote->vehicle->make }} {{ $quote->vehicle->model }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Quote Total:</span>
            <span class="info-value">£{{ number_format($quote->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="info-box">
        <h3>📝 Customer Request</h3>
        <p style="margin:0;white-space:pre-line;">{{ $messageBody }}</p>
    </div>

    <p style="margin-top:20px;">
        <a href="{{ config('app.url') }}/quotes/{{ $quote->id }}" class="button" style="background:#2563eb;">
            View Quote in Dashboard
        </a>
    </p>
@endsection
