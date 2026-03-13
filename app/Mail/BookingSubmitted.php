<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $customer;
    public $vehicle;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
        $this->customer    = $appointment->customer;
        $this->vehicle     = $appointment->vehicle;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address(env('GARAGE_EMAIL', 'info@doyenauto.co.uk'), env('GARAGE_NAME', 'Doyen Auto Services'))],
            subject: 'Booking Received — ' . $this->appointment->reference_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-submitted',
        );
    }
}
