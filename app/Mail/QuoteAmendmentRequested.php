<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteAmendmentRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public string $messageBody,
        public string $source = 'customer portal',
    ) {}

    public function build()
    {
        return $this->subject('Quote Amendment Requested - ' . $this->quote->quote_number)
            ->view('emails.admin.quote-amendment-requested');
    }
}
