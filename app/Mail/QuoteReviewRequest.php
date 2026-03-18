<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReviewRequest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Quote $quote,
        public readonly string $reviewUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Quote ' . $this->quote->quote_number . ' — Review & Approve',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-review-request',
            with: [
                'quote'     => $this->quote,
                'reviewUrl' => $this->reviewUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
