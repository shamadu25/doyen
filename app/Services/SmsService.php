<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    protected $twilioSid;
    protected $twilioToken;
    protected $twilioFrom;
    protected $enabled;

    public function __construct()
    {
        $this->twilioSid = env('TWILIO_SID');
        $this->twilioToken = env('TWILIO_TOKEN');
        $this->twilioFrom = env('TWILIO_FROM');
        $this->enabled = env('SMS_ENABLED', false);
    }

    /**
     * Send an SMS message
     *
     * @param string $to Phone number to send to
     * @param string $message Message content
     * @return bool Success status
     */
    public function send($to, $message)
    {
        if (!$this->enabled) {
            Log::info('SMS not sent (disabled): ' . $to . ' - ' . $message);
            return false;
        }

        if (!$this->twilioSid || !$this->twilioToken || !$this->twilioFrom) {
            Log::warning('SMS not sent: Twilio credentials are not fully configured');
            return false;
        }

        try {
            $twilio = new \Twilio\Rest\Client($this->twilioSid, $this->twilioToken);
            
            $twilio->messages->create(
                $this->formatPhoneNumber($to),
                [
                    'from' => $this->twilioFrom,
                    'body' => $message
                ]
            );

            Log::info('SMS sent successfully to: ' . $to);
            return true;

        } catch (Throwable $e) {
            // Network/provider outages are operational warnings, not application crashes.
            Log::warning('Failed to send SMS', [
                'reason' => $e->getMessage(),
                'to' => $to,
            ]);
            return false;
        }
    }

    /**
     * Send appointment confirmation SMS
     */
    public function sendAppointmentConfirmation($appointment)
    {
        $customer = $appointment->customer;
        $vehicle = $appointment->vehicle;
        
        $message = "DOYEN AUTO: Booking confirmed!\n\n"
            . "Ref: " . $appointment->reference_number . "\n"
            . "Date: " . \Carbon\Carbon::parse($appointment->appointment_date)->format('D, jS M Y') . "\n"
            . "Time: " . \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') . "\n"
            . "Service: " . ucfirst($appointment->service_type) . "\n"
            . "Vehicle: " . $vehicle->registration . "\n\n"
            . "Call us: " . env('GARAGE_PHONE', '020 7890 1234');

        return $this->send($customer->phone, $message);
    }

    /**
     * Send appointment reminder SMS
     */
    public function sendAppointmentReminder($appointment)
    {
        $customer = $appointment->customer;
        
        $message = "DOYEN AUTO: Reminder!\n\n"
            . "Your appointment is TOMORROW at " . \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') . "\n\n"
            . "Ref: " . $appointment->reference_number . "\n"
            . "Service: " . ucfirst($appointment->service_type) . "\n\n"
            . "Need to reschedule? Call: " . env('GARAGE_PHONE', '020 7890 1234');

        return $this->send($customer->phone, $message);
    }

    /**
     * Send work started notification
     */
    public function sendWorkStarted($jobCard)
    {
        $customer = $jobCard->customer;
        
        $message = "DOYEN AUTO: We've started working on your vehicle!\n\n"
            . "Job: " . $jobCard->job_number . "\n"
            . "Vehicle: " . $jobCard->vehicle->registration . "\n\n"
            . "We'll update you when it's ready.";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send work completed notification
     */
    public function sendWorkCompleted($jobCard)
    {
        $customer = $jobCard->customer;
        
        $message = "DOYEN AUTO: Great news! Your vehicle is ready for collection.\n\n"
            . "Job: " . $jobCard->job_number . "\n"
            . "Vehicle: " . $jobCard->vehicle->registration . "\n\n"
            . "Call us to arrange pickup: " . env('GARAGE_PHONE', '020 7890 1234');

        return $this->send($customer->phone, $message);
    }

    /**
     * Send invoice notification
     */
    public function sendInvoiceNotification($invoice)
    {
        $customer = $invoice->customer;
        
        $message = "DOYEN AUTO: Invoice ready!\n\n"
            . "Invoice: " . $invoice->invoice_number . "\n"
            . "Amount: £" . number_format($invoice->total_amount, 2) . "\n\n"
            . "Pay online: " . route('invoice.pay', $invoice->id) . "\n\n"
            . "Questions? Call: " . env('GARAGE_PHONE', '020 7890 1234');

        return $this->send($customer->phone, $message);
    }

    /**
     * Send MOT reminder
     */
    public function sendMotReminder($vehicle, $daysUntilDue)
    {
        $customer = $vehicle->customer;
        $garageName = env('GARAGE_NAME', 'DOYEN AUTO');
        $phone = env('GARAGE_PHONE', '020 7890 1234');
        
        $message = "{$garageName}: MOT Reminder\n\n"
            . "Vehicle: " . $vehicle->registration . "\n"
            . "MOT Due: " . $vehicle->mot_due_date->format('d M Y') . " ({$daysUntilDue} days)\n\n"
            . "Book now: {$phone}";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send service reminder
     */
    public function sendServiceReminder($vehicle, $serviceType = 'service')
    {
        $customer = $vehicle->customer;
        $garageName = env('GARAGE_NAME', 'DOYEN AUTO');
        $phone = env('GARAGE_PHONE', '020 7890 1234');
        
        $message = "{$garageName}: Service Reminder\n\n"
            . "Vehicle: " . $vehicle->registration . "\n"
            . "Your {$serviceType} is due.\n\n"
            . "Book now: {$phone}";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send quote notification
     */
    public function sendQuoteNotification($quote)
    {
        $customer = $quote->customer;
        $garageName = env('GARAGE_NAME', 'DOYEN AUTO');
        
        $message = "{$garageName}: Quote Ready\n\n"
            . "Quote: " . $quote->quote_number . "\n"
            . "Amount: £" . number_format($quote->total_amount, 2) . "\n"
            . "Valid until: " . $quote->valid_until->format('d M Y') . "\n\n"
            . "Check your email for details.";

        return $this->send($customer->phone, $message);
    }

    /**
     * Check if SMS is enabled
     */
    public function isEnabled()
    {
        return $this->enabled && $this->twilioSid && $this->twilioToken;
    }

    /**
     * Send job card status update SMS
     */
    public function sendJobCardUpdate($jobCard, $status)
    {
        if (!$this->isEnabled() || !$jobCard->customer->phone) {
            return false;
        }

        $messages = [
            'in-progress' => "🔧 Work Started!\n\nYour {$jobCard->vehicle->registration} is now being serviced. We'll notify you once it's ready.",
            'completed' => "✅ Ready for Collection!\n\nYour {$jobCard->vehicle->registration} is ready!\n\nTotal: £" . number_format($jobCard->total_amount, 2),
            'quality-check' => "🔍 Quality Check Complete!\n\nYour {$jobCard->vehicle->registration} has passed our inspection.",
        ];

        $message = $messages[$status] ?? "Update: Your vehicle service status has been updated.";

        return $this->send($jobCard->customer->phone, $message);
    }

    /**
     * Send invoice created notification
     */
    public function sendInvoiceCreated($invoice)
    {
        if (!$this->isEnabled() || !$invoice->customer->phone) {
            return false;
        }

        $message = "📄 Invoice Created\n\n"
                 . "Invoice #{$invoice->invoice_number}\n"
                 . "Amount: £" . number_format($invoice->total_amount, 2) . "\n"
                 . "Vehicle: {$invoice->vehicle->registration}";

        return $this->send($invoice->customer->phone, $message);
    }

    /**
     * Send payment received confirmation
     */
    public function sendPaymentReceived($invoice)
    {
        if (!$this->isEnabled() || !$invoice->customer->phone) {
            return false;
        }

        $message = "✅ Payment Received!\n\n"
                 . "Thank you for your payment of £" . number_format($invoice->total_amount, 2) . "\n"
                 . "Invoice: {$invoice->invoice_number}";

        return $this->send($invoice->customer->phone, $message);
    }

    /**
     * Send admin alert for new booking
     */
    public function sendAdminBookingAlert($appointment)
    {
        $adminPhone = env('ADMIN_PHONE', env('GARAGE_PHONE'));
        if (!$adminPhone) {
            return false;
        }

        $customer = $appointment->customer;
        $vehicle  = $appointment->vehicle;
        $garageName = env('GARAGE_NAME', 'DOYEN AUTO');

        $message = "{$garageName}: NEW BOOKING ALERT\n\n"
            . "Ref: " . $appointment->reference_number . "\n"
            . "Customer: " . $customer->full_name . "\n"
            . "Phone: " . $customer->phone . "\n"
            . "Service: " . ucfirst(str_replace(['_', '-'], ' ', $appointment->appointment_type)) . "\n"
            . "Vehicle: " . strtoupper($vehicle->registration_number) . "\n"
            . "Date: " . \Carbon\Carbon::parse($appointment->scheduled_date)->format('D d/m/Y g:i A') . "\n"
            . "Status: " . ucfirst($appointment->status);

        return $this->send($adminPhone, $message);
    }

    /**
     * Send admin alert for payment received
     */
    public function sendAdminPaymentAlert($payment, $invoice)
    {
        $adminPhone = env('ADMIN_PHONE', env('GARAGE_PHONE'));
        if (!$adminPhone) {
            return false;
        }

        $garageName = env('GARAGE_NAME', 'DOYEN AUTO');
        $customer   = $invoice->customer;

        $message = "{$garageName}: PAYMENT RECEIVED\n\n"
            . "Invoice: " . $invoice->invoice_number . "\n"
            . "Amount: £" . number_format($payment->amount, 2) . "\n"
            . "Method: " . ucfirst($payment->payment_method) . "\n"
            . "Customer: " . ($customer ? $customer->full_name : 'N/A') . "\n"
            . "Invoice Status: " . ucfirst($invoice->status);

        return $this->send($adminPhone, $message);
    }

    /**
     * Send admin alert for a status change on a booking
     */
    public function sendAdminBookingStatusAlert($appointment, $status)
    {
        $adminPhone = env('ADMIN_PHONE', env('GARAGE_PHONE'));
        if (!$adminPhone) {
            return false;
        }

        $garageName = env('GARAGE_NAME', 'DOYEN AUTO');
        $labels = [
            'confirmed'  => 'BOOKING CONFIRMED',
            'cancelled'  => 'BOOKING CANCELLED',
            'completed'  => 'BOOKING COMPLETED',
        ];
        $label = $labels[$status] ?? strtoupper("BOOKING {$status}");

        $message = "{$garageName}: {$label}\n\n"
            . "Ref: " . $appointment->reference_number . "\n"
            . "Customer: " . $appointment->customer->full_name . "\n"
            . "Date: " . \Carbon\Carbon::parse($appointment->scheduled_date)->format('D d/m/Y g:i A');

        return $this->send($adminPhone, $message);
    }

    /**
     * Send health check completed notification
     */
    public function sendHealthCheckCompleted($healthCheck)
    {
        if (!$this->isEnabled() || !$healthCheck->vehicle->customer->phone) {
            return false;
        }

        $customer = $healthCheck->vehicle->customer;
        
        $message = "🔍 Health Check Complete\n\n"
                 . "Vehicle: {$healthCheck->vehicle->registration}\n"
                 . "Condition: " . ucfirst($healthCheck->overall_condition) . "\n\n"
                 . "Full report emailed to you.";

        return $this->send($customer->phone, $message);
    }

    /**
     * Format phone number for Twilio (E.164 format)
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If it starts with 0, replace with +44 (UK)
        if (substr($phone, 0, 1) === '0') {
            $phone = '+44' . substr($phone, 1);
        }
        
        // If it doesn't start with +, assume UK and add +44
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+44' . $phone;
        }
        
        return $phone;
    }
}
