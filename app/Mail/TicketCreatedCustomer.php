<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreatedCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->ticket->ticket_number . '] Support Ticket Received – ' . $this->ticket->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created-customer',
        );
    }
}
