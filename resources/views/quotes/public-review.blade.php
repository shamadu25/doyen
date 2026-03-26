<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Review — {{ config('app.garage_name', 'Doyen Auto Services') }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; background:#f1f5f9; color:#1e293b; }
        .header { background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%); color:#fff; padding:24px 20px; text-align:center; }
        .header h1 { font-size:22px; font-weight:700; }
        .header p { font-size:13px; opacity:.85; margin-top:4px; }
        .container { max-width:740px; margin:30px auto; padding:0 16px 60px; }

        /* Alert banners */
        .alert { padding:14px 18px; border-radius:8px; margin-bottom:20px; font-size:14px; }
        .alert-success { background:#dcfce7; color:#166534; border-left:4px solid #16a34a; }
        .alert-error   { background:#fee2e2; color:#991b1b; border-left:4px solid #dc2626; }
        .alert-warning { background:#fef9c3; color:#854d0e; border-left:4px solid #eab308; }

        /* Card */
        .card { background:#fff; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.08); margin-bottom:20px; overflow:hidden; }
        .card-header { padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
        .card-header h2 { font-size:16px; font-weight:600; color:#1e40af; }
        .card-body { padding:20px; }

        /* Status badge */
        .badge { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; }
        .badge-draft    { background:#f1f5f9; color:#475569; }
        .badge-sent     { background:#eff6ff; color:#2563eb; }
        .badge-approved { background:#dcfce7; color:#15803d; }
        .badge-declined { background:#fee2e2; color:#dc2626; }
        .badge-expired  { background:#fef3c7; color:#92400e; }
        .badge-converted{ background:#f3e8ff; color:#7c3aed; }

        /* Info rows */
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        @media(max-width:500px){ .info-grid{ grid-template-columns:1fr; } }
        .info-item label { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:3px; }
        .info-item span  { font-size:14px; color:#1e293b; }

        /* Items table */
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th { background:#1e40af; color:#fff; padding:10px 12px; text-align:left; font-size:12px; font-weight:600; }
        th:last-child, td:last-child { text-align:right; }
        th:nth-child(2), th:nth-child(3), th:nth-child(4), td:nth-child(2), td:nth-child(3), td:nth-child(4) { text-align:right; }
        td { padding:10px 12px; border-bottom:1px solid #f1f5f9; }
        tr:nth-child(even) td { background:#f8fafc; }
        .item-type { font-size:10px; font-weight:700; text-transform:uppercase; color:#94a3b8; }
        tfoot tr td { font-weight:600; }
        .total-row td { background:#1e40af !important; color:#fff; font-size:16px; padding:12px; }

        /* Action buttons */
        .actions { display:flex; gap:14px; margin-top:10px; flex-wrap:wrap; }
        @media(max-width:500px){ .actions{ flex-direction:column; } }
        .btn { display:block; flex:1; padding:14px 20px; border:none; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; text-align:center; text-decoration:none; min-width:150px; }
        .btn-approve  { background:#16a34a; color:#fff; }
        .btn-approve:hover  { background:#15803d; }
        .btn-decline  { background:#fff; color:#dc2626; border:2px solid #dc2626; }
        .btn-decline:hover  { background:#fee2e2; }
        .btn-suggest  { background:#fff; color:#1d4ed8; border:2px solid #1d4ed8; }
        .btn-suggest:hover  { background:#eff6ff; }
        .btn-disabled { background:#e2e8f0; color:#94a3b8; cursor:not-allowed; }

        /* Inline forms for decline/suggest */
        .collapsible-form { display:none; margin-top:16px; padding:16px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; }
        .collapsible-form.visible { display:block; }
        .form-group { margin-bottom:12px; }
        .form-group label { display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:4px; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:6px;
            font-size:14px; color:#1e293b; background:#fff; box-sizing:border-box;
        }
        .form-group textarea { resize:vertical; min-height:70px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        @media(max-width:500px){ .form-row{ grid-template-columns:1fr; } }
        .btn-submit { padding:10px 24px; border:none; border-radius:6px; font-size:14px; font-weight:700; cursor:pointer; }
        .btn-submit-decline { background:#dc2626; color:#fff; }
        .btn-submit-suggest { background:#1d4ed8; color:#fff; }
        .btn-cancel-form { padding:10px 16px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#fff; cursor:pointer; margin-left:8px; }

        .divider { height:1px; background:#e2e8f0; margin:16px 0; }
        .notes-box { background:#f8fafc; border-left:3px solid #3b82f6; padding:12px 16px; border-radius:4px; font-size:14px; }
        .footer { text-align:center; font-size:12px; color:#94a3b8; margin-top:30px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.garage_name', 'Doyen Auto Services') }}</h1>
    <p>VAT Quote &amp; Estimate Review</p>
</div>

<div class="container">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">⚠️ {{ session('error') }}</div>
    @endif

    {{-- Expired notice --}}
    @if($quote->status === 'expired')
        <div class="alert alert-warning">
            ⏰ <strong>This quote has expired.</strong> The validity period ended on {{ $quote->valid_until->format('F j, Y') }}.
            Please contact us to request an updated quote.
        </div>
    @elseif($quote->status === 'approved')
        <div class="alert alert-success">
            ✅ <strong>Quote Approved.</strong> Thank you! We will confirm your booking shortly.
        </div>
    @elseif($quote->status === 'declined')
        <div class="alert alert-error">
            ❌ <strong>Quote Declined.</strong> Thank you for your response. Please contact us if you have any questions.
        </div>
    @elseif($quote->status === 'converted')
        <div class="alert alert-success">
            🔧 <strong>This quote has been converted to a job card.</strong> Your booking is confirmed.
        </div>
    @endif

    {{-- Quote header card --}}
    <div class="card">
        <div class="card-header">
            <h2>Quote {{ $quote->quote_number }}</h2>
            <span class="badge badge-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Customer</label>
                    <span>{{ $quote->customer->full_name }}</span>
                </div>
                @if($quote->vehicle)
                <div class="info-item">
                    <label>Vehicle</label>
                    <span>{{ $quote->vehicle->registration_number }} — {{ $quote->vehicle->make }} {{ $quote->vehicle->model }}</span>
                </div>
                @endif
                <div class="info-item">
                    <label>Quote Date</label>
                    <span>{{ $quote->quote_date->format('F j, Y') }}</span>
                </div>
                <div class="info-item">
                    <label>Valid Until</label>
                    <span @class(['text-red-600' => $quote->isExpired()])>
                        {{ $quote->valid_until->format('F j, Y') }}
                        @if($quote->isExpired()) <em style="color:#dc2626;">(expired)</em> @endif
                    </span>
                </div>
                @if($quote->appointment)
                <div class="info-item">
                    <label>Booking Reference</label>
                    <span>{{ $quote->appointment->reference_number }}</span>
                </div>
                <div class="info-item">
                    <label>Requested Date</label>
                    <span>{{ \Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, F j, Y \a\t g:i A') }}</span>
                </div>
                @endif
                @if($quote->description)
                <div class="info-item" style="grid-column:1/-1">
                    <label>Work Description</label>
                    <span>{{ $quote->description }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="card">
        <div class="card-header"><h2>Line Items</h2></div>
        <div class="card-body" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>VAT</th>
                        <th>Total (inc. VAT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quote->items as $item)
                    @php
                        $itemVatRate = $item->tax_exempt ? 0 : (float)($item->vat_rate ?? $quote->vat_rate ?? 20);
                        $netPrice    = $item->total_price;
                        $vatAmt      = round($netPrice * $itemVatRate / 100, 2);
                        $gross       = $netPrice + $vatAmt;
                    @endphp
                    <tr>
                        <td>
                            <div class="item-type">{{ $item->item_type }}</div>
                            {{ $item->description }}
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>£{{ number_format($item->unit_price, 2) }}</td>
                        <td>
                            @if($item->tax_exempt)
                                <span style="font-size:10px;background:#fef9c3;color:#854d0e;padding:2px 6px;border-radius:3px;font-weight:700;">EXEMPT</span>
                            @else
                                {{ number_format($itemVatRate, 0) }}% / £{{ number_format($vatAmt, 2) }}
                            @endif
                        </td>
                        <td>£{{ number_format($gross, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;color:#64748b;">Subtotal (ex. VAT)</td>
                        <td>£{{ number_format($quote->subtotal, 2) }}</td>
                    </tr>
                    @if($quote->discount_amount > 0)
                    <tr>
                        <td colspan="4" style="text-align:right;color:#dc2626;">Discount ({{ $quote->discount_percentage }}%)</td>
                        <td style="color:#dc2626;">−£{{ number_format($quote->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" style="text-align:right;color:#64748b;">VAT ({{ $quote->vat_rate }}%)</td>
                        <td>£{{ number_format($quote->vat_amount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right;">Total</td>
                        <td>£{{ number_format($quote->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($quote->notes)
    <div class="card">
        <div class="card-header"><h2>Notes</h2></div>
        <div class="card-body">
            <div class="notes-box">{{ $quote->notes }}</div>
        </div>
    </div>
    @endif

    {{-- Action buttons — only shown when quote is still 'sent' --}}
    @if($quote->status === 'sent' && !$quote->isExpired())
    <div class="card">
        <div class="card-header"><h2>Your Response</h2></div>
        <div class="card-body">
            <p style="font-size:14px;color:#475569;margin-bottom:18px;">
                Please review the quote above and let us know how you would like to proceed.
                Approving will confirm your booking.
            </p>

            {{-- Three main action buttons --}}
            <div class="actions">
                {{-- Approve --}}
                <form method="POST" action="{{ route('quote.approve', $token) }}" style="flex:1;">
                    @csrf
                    <button type="submit" class="btn btn-approve" style="width:100%;">✅ Approve &amp; Confirm Booking</button>
                </form>

                {{-- Suggest Date toggle --}}
                <button type="button" class="btn btn-suggest" onclick="toggleForm('suggestForm', 'declineForm')">
                    📅 Suggest Different Date
                </button>

                {{-- Decline toggle --}}
                <button type="button" class="btn btn-decline" onclick="toggleForm('declineForm', 'suggestForm')">
                    ❌ Decline Quote
                </button>
            </div>

            {{-- Suggest date inline form --}}
            <div class="collapsible-form" id="suggestForm">
                <p style="font-size:13px;color:#475569;margin-bottom:12px;">
                    Enter your preferred date and time below. We will review your request and confirm as soon as possible.
                </p>
                <form method="POST" action="{{ route('quote.suggest-date', $token) }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="suggested_date">Preferred Date *</label>
                            <input type="date" id="suggested_date" name="suggested_date"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   max="{{ now()->addDays(90)->format('Y-m-d') }}"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="suggested_time">Preferred Time *</label>
                            <select id="suggested_time" name="suggested_time" required>
                                <option value="">-- Select time --</option>
                                <option value="08:00">08:00 AM</option>
                                <option value="08:30">08:30 AM</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="09:30">09:30 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">01:00 PM</option>
                                <option value="13:30">01:30 PM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="14:30">02:30 PM</option>
                                <option value="15:00">03:00 PM</option>
                                <option value="15:30">03:30 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="16:30">04:30 PM</option>
                                <option value="17:00">05:00 PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="suggestion_notes">Additional Notes (optional)</label>
                        <textarea id="suggestion_notes" name="suggestion_notes" maxlength="500"
                                  placeholder="Any specific requirements or reason for the date change…"></textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="submit" class="btn-submit btn-submit-suggest">📅 Send Date Suggestion</button>
                        <button type="button" class="btn-cancel-form" onclick="toggleForm(null)">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Decline inline form --}}
            <div class="collapsible-form" id="declineForm">
                <p style="font-size:13px;color:#475569;margin-bottom:12px;">
                    You can let us know why you are declining. This helps us improve our service.
                </p>
                <form method="POST" action="{{ route('quote.decline', $token) }}">
                    @csrf
                    <div class="form-group">
                        <label for="decline_reason">Reason for declining (optional)</label>
                        <textarea id="decline_reason" name="decline_reason" maxlength="500"
                                  placeholder="e.g. Too expensive, found another garage, no longer needed…"></textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="submit" class="btn-submit btn-submit-decline">❌ Confirm Decline</button>
                        <button type="button" class="btn-cancel-form" onclick="toggleForm(null)">Cancel</button>
                    </div>
                </form>
            </div>

            <p style="font-size:12px;color:#94a3b8;margin-top:16px;text-align:center;">
                This quote is valid until <strong>{{ $quote->valid_until->format('F j, Y') }}</strong>.
                If you have any questions, please contact us.
            </p>
        </div>
    </div>

    <script>
    function toggleForm(showId, hideId) {
        if (hideId) {
            var el = document.getElementById(hideId);
            if (el) el.classList.remove('visible');
        }
        if (showId) {
            var target = document.getElementById(showId);
            if (target) {
                var isVisible = target.classList.contains('visible');
                target.classList.toggle('visible', !isVisible);
            }
        }
    }
    </script>
    @endif

    <div class="footer">
        <p>{{ config('app.garage_name', 'Doyen Auto Services') }}</p>
        <p style="margin-top:4px;">
            Questions? Contact us at
            <a href="mailto:{{ config('mail.from.address') }}" style="color:#3b82f6;">{{ config('mail.from.address') }}</a>
        </p>
    </div>

</div>
</body>
</html>
