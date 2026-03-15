@extends('emails.layout')

@section('title', 'Quote Approved — Thank You!')

@section('content')
    <div class="greeting">
        Hello {{ $quote->customer->full_name }},
    </div>

    <div class="alert" style="background:#dcfce7;border-left:4px solid #16a34a;color:#166534;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>✅ Great news! Your quote has been approved.</strong><br>
        We have received your approval for quote {{ $quote->quote_number }}.
        Our team will be in touch shortly to confirm your booking.
    </div>

    <div class="info-box">
        <h3>📋 Quote Summary</h3>
        <div class="info-row">
            <span class="info-label">Quote Number:</span>
            <span class="info-value"><strong>{{ $quote->quote_number }}</strong></span>
        </div>
        @if($quote->vehicle)
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $quote->vehicle->registration_number }} — {{ $quote->vehicle->make }} {{ $quote->vehicle->model }}</span>
        </div>
        @endif
        @if($quote->description)
        <div class="info-row">
            <span class="info-label">Work:</span>
            <span class="info-value">{{ $quote->description }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total Amount:</span>
            <span class="info-value"><strong style="font-size:16px;">£{{ number_format($quote->total_amount, 2) }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Approved At:</span>
            <span class="info-value">{{ $quote->approved_at ? $quote->approved_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    @if($quote->appointment)
    <div class="info-box">
        <h3>📅 Your Booking</h3>
        <div class="info-row">
            <span class="info-label">Booking Reference:</span>
            <span class="info-value"><strong>{{ $quote->appointment->reference_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, d F Y \a\t H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value" style="color:#16a34a;font-weight:600;">✅ Confirmed</span>
        </div>
    </div>
    @endif

    <div class="alert" style="background:#eff6ff;border-left:4px solid #3b82f6;color:#1e3a8a;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>📌 What happens next?</strong><br>
        • A member of our team will contact you to confirm the exact date and time<br>
        • You will receive a booking confirmation once everything is arranged<br>
        • Please keep your quote number <strong>{{ $quote->quote_number }}</strong> handy
    </div>

    <p style="color:#64748b;font-size:14px;margin-top:20px;">
        Have questions? Contact us at
        <a href="tel:{{ preg_replace('/\s+/', '', config('app.garage_phone', '07760926245')) }}" style="color:#3b82f6;">{{ config('app.garage_phone', '07760 926 245') }}</a> or
        <a href="mailto:{{ config('mail.from.address') }}" style="color:#3b82f6;">{{ config('mail.from.address') }}</a>
    </p>
@endsection
