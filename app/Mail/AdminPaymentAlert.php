<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPaymentAlert extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;
    public Invoice $invoice;
    public $customer;

    public function __construct(Payment $payment, Invoice $invoice)
    {
        $this->payment  = $payment;
        $this->invoice  = $invoice;
        $this->customer = $invoice->customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '💰 Payment Received — £' . number_format($this->payment->amount, 2) . ' (' . $this->invoice->invoice_number . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.payment-alert',
        );
    }
}
