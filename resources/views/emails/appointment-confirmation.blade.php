@extends('emails.layout')

@section('title', 'Appointment Confirmed - Doyen Auto Services')

@section('content')
    <div class="greeting">
        Hello {{ $customer->full_name }},
    </div>

    <div class="alert alert-success">
        <strong>✅ Appointment Confirmed!</strong><br>
        Your booking has been reviewed and confirmed by our team. We look forward to seeing you!
    </div>

    <p>
        Thank you for choosing Doyen Auto Services. Everything is set — please see your confirmed appointment details below.
    </p>

    <div class="info-box">
        <h3>📋 Booking Details</h3>
        <div class="info-row">
            <span class="info-label">Reference Number:</span>
            <span class="info-value"><strong>{{ $appointment->reference_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Service Type:</span>
            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Time:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('l, F j, Y \a\t g:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Duration:</span>
            <span class="info-value">{{ $appointment->duration_minutes }} minutes</span>
        </div>
        @if($appointment->description)
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value">{{ $appointment->description }}</span>
        </div>
        @endif
    </div>

    <div class="info-box">
        <h3>🚗 Vehicle Information</h3>
        <div class="info-row">
            <span class="info-label">Registration:</span>
            <span class="info-value"><strong>{{ strtoupper($vehicle->registration_number) }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</span>
        </div>
        @if($vehicle->color)
        <div class="info-row">
            <span class="info-label">Color:</span>
            <span class="info-value">{{ ucfirst($vehicle->color) }}</span>
        </div>
        @endif
    </div>

    <div class="alert alert-info">
        <strong>📌 Important Information:</strong><br>
        • Please arrive 10 minutes before your appointment<br>
        • Bring your vehicle documents and keys<br>
        • If you need to reschedule, please call us at least 24 hours in advance<br>
        • We'll send you a reminder 24 hours before your appointment
    </div>

    <div style="text-align: center;">
        <a href="{{ url('/') }}" class="button">Visit Our Website</a>
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        Need to make changes to your booking? Please contact us at 
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', \App\Models\Setting::get('phone', '+44 141 482 0726')) }}" style="color: #3b82f6;">{{ \App\Models\Setting::get('phone', '+44 141 482 0726') }}</a> or 
        <a href="mailto:{{ \App\Models\Setting::get('email', 'info@doyenautos.co.uk') }}" style="color: #3b82f6;">{{ \App\Models\Setting::get('email', 'info@doyenautos.co.uk') }}</a>
    </p>
@endsection
