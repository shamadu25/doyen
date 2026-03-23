<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminBookingAlert extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public $customer;
    public $vehicle;
    public string $alertType;

    /**
     * @param Appointment $appointment
     * @param string $alertType  'new' | 'confirmed' | 'cancelled' | 'completed'
     */
    public function __construct(Appointment $appointment, string $alertType = 'new')
    {
        $this->appointment = $appointment;
        $this->customer    = $appointment->customer;
        $this->vehicle     = $appointment->vehicle;
        $this->alertType   = $alertType;
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'new'       => '🆕 New Booking — ' . $this->appointment->reference_number,
            'confirmed' => '✅ Booking Confirmed — ' . $this->appointment->reference_number,
            'cancelled' => '❌ Booking Cancelled — ' . $this->appointment->reference_number,
            'completed' => '🏁 Booking Completed — ' . $this->appointment->reference_number,
        ];

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subjects[$this->alertType] ?? 'Booking Update — ' . $this->appointment->reference_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.booking-alert',
        );
    }
}
