<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\WebsiteContent;
use Inertia\Inertia;

class LandingController extends Controller
{
    private function contentDefaults(): array
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
            'stat_2_value'       => 'AKL',
            'stat_2_label'       => 'All-Key-Lost Solution',
            'stat_3_value'       => 'ECU',
            'stat_3_label'       => 'Specialist',
        ],
        'trust' => [
            'items' => '[{"icon":"tool","title":"Dealer-Level Equipment","subtitle":"Professional-grade diagnostic tools"},{"icon":"clock","title":"16+ Years Experience","subtitle":"Expert automotive knowledge"},{"icon":"cpu","title":"ECU & Airbag Specialist","subtitle":"Advanced system programming"},{"icon":"bolt","title":"Fast Turnaround","subtitle":"Minimal disruption to your day"}]',
        ],
        'testimonials' => [
            'items' => '[{"name":"Mark D.","location":"Glasgow","service":"ECU Repair","text":"Took my BMW 3 Series in with an ECU fault that two other garages couldn\'t fix. Doyen diagnosed it within the hour, repaired and recoded the ECU the same day. Saved me over £900 vs main dealer. Absolutely fantastic — these guys genuinely know their stuff.","rating":5},{"name":"Tariq H.","location":"Rutherglen","service":"Airbag Reset","text":"Airbag warning light had been on for months after a minor bump. Doyen cleared the crash data, reset the SRS module and sorted a faulty seatbelt pretensioner — all in under 2 hours. Honest pricing, job done properly. Highly recommend.","rating":5},{"name":"Carolyn P.","location":"Cambuslang","service":"Full Diagnostics","text":"My Audi Q5 had a persistent AdBlue warning and the dealer wanted £1,400. Doyen ran a full diagnostic, replaced the NOx sensor and reset the system for a fraction of the price. Really professional, kept me informed throughout. Won\'t go anywhere else now.","rating":5}]',
        ],
        'process' => [
            'items' => '[{"step":"1","icon":"📅","title":"Book Online","desc":"Simple 3-step booking process. Choose your service, select a time that suits you."},{"step":"2","icon":"🔍","title":"We Inspect","desc":"Our certified technicians perform a thorough inspection using the latest diagnostic equipment."},{"step":"3","icon":"💰","title":"Get Quote","desc":"Receive a detailed quote with no hidden charges. We only proceed with your approval."},{"step":"4","icon":"✅","title":"Quality Work","desc":"Expert repairs using genuine parts with warranty. Your vehicle is our priority."}]',
        ],
        'hours' => [
            'topbar'   => 'Mon-Fri: 8:00-17:30 | Sat: 8:00-12:30',
            'mon_fri'  => 'Mon–Fri: 9:00 am – 6:00 pm',
            'saturday' => 'Saturday: 10:00 am – 3:00 pm',
            'sunday'   => 'Sunday: Closed',
            'note'     => '',
        ],
        'seo' => [
            'meta_title'       => 'Doyen Auto Services | Glasgow Auto Specialists',
            'meta_description' => 'Expert vehicle diagnostics, ECU repair, MOT testing, and servicing in Glasgow. Dealer-level equipment. Certified technicians. No hidden charges.',
            'og_image'         => '',
        ],
        'social' => [
            'facebook_url'  => '',
            'instagram_url' => '',
            'tiktok_url'    => '',
        ],
        ];
    }

    private function getWebsiteServices()
    {
        return Service::websiteVisible()
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->map(function ($s) {
                    return [
                        'id'               => $s->id,
                        'name'             => $s->name,
                        'icon'             => $s->icon,
                        'category'         => $s->category,
                        'description'      => $s->website_description ?: $s->description,
                        'price'            => $s->default_price,
                        'duration_minutes' => $s->estimated_duration_minutes,
                        'requires_booking' => $s->requires_booking,
                    ];
                })->values();
            });
    }

    private function getWebsiteContent(): array
    {
        $stored = WebsiteContent::getAllSections();
        $websiteContent = [];
        foreach ($this->contentDefaults() as $section => $defaults) {
            $websiteContent[$section] = array_merge($defaults, $stored[$section] ?? []);
        }

        return $websiteContent;
    }

    public function index()
    {
        $services = $this->getWebsiteServices();
        $websiteContent = $this->getWebsiteContent();

        return Inertia::render('Landing', [
            'websiteServices' => $services,
            'websiteContent'  => $websiteContent,
        ]);
    }

    public function servicesPage()
    {
        return Inertia::render('ServicesPublic', [
            'websiteServices' => $this->getWebsiteServices(),
            'websiteContent' => $this->getWebsiteContent(),
        ]);
    }

    public function contactPage()
    {
        return Inertia::render('ContactPublic', [
            'websiteContent' => $this->getWebsiteContent(),
        ]);
    }

    public function testimonialsPage()
    {
        return Inertia::render('TestimonialsPublic', [
            'websiteContent' => $this->getWebsiteContent(),
        ]);
    }
}
