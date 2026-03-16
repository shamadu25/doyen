@extends('emails.layout')

@section('title', 'Invoice Ready - Doyen Auto Services')

@section('content')
    <div class="greeting">
        Hello {{ $customer->full_name }},
    </div>

    <div class="alert alert-success">
        <strong>✓ Your Invoice is Ready!</strong><br>
        Your vehicle service has been completed and your invoice is now available.
    </div>

    <p>
        Thank you for choosing Doyen Auto Services for your vehicle maintenance. We appreciate your business!
    </p>

    <div class="info-box">
        <h3>📄 Invoice Details</h3>
        <div class="info-row">
            <span class="info-label">Invoice Number:</span>
            <span class="info-value"><strong>{{ $invoice->invoice_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F j, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Due Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') }}</span>
        </div>
    </div>

    @if($jobCard)
    <div class="info-box">
        <h3>🔧 Service Information</h3>
        <div class="info-row">
            <span class="info-label">Job Card:</span>
            <span class="info-value">{{ $jobCard->job_number }}</span>
        </div>
        @if($jobCard->vehicle)
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $jobCard->vehicle->make }} {{ $jobCard->vehicle->model }} - {{ strtoupper($jobCard->vehicle->registration_number) }}</span>
        </div>
        @endif
    </div>
    @endif

    <div class="info-box" style="background: #f0f9ff; border-left-color: #0ea5e9;">
        <h3 style="color: #0369a1;">💰 Payment Summary</h3>
        <div class="info-row">
            <span class="info-label">Subtotal:</span>
            <span class="info-value">£{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">VAT (20%):</span>
            <span class="info-value">£{{ number_format($invoice->tax_amount, 2) }}</span>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="info-row" style="color: #16a34a;">
            <span class="info-label">Discount:</span>
            <span class="info-value">-£{{ number_format($invoice->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="divider"></div>
        <div class="info-row" style="font-size: 18px;">
            <span class="info-label"><strong>Total Amount:</strong></span>
            <span class="info-value" style="color: #0369a1;"><strong>£{{ number_format($invoice->total_amount, 2) }}</strong></span>
        </div>
        @if($invoice->amount_paid > 0)
        <div class="info-row" style="color: #16a34a;">
            <span class="info-label">Amount Paid:</span>
            <span class="info-value">£{{ number_format($invoice->amount_paid, 2) }}</span>
        </div>
        <div class="info-row" style="font-weight: 600;">
            <span class="info-label">Balance Due:</span>
            <span class="info-value" style="color: #dc2626;">£{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</span>
        </div>
        @endif
    </div>

    @if($invoice->payment_status !== 'paid')
    <div class="alert alert-warning">
        <strong>💳 Payment Information:</strong><br>
        Please arrange payment by {{ \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') }}.<br>
        We accept: Cash, Card, Bank Transfer
    </div>

    <div style="text-align: center;">
        <a href="{{ route('invoices.show', $invoice->id) }}" class="button">View & Pay Invoice</a>
    </div>
    @else
    <div class="alert alert-success">
        <strong>✓ Payment Received!</strong><br>
        Thank you for your payment. This invoice has been marked as paid.
    </div>
    @endif

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        If you have any questions about this invoice, please don't hesitate to contact us at 
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', \App\Models\Setting::get('phone', '+44 141 482 0726')) }}" style="color: #3b82f6;">{{ \App\Models\Setting::get('phone', '+44 141 482 0726') }}</a> or 
        <a href="mailto:{{ \App\Models\Setting::get('email', 'info@doyenautos.co.uk') }}" style="color: #3b82f6;">{{ \App\Models\Setting::get('email', 'info@doyenautos.co.uk') }}</a>
    </p>

    <div class="divider"></div>

    <p style="text-align: center; color: #64748b; font-size: 12px;">
        <strong>Payment Terms:</strong> Payment due within {{ $invoice->payment_terms ?? 7 }} days<br>
        <strong>Bank Details:</strong> Sort: 00-00-00 | Account: 12345678 | Ref: {{ $invoice->invoice_number }}
    </p>
@endsection
