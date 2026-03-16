<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company-info h1 { font-size: 22px; color: #1e40af; margin-bottom: 4px; }
        .company-info p { font-size: 11px; color: #666; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 28px; color: #1e40af; letter-spacing: 2px; }
        .invoice-title p { font-size: 11px; color: #666; margin-top: 4px; }
        .bill-to { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 24px; }
        .bill-to .label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 1px; margin-bottom: 6px; }
        .bill-to .name { font-weight: 600; font-size: 14px; }
        .vehicle-info { margin-bottom: 24px; font-size: 11px; color: #666; }
        .vehicle-info strong { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead th { background: #1e40af; color: white; padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        thead th:last-child, thead th:nth-child(3), thead th:nth-child(4) { text-align: right; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; }
        tbody td:last-child, tbody td:nth-child(3), tbody td:nth-child(4) { text-align: right; }
        .totals { float: right; width: 260px; }
        .totals table { margin-bottom: 0; }
        .totals td { padding: 6px 12px; border: none; }
        .totals .subtotal td { border-bottom: none; }
        .totals .total td { border-top: 2px solid #1e40af; font-size: 16px; font-weight: 700; color: #1e40af; }
        .totals .label { text-align: left; color: #666; }
        .totals .amount { text-align: right; font-weight: 600; }
        .notes { clear: both; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        .notes .label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 1px; margin-bottom: 4px; }
        .notes p { font-size: 11px; color: #666; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <table style="margin-bottom: 40px; border: none;">
        <tr>
            <td style="border: none; padding: 0; width: 50%; vertical-align: top;">
                <div class="company-info">
                    <h1>{{ $garage['garage_name'] ?? 'Doyen Auto Services' }}</h1>
                    @if(!empty($garage['address']))<p>{{ $garage['address'] }}</p>@endif
                    @if(!empty($garage['city']) || !empty($garage['postcode']))<p>{{ trim(($garage['city'] ?? '') . ' ' . ($garage['postcode'] ?? '')) }}</p>@endif
                    @if(!empty($garage['phone']))<p>Tel: {{ $garage['phone'] }}</p>@endif
                    @if(!empty($garage['email']))<p>Email: {{ $garage['email'] }}</p>@endif
                    @if(!empty($garage['vat_number']))<p>VAT No: {{ $garage['vat_number'] }}</p>@endif
                </div>
            </td>
            <td style="border: none; padding: 0; width: 50%; vertical-align: top; text-align: right;">
                <div class="invoice-title">
                    <h2>INVOICE</h2>
                    <p><strong>{{ $invoice->invoice_number }}</strong></p>
                    <p>Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</p>
                    @if($invoice->due_date)
                    <p>Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Bill To -->
    <div class="bill-to">
        <div class="label">Bill To</div>
        <div class="name">{{ $invoice->customer->first_name ?? '' }} {{ $invoice->customer->last_name ?? '' }}</div>
        @if($invoice->customer->address ?? false)<p>{{ $invoice->customer->address }}</p>@endif
        @if($invoice->customer->city ?? false)<p>{{ $invoice->customer->city }} {{ $invoice->customer->postcode ?? '' }}</p>@endif
        @if($invoice->customer->email ?? false)<p>{{ $invoice->customer->email }}</p>@endif
        @if($invoice->customer->phone ?? false)<p>{{ $invoice->customer->phone }}</p>@endif
    </div>

    <!-- Vehicle -->
    @if($invoice->vehicle)
    <div class="vehicle-info">
        <strong>Vehicle:</strong> {{ $invoice->vehicle->registration_number }} &mdash; {{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }} {{ $invoice->vehicle->year }}
    </div>
    @endif

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($invoice->items ?? $invoice->invoiceItems ?? []) as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-transform: capitalize;">{{ $item->item_type ?? '-' }}</td>
                <td style="text-align: right;">{{ $item->quantity }}</td>
                <td style="text-align: right;">&pound;{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right;">&pound;{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals clearfix">
        <table>
            <tr class="subtotal">
                <td class="label">Subtotal:</td>
                <td class="amount">&pound;{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">VAT:</td>
                <td class="amount">&pound;{{ number_format($invoice->vat_amount ?? $invoice->tax_amount ?? 0, 2) }}</td>
            </tr>
            <tr class="total">
                <td class="label">Total:</td>
                <td class="amount">&pound;{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="notes">
        <div class="label">Notes</div>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <!-- Payment Terms -->
    <div class="notes" style="margin-top: 20px;">
        <div class="label">Payment Terms</div>
        <p>Payment due within 30 days of invoice date. Bank transfers accepted.</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            {{ $garage['garage_name'] ?? 'Doyen Auto Services' }}
            @if(!empty($garage['address'])) &bull; {{ $garage['address'] }} @endif
            @if(!empty($garage['city'])) {{ $garage['city'] }} @endif
            @if(!empty($garage['postcode'])) {{ $garage['postcode'] }} @endif
            @if(!empty($garage['phone'])) &bull; Tel: {{ $garage['phone'] }} @endif
        </p>
        <p>Thank you for your business</p>
    </div>
</div>
</body>
</html>
