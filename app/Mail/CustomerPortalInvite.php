<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPortalInvite extends Mailable
{
    use Queueable, SerializesModels;

    public Customer $customer;
    public string $link;

    public function __construct(Customer $customer, string $link)
    {
        $this->customer = $customer;
        $this->link = $link;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Doyen Auto Services Customer Portal Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-portal-invite',
        );
    }
}
