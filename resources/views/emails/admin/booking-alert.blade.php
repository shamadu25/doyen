@extends('emails.layout')

@section('title', 'Booking Alert — ' . $appointment->reference_number)

@section('content')
    @php
        $alertStyles = [
            'new'       => ['bg' => '#eff6ff', 'border' => '#3b82f6', 'color' => '#1e3a8a', 'icon' => '🆕', 'label' => 'New Booking Received'],
            'confirmed' => ['bg' => '#f0fdf4', 'border' => '#22c55e', 'color' => '#14532d', 'icon' => '✅', 'label' => 'Booking Confirmed'],
            'cancelled' => ['bg' => '#fef2f2', 'border' => '#ef4444', 'color' => '#7f1d1d', 'icon' => '❌', 'label' => 'Booking Cancelled'],
            'completed' => ['bg' => '#f0fdf4', 'border' => '#16a34a', 'color' => '#14532d', 'icon' => '🏁', 'label' => 'Booking Completed'],
        ];
        $style = $alertStyles[$alertType] ?? $alertStyles['new'];
    @endphp

    <div class="greeting">
        Admin Notification
    </div>

    <div class="alert" style="background:{{ $style['bg'] }};border-left:4px solid {{ $style['border'] }};color:{{ $style['color'] }};padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>{{ $style['icon'] }} {{ $style['label'] }}</strong>
    </div>

    <div class="info-box">
        <h3>📋 Booking Details</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><strong>{{ $appointment->reference_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">{{ ucfirst($appointment->status) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value">{{ ucfirst(str_replace(['_', '-'], ' ', $appointment->appointment_type)) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date &amp; Time:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('l, j F Y \a\t g:i A') }}</span>
        </div>
        @if($alertType === 'cancelled' && $appointment->cancellation_reason)
        <div class="info-row">
            <span class="info-label">Cancellation Reason:</span>
            <span class="info-value">{{ $appointment->cancellation_reason }}</span>
        </div>
        @endif
        @if($appointment->description)
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value">{{ Str::limit($appointment->description, 120) }}</span>
        </div>
        @endif
    </div>

    <div class="info-box">
        <h3>👤 Customer</h3>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value"><strong>{{ $customer->full_name }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value"><a href="mailto:{{ $customer->email }}" style="color:#3b82f6;">{{ $customer->email }}</a></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value"><a href="tel:{{ $customer->phone }}" style="color:#3b82f6;">{{ $customer->phone }}</a></span>
        </div>
    </div>

    @if($vehicle)
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
    @endif

    <p style="margin-top: 20px; color: #64748b; font-size: 13px;">
        Log in to the admin panel to manage this booking.
    </p>
@endsection
