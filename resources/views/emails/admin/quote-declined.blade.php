@extends('emails.layout')

@section('title', 'Quote Declined — ' . $quote->quote_number)

@section('content')
    <div class="greeting">
        Quote Declined — {{ $quote->quote_number }}
    </div>

    <div class="alert" style="background:#fee2e2;border-left:4px solid #dc2626;color:#991b1b;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>❌ Customer Declined Quote {{ $quote->quote_number }}</strong><br>
        {{ $quote->customer->full_name }} has declined the quote. You may wish to follow up to understand their requirements.
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
            <span class="info-label">Declined At:</span>
            <span class="info-value">{{ $quote->declined_at ? $quote->declined_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Quote Total:</span>
            <span class="info-value">£{{ number_format($quote->total_amount, 2) }}</span>
        </div>
    </div>

    <p style="margin-top:20px;">
        <a href="{{ config('app.url') }}/quotes/{{ $quote->id }}" class="button" style="background:#dc2626;">
            View Quote in Dashboard
        </a>
    </p>
@endsection
