@extends('emails.layout')

@section('title', 'Appointment Reminder - Doyen Auto Services')

@section('content')
    <div class="greeting">
        Hello {{ $customer->full_name }},
    </div>

    <div class="alert alert-info">
        <strong>⏰ Appointment Reminder</strong><br>
        This is a friendly reminder about your upcoming appointment with Doyen Auto Services.
    </div>

    <p>
        We're looking forward to seeing you soon!
    </p>

    <div class="info-box">
        <h3>📋 Appointment Details</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><strong>{{ $appointment->reference_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Time:</span>
            <span class="info-value"><strong>{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('l, F j, Y \a\t g:i A') }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $vehicle->make }} {{ $vehicle->model }} - {{ strtoupper($vehicle->registration_number) }}</span>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>📌 Please Remember:</strong><br>
        • Arrive 10 minutes early<br>
        • Bring your vehicle keys and any relevant documents<br>
        • Our address: {{ \App\Models\Setting::get('address', '59 Southcroft Road') }}, {{ \App\Models\Setting::get('city', 'Rutherglen, Glasgow') }}, {{ \App\Models\Setting::get('postcode', 'G73 1UG') }}<br>
        • Parking available on site
    </div>

    <div style="text-align: center;">
        <a href="https://maps.google.com/?q={{ urlencode(\App\Models\Setting::get('address', '59 Southcroft Road') . ' ' . \App\Models\Setting::get('city', 'Rutherglen Glasgow') . ' ' . \App\Models\Setting::get('postcode', 'G73 1SP')) }}" class="button">Get Directions</a>
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        <strong>Need to reschedule?</strong><br>
        Please call us as soon as possible at <a href="tel:{{ preg_replace('/[^0-9+]/', '', \App\Models\Setting::get('phone', '+44 141 482 0726')) }}" style="color: #3b82f6;">{{ \App\Models\Setting::get('phone', '+44 141 482 0726') }}</a>
    </p>
@endsection
