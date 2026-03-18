@extends('emails.layout')

@section('title', 'Quote Approved — ' . $quote->quote_number)

@section('content')
    <div class="greeting">
        New Quote Approval — Action Required
    </div>

    <div class="alert alert-success" style="background:#dcfce7;border-left:4px solid #16a34a;color:#166534;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>✅ Customer Approved Quote {{ $quote->quote_number }}</strong><br>
        {{ $quote->customer->full_name }} has approved the quote. Please proceed with scheduling the work.
    </div>

    <div class="info-box">
        <h3>📋 Quote Details</h3>
        <div class="info-row">
            <span class="info-label">Quote Number:</span>
            <span class="info-value"><strong>{{ $quote->quote_number }}</strong></span>
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
            <span class="info-value">{{ $quote->customer->phone }}</span>
        </div>
        @if($quote->vehicle)
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $quote->vehicle->registration_number }} — {{ $quote->vehicle->make }} {{ $quote->vehicle->model }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Approved At:</span>
            <span class="info-value">{{ $quote->approved_at ? $quote->approved_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total:</span>
            <span class="info-value"><strong>£{{ number_format($quote->total_amount, 2) }}</strong></span>
        </div>
    </div>

    @if($quote->appointment)
    <div class="info-box">
        <h3>📅 Linked Booking</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value">{{ $quote->appointment->reference_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, d F Y \a\t H:i') }}</span>
        </div>
    </div>
    @endif

    <p style="margin-top:20px;">
        <a href="{{ config('app.url') }}/quotes/{{ $quote->id }}" class="button" style="background:#16a34a;">
            View Quote in Dashboard
        </a>
    </p>
@endsection
