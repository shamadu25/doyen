<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /** Default per-day opening hours used when no settings exist yet. */
    private const DEFAULT_HOURS = [
        'monday'    => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'tuesday'   => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'wednesday' => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'thursday'  => ['open' => true,  'start' => '08:00', 'end' => '17:30'],
        'friday'    => ['open' => true,  'start' => '08:00', 'end' => '17:00'],
        'saturday'  => ['open' => true,  'start' => '09:00', 'end' => '13:00'],
        'sunday'    => ['open' => false, 'start' => '09:00', 'end' => '12:00'],
    ];

    public function index()
    {
        $settings = Setting::getAllSettings();
        $smsTemplates = SmsService::editableTemplates();
        $smsLogs = collect();
        $smsStats = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        if (Schema::hasTable('sms_logs')) {
            $smsLogs = SmsLog::query()
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn (SmsLog $log) => [
                    'id' => $log->id,
                    'template_key' => $log->template_key,
                    'audience' => $log->audience,
                    'recipient' => $log->recipient,
                    'message' => $log->message,
                    'status' => $log->status,
                    'error_message' => $log->error_message,
                    'provider' => $log->provider,
                    'created_at' => optional($log->created_at)->toIso8601String(),
                    'sent_at' => optional($log->sent_at)->toIso8601String(),
                ]);

            $smsStats = [
                'total' => SmsLog::count(),
                'sent' => SmsLog::where('status', 'sent')->count(),
                'failed' => SmsLog::where('status', 'failed')->count(),
                'skipped' => SmsLog::where('status', 'skipped')->count(),
            ];
        }

        $bookingHours = isset($settings['booking_hours'])
            ? json_decode($settings['booking_hours'], true)
            : self::DEFAULT_HOURS;

        $closedDates = isset($settings['booking_closed_dates'])
            ? json_decode($settings['booking_closed_dates'], true)
            : [];

        return Inertia::render('Settings/Index', [
            'settings' => [
                'garage_name'           => $settings['garage_name'] ?? 'Doyen Auto Services',
                'garage_address'        => $settings['address'] ?? '',
                'garage_city'           => $settings['city'] ?? '',
                'garage_postcode'       => $settings['postcode'] ?? '',
                'garage_phone'          => $settings['phone'] ?? '',
                'garage_email'          => $settings['email'] ?? '',
                'garage_website'        => $settings['website'] ?? '',
                'vat_number'            => $settings['vat_number'] ?? '',
                'vat_rate'              => $settings['vat_rate'] ?? '20',
                'mot_station_number'    => $settings['mot_station_number'] ?? '',
                'default_labour_rate'   => $settings['default_labour_rate'] ?? '65.00',
                'booking_slot_duration' => $settings['booking_slot_duration'] ?? '60',
                'invoice_prefix'        => $settings['invoice_prefix'] ?? 'INV',
                'invoice_terms'         => $settings['invoice_terms'] ?? 'Payment due within 30 days of invoice date.',
                'invoice_due_days'      => $settings['invoice_due_days'] ?? '30',
                'invoice_bank_name'     => $settings['invoice_bank_name'] ?? '',
                'invoice_sort_code'     => $settings['invoice_sort_code'] ?? '',
                'invoice_account_number'=> $settings['invoice_account_number'] ?? '',
                'invoice_account_name'  => $settings['invoice_account_name'] ?? '',
                'invoice_company_number'  => $settings['invoice_company_number'] ?? '',
                'invoice_footer_note'     => $settings['invoice_footer_note'] ?? 'Thank you for your custom.',
                'invoice_late_payment'    => $settings['invoice_late_payment'] ?? 'Interest may be charged on overdue invoices at 8% above base rate under the Late Payment of Commercial Debts (Interest) Act 1998.',
                // Invoice header – overrides garage details on the invoice PDF
                'invoice_header_name'     => $settings['invoice_header_name']     ?? '',
                'invoice_header_address'  => $settings['invoice_header_address']  ?? '',
                'invoice_header_city'     => $settings['invoice_header_city']     ?? '',
                'invoice_header_postcode' => $settings['invoice_header_postcode'] ?? '',
                'invoice_header_phone'    => $settings['invoice_header_phone']    ?? '',
                'invoice_header_email'    => $settings['invoice_header_email']    ?? '',
                'invoice_header_website'  => $settings['invoice_header_website']  ?? '',
                'sms_enabled'             => $settings['sms_enabled'] ?? '0',
                'sms_admin_phone'         => $settings['sms_admin_phone'] ?? env('ADMIN_PHONE', env('GARAGE_PHONE', '')),
                'email_notifications'   => $settings['email_notifications'] ?? '1',
            ],
            'bookingHours'  => $bookingHours,
            'closedDates'   => array_values($closedDates),
            'smsTemplates' => $smsTemplates,
            'smsLogs' => $smsLogs,
            'smsStats' => $smsStats,
            'smsConfig' => [
                'enabled' => filter_var($settings['sms_enabled'] ?? config('services.sms.enabled', false), FILTER_VALIDATE_BOOLEAN),
                'admin_phone' => $settings['sms_admin_phone'] ?? env('ADMIN_PHONE', env('GARAGE_PHONE', '')),
                'twilio_sid_configured' => !empty(config('services.twilio.sid')),
                'twilio_token_configured' => !empty(config('services.twilio.token')),
                'twilio_from' => (string) (config('services.twilio.from') ?? ''),
            ],
        ]);
    }

    public function smsTest()
    {
        $twilioSid = (string) config('services.twilio.sid');
        $smsEnabled = filter_var(Setting::get('sms_enabled', config('services.sms.enabled', false)), FILTER_VALIDATE_BOOLEAN);

        return Inertia::render('Settings/SmsTest', [
            'smsConfig' => [
                'enabled' => $smsEnabled,
                'twilio_sid_configured' => !empty(config('services.twilio.sid')),
                'twilio_token_configured' => !empty(config('services.twilio.token')),
                'twilio_from' => (string) (config('services.twilio.from') ?? ''),
                'twilio_sid_preview' => $twilioSid ? ('***' . substr($twilioSid, -6)) : '',
            ],
        ]);
    }

    public function sendTestSms(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:480',
        ]);

        if (!$smsService->isEnabled()) {
            return back()->with('error', 'SMS is not enabled/configured. Check SMS_ENABLED, TWILIO_SID and TWILIO_TOKEN in .env.');
        }

        $sent = $smsService->send($validated['phone'], $validated['message']);

        if (!$sent) {
            return back()->with('error', 'SMS send failed. Check logs and your Twilio credentials/network connectivity.');
        }

        return back()->with('success', 'Test SMS sent successfully.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'garage_name'           => 'required|string|max:255',
            'garage_address'        => 'nullable|string|max:500',
            'garage_city'           => 'nullable|string|max:100',
            'garage_postcode'       => 'nullable|string|max:10',
            'garage_phone'          => 'nullable|string|max:20',
            'garage_email'          => 'nullable|email|max:255',
            'garage_website'        => 'nullable|string|max:255',
            'vat_number'            => 'nullable|string|max:20',
            'vat_rate'              => 'required|numeric|min:0|max:100',
            'mot_station_number'    => 'nullable|string|max:20',
            'default_labour_rate'   => 'required|numeric|min:0',
            'booking_slot_duration' => 'nullable|integer|min:15|max:480',
            'invoice_prefix'          => 'nullable|string|max:10',
            'invoice_terms'           => 'nullable|string|max:2000',
            'invoice_due_days'        => 'nullable|integer|min:0|max:365',
            'invoice_bank_name'       => 'nullable|string|max:100',
            'invoice_sort_code'       => 'nullable|string|max:8',
            'invoice_account_number'  => 'nullable|string|max:8',
            'invoice_account_name'    => 'nullable|string|max:100',
            'invoice_company_number'  => 'nullable|string|max:20',
            'invoice_footer_note'      => 'nullable|string|max:500',
            'invoice_late_payment'     => 'nullable|string|max:1000',
            'invoice_header_name'      => 'nullable|string|max:255',
            'invoice_header_address'   => 'nullable|string|max:500',
            'invoice_header_city'      => 'nullable|string|max:100',
            'invoice_header_postcode'  => 'nullable|string|max:10',
            'invoice_header_phone'     => 'nullable|string|max:30',
            'invoice_header_email'     => 'nullable|email|max:255',
            'invoice_header_website'   => 'nullable|string|max:255',
            'sms_enabled'              => 'nullable',
            'sms_admin_phone'          => 'nullable|string|max:30',
            'sms_templates'            => 'nullable|array',
            'sms_templates.*'          => 'nullable|string|max:1200',
            'email_notifications'      => 'nullable',
        ]);

        // Map garage_-prefixed form keys to the plain DB storage keys
        $keyMap = [
            'garage_address'  => 'address',
            'garage_city'     => 'city',
            'garage_postcode' => 'postcode',
            'garage_phone'    => 'phone',
            'garage_email'    => 'email',
            'garage_website'  => 'website',
        ];

        foreach ($validated as $key => $value) {
            if ($key === 'sms_templates') {
                continue;
            }

            $storageKey = $keyMap[$key] ?? $key;
            $group = str_starts_with($storageKey, 'sms_') ? 'sms' : 'garage';
            Setting::set($storageKey, $value ?? '', $group);
        }

        $allowedTemplateKeys = array_keys(SmsService::templateDefinitions());
        foreach (($validated['sms_templates'] ?? []) as $templateKey => $content) {
            if (!in_array($templateKey, $allowedTemplateKeys, true)) {
                continue;
            }

            Setting::set(SmsService::templateSettingKey($templateKey), $content ?? '', 'sms');
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            Setting::set('logo_path', $path, 'garage');
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Save booking hours (per-day open/close times and enabled flag).
     * Expects JSON body: { monday: { open: bool, start: "HH:MM", end: "HH:MM" }, … }
     */
    public function updateAvailability(Request $request)
    {
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        $rules = [];
        foreach ($days as $day) {
            $rules["{$day}.open"]  = 'required';
            $rules["{$day}.start"] = 'required|string|max:5';
            $rules["{$day}.end"]   = 'required|string|max:5';
        }

        $validated = $request->validate($rules);

        // Normalise 'open' to a proper PHP boolean regardless of how it was sent
        // (boolean true/false, int 1/0, string "true"/"false"/"1"/"0")
        $hoursData = [];
        foreach ($days as $day) {
            $open = $validated[$day]['open'];
            $hoursData[$day] = [
                'open'  => filter_var($open, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$open,
                'start' => $validated[$day]['start'],
                'end'   => $validated[$day]['end'],
            ];
        }

        Setting::set('booking_hours', json_encode($hoursData), 'booking');

        return back()->with('success', 'Booking hours updated successfully.');
    }

    /** Add a single closed/blocked date (YYYY-MM-DD). */
    public function addClosedDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        $existing = json_decode(Setting::get('booking_closed_dates', '[]'), true);

        if (!in_array($request->date, $existing, true)) {
            $existing[] = $request->date;
            sort($existing);
            Setting::set('booking_closed_dates', json_encode($existing), 'booking');
        }

        return back()->with('success', 'Date blocked successfully.');
    }

    /** Remove a single closed/blocked date. */
    public function removeClosedDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $existing = json_decode(Setting::get('booking_closed_dates', '[]'), true);
        $existing = array_values(array_filter($existing, fn($d) => $d !== $request->date));
        Setting::set('booking_closed_dates', json_encode($existing), 'booking');

        return back()->with('success', 'Date unblocked successfully.');
    }
}
