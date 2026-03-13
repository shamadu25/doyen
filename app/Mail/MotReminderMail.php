<?php

namespace App\Mail;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MotReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vehicle;
    public $daysUntilExpiry;

    /**
     * Create a new message instance.
     */
    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->daysUntilExpiry = now()->diffInDays($vehicle->mot_due_date, false);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MOT Reminder - ' . $this->vehicle->registration_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mot-reminder',
            with: [
                'customerName' => $this->vehicle->customer->first_name . ' ' . $this->vehicle->customer->last_name,
                'vehicleReg' => $this->vehicle->registration_number,
                'vehicleMake' => $this->vehicle->make,
                'vehicleModel' => $this->vehicle->model,
                'expiryDate' => $this->vehicle->mot_due_date->format('d/m/Y'),
                'daysRemaining' => $this->daysUntilExpiry,
                'isUrgent' => $this->daysUntilExpiry <= 7,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
