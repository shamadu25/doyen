@extends('emails.layout')

@section('title', 'Your Quote ' . $quote->quote_number . ' — View Online')

@section('content')
    <div class="greeting">
        Hello {{ $quote->customer->full_name }},
    </div>

    <p>
        Thank you for choosing <strong>{{ config('app.garage_name', 'Doyen Auto Services') }}</strong>!
        We have prepared a quote for the work requested on your vehicle.
        Please review the details below online. From the quote page, you can approve it, download it, or request an amendment.
    </p>

    @if($quote->appointment)
    <div class="info-box">
        <h3>📅 Booking Reference</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><strong>{{ $quote->appointment->reference_number }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, F j, Y \a\t g:i A') }}</span>
        </div>
    </div>
    @endif

    <div class="info-box">
        <h3>🔧 Quote Summary — {{ $quote->quote_number }}</h3>
        <div class="info-row">
            <span class="info-label">Quote Date:</span>
            <span class="info-value">{{ $quote->quote_date->format('F j, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Valid Until:</span>
            <span class="info-value">{{ $quote->valid_until->format('F j, Y') }}</span>
        </div>
        @if($quote->vehicle)
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value">{{ $quote->vehicle->registration_number }} — {{ $quote->vehicle->make }} {{ $quote->vehicle->model }}</span>
        </div>
        @endif
        @if($quote->description)
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value">{{ $quote->description }}</span>
        </div>
        @endif
    </div>

    {{-- Line items table --}}
    @php
        $subtotal = (float) ($quote->subtotal ?? 0);
        $discount = (float) ($quote->discount_amount ?? 0);
        $discountRatio = $subtotal > 0 ? (1 - ($discount / $subtotal)) : 1;
    @endphp
    <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px;">
        <thead>
            <tr style="background:#1e40af;color:#fff;">
                <th style="padding:10px;text-align:left;">Description</th>
                <th style="padding:10px;text-align:center;">Qty</th>
                <th style="padding:10px;text-align:right;">Unit Price</th>
                <th style="padding:10px;text-align:right;">VAT</th>
                <th style="padding:10px;text-align:right;">Total (inc. VAT)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $i => $item)
            @php
                $net = (float) $item->total_price * $discountRatio;
                $itemVatRate = $item->tax_exempt ? 0 : (float) ($item->vat_rate ?? $quote->vat_rate ?? 20);
                $vat = round($net * ($itemVatRate / 100), 2);
                $gross = $net + $vat;
            @endphp
            <tr style="background:{{ $i % 2 === 0 ? '#f8fafc' : '#ffffff' }};border-bottom:1px solid #e5e7eb;">
                <td style="padding:9px 10px;">
                    <span style="font-size:11px;color:#64748b;text-transform:uppercase;">{{ $item->item_type }}</span><br>
                    {{ $item->description }}
                </td>
                <td style="padding:9px 10px;text-align:center;">{{ $item->quantity }}</td>
                <td style="padding:9px 10px;text-align:right;">£{{ number_format($item->unit_price, 2) }}</td>
                <td style="padding:9px 10px;text-align:right;">
                    @if($item->tax_exempt)
                        EXEMPT
                    @else
                        {{ number_format($itemVatRate, 0) }}% / £{{ number_format($vat, 2) }}
                    @endif
                </td>
                <td style="padding:9px 10px;text-align:right;">£{{ number_format($gross, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if ($quote->discount_amount > 0)
            <tr>
                <td colspan="4" style="padding:8px 10px;text-align:right;color:#64748b;">Subtotal:</td>
                <td style="padding:8px 10px;text-align:right;">£{{ number_format($quote->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:8px 10px;text-align:right;color:#dc2626;">Discount ({{ $quote->discount_percentage }}%):</td>
                <td style="padding:8px 10px;text-align:right;color:#dc2626;">−£{{ number_format($quote->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="4" style="padding:8px 10px;text-align:right;color:#64748b;">VAT:</td>
                <td style="padding:8px 10px;text-align:right;">£{{ number_format($quote->vat_amount, 2) }}</td>
            </tr>
            <tr style="background:#1e40af;color:#fff;font-weight:700;font-size:16px;">
                <td colspan="4" style="padding:12px 10px;text-align:right;">Total:</td>
                <td style="padding:12px 10px;text-align:right;">£{{ number_format($quote->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($quote->notes)
    <div class="info-box">
        <h3>📝 Notes</h3>
        <p style="margin:0;">{{ $quote->notes }}</p>
    </div>
    @endif

    <div style="text-align:center;margin:30px 0;">
        <p style="font-size:12px;color:#64748b;margin-bottom:10px;">
            This is a quotation, not a VAT invoice. A VAT invoice will be issued once work is completed.
        </p>
        <p style="color:#374151;margin-bottom:15px;">To view your quote online, click the button below:</p>
        <a href="{{ $reviewUrl }}" class="button" style="background:#16a34a;padding:14px 40px;font-size:16px;">
            View Quote
        </a>
        <p style="font-size:13px;color:#64748b;margin-top:12px;">
            This link is unique to you. Do not share it.<br>
            Quote valid until <strong>{{ $quote->valid_until->format('F j, Y') }}</strong>.
        </p>
    </div>

    <div class="divider"></div>
    <p style="font-size:13px;color:#64748b;">
        If the button above does not work, copy and paste this link into your browser:<br>
        <a href="{{ $reviewUrl }}" style="color:#3b82f6;word-break:break-all;">{{ $reviewUrl }}</a>
    </p>
@endsection
