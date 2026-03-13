<?php

namespace App\Mail;

use App\Models\JobCard;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobCard $jobCard
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We\'d Love Your Feedback! 🌟',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.review-request',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
