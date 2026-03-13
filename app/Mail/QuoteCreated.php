<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quote ' . $this->quote->quote_number . ' from ' . config('app.garage_name', 'DOYEN AUTO'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
