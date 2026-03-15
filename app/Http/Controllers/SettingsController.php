<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
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

        $bookingHours = isset($settings['booking_hours'])
            ? json_decode($settings['booking_hours'], true)
            : self::DEFAULT_HOURS;

        $closedDates = isset($settings['booking_closed_dates'])
            ? json_decode($settings['booking_closed_dates'], true)
            : [];

        return Inertia::render('Settings/Index', [
            'settings' => [
                'garage_name'          => $settings['garage_name'] ?? 'Doyen Auto Services',
                'address'              => $settings['address'] ?? '',
                'city'                 => $settings['city'] ?? '',
                'postcode'             => $settings['postcode'] ?? '',
                'phone'                => $settings['phone'] ?? '',
                'email'                => $settings['email'] ?? '',
                'website'              => $settings['website'] ?? '',
                'vat_number'           => $settings['vat_number'] ?? '',
                'vat_rate'             => $settings['vat_rate'] ?? '20',
                'default_labour_rate'  => $settings['default_labour_rate'] ?? '65.00',
                'invoice_prefix'       => $settings['invoice_prefix'] ?? 'INV',
                'invoice_terms'        => $settings['invoice_terms'] ?? 'Payment due within 30 days.',
            ],
            'bookingHours'  => $bookingHours,
            'closedDates'   => array_values($closedDates),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'garage_name'         => 'required|string|max:255',
            'address'             => 'nullable|string|max:500',
            'city'                => 'nullable|string|max:100',
            'postcode'            => 'nullable|string|max:10',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255',
            'website'             => 'nullable|url|max:255',
            'vat_number'          => 'nullable|string|max:20',
            'vat_rate'            => 'required|numeric|min:0|max:100',
            'default_labour_rate' => 'required|numeric|min:0',
            'invoice_prefix'      => 'nullable|string|max:10',
            'invoice_terms'       => 'nullable|string|max:2000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'garage');
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
            $rules["{$day}.open"]  = 'required|boolean';
            $rules["{$day}.start"] = 'required|string|max:5';
            $rules["{$day}.end"]   = 'required|string|max:5';
        }

        $validated = $request->validate($rules);

        Setting::set('booking_hours', json_encode($validated), 'booking');

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
