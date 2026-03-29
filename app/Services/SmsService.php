<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SmsService
{
    protected string $twilioSid;
    protected string $twilioToken;
    protected string $twilioFrom;
    protected bool $enabled;

    private const TEMPLATE_DEFINITIONS = [
        'appointment_confirmation_customer' => [
            'label' => 'Appointment Confirmation',
            'audience' => 'customer',
            'description' => 'Sent when an appointment is confirmed.',
            'content' => "DOYEN AUTO: Booking confirmed!\n\nRef: {{reference_number}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nService: {{service_type}}\nVehicle: {{vehicle_registration}}\n\nCall us: {{garage_phone}}",
            'placeholders' => ['garage_name', 'garage_phone', 'reference_number', 'appointment_date', 'appointment_time', 'service_type', 'vehicle_registration', 'customer_name'],
        ],
        'appointment_reminder_customer' => [
            'label' => 'Appointment Reminder',
            'audience' => 'customer',
            'description' => 'Sent before a scheduled appointment.',
            'content' => "DOYEN AUTO: Reminder!\n\nYour appointment is on {{appointment_date}} at {{appointment_time}}\n\nRef: {{reference_number}}\nService: {{service_type}}\nVehicle: {{vehicle_registration}}\n\nNeed to reschedule? Call: {{garage_phone}}",
            'placeholders' => ['garage_name', 'garage_phone', 'reference_number', 'appointment_date', 'appointment_time', 'service_type', 'vehicle_registration', 'customer_name'],
        ],
        'work_started_customer' => [
            'label' => 'Work Started',
            'audience' => 'customer',
            'description' => 'Sent when work begins on a job card.',
            'content' => "{{garage_name}}: We have started work on your vehicle.\n\nJob: {{job_number}}\nVehicle: {{vehicle_registration}}\n\nWe will update you when it is ready.",
            'placeholders' => ['garage_name', 'job_number', 'vehicle_registration', 'customer_name'],
        ],
        'work_completed_customer' => [
            'label' => 'Work Completed',
            'audience' => 'customer',
            'description' => 'Sent when work is completed and the vehicle is ready.',
            'content' => "{{garage_name}}: Your vehicle is ready for collection.\n\nJob: {{job_number}}\nVehicle: {{vehicle_registration}}\n\nCall us: {{garage_phone}}",
            'placeholders' => ['garage_name', 'garage_phone', 'job_number', 'vehicle_registration', 'customer_name'],
        ],
        'invoice_notification_customer' => [
            'label' => 'Invoice Payment Link',
            'audience' => 'customer',
            'description' => 'Sent when an invoice is ready with the payment link.',
            'content' => "{{garage_name}}: Invoice ready.\n\nInvoice: {{invoice_number}}\nAmount: {{invoice_amount}}\n\nPay online: {{invoice_payment_url}}\n\nQuestions? Call: {{garage_phone}}",
            'placeholders' => ['garage_name', 'garage_phone', 'invoice_number', 'invoice_amount', 'invoice_payment_url', 'customer_name', 'vehicle_registration'],
        ],
        'mot_reminder_customer' => [
            'label' => 'MOT Reminder',
            'audience' => 'customer',
            'description' => 'Sent for MOT expiry reminders.',
            'content' => "{{garage_name}}: MOT Reminder\n\nVehicle: {{vehicle_registration}}\nMOT Due: {{mot_due_date}} ({{days_until_due}} days)\n\nBook now: {{garage_phone}}",
            'placeholders' => ['garage_name', 'garage_phone', 'vehicle_registration', 'mot_due_date', 'days_until_due', 'customer_name'],
        ],
        'service_reminder_customer' => [
            'label' => 'Service Reminder',
            'audience' => 'customer',
            'description' => 'Sent for service due reminders.',
            'content' => "{{garage_name}}: Service Reminder\n\nVehicle: {{vehicle_registration}}\nYour {{service_type}} is due.\n\nBook now: {{garage_phone}}",
            'placeholders' => ['garage_name', 'garage_phone', 'vehicle_registration', 'service_type', 'customer_name'],
        ],
        'quote_notification_customer' => [
            'label' => 'Quote Ready',
            'audience' => 'customer',
            'description' => 'Sent when a quote is ready for review.',
            'content' => "{{garage_name}}: Quote Ready\n\nQuote: {{quote_number}}\nAmount: {{quote_amount}}\nValid until: {{quote_valid_until}}\n\nPlease check your email to view the full quote.",
            'placeholders' => ['garage_name', 'quote_number', 'quote_amount', 'quote_valid_until', 'customer_name', 'vehicle_registration'],
        ],
        'job_card_in_progress_customer' => [
            'label' => 'Job Card In Progress',
            'audience' => 'customer',
            'description' => 'Sent when a job card status changes to in progress.',
            'content' => "{{garage_name}}: Work has started on your {{vehicle_registration}}.\n\nJob: {{job_number}}\nStatus: {{job_status}}",
            'placeholders' => ['garage_name', 'job_number', 'job_status', 'vehicle_registration', 'customer_name'],
        ],
        'job_card_completed_customer' => [
            'label' => 'Job Card Completed',
            'audience' => 'customer',
            'description' => 'Sent when a job card is completed.',
            'content' => "{{garage_name}}: Ready for collection.\n\nVehicle: {{vehicle_registration}}\nJob: {{job_number}}\nTotal: {{job_total_amount}}",
            'placeholders' => ['garage_name', 'job_number', 'job_total_amount', 'vehicle_registration', 'customer_name'],
        ],
        'job_card_quality_check_customer' => [
            'label' => 'Job Card Quality Check',
            'audience' => 'customer',
            'description' => 'Sent after quality check is completed.',
            'content' => "{{garage_name}}: Quality check complete.\n\nVehicle: {{vehicle_registration}}\nJob: {{job_number}}",
            'placeholders' => ['garage_name', 'job_number', 'vehicle_registration', 'customer_name'],
        ],
        'invoice_created_customer' => [
            'label' => 'Invoice Created',
            'audience' => 'customer',
            'description' => 'Sent when an invoice is created.',
            'content' => "{{garage_name}}: Invoice Created\n\nInvoice: {{invoice_number}}\nAmount: {{invoice_amount}}\nVehicle: {{vehicle_registration}}",
            'placeholders' => ['garage_name', 'invoice_number', 'invoice_amount', 'vehicle_registration', 'customer_name'],
        ],
        'payment_received_customer' => [
            'label' => 'Payment Received',
            'audience' => 'customer',
            'description' => 'Sent after a payment is received.',
            'content' => "{{garage_name}}: Payment Received\n\nThank you for your payment of {{payment_amount}}\nInvoice: {{invoice_number}}",
            'placeholders' => ['garage_name', 'payment_amount', 'invoice_number', 'customer_name'],
        ],
        'health_check_completed_customer' => [
            'label' => 'Health Check Completed',
            'audience' => 'customer',
            'description' => 'Sent when a vehicle health check is completed.',
            'content' => "{{garage_name}}: Health Check Complete\n\nVehicle: {{vehicle_registration}}\nCondition: {{health_check_condition}}\n\nYour full report has been emailed.",
            'placeholders' => ['garage_name', 'vehicle_registration', 'health_check_condition', 'customer_name'],
        ],
        'admin_booking_alert' => [
            'label' => 'Admin New Booking Alert',
            'audience' => 'admin',
            'description' => 'Sent to admin when a new booking is created.',
            'content' => "{{garage_name}}: NEW BOOKING ALERT\n\nRef: {{reference_number}}\nCustomer: {{customer_name}}\nPhone: {{customer_phone}}\nService: {{service_type}}\nVehicle: {{vehicle_registration}}\nDate: {{appointment_datetime}}\nStatus: {{appointment_status}}",
            'placeholders' => ['garage_name', 'reference_number', 'customer_name', 'customer_phone', 'service_type', 'vehicle_registration', 'appointment_datetime', 'appointment_status'],
        ],
        'admin_payment_alert' => [
            'label' => 'Admin Payment Alert',
            'audience' => 'admin',
            'description' => 'Sent to admin when a payment is received.',
            'content' => "{{garage_name}}: PAYMENT RECEIVED\n\nInvoice: {{invoice_number}}\nAmount: {{payment_amount}}\nMethod: {{payment_method}}\nCustomer: {{customer_name}}\nInvoice Status: {{invoice_status}}",
            'placeholders' => ['garage_name', 'invoice_number', 'payment_amount', 'payment_method', 'customer_name', 'invoice_status'],
        ],
        'admin_booking_confirmed' => [
            'label' => 'Admin Booking Confirmed',
            'audience' => 'admin',
            'description' => 'Sent to admin when a booking is confirmed.',
            'content' => "{{garage_name}}: BOOKING CONFIRMED\n\nRef: {{reference_number}}\nCustomer: {{customer_name}}\nDate: {{appointment_datetime}}\nStatus: {{appointment_status}}",
            'placeholders' => ['garage_name', 'reference_number', 'customer_name', 'appointment_datetime', 'appointment_status'],
        ],
        'admin_booking_cancelled' => [
            'label' => 'Admin Booking Cancelled',
            'audience' => 'admin',
            'description' => 'Sent to admin when a booking is cancelled.',
            'content' => "{{garage_name}}: BOOKING CANCELLED\n\nRef: {{reference_number}}\nCustomer: {{customer_name}}\nDate: {{appointment_datetime}}\nStatus: {{appointment_status}}",
            'placeholders' => ['garage_name', 'reference_number', 'customer_name', 'appointment_datetime', 'appointment_status'],
        ],
        'admin_booking_completed' => [
            'label' => 'Admin Booking Completed',
            'audience' => 'admin',
            'description' => 'Sent to admin when a booking is completed.',
            'content' => "{{garage_name}}: BOOKING COMPLETED\n\nRef: {{reference_number}}\nCustomer: {{customer_name}}\nDate: {{appointment_datetime}}\nStatus: {{appointment_status}}",
            'placeholders' => ['garage_name', 'reference_number', 'customer_name', 'appointment_datetime', 'appointment_status'],
        ],
    ];

    public function __construct()
    {
        $this->twilioSid = (string) config('services.twilio.sid');
        $this->twilioToken = (string) config('services.twilio.token');
        $this->twilioFrom = (string) config('services.twilio.from');
        $this->enabled = filter_var(Setting::get('sms_enabled', config('services.sms.enabled', false)), FILTER_VALIDATE_BOOLEAN);
    }

    public static function templateDefinitions(): array
    {
        return self::TEMPLATE_DEFINITIONS;
    }

    public static function editableTemplates(): array
    {
        return collect(self::TEMPLATE_DEFINITIONS)->map(function (array $definition, string $key) {
            return [
                'key' => $key,
                'label' => $definition['label'],
                'audience' => $definition['audience'],
                'description' => $definition['description'],
                'placeholders' => $definition['placeholders'],
                'content' => Setting::get(self::templateSettingKey($key), $definition['content']),
            ];
        })->values()->all();
    }

    public static function templateSettingKey(string $templateKey): string
    {
        return 'sms_template_' . $templateKey;
    }

    public function send($to, $message, array $context = []): bool
    {
        $rawRecipient = trim((string) $to);
        $normalizedRecipient = $rawRecipient !== '' ? $this->formatPhoneNumber($rawRecipient) : '';

        if ($rawRecipient === '') {
            $this->logSms($context, $rawRecipient, $normalizedRecipient, (string) $message, 'skipped', 'Recipient phone number missing.');
            return false;
        }

        if (!$this->enabled) {
            Log::info('SMS not sent (disabled): ' . $rawRecipient . ' - ' . $message);
            $this->logSms($context, $rawRecipient, $normalizedRecipient, (string) $message, 'skipped', 'SMS is disabled in settings.');
            return false;
        }

        if (!$this->twilioSid || !$this->twilioToken || !$this->twilioFrom) {
            Log::warning('SMS not sent: Twilio credentials are not fully configured');
            $this->logSms($context, $rawRecipient, $normalizedRecipient, (string) $message, 'skipped', 'Twilio credentials are not fully configured.');
            return false;
        }

        try {
            $twilio = new \Twilio\Rest\Client($this->twilioSid, $this->twilioToken);

            $response = $twilio->messages->create(
                $normalizedRecipient,
                [
                    'from' => $this->twilioFrom,
                    'body' => $message,
                ]
            );

            Log::info('SMS sent successfully to: ' . $rawRecipient);
            $this->logSms($context, $rawRecipient, $normalizedRecipient, (string) $message, 'sent', null, [
                'provider_message_id' => $response->sid ?? null,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('Failed to send SMS', [
                'reason' => $e->getMessage(),
                'to' => $rawRecipient,
            ]);
            $this->logSms($context, $rawRecipient, $normalizedRecipient, (string) $message, 'failed', $e->getMessage());
            return false;
        }
    }

    public function sendAppointmentConfirmation($appointment): bool
    {
        $appointment->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($appointment, 'customer.phone'),
            'appointment_confirmation_customer',
            $this->appointmentReplacements($appointment),
            [
                'audience' => 'customer',
                'related' => $appointment,
            ]
        );
    }

    public function sendAppointmentReminder($appointment): bool
    {
        $appointment->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($appointment, 'customer.phone'),
            'appointment_reminder_customer',
            $this->appointmentReplacements($appointment),
            [
                'audience' => 'customer',
                'related' => $appointment,
            ]
        );
    }

    public function sendWorkStarted($jobCard): bool
    {
        $jobCard->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($jobCard, 'customer.phone'),
            'work_started_customer',
            $this->jobCardReplacements($jobCard),
            [
                'audience' => 'customer',
                'related' => $jobCard,
            ]
        );
    }

    public function sendWorkCompleted($jobCard): bool
    {
        $jobCard->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($jobCard, 'customer.phone'),
            'work_completed_customer',
            $this->jobCardReplacements($jobCard),
            [
                'audience' => 'customer',
                'related' => $jobCard,
            ]
        );
    }

    public function sendInvoiceNotification($invoice): bool
    {
        $invoice->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($invoice, 'customer.phone'),
            'invoice_notification_customer',
            $this->invoiceReplacements($invoice),
            [
                'audience' => 'customer',
                'related' => $invoice,
            ]
        );
    }

    public function sendMotReminder($vehicle, $daysUntilDue): bool
    {
        $vehicle->loadMissing('customer');

        return $this->sendTemplated(
            data_get($vehicle, 'customer.phone'),
            'mot_reminder_customer',
            array_merge($this->vehicleReplacements($vehicle), [
                'days_until_due' => (string) $daysUntilDue,
                'mot_due_date' => $this->formatDate(data_get($vehicle, 'mot_due_date')),
            ]),
            [
                'audience' => 'customer',
                'related' => $vehicle,
            ]
        );
    }

    public function sendServiceReminder($vehicle, $serviceType = 'service'): bool
    {
        $vehicle->loadMissing('customer');

        return $this->sendTemplated(
            data_get($vehicle, 'customer.phone'),
            'service_reminder_customer',
            array_merge($this->vehicleReplacements($vehicle), [
                'service_type' => (string) $serviceType,
            ]),
            [
                'audience' => 'customer',
                'related' => $vehicle,
            ]
        );
    }

    public function sendQuoteNotification($quote): bool
    {
        $quote->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($quote, 'customer.phone'),
            'quote_notification_customer',
            $this->quoteReplacements($quote),
            [
                'audience' => 'customer',
                'related' => $quote,
            ]
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->twilioSid !== '' && $this->twilioToken !== '';
    }

    public function sendJobCardUpdate($jobCard, $status): bool
    {
        $jobCard->loadMissing(['customer', 'vehicle']);

        $templateMap = [
            'in-progress' => 'job_card_in_progress_customer',
            'completed' => 'job_card_completed_customer',
            'quality-check' => 'job_card_quality_check_customer',
        ];

        $templateKey = $templateMap[$status] ?? 'job_card_in_progress_customer';

        return $this->sendTemplated(
            data_get($jobCard, 'customer.phone'),
            $templateKey,
            array_merge($this->jobCardReplacements($jobCard), [
                'job_status' => (string) $status,
            ]),
            [
                'audience' => 'customer',
                'related' => $jobCard,
            ]
        );
    }

    public function sendInvoiceCreated($invoice): bool
    {
        $invoice->loadMissing(['customer', 'vehicle']);

        return $this->sendTemplated(
            data_get($invoice, 'customer.phone'),
            'invoice_created_customer',
            $this->invoiceReplacements($invoice),
            [
                'audience' => 'customer',
                'related' => $invoice,
            ]
        );
    }

    public function sendPaymentReceived($invoice): bool
    {
        $invoice->loadMissing(['customer', 'vehicle', 'payments']);

        $latestPaymentAmount = $invoice->payments()
            ->where('status', 'completed')
            ->latest('payment_date')
            ->value('amount');

        return $this->sendTemplated(
            data_get($invoice, 'customer.phone'),
            'payment_received_customer',
            array_merge($this->invoiceReplacements($invoice), [
                'payment_amount' => $this->money($latestPaymentAmount ?? data_get($invoice, 'total_amount')),
            ]),
            [
                'audience' => 'customer',
                'related' => $invoice,
            ]
        );
    }

    public function sendAdminBookingAlert($appointment): bool
    {
        $appointment->loadMissing(['customer', 'vehicle']);
        $adminPhone = $this->getAdminPhone();

        return $this->sendTemplated(
            $adminPhone,
            'admin_booking_alert',
            $this->appointmentReplacements($appointment),
            [
                'audience' => 'admin',
                'related' => $appointment,
            ]
        );
    }

    public function sendAdminPaymentAlert($payment, $invoice): bool
    {
        $invoice->loadMissing('customer');
        $adminPhone = $this->getAdminPhone();

        return $this->sendTemplated(
            $adminPhone,
            'admin_payment_alert',
            array_merge($this->invoiceReplacements($invoice), [
                'payment_amount' => $this->money(data_get($payment, 'amount')),
                'payment_method' => Str::title(str_replace(['_', '-'], ' ', (string) data_get($payment, 'payment_method'))),
                'invoice_status' => Str::title((string) data_get($invoice, 'status')),
            ]),
            [
                'audience' => 'admin',
                'related' => $payment,
            ]
        );
    }

    public function sendAdminBookingStatusAlert($appointment, $status): bool
    {
        $appointment->loadMissing(['customer', 'vehicle']);

        $templateMap = [
            'confirmed' => 'admin_booking_confirmed',
            'cancelled' => 'admin_booking_cancelled',
            'completed' => 'admin_booking_completed',
        ];

        $templateKey = $templateMap[$status] ?? 'admin_booking_alert';

        return $this->sendTemplated(
            $this->getAdminPhone(),
            $templateKey,
            array_merge($this->appointmentReplacements($appointment), [
                'appointment_status' => Str::title((string) $status),
            ]),
            [
                'audience' => 'admin',
                'related' => $appointment,
            ]
        );
    }

    public function sendHealthCheckCompleted($healthCheck): bool
    {
        $healthCheck->loadMissing('vehicle.customer');

        return $this->sendTemplated(
            data_get($healthCheck, 'vehicle.customer.phone'),
            'health_check_completed_customer',
            array_merge($this->vehicleReplacements(data_get($healthCheck, 'vehicle')), [
                'health_check_condition' => Str::title((string) data_get($healthCheck, 'overall_condition')),
            ]),
            [
                'audience' => 'customer',
                'related' => $healthCheck,
            ]
        );
    }

    protected function sendTemplated($to, string $templateKey, array $replacements, array $context = []): bool
    {
        $message = $this->renderTemplate($templateKey, $replacements);

        return $this->send($to, $message, array_merge($context, [
            'template_key' => $templateKey,
            'replacements' => $replacements,
        ]));
    }

    protected function renderTemplate(string $templateKey, array $replacements): string
    {
        $definition = self::TEMPLATE_DEFINITIONS[$templateKey] ?? null;
        $template = Setting::get(self::templateSettingKey($templateKey), $definition['content'] ?? '');

        $compiled = (string) $template;
        foreach ($replacements as $key => $value) {
            $compiled = str_replace('{{' . $key . '}}', (string) $value, $compiled);
        }

        return preg_replace('/{{\s*[\w]+\s*}}/', '', $compiled) ?? $compiled;
    }

    protected function appointmentReplacements($appointment): array
    {
        $vehicle = data_get($appointment, 'vehicle');
        $serviceType = data_get($appointment, 'service_type')
            ?: data_get($appointment, 'appointment_type');
        $scheduledDate = data_get($appointment, 'scheduled_date')
            ?: data_get($appointment, 'appointment_date');
        $scheduledTime = data_get($appointment, 'appointment_time')
            ?: data_get($appointment, 'scheduled_date');
        $appointmentDateTime = $this->combineDateAndTime($scheduledDate, $scheduledTime);

        return array_merge($this->baseReplacements(), [
            'reference_number' => (string) data_get($appointment, 'reference_number'),
            'appointment_date' => $this->formatDate($scheduledDate),
            'appointment_time' => $this->formatTime($scheduledTime),
            'appointment_datetime' => $this->formatDateTime($appointmentDateTime ?: $scheduledDate ?: $scheduledTime),
            'appointment_status' => Str::title((string) data_get($appointment, 'status')),
            'service_type' => Str::title(str_replace(['_', '-'], ' ', (string) $serviceType)),
            'customer_name' => (string) data_get($appointment, 'customer.full_name'),
            'customer_phone' => (string) data_get($appointment, 'customer.phone'),
            'vehicle_registration' => $this->vehicleRegistration($vehicle),
        ]);
    }

    protected function jobCardReplacements($jobCard): array
    {
        return array_merge($this->baseReplacements(), [
            'job_number' => (string) data_get($jobCard, 'job_number'),
            'job_status' => Str::title((string) data_get($jobCard, 'status')),
            'job_total_amount' => $this->money(data_get($jobCard, 'total_amount')),
            'customer_name' => (string) data_get($jobCard, 'customer.full_name'),
            'vehicle_registration' => $this->vehicleRegistration(data_get($jobCard, 'vehicle')),
        ]);
    }

    protected function invoiceReplacements($invoice): array
    {
        return array_merge($this->baseReplacements(), [
            'invoice_number' => (string) data_get($invoice, 'invoice_number'),
            'invoice_amount' => $this->money(data_get($invoice, 'total_amount')),
            'invoice_payment_url' => route('invoice.pay', data_get($invoice, 'id')),
            'invoice_status' => Str::title((string) data_get($invoice, 'status')),
            'customer_name' => (string) data_get($invoice, 'customer.full_name'),
            'vehicle_registration' => $this->vehicleRegistration(data_get($invoice, 'vehicle')),
        ]);
    }

    protected function quoteReplacements($quote): array
    {
        return array_merge($this->baseReplacements(), [
            'quote_number' => (string) data_get($quote, 'quote_number'),
            'quote_amount' => $this->money(data_get($quote, 'total_amount')),
            'quote_valid_until' => $this->formatDate(data_get($quote, 'valid_until')),
            'customer_name' => (string) data_get($quote, 'customer.full_name'),
            'vehicle_registration' => $this->vehicleRegistration(data_get($quote, 'vehicle')),
        ]);
    }

    protected function vehicleReplacements($vehicle): array
    {
        return array_merge($this->baseReplacements(), [
            'customer_name' => (string) data_get($vehicle, 'customer.full_name'),
            'vehicle_registration' => $this->vehicleRegistration($vehicle),
        ]);
    }

    protected function baseReplacements(): array
    {
        return [
            'garage_name' => (string) Setting::get('garage_name', env('GARAGE_NAME', 'DOYEN AUTO')),
            'garage_phone' => (string) Setting::get('phone', env('GARAGE_PHONE', '')),
        ];
    }

    protected function getAdminPhone(): string
    {
        return (string) Setting::get('sms_admin_phone', env('ADMIN_PHONE', env('GARAGE_PHONE', '')));
    }

    protected function vehicleRegistration($vehicle): string
    {
        return (string) (
            data_get($vehicle, 'registration_number')
            ?: data_get($vehicle, 'registration')
            ?: ''
        );
    }

    protected function formatPhoneNumber($phone): string
    {
        $raw = trim((string) $phone);

        if (str_starts_with($raw, '+')) {
            return '+' . preg_replace('/\D+/', '', substr($raw, 1));
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($digits, '0')) {
            return '+44' . substr($digits, 1);
        }

        if (str_starts_with($digits, '44')) {
            return '+' . $digits;
        }

        return '+44' . $digits;
    }

    protected function formatDate($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('D, j M Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    protected function formatTime($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('g:i A');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    protected function formatDateTime($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('D d/m/Y g:i A');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    protected function combineDateAndTime($date, $time): ?string
    {
        if (!$date && !$time) {
            return null;
        }

        if ($date && $time) {
            return trim((string) $date . ' ' . (string) $time);
        }

        return $date ?: $time;
    }

    protected function money($value): string
    {
        if ($value === null || $value === '') {
            return '£0.00';
        }

        return '£' . number_format((float) $value, 2);
    }

    protected function logSms(array $context, string $recipient, string $normalizedRecipient, string $message, string $status, ?string $errorMessage = null, array $extraMeta = []): void
    {
        try {
            $related = $context['related'] ?? null;
            $metadata = array_merge($extraMeta, [
                'replacements' => $context['replacements'] ?? null,
            ]);

            SmsLog::create([
                'template_key' => $context['template_key'] ?? null,
                'audience' => $context['audience'] ?? 'customer',
                'recipient' => $recipient,
                'normalized_recipient' => $normalizedRecipient,
                'message' => $message,
                'status' => $status,
                'provider' => 'twilio',
                'provider_reference' => $extraMeta['provider_message_id'] ?? null,
                'error_message' => $errorMessage,
                'related_type' => $related ? $related::class : null,
                'related_id' => $related?->getKey(),
                'metadata' => $metadata,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to record SMS log', [
                'reason' => $e->getMessage(),
                'recipient' => $recipient,
                'status' => $status,
            ]);
        }
    }
}
