<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11.5px; color: #1a1a1a; line-height: 1.55; }
        .page { padding: 36px 44px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        .company-name { font-size: 20px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px; }
        .company-meta { font-size: 10.5px; color: #555; line-height: 1.7; }
        .company-meta span { display: block; }
        .doc-badge { text-align: right; }
        .doc-badge .type { font-size: 26px; font-weight: 800; color: #1e3a8a; letter-spacing: 3px; line-height: 1; }
        .doc-badge .number { font-size: 13px; font-weight: 600; color: #333; margin-top: 6px; }
        .doc-badge .meta { font-size: 10.5px; color: #555; margin-top: 3px; }
        .address-row { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .address-row td { border: none; padding: 0; width: 50%; vertical-align: top; }
        .address-box { background: #f1f5f9; border-left: 3px solid #1e3a8a; padding: 12px 14px; }
        .address-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #94a3b8; margin-bottom: 5px; }
        .address-name { font-size: 13px; font-weight: 600; color: #111; margin-bottom: 2px; }
        .address-detail { font-size: 10.5px; color: #555; line-height: 1.65; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead th { background: #1e3a8a; color: #fff; padding: 9px 11px; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600; }
        .items-table thead th.r { text-align: right; }
        .items-table tbody td { padding: 9px 11px; border-bottom: 1px solid #e8edf3; font-size: 11px; vertical-align: top; }
        .items-table tbody td.r { text-align: right; }
        .items-table tbody tr:nth-child(even) td { background: #fafbfc; }
        .totals-wrap { float: right; width: 280px; margin-top: 10px; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 5px 10px; border: none; font-size: 11.5px; }
        .totals-table .t-label { color: #555; text-align: left; }
        .totals-table .t-val { text-align: right; font-weight: 600; }
        .totals-table .t-vat td { background: #f1f5f9; }
        .totals-table .t-total td { border-top: 2px solid #1e3a8a; font-size: 14px; font-weight: 700; color: #1e3a8a; padding-top: 8px; }
        .discount-label { color: #dc2626; }
        .vat-summary { clear: both; margin-top: 28px; }
        .vat-summary-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        .vat-summary-table th { background: #f1f5f9; color: #555; padding: 6px 10px; font-weight: 600; text-align: left; border: 1px solid #e2e8f0; }
        .vat-summary-table th.r { text-align: right; }
        .vat-summary-table td { padding: 6px 10px; border: 1px solid #e2e8f0; }
        .vat-summary-table td.r { text-align: right; }
        .section-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px; }
        .info-row { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .info-row td { border: none; padding: 0; vertical-align: top; }
        .info-row p { font-size: 11px; color: #444; line-height: 1.7; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 22px 0; }
        .footer { margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 12px; text-align: center; font-size: 9.5px; color: #94a3b8; line-height: 1.7; }
        .footer strong { color: #666; }
        .exempt-badge { font-size: 9px; background: #fef9c3; color: #854d0e; padding: 1px 5px; border-radius: 3px; font-weight: 600; }
        .status-valid { background: #dcfce7; color: #15803d; display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; vertical-align: middle; margin-left: 8px; }
        .status-expired { background: #fee2e2; color: #b91c1c; display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; vertical-align: middle; margin-left: 8px; }
        .quote-note { background: #fffbeb; border: 1px solid #fde68a; padding: 10px 14px; border-radius: 6px; font-size: 10.5px; color: #92400e; margin-bottom: 20px; }
    </style>
</head>
<body>
@php
    $hdrName     = !empty($garage['invoice_header_name'])     ? $garage['invoice_header_name']     : ($garage['garage_name'] ?? 'Doyen Auto Services');
    $hdrAddress  = !empty($garage['invoice_header_address'])  ? $garage['invoice_header_address']  : ($garage['address']     ?? '');
    $hdrCity     = !empty($garage['invoice_header_city'])     ? $garage['invoice_header_city']     : ($garage['city']        ?? '');
    $hdrPostcode = !empty($garage['invoice_header_postcode']) ? $garage['invoice_header_postcode'] : ($garage['postcode']    ?? '');
    $hdrPhone    = !empty($garage['invoice_header_phone'])    ? $garage['invoice_header_phone']    : ($garage['phone']       ?? '');
    $hdrEmail    = !empty($garage['invoice_header_email'])    ? $garage['invoice_header_email']    : ($garage['email']       ?? '');
    $hdrWebsite  = !empty($garage['invoice_header_website'])  ? $garage['invoice_header_website']  : ($garage['website']     ?? '');
    $vatNumber     = $garage['vat_number']             ?? '';
    $defaultVatRate= (float)($garage['vat_rate']       ?? 20);
    $companyNumber = $garage['invoice_company_number'] ?? '';
    $terms         = $garage['invoice_terms']          ?? 'This quote is valid for the period shown above. Prices are subject to change after the validity date.';
    $footerNote    = $garage['invoice_footer_note']    ?? 'Thank you for considering us.';
    $isVatDoc      = !empty($vatNumber);

    $subtotal      = (float)($quote->subtotal ?? 0);
    $discount      = (float)($quote->discount_amount ?? 0);
    $vatAmount     = (float)($quote->vat_amount ?? 0);
    $totalAmount   = (float)($quote->total_amount ?? $subtotal + $vatAmount - $discount);
    $isExpired     = $quote->valid_until < \Carbon\Carbon::today();

    // Build per-rate VAT summary groups
    $vatGroups = [];
    foreach ($quote->items as $item) {
        if ($item->tax_exempt) {
            $code = 'E';
            $desc = 'Exempt';
            $rate = 0;
        } elseif ((float)($item->vat_rate ?? $defaultVatRate) == 0) {
            $code = 'Z';
            $desc = 'Zero-Rated';
            $rate = 0;
        } else {
            $rate = (float)($item->vat_rate ?? $defaultVatRate);
            $code = 'S';
            $desc = 'Standard Rate';
        }
        $key = $code . '_' . $rate;
        $discountRatio = $subtotal > 0 ? (1 - ($discount / $subtotal)) : 1;
        $net   = (float)$item->total_price * $discountRatio;
        $iVat  = $item->tax_exempt ? 0 : round($net * ($rate / 100), 2);
        if (!isset($vatGroups[$key])) {
            $vatGroups[$key] = ['code' => $code, 'desc' => $desc, 'rate' => $rate, 'net' => 0, 'vat' => 0];
        }
        $vatGroups[$key]['net'] += $net;
        $vatGroups[$key]['vat'] += $iVat;
    }
@endphp

<div class="page">

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width:55%">
                <div class="company-name">{{ $hdrName }}</div>
                <div class="company-meta">
                    @if(!empty($hdrAddress))<span>{{ $hdrAddress }}</span>@endif
                    @if(!empty($hdrCity) || !empty($hdrPostcode))<span>{{ trim($hdrCity.' '.$hdrPostcode) }}</span>@endif
                    @if(!empty($hdrPhone))<span>Tel: {{ $hdrPhone }}</span>@endif
                    @if(!empty($hdrEmail))<span>{{ $hdrEmail }}</span>@endif
                    @if(!empty($hdrWebsite))<span>{{ $hdrWebsite }}</span>@endif
                    @if($isVatDoc)
                        <span style="margin-top:4px;font-weight:600;color:#1e3a8a;">VAT Reg No: {{ $vatNumber }}</span>
                    @endif
                    @if(!empty($companyNumber))
                        <span>Company No: {{ $companyNumber }}</span>
                    @endif
                </div>
            </td>
            <td style="width:45%;text-align:right;">
                <div class="doc-badge">
                    <div class="type">{{ $isVatDoc ? 'VAT QUOTE' : 'QUOTE' }}</div>
                    <div class="number">
                        {{ $quote->quote_number }}
                        @if($isExpired)
                            <span class="status-expired">EXPIRED</span>
                        @elseif($quote->status === 'approved')
                            <span class="status-valid">APPROVED</span>
                        @else
                            <span class="status-valid">VALID</span>
                        @endif
                    </div>
                    <div class="meta">
                        Quote Date: {{ \Carbon\Carbon::parse($quote->quote_date)->format('d M Y') }}<br>
                        Valid Until: {{ \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') }}<br>
                        Status: {{ ucfirst($quote->status) }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- IMPORTANT NOTICE --}}
    <div class="quote-note">
        <strong>Important:</strong> This is a quotation only and does not constitute a tax invoice.
        Prices shown {{ $isVatDoc ? 'include VAT at the rates shown' : 'are exclusive of VAT unless otherwise stated' }}.
        A VAT invoice will be issued upon completion of work.
    </div>

    {{-- QUOTE TO + VEHICLE --}}
    <table class="address-row">
        <tr>
            <td style="padding-right:14px;">
                <div class="address-box">
                    <div class="address-label">Quote Prepared For</div>
                    <div class="address-name">{{ ($quote->customer->first_name ?? '').' '.($quote->customer->last_name ?? '') }}</div>
                    <div class="address-detail">
                        @if(!empty($quote->customer->address)){{ $quote->customer->address }}<br>@endif
                        @php $cLine = trim(($quote->customer->city ?? '').' '.($quote->customer->postcode ?? '')); @endphp
                        @if(!empty($cLine)){{ $cLine }}<br>@endif
                        @if(!empty($quote->customer->email)){{ $quote->customer->email }}<br>@endif
                        @if(!empty($quote->customer->phone)){{ $quote->customer->phone }}@endif
                    </div>
                </div>
            </td>
            <td style="padding-left:8px;">
                @if($quote->vehicle)
                <div class="address-box" style="border-left-color:#2563eb;">
                    <div class="address-label">Vehicle</div>
                    <div class="address-name" style="font-size:14px;letter-spacing:1px;">{{ $quote->vehicle->registration_number }}</div>
                    <div class="address-detail">
                        {{ $quote->vehicle->make }} {{ $quote->vehicle->model }}
                        @if(!empty($quote->vehicle->year)) &bull; {{ $quote->vehicle->year }}@endif
                        @if(!empty($quote->vehicle->fuel_type)) &bull; {{ ucfirst($quote->vehicle->fuel_type) }}@endif
                        @if(!empty($quote->vehicle->mileage))<br>Mileage: {{ number_format($quote->vehicle->mileage) }} miles@endif
                    </div>
                </div>
                @endif
                @if($quote->description)
                <div style="margin-top:8px; font-size:10.5px; color:#555;">
                    <strong>Work:</strong> {{ $quote->description }}
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- LINE ITEMS --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th>Type</th>
                <th class="r" style="width:7%">Qty</th>
                <th class="r" style="width:12%">Unit Price</th>
                @if($isVatDoc)
                    <th class="r" style="width:8%">VAT %</th>
                @endif
                <th class="r" style="width:12%">Net</th>
                @if($isVatDoc)
                    <th class="r" style="width:10%">VAT</th>
                @endif
                <th class="r" style="width:12%">Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse($quote->items as $item)
            @php
                $qty    = (float)($item->quantity  ?? 1);
                $unit   = (float)($item->unit_price ?? 0);
                $net    = $qty * $unit;
                $vr     = $item->tax_exempt ? 0 : (float)($item->vat_rate ?? $defaultVatRate);
                $iVat   = $isVatDoc ? round($net * $vr / 100, 2) : 0;
                $iTotal = $isVatDoc ? $net + $iVat : $net;
            @endphp
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-transform:capitalize;color:#555;">{{ $item->item_type }}</td>
                <td class="r">{{ $qty == intval($qty) ? intval($qty) : number_format($qty, 2) }}</td>
                <td class="r">&pound;{{ number_format($unit, 2) }}</td>
                @if($isVatDoc)
                    <td class="r">
                        @if($item->tax_exempt)
                            <span class="exempt-badge">EXEMPT</span>
                        @else
                            {{ number_format($vr, 0) }}%
                        @endif
                    </td>
                @endif
                <td class="r">&pound;{{ number_format($net, 2) }}</td>
                @if($isVatDoc)
                    <td class="r">&pound;{{ number_format($iVat, 2) }}</td>
                @endif
                <td class="r">&pound;{{ number_format($iTotal, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $isVatDoc ? 8 : 6 }}" style="text-align:center;color:#aaa;padding:16px;">No items.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- TOTALS --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="t-label">Subtotal (ex. VAT):</td>
                <td class="t-val">&pound;{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($discount > 0)
            <tr>
                <td class="t-label discount-label">Discount ({{ $quote->discount_percentage }}%):</td>
                <td class="t-val discount-label">&minus;&pound;{{ number_format($discount, 2) }}</td>
            </tr>
            @endif
            @if($isVatDoc)
            <tr class="t-vat">
                <td class="t-label">VAT:</td>
                <td class="t-val">&pound;{{ number_format($vatAmount, 2) }}</td>
            </tr>
            @endif
            <tr class="t-total">
                <td class="t-label">Total (inc. VAT):</td>
                <td class="t-val">&pound;{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </table>
    </div>
    <div style="clear:both;"></div>

    {{-- VAT SUMMARY (VAT-registered only) --}}
    @if($isVatDoc && count($vatGroups))
    <div class="vat-summary">
        <div class="section-label">VAT Analysis</div>
        <table class="vat-summary-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Rate</th>
                    <th class="r">Net Amount</th>
                    <th class="r">VAT Amount</th>
                    <th class="r">Gross Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vatGroups as $g)
                <tr>
                    <td>{{ $g['code'] }}</td>
                    <td>{{ $g['desc'] }}</td>
                    <td>{{ $g['rate'] > 0 ? $g['rate'].'%' : '0%' }}</td>
                    <td class="r">&pound;{{ number_format($g['net'], 2) }}</td>
                    <td class="r">&pound;{{ number_format($g['vat'], 2) }}</td>
                    <td class="r">&pound;{{ number_format($g['net'] + $g['vat'], 2) }}</td>
                </tr>
                @endforeach
                <tr style="font-weight:700;background:#f1f5f9;">
                    <td colspan="3">Total</td>
                    <td class="r">&pound;{{ number_format($subtotal - $discount, 2) }}</td>
                    <td class="r">&pound;{{ number_format($vatAmount, 2) }}</td>
                    <td class="r">&pound;{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- TERMS --}}
    <hr class="divider">
    <table class="info-row">
        <tr>
            <td>
                <div class="section-label">Quote Terms &amp; Conditions</div>
                <p>{{ $terms }}</p>
                @if($quote->notes)
                <div style="margin-top:10px;">
                    <div class="section-label">Notes</div>
                    <p>{{ $quote->notes }}</p>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        <strong>{{ $hdrName }}</strong>
        @if(!empty($vatNumber)) &bull; <strong>VAT No:</strong> {{ $vatNumber }}@endif
        @if(!empty($companyNumber)) &bull; <strong>Co No:</strong> {{ $companyNumber }}@endif
        <br>
        {{ $footerNote }}<br>
        This quotation is not a VAT invoice. A formal VAT invoice will be issued on completion of work.
    </div>

</div>
</body>
</html>
