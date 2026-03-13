<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        if ($this->isEnabled()) {
            $this->client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
            $this->from = 'whatsapp:' . config('services.twilio.whatsapp_from');
        }
    }

    /**
     * Check if WhatsApp is enabled
     */
    protected function isEnabled(): bool
    {
        return config('services.twilio.whatsapp_enabled', false) 
            && !empty(config('services.twilio.sid'))
            && !empty(config('services.twilio.token'))
            && !empty(config('services.twilio.whatsapp_from'));
    }

    /**
     * Send WhatsApp message
     */
    public function send(string $to, string $message): bool
    {
        if (!$this->isEnabled()) {
            Log::info('WhatsApp is disabled, skipping message');
            return false;
        }

        try {
            // Format phone number for WhatsApp
            $to = $this->formatPhoneNumber($to);
            
            if (!$to) {
                return false;
            }

            $this->client->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => $this->from,
                    'body' => $message
                ]
            );

            Log::info('WhatsApp sent successfully', ['to' => $to]);
            return true;

        } catch (\Exception $e) {
            Log::error('WhatsApp failed: ' . $e->getMessage(), [
                'to' => $to,
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Send appointment confirmation
     */
    public function sendAppointmentConfirmation($appointment)
    {
        $customer = $appointment->customer;
        
        if (!$customer->phone || !$customer->sms_notifications) {
            return false;
        }

        $message = "🚗 *Appointment Confirmed!*\n\n"
            . "Vehicle: {$appointment->vehicle->registration}\n"
            . "Date: " . \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y, h:i A') . "\n"
            . ($appointment->service ? "Service: {$appointment->service->name}\n" : "")
            . "\nWe'll see you soon! 👍";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send quote notification
     */
    public function sendQuoteAvailable($quote)
    {
        $customer = $quote->customer;
        
        if (!$customer->phone || !$customer->sms_notifications) {
            return false;
        }

        $message = "📋 *Quote Ready!*\n\n"
            . "Your quote for {$quote->vehicle->registration} is ready.\n"
            . "Amount: £" . number_format($quote->total_amount, 2) . "\n"
            . "Valid until: " . \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') . "\n\n"
            . "View and approve it in your customer portal! 💻";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send job card update
     */
    public function sendJobCardUpdate($jobCard, $status)
    {
        $customer = $jobCard->vehicle->customer;
        
        if (!$customer->phone || !$customer->sms_notifications) {
            return false;
        }

        $messages = [
            'in-progress' => "🔧 *Work Started!*\n\nWe've started work on your {$jobCard->vehicle->registration}. We'll keep you updated!",
            'completed' => "✅ *Work Completed!*\n\nYour {$jobCard->vehicle->registration} is ready for collection!\n\nJob #{$jobCard->job_number}",
            'quality-check' => "🔍 *Quality Check*\n\nYour {$jobCard->vehicle->registration} is undergoing final quality checks. Almost done!",
        ];

        $message = $messages[$status] ?? "Update on your {$jobCard->vehicle->registration}";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send invoice notification
     */
    public function sendInvoiceCreated($invoice)
    {
        $customer = $invoice->jobCard->vehicle->customer;
        
        if (!$customer->phone || !$customer->sms_notifications) {
            return false;
        }

        $message = "💷 *Invoice Ready*\n\n"
            . "Invoice #{$invoice->invoice_number}\n"
            . "Amount: £" . number_format($invoice->total_amount, 2) . "\n\n"
            . "Pay online in your portal or in person! 💳";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send payment received confirmation
     */
    public function sendPaymentReceived($invoice)
    {
        $customer = $invoice->jobCard->vehicle->customer;
        
        if (!$customer->phone || !$customer->sms_notifications) {
            return false;
        }

        $message = "✅ *Payment Received*\n\n"
            . "Thank you for your payment of £" . number_format($invoice->payments->last()->amount, 2) . "!\n"
            . "Invoice #{$invoice->invoice_number}\n\n"
            . "We appreciate your business! 🙏";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send MOT reminder
     */
    public function sendMotReminder($vehicle, $daysUntilExpiry)
    {
        $customer = $vehicle->customer;
        
        if (!$customer->phone || !$customer->mot_reminders) {
            return false;
        }

        $message = "⚠️ *MOT Reminder*\n\n"
            . "Your {$vehicle->registration} MOT expires in {$daysUntilExpiry} days!\n\n"
            . "Book your MOT test now to avoid fines. 📅";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send service reminder
     */
    public function sendServiceReminder($vehicle)
    {
        $customer = $vehicle->customer;
        
        if (!$customer->phone || !$customer->service_reminders) {
            return false;
        }

        $message = "🔧 *Service Reminder*\n\n"
            . "Your {$vehicle->registration} is due for a service.\n\n"
            . "Book online through your portal! 💻";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send a free-form support / admin message to any phone number.
     * Returns ['sid' => '...'] on success, false on failure.
     */
    public function sendSupport(string $to, string $message): array|false
    {
        if (!$this->isEnabled()) {
            Log::info('WhatsApp disabled – support message not sent', ['to' => $to]);
            return false;
        }

        try {
            $formattedTo = $this->formatPhoneNumber($to);
            if (!$formattedTo) {
                Log::warning('sendSupport: invalid phone', ['to' => $to]);
                return false;
            }

            $result = $this->client->messages->create(
                'whatsapp:' . $formattedTo,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );

            Log::info('WhatsApp support message sent', ['to' => $formattedTo, 'sid' => $result->sid]);
            return ['sid' => $result->sid];

        } catch (\Exception $e) {
            Log::error('WhatsApp sendSupport failed: ' . $e->getMessage(), ['to' => $to]);
            return false;
        }
    }

    /**
     * Format UK phone number
     */
    protected function formatPhoneNumber(string $phone): ?string
    {
        // Remove all spaces, dashes, etc
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with 0, replace with +44
        if (substr($phone, 0, 1) === '0') {
            $phone = '+44' . substr($phone, 1);
        }
        
        // If it doesn't start with +, assume UK and add +44
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+44' . $phone;
        }
        
        // Validate it looks like a UK mobile number
        if (!preg_match('/^\+447\d{9}$/', $phone)) {
            Log::warning('Invalid UK mobile number format', ['phone' => $phone]);
            return null;
        }
        
        return $phone;
    }
}
