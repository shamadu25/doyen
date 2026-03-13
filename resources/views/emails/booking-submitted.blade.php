@extends('emails.layout')

@section('title', 'Booking Received - Doyen Auto Services')

@section('content')
    <div class="greeting">
        Hello {{ $customer->full_name }},
    </div>

    <div class="alert" style="background:#fefce8;border-left:4px solid #eab308;color:#713f12;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>⏳ Booking Received — Pending Confirmation</strong><br>
        We've received your booking request. Our team will review it and confirm your appointment shortly.
    </div>

    <p>
        Thank you for choosing Doyen Auto Services! We aim to confirm all bookings within 1 business hour.
        You will receive a separate confirmation email once your appointment is approved.
    </p>

    <div class="info-box">
        <h3>📋 Your Booking Request</h3>
        <div class="info-row">
            <span class="info-label">Reference Number:</span>
            <span class="info-value"><strong>{{ $appointment->reference_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value" style="color:#d97706;font-weight:600;">⏳ Pending Review</span>
        </div>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value">{{ ucfirst(str_replace(['_', '-'], ' ', $appointment->appointment_type)) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date &amp; Time:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('l, F j, Y \a\t g:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Duration:</span>
            <span class="info-value">{{ $appointment->duration_minutes }} minutes</span>
        </div>
        @if($appointment->customer_notes)
        <div class="info-row">
            <span class="info-label">Your Notes:</span>
            <span class="info-value">{{ $appointment->customer_notes }}</span>
        </div>
        @endif
    </div>

    <div class="info-box">
        <h3>🚗 Vehicle</h3>
        <div class="info-row">
            <span class="info-label">Registration:</span>
            <span class="info-value"><strong>{{ strtoupper($vehicle->registration_number) }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $vehicle->make }} {{ $vehicle->model }}{{ $vehicle->year ? ' (' . $vehicle->year . ')' : '' }}</span>
        </div>
    </div>

    <div class="alert" style="background:#eff6ff;border-left:4px solid #3b82f6;color:#1e3a8a;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>📌 What happens next?</strong><br>
        • Our team will review your booking request<br>
        • You'll receive a <strong>confirmation email</strong> once approved<br>
        • If we need to adjust the time, we'll contact you to agree an alternative<br>
        • Keep your reference number <strong>{{ $appointment->reference_number }}</strong> handy
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        Have questions? Contact us at
        <a href="tel:{{ preg_replace('/\s+/', '', env('GARAGE_PHONE', '07760926245')) }}" style="color:#3b82f6;">{{ env('GARAGE_PHONE', '07760 926 245') }}</a> or
        <a href="mailto:{{ env('GARAGE_EMAIL', 'info@doyenautos.co.uk') }}" style="color:#3b82f6;">{{ env('GARAGE_EMAIL', 'info@doyenautos.co.uk') }}</a>
    </p>
@endsection
