<?php

namespace App\Http\Controllers;

use App\Models\WebsiteContent;
use Inertia\Inertia;

class AboutController extends Controller
{
    private function aboutDefaults(): array
    {
        return [
            'hero_badge'             => 'About Doyen Auto Services',
            'hero_headline'          => 'Glasgow-based Precision',
            'hero_headline_gradient' => 'Vehicle Diagnostics',
            'hero_subline'           => 'Glasgow-based precision vehicle diagnostics and technical solutions centre. Serving vehicle owners and trade professionals across Scotland.',
            'stat_1_value'           => '16+',
            'stat_1_label'           => 'Years Experience',
            'stat_2_value'           => 'Glasgow',
            'stat_2_label'           => 'Based in Rutherglen',
            'stat_3_value'           => 'ECU',
            'stat_3_label'           => 'Diagnostics Specialist',
            'stat_4_value'           => 'Scotland',
            'stat_4_label'           => 'Wide Coverage',
            'who_intro_1'            => 'Doyen Auto Services is a Glasgow-based advanced vehicle electrical diagnostics and repair specialist serving customers across Glasgow and the wider Scotland region.',
            'who_intro_2'            => 'Founded by Ganiyu Ajayi, who brings over 16 years of hands-on automotive industry experience, the business was built on a single principle: deliver honest, precise, dealer-quality diagnostics and repairs at a fair price.',
            'who_intro_3'            => 'We specialise in advanced electrical fault tracing, ECU diagnostics and repair, airbag and SRS system reset, immobiliser and key programming — services that demand deep technical knowledge and professional-grade equipment.',
            'founder_name'           => 'Ganiyu Ajayi',
            'founder_title'          => 'Founder & Lead Technician',
            'founder_experience'     => '16+ Years Automotive Industry Experience',
            'founder_quote'          => "My goal has always been simple — give every customer the same level of diagnostics and care that a main dealer would provide, but with the honesty and personal service that only an independent specialist can offer.",
            'values'                 => json_encode([
                ['icon' => '🔬', 'title' => 'Precision Diagnostics',   'desc' => 'We use dealer-level diagnostic equipment to pinpoint faults accurately — no guesswork, no unnecessary parts replacement.'],
                ['icon' => '🤝', 'title' => 'Honest & Transparent',    'desc' => 'Straightforward pricing with no hidden charges. We explain every fault clearly and only proceed with your full approval.'],
                ['icon' => '⚡', 'title' => 'Specialist Expertise',    'desc' => "Advanced vehicle electrical diagnostics, ECU repair, airbag SRS reset and fault tracing — skills most general garages simply don't offer."],
                ['icon' => '🌍', 'title' => 'Serving All of Scotland', 'desc' => 'Based in Rutherglen, Glasgow, we serve customers across the wider Scotland region — both private vehicle owners and trade professionals.'],
            ]),
            'cta_headline'           => 'Ready to Book Your Diagnostic?',
            'cta_subline'            => 'Get dealer-level precision without the dealer price. Book online in minutes or call us now.',
        ];
    }

    public function index()
    {
        $stored = WebsiteContent::getSection('about');
        $hours  = WebsiteContent::getSection('hours');
        $about  = array_merge($this->aboutDefaults(), $stored);

        return Inertia::render('About', [
            'hours' => $hours,
            'about' => $about,
        ]);
    }
}
