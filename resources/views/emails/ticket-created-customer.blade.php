@extends('emails.layout')

@section('title', 'Support Ticket Received')

@section('content')
<p class="greeting">Hi {{ $ticket->customer->name }},</p>
<p>Thank you for contacting us. We have received your support ticket and our team will respond shortly.</p>

<div class="info-box" style="margin: 20px 0;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px; width:140px;">Ticket Reference:</td>
            <td style="padding:6px 0; font-weight:600; color:#1e40af;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Subject:</td>
            <td style="padding:6px 0; font-weight:500;">{{ $ticket->subject }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Category:</td>
            <td style="padding:6px 0; text-transform:capitalize;">{{ str_replace('_', ' ', $ticket->category) }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Priority:</td>
            <td style="padding:6px 0; text-transform:capitalize;">{{ $ticket->priority }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Status:</td>
            <td style="padding:6px 0;"><span style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">Open</span></td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Submitted:</td>
            <td style="padding:6px 0;">{{ $ticket->created_at->format('d M Y, H:i') }}</td>
        </tr>
    </table>
</div>

<p style="font-weight:600; margin-top:20px; margin-bottom:6px;">Your message:</p>
<div style="background:#f8fafc; border-left:4px solid #3b82f6; padding:15px; border-radius:0 6px 6px 0; font-size:14px; color:#475569; white-space:pre-wrap;">{{ $ticket->message }}</div>

<p style="margin-top:20px;">You can view your ticket and any replies by logging into the customer portal:</p>

<div style="text-align:center; margin:25px 0;">
    <a href="{{ config('app.url') }}/customer/tickets/{{ $ticket->id }}" style="background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%); color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">View My Ticket</a>
</div>

<p style="color:#64748b; font-size:13px;">Please keep your ticket reference number <strong>{{ $ticket->ticket_number }}</strong> for your records.</p>
@endsection
