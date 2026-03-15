<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Date Suggestion</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; margin: 0; padding: 0; }
  .wrapper { max-width: 600px; margin: 0 auto; padding: 30px 16px; }
  .header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #fff; padding: 24px 28px; border-radius: 12px 12px 0 0; }
  .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
  .header p { margin: 4px 0 0; font-size: 13px; opacity: .85; }
  .body { background: #fff; padding: 28px; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; border-top: none; }
  .alert { background: #fef9c3; border-left: 4px solid #eab308; padding: 14px 18px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; color: #854d0e; }
  .section { margin-bottom: 20px; }
  .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 4px; }
  .section-value { font-size: 15px; color: #1e293b; font-weight: 600; }
  .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background: #f8fafc; margin-bottom: 20px; }
  .notes-box { background: #f8fafc; border-left: 3px solid #eab308; padding: 12px 16px; border-radius: 4px; font-size: 14px; color: #475569; }
  .btn { display: inline-block; background: #1e40af; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; }
  .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 24px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📅 Customer Requesting Alternative Date</h1>
    <p>A customer would like to suggest a different date for their booking</p>
  </div>
  <div class="body">
    <div class="alert">
      <strong>Action Required:</strong> The customer linked to Quote <strong>#{{ $quote->quote_number }}</strong>
      has requested a different appointment date. Please review and confirm.
    </div>

    <div class="detail-grid">
      <div>
        <div class="section-label">Quote Number</div>
        <div class="section-value">{{ $quote->quote_number }}</div>
      </div>
      <div>
        <div class="section-label">Customer</div>
        <div class="section-value">{{ $quote->customer->name ?? 'N/A' }}</div>
      </div>
      <div>
        <div class="section-label">Customer Email</div>
        <div class="section-value" style="font-size:13px;">{{ $quote->customer->email ?? 'N/A' }}</div>
      </div>
      <div>
        <div class="section-label">Customer Phone</div>
        <div class="section-value">{{ $quote->customer->phone ?? 'N/A' }}</div>
      </div>
      @if($quote->appointment)
      <div>
        <div class="section-label">Original Date</div>
        <div class="section-value">{{ \Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('D, j M Y') }}</div>
      </div>
      <div>
        <div class="section-label">Booking Reference</div>
        <div class="section-value">{{ $quote->appointment->reference_number }}</div>
      </div>
      @endif
      <div>
        <div class="section-label">Suggested Date</div>
        <div class="section-value" style="color:#1d4ed8;">{{ \Carbon\Carbon::parse($suggestedDate)->format('D, j M Y') }}</div>
      </div>
      <div>
        <div class="section-label">Suggested Time</div>
        <div class="section-value" style="color:#1d4ed8;">{{ $suggestedTime }}</div>
      </div>
    </div>

    @if($notes)
    <div class="section">
      <div class="section-label">Customer Note</div>
      <div class="notes-box">{{ $notes }}</div>
    </div>
    @endif

    <p style="font-size:13px;color:#475569;margin-bottom:20px;">
      Log in to the admin panel to view the booking and confirm or propose a new date to the customer.
    </p>

    @if($quote->appointment)
    <a href="{{ config('app.url') }}/bookings/{{ $quote->appointment->id }}" class="btn">View Booking</a>
    @endif
  </div>
  <div class="footer">
    <p>{{ config('app.garage_name', 'Doyen Auto Services') }} — Admin Notification</p>
  </div>
</div>
</body>
</html>
