@extends('emails.layout')

@section('title', 'New Support Ticket')

@section('content')
<p class="greeting">New Support Ticket Received</p>
<p>A customer has submitted a new support ticket that requires your attention.</p>

<div class="info-box" style="margin: 20px 0;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px; width:140px;">Ticket Ref:</td>
            <td style="padding:6px 0; font-weight:600; color:#1e40af;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Customer:</td>
            <td style="padding:6px 0; font-weight:500;">{{ $ticket->customer->name }} ({{ $ticket->customer->email }})</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Subject:</td>
            <td style="padding:6px 0;">{{ $ticket->subject }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Category:</td>
            <td style="padding:6px 0; text-transform:capitalize;">{{ str_replace('_', ' ', $ticket->category) }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Priority:</td>
            <td style="padding:6px 0;">
                @if($ticket->priority === 'urgent')
                    <span style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">URGENT</span>
                @elseif($ticket->priority === 'high')
                    <span style="background:#ffedd5;color:#ea580c;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">High</span>
                @else
                    <span style="text-transform:capitalize;">{{ $ticket->priority }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Submitted:</td>
            <td style="padding:6px 0;">{{ $ticket->created_at->format('d M Y, H:i') }}</td>
        </tr>
    </table>
</div>

<p style="font-weight:600; margin-top:20px; margin-bottom:6px;">Customer's message:</p>
<div style="background:#f8fafc; border-left:4px solid #3b82f6; padding:15px; border-radius:0 6px 6px 0; font-size:14px; color:#475569; white-space:pre-wrap;">{{ $ticket->message }}</div>

<div style="text-align:center; margin:25px 0;">
    <a href="{{ config('app.url') }}/tickets/{{ $ticket->id }}" style="background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%); color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">View & Respond to Ticket</a>
</div>
@endsection
