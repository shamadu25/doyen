<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WebsiteContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebsiteController extends Controller
{
    /**
     * Default content for each section (used when DB has nothing yet).
     */
    private function defaults(): array
    {
        return [
        'hero' => [
            'badge_text'         => 'Advanced Vehicle Electrical Diagnostics & Repair',
            'headline'           => 'Precision Vehicle Diagnostics',
            'headline_gradient'  => '& Technical Solutions Centre',
            'subline'            => 'Advanced vehicle diagnostics, ECU repair, airbag SRS reset and electrical fault tracing. 16+ years of automotive experience. Glasgow-based, serving Scotland.',
            'cta_primary'        => 'Book Your Appointment',
            'cta_secondary'      => 'Get a Quote',
            'stat_1_value'       => '16+',
            'stat_1_label'       => 'Years Experience',
            'stat_2_value'       => '2023',
            'stat_2_label'       => 'Est. Glasgow',
            'stat_3_value'       => 'ECU',
            'stat_3_label'       => 'Specialist',
        ],
        'trust' => [
            'items' => json_encode([
                ['icon' => 'tool',    'title' => 'Dealer-Level Equipment',    'subtitle' => 'Professional-grade diagnostic tools'],
                ['icon' => 'clock',   'title' => '16+ Years Experience',      'subtitle' => 'Expert automotive knowledge'],
                ['icon' => 'cpu',     'title' => 'ECU & Airbag Specialist',   'subtitle' => 'Advanced system programming'],
                ['icon' => 'bolt',    'title' => 'Fast Turnaround',           'subtitle' => 'Minimal disruption to your day'],
            ]),
        ],
        'testimonials' => [
            'items' => json_encode([
                [
                    'name'     => 'Mark D.',
                    'location' => 'Glasgow',
                    'service'  => 'ECU Repair',
                    'text'     => "Took my BMW 3 Series in with an ECU fault that two other garages couldn't fix. Doyen diagnosed it within the hour, repaired and recoded the ECU the same day. Saved me over £900 vs main dealer. Absolutely fantastic — these guys genuinely know their stuff.",
                    'rating'   => 5,
                ],
                [
                    'name'     => 'Tariq H.',
                    'location' => 'Rutherglen',
                    'service'  => 'Airbag Reset',
                    'text'     => 'Airbag warning light had been on for months after a minor bump. Doyen cleared the crash data, reset the SRS module and sorted a faulty seatbelt pretensioner — all in under 2 hours. Honest pricing, job done properly. Highly recommend.',
                    'rating'   => 5,
                ],
                [
                    'name'     => 'Carolyn P.',
                    'location' => 'Cambuslang',
                    'service'  => 'Full Diagnostics',
                    'text'     => "My Audi Q5 had a persistent AdBlue warning and the dealer wanted £1,400. Doyen ran a full diagnostic, replaced the NOx sensor and reset the system for a fraction of the price. Really professional, kept me informed throughout. Won't go anywhere else now.",
                    'rating'   => 5,
                ],
            ]),
        ],
        'process' => [
            'items' => json_encode([
                ['step' => '1', 'icon' => '📅', 'title' => 'Book Online',    'desc' => 'Simple 3-step booking process. Choose your service, select a time that suits you.'],
                ['step' => '2', 'icon' => '🔍', 'title' => 'We Inspect',     'desc' => 'Our certified technicians perform a thorough inspection using the latest diagnostic equipment.'],
                ['step' => '3', 'icon' => '💰', 'title' => 'Get Quote',      'desc' => 'Receive a detailed quote with no hidden charges. We only proceed with your approval.'],
                ['step' => '4', 'icon' => '✅', 'title' => 'Quality Work',   'desc' => 'Expert repairs using genuine parts with warranty. Your vehicle is our priority.'],
            ]),
        ],
        'hours' => [
            'topbar'    => 'Mon-Fri: 8:00-17:30 | Sat: 8:00-12:30',
            'mon_fri'   => 'Mon–Fri: 9:00 am – 6:00 pm',
            'saturday'  => 'Saturday: 10:00 am – 3:00 pm',
            'sunday'    => 'Sunday: Closed',
            'note'      => '',
        ],
        'seo' => [
            'meta_title'       => 'Doyen Auto Services | Glasgow Auto Specialists',
            'meta_description' => 'Expert vehicle diagnostics, ECU repair, MOT testing, and servicing in Glasgow. Dealer-level equipment. Certified technicians. No hidden charges.',
            'og_image'         => '',
        ],
        'social' => [
            'facebook_url'   => '',
            'instagram_url'  => '',
            'tiktok_url'     => '',
        ],
        'contact' => [
            'address'         => '59 Southcroft Rd, Rutherglen',
            'city'            => 'Glasgow',
            'postcode'        => 'G73 1UG',
            'phone'           => '+44 7760 926245',
            'email'           => 'info@doyenautos.co.uk',
            'whatsapp_number' => '',
        ],
        'about' => [
            'hero_badge'           => 'About Doyen Auto Services',
            'hero_headline'        => 'Glasgow-based Precision',
            'hero_headline_gradient' => 'Vehicle Diagnostics',
            'hero_subline'         => 'Glasgow-based precision vehicle diagnostics and technical solutions centre. Serving vehicle owners and trade professionals across Scotland.',
            'stat_1_value'         => '16+',
            'stat_1_label'         => 'Years Experience',
            'stat_2_value'         => 'Glasgow',
            'stat_2_label'         => 'Based in Rutherglen',
            'stat_3_value'         => 'ECU',
            'stat_3_label'         => 'Diagnostics Specialist',
            'stat_4_value'         => 'Scotland',
            'stat_4_label'         => 'Wide Coverage',
            'who_intro_1'          => 'Doyen Auto Services is a Glasgow-based advanced vehicle electrical diagnostics and repair specialist serving customers across Glasgow and the wider Scotland region.',
            'who_intro_2'          => 'Founded by Ganiyu Ajayi, who brings over 16 years of hands-on automotive industry experience, the business was built on a single principle: deliver honest, precise, dealer-quality diagnostics and repairs at a fair price.',
            'who_intro_3'          => 'We specialise in advanced electrical fault tracing, ECU diagnostics and repair, airbag and SRS system reset, immobiliser and key programming — services that demand deep technical knowledge and professional-grade equipment.',
            'founder_name'         => 'Ganiyu Ajayi',
            'founder_title'        => 'Founder & Lead Technician',
            'founder_experience'   => '16+ Years Automotive Industry Experience',
            'founder_quote'        => 'My goal has always been simple — give every customer the same level of diagnostics and care that a main dealer would provide, but with the honesty and personal service that only an independent specialist can offer.',
            'values'               => json_encode([
                ['icon' => '🔬', 'title' => 'Precision Diagnostics',  'desc' => 'We use dealer-level diagnostic equipment to pinpoint faults accurately — no guesswork, no unnecessary parts replacement.'],
                ['icon' => '🤝', 'title' => 'Honest & Transparent',   'desc' => 'Straightforward pricing with no hidden charges. We explain every fault clearly and only proceed with your full approval.'],
                ['icon' => '⚡', 'title' => 'Specialist Expertise',   'desc' => "Advanced vehicle electrical diagnostics, ECU repair, airbag SRS reset and fault tracing — skills most general garages simply don't offer."],
                ['icon' => '🌍', 'title' => 'Serving All of Scotland','desc' => 'Based in Rutherglen, Glasgow, we serve customers across the wider Scotland region — both private vehicle owners and trade professionals.'],
            ]),
            'cta_headline'         => 'Ready to Book Your Diagnostic?',
            'cta_subline'          => 'Get dealer-level precision without the dealer price. Book online in minutes or call us now.',
        ],
        ];
    }

    public function index()
    {
        $stored = WebsiteContent::getAllSections();

        // Merge defaults with DB values so all keys are present
        $content = [];
        foreach ($this->defaults() as $section => $keys) {
            $content[$section] = array_merge($keys, $stored[$section] ?? []);
        }

        // Contact section is stored in the Setting model (shared globally via garageSettings)
        $settingsAll = Setting::getAllSettings();
        $contactDefaults = $this->defaults()['contact'];
        $content['contact'] = [
            'address'         => $settingsAll['address']         ?? $settingsAll['garage_address']  ?? $contactDefaults['address'],
            'city'            => $settingsAll['city']            ?? $settingsAll['garage_city']     ?? $contactDefaults['city'],
            'postcode'        => $settingsAll['postcode']        ?? $settingsAll['garage_postcode'] ?? $contactDefaults['postcode'],
            'phone'           => $settingsAll['phone']           ?? $settingsAll['garage_phone']    ?? $contactDefaults['phone'],
            'email'           => $settingsAll['email']           ?? $settingsAll['garage_email']    ?? $contactDefaults['email'],
            'whatsapp_number' => $settingsAll['whatsapp_number'] ?? '',
        ];

        return Inertia::render('Website/Index', [
            'content' => $content,
        ]);
    }

    public function update(Request $request, string $section)
    {
        if (!array_key_exists($section, $this->defaults())) {
            abort(404);
        }

        // Contact section is stored in the Setting model so it propagates site-wide
        if ($section === 'contact') {
            $validated = $request->validate([
                'address'         => 'nullable|string|max:255',
                'city'            => 'nullable|string|max:100',
                'postcode'        => 'nullable|string|max:20',
                'phone'           => 'nullable|string|max:30',
                'email'           => 'nullable|email|max:255',
                'whatsapp_number' => 'nullable|string|max:30',
            ]);

            foreach ($validated as $key => $value) {
                Setting::set($key, $value ?? '', 'garage');
            }

            return back()->with('success', 'Contact details updated successfully.');
        }

        $data = $request->all();

        // Save each field submitted
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                WebsiteContent::set($section, $key, json_encode($value), 'json');
            } else {
                WebsiteContent::set($section, $key, $value ?? '', 'text');
            }
        }

        return back()->with('success', ucfirst($section) . ' section updated successfully.');
    }
}
