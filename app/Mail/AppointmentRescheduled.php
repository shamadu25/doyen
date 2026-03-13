<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $acceptUrl,
        public string $declineUrl,
    ) {}

    public function envelope(): Envelope
    {
        $customerName = $this->appointment->customer->first_name ?? 'Customer';
        return new Envelope(
            subject: "Appointment Reschedule Request — Doyen Auto Services",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-rescheduled',
        );
    }
}
