@extends('emails.layout')

@section('title', 'Payment Received — ' . $invoice->invoice_number)

@section('content')
    <div class="greeting">
        Admin Notification
    </div>

    <div class="alert" style="background:#f0fdf4;border-left:4px solid #22c55e;color:#14532d;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>💰 Payment Received</strong><br>
        A payment has been processed and recorded in the system.
    </div>

    <div class="info-box">
        <h3>💳 Payment Details</h3>
        <div class="info-row">
            <span class="info-label">Amount:</span>
            <span class="info-value"><strong style="font-size:18px;color:#15803d;">£{{ number_format($payment->amount, 2) }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Method:</span>
            <span class="info-value">{{ ucfirst($payment->payment_method) }}</span>
        </div>
        @if($payment->payment_reference)
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value">{{ $payment->payment_reference }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($payment->payment_date)->format('l, j F Y \a\t g:i A') }}</span>
        </div>
    </div>

    <div class="info-box">
        <h3>📄 Invoice</h3>
        <div class="info-row">
            <span class="info-label">Invoice Number:</span>
            <span class="info-value"><strong>{{ $invoice->invoice_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Total:</span>
            <span class="info-value">£{{ number_format($invoice->total_amount, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Amount Paid:</span>
            <span class="info-value">£{{ number_format($invoice->paid_amount, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Balance Remaining:</span>
            <span class="info-value">
                @php $balance = $invoice->total_amount - $invoice->paid_amount; @endphp
                @if($balance <= 0)
                    <span style="color:#15803d;font-weight:600;">£0.00 — Fully Paid ✅</span>
                @else
                    <span style="color:#d97706;font-weight:600;">£{{ number_format($balance, 2) }}</span>
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Status:</span>
            <span class="info-value">{{ ucfirst($invoice->status) }}</span>
        </div>
    </div>

    @if($customer)
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
    @endif

    <p style="margin-top: 20px; color: #64748b; font-size: 13px;">
        Log in to the admin panel to view the full payment and invoice history.
    </p>
@endsection
