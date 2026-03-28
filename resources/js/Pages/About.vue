<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    hours?: Record<string, string>
    about?: Record<string, string>
}>()

const garage = computed(() => (usePage().props as any).garageSettings ?? {})
const garageName     = computed(() => garage.value.garage_name || 'Doyen Auto Services')
const garagePhone    = computed(() => garage.value.phone    || '+44 7760 926245')
const garageEmail    = computed(() => garage.value.email    || 'info@doyenautos.co.uk')
const garageAddress  = computed(() => garage.value.address  || '59 Southcroft Rd')
const garageCity     = computed(() => garage.value.city     || 'Rutherglen, Glasgow')
const garagePostcode = computed(() => garage.value.postcode || 'G73 1UG')

const garageTelHref  = computed(() => 'tel:+44' + garagePhone.value.replace(/\s/g, '').replace(/^0/, ''))
const garageMailHref = computed(() => 'mailto:' + garageEmail.value)
const whatsappHref   = computed(() => {
    const num = garage.value.whatsapp_number || garagePhone.value
    const raw = num.replace(/\s/g, '').replace(/^\+/, '').replace(/^0/, '44')
    return `https://wa.me/${raw}?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20your%20services.`
})

const hoursMonFri  = computed(() => props.hours?.mon_fri  || 'Mon–Fri: 9:00 am – 6:00 pm')
const hoursSat     = computed(() => props.hours?.saturday || 'Saturday: 10:00 am – 3:00 pm')
const hoursSun     = computed(() => props.hours?.sunday   || 'Sunday: Closed')
const hoursTopbar  = computed(() => props.hours?.topbar   || 'Mon-Fri: 8:00-17:30 | Sat: 8:00-12:30')

// About content — DB driven with fallbacks
const a = computed(() => props.about ?? {})
const heroBadge            = computed(() => a.value.hero_badge            || 'About Doyen Auto Services')
const heroHeadline         = computed(() => a.value.hero_headline         || 'Glasgow-based Precision')
const heroHeadlineGradient = computed(() => a.value.hero_headline_gradient || 'Vehicle Diagnostics')
const heroSubline          = computed(() => a.value.hero_subline          || 'Glasgow-based precision vehicle diagnostics and technical solutions centre.')
const stat1Value           = computed(() => a.value.stat_1_value          || '16+')
const stat1Label           = computed(() => a.value.stat_1_label          || 'Years Experience')
const stat2Value           = computed(() => a.value.stat_2_value          || 'Glasgow')
const stat2Label           = computed(() => a.value.stat_2_label          || 'Based in Rutherglen')
const stat3Value           = computed(() => a.value.stat_3_value          || 'ECU')
const stat3Label           = computed(() => a.value.stat_3_label          || 'Diagnostics Specialist')
const stat4Value           = computed(() => a.value.stat_4_value          || 'Scotland')
const stat4Label           = computed(() => a.value.stat_4_label          || 'Wide Coverage')
const whoIntro1            = computed(() => a.value.who_intro_1           || '')
const whoIntro2            = computed(() => a.value.who_intro_2           || '')
const whoIntro3            = computed(() => a.value.who_intro_3           || '')
const founderName          = computed(() => a.value.founder_name          || 'Ganiyu Ajayi')
const founderTitle         = computed(() => a.value.founder_title         || 'Founder & Lead Technician')
const founderExperience    = computed(() => a.value.founder_experience    || '16+ Years Automotive Industry Experience')
const founderQuote         = computed(() => a.value.founder_quote         || '')
const ctaHeadline          = computed(() => a.value.cta_headline          || 'Ready to Book Your Diagnostic?')
const ctaSubline           = computed(() => a.value.cta_subline           || 'Get dealer-level precision without the dealer price.')

const values = computed(() => {
    try {
        return JSON.parse(a.value.values || '[]')
    } catch {
        return [
            { icon: '🔬', title: 'Precision Diagnostics',   desc: 'We use dealer-level diagnostic equipment to pinpoint faults accurately.' },
            { icon: '🤝', title: 'Honest & Transparent',    desc: 'Straightforward pricing with no hidden charges.' },
            { icon: '⚡', title: 'Specialist Expertise',    desc: 'Advanced vehicle electrical diagnostics, ECU repair, airbag SRS reset.' },
            { icon: '🌍', title: 'Serving All of Scotland', desc: 'Based in Rutherglen, Glasgow, serving customers across Scotland.' },
        ]
    }
})

const services = [
    { icon: '💻', name: 'Advanced Diagnostics & ECU Services' },
    { icon: '🛡️', name: 'Airbag & SRS Safety Systems' },
    { icon: '⚡', name: 'ECU Remapping & Performance Tuning' },
    { icon: '🔑', name: 'Immobiliser & Key Programming' },
    { icon: '🔧', name: 'General Vehicle Repairs' },
    { icon: '📋', name: 'MOT Testing & Servicing' },
    { icon: '🚗', name: 'Air Conditioning Services' },
    { icon: '🔩', name: 'Tyres, Brakes & Suspension' },
]

const aboutTitle       = 'About Doyen Auto Services | Glasgow Vehicle Diagnostic Specialists'
const aboutDescription = computed(() => `${garageName.value} is a Glasgow-based advanced vehicle diagnostics and repair specialist. Founded by ${founderName.value} with over 16 years of experience. Serving Rutherglen, Glasgow and across Scotland.`)
const canonicalUrl     = 'https://doyenautos.co.uk/about'

const aboutSchema = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'AboutPage',
    name: aboutTitle,
    description: aboutDescription.value,
    url: canonicalUrl,
    breadcrumb: {
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://doyenautos.co.uk' },
            { '@type': 'ListItem', position: 2, name: 'About Us', item: canonicalUrl },
        ],
    },
    mainEntity: {
        '@type': 'AutoRepair',
        name: garageName.value,
        foundingDate: '2008',
        founder: {
            '@type': 'Person',
            name: founderName.value,
            jobTitle: founderTitle.value,
        },
        address: {
            '@type': 'PostalAddress',
            streetAddress: garageAddress.value,
            addressLocality: garageCity.value,
            postalCode: garagePostcode.value,
            addressCountry: 'GB',
        },
        telephone: garagePhone.value,
        email: garageEmail.value,
        url: 'https://doyenautos.co.uk',
    },
}))
</script>

<template>
    <Head :title="aboutTitle">
        <meta name="description" :content="aboutDescription" />
        <meta name="keywords" content="about Doyen Auto Services, Glasgow garage, vehicle diagnostics specialist, ECU repair Glasgow, auto repair Rutherglen" />
        <link rel="canonical" :href="canonicalUrl" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:title" :content="aboutTitle" />
        <meta property="og:description" :content="aboutDescription" />
        <meta property="og:image" content="/images/og-image.jpg" />
        <meta property="og:locale" content="en_GB" />
        <meta property="og:site_name" content="Doyen Auto Services" />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="aboutTitle" />
        <meta name="twitter:description" :content="aboutDescription" />
        <meta name="twitter:image" content="/images/og-image.jpg" />

        <!-- JSON-LD -->
        <script type="application/ld+json" v-html="aboutSchema"></script>
    </Head>

    <div class="min-h-screen bg-white">

        <!-- Top Bar -->
        <div class="bg-gradient-to-r from-navy-950 to-navy-900 text-white py-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between text-xs sm:text-sm">
                    <div class="flex items-center gap-4">
                        <a :href="garageTelHref" class="flex items-center gap-2 hover:text-electric-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="hidden sm:inline">{{ garagePhone }}</span>
                        </a>
                        <span class="text-gray-400 hidden sm:inline">|</span>
                        <a :href="garageMailHref" class="hidden md:flex items-center gap-2 hover:text-electric-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>{{ garageEmail }}</span>
                        </a>
                        <span class="text-gray-400 hidden md:inline">|</span>
                        <div class="flex items-center gap-2 hidden md:flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ hoursTopbar }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('/customer/login')" class="hover:text-electric-400 transition hidden sm:inline">
                            Customer Portal
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <Link :href="route('/')" class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-electric-600 to-electric-700 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-xl">D</span>
                        </div>
                        <div>
                            <span class="text-2xl font-bold bg-gradient-to-r from-electric-600 to-electric-700 bg-clip-text text-transparent">{{ garageName }}</span>
                            <div class="text-xs text-gray-500 font-medium">Auto Electrical and Diagnostic Specialist</div>
                        </div>
                    </Link>
                    <div class="hidden md:flex items-center gap-8">
                        <Link :href="route('/')" class="text-gray-700 hover:text-electric-600 font-medium transition">Home</Link>
                        <Link :href="route('/our-services')" class="text-gray-700 hover:text-electric-600 font-medium transition">Services</Link>
                        <Link :href="route('/about')" class="text-electric-600 font-semibold border-b-2 border-electric-600 pb-0.5">About</Link>
                        <Link :href="route('/testimonials')" class="text-gray-700 hover:text-electric-600 font-medium transition">Reviews</Link>
                        <Link :href="route('/contact')" class="text-gray-700 hover:text-electric-600 font-medium transition">Contact</Link>
                        <Link :href="route('/customer/login')" class="inline-flex items-center px-4 py-2 border border-electric-600 text-electric-600 font-medium rounded-lg hover:bg-electric-50 transition text-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            My Account
                        </Link>
                        <Link :href="route('/book-online')" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-electric-600 to-electric-700 text-white font-semibold rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Book Online
                        </Link>
                    </div>
                    <!-- Mobile -->
                    <div class="flex md:hidden items-center gap-2">
                        <Link :href="route('/customer/login')" class="inline-flex items-center px-3 py-2 border border-electric-600 text-electric-600 font-medium rounded-lg hover:bg-electric-50 transition text-xs">
                            My Account
                        </Link>
                        <Link :href="route('/book-online')" class="inline-flex items-center px-3 py-2 bg-electric-600 text-white font-semibold rounded-lg transition text-xs">
                            Book
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero / Page Header -->
        <section class="relative overflow-hidden bg-gradient-to-br from-navy-950 via-navy-800 to-navy-950 py-20 lg:py-28">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
                <div class="inline-flex items-center gap-2 bg-electric-600/30 backdrop-blur-sm px-4 py-2 rounded-full mb-6 border border-electric-400/30">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-semibold">{{ heroBadge }}</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                    {{ heroHeadline }}
                    <span class="block bg-gradient-to-r from-electric-400 to-electric-200 bg-clip-text text-transparent">{{ heroHeadlineGradient }}</span>
                </h1>
                <p class="text-xl text-electric-200 max-w-3xl mx-auto leading-relaxed">
                    {{ heroSubline }}
                </p>
                <div class="flex flex-wrap justify-center gap-4 mt-10">
                    <Link :href="route('/book-online')" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-electric-600 to-electric-700 text-white font-bold rounded-xl hover:shadow-2xl transform hover:scale-105 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Book an Appointment
                    </Link>
                    <a :href="garageTelHref" class="inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-xl border-2 border-white/30 hover:bg-white/20 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Call Us Now
                    </a>
                </div>
            </div>
        </section>

        <!-- Quick Stats -->
        <section class="bg-electric-600 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
                    <div>
                        <div class="text-4xl font-bold mb-1">{{ stat1Value }}</div>
                        <div class="text-electric-100 text-sm font-medium">{{ stat1Label }}</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold mb-1">{{ stat2Value }}</div>
                        <div class="text-electric-100 text-sm font-medium">{{ stat2Label }}</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold mb-1">{{ stat3Value }}</div>
                        <div class="text-electric-100 text-sm font-medium">{{ stat3Label }}</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold mb-1">{{ stat4Value }}</div>
                        <div class="text-electric-100 text-sm font-medium">{{ stat4Label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Who We Are -->
        <section class="py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

                    <!-- Text -->
                    <div>
                        <div class="inline-block px-4 py-2 bg-electric-100 rounded-full text-electric-700 font-semibold text-sm mb-6">WHO WE ARE</div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-6 leading-tight">
                            Advanced Vehicle Electrical<br>
                            <span class="text-electric-600">Diagnostics &amp; Repair</span>
                        </h2>
                        <div class="space-y-4 text-gray-600 text-lg leading-relaxed">
                            <p>{{ whoIntro1 }}</p>
                            <p>{{ whoIntro2 }}</p>
                            <p>{{ whoIntro3 }}</p>
                        </div>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <Link :href="route('/book-online')" class="inline-flex items-center px-6 py-3 bg-electric-600 text-white font-semibold rounded-lg hover:bg-electric-700 transition">
                                Book a Diagnostic
                            </Link>
                            <a :href="garageTelHref" class="inline-flex items-center px-6 py-3 border-2 border-electric-600 text-electric-600 font-semibold rounded-lg hover:bg-electric-50 transition">
                                {{ garagePhone }}
                            </a>
                        </div>
                    </div>

                    <!-- Founder Card -->
                    <div class="relative">
                        <div class="bg-gradient-to-br from-navy-950 to-navy-800 rounded-3xl p-10 text-white shadow-2xl">
                            <div class="w-20 h-20 bg-gradient-to-br from-electric-500 to-electric-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <blockquote class="text-electric-200 text-lg italic leading-relaxed mb-6">
                                "{{ founderQuote }}"
                            </blockquote>
                            <div class="border-t border-white/20 pt-6">
                                <div class="font-bold text-xl text-white">{{ founderName }}</div>
                                <div class="text-electric-400 font-medium text-sm mt-1">{{ founderTitle }}</div>
                                <div class="text-gray-400 text-sm mt-1">{{ founderExperience }}</div>
                            </div>
                            <!-- Trust badges -->
                            <div class="mt-6 grid grid-cols-2 gap-4">
                                <div class="bg-white/10 rounded-xl p-3 text-center">
                                    <div class="text-2xl font-bold text-white">16+</div>
                                    <div class="text-xs text-electric-300">Years Experience</div>
                                </div>
                                <div class="bg-white/10 rounded-xl p-3 text-center">
                                    <div class="text-2xl font-bold text-white">ECU</div>
                                    <div class="text-xs text-electric-300">Specialist</div>
                                </div>
                            </div>
                        </div>
                        <!-- Decorative accent -->
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-electric-600/20 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-electric-400/10 rounded-full blur-3xl"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Values -->
        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <div class="inline-block px-4 py-2 bg-electric-100 rounded-full text-electric-700 font-semibold text-sm mb-4">WHY CHOOSE US</div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Built on Trust &amp; Technical Excellence</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Every vehicle that comes through our doors receives the same commitment to accuracy, transparency and quality.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="value in values" :key="value.title"
                         class="bg-white rounded-2xl p-8 shadow-md hover:shadow-xl transition-all text-center">
                        <div class="text-4xl mb-4">{{ value.icon }}</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3">{{ value.title }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ value.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Overview -->
        <section class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <div class="inline-block px-4 py-2 bg-electric-100 rounded-full text-electric-700 font-semibold text-sm mb-4">WHAT WE DO</div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Services</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        From advanced ECU diagnostics to routine maintenance — we cover the full spectrum.
                    </p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                    <div v-for="svc in services" :key="svc.name"
                         class="flex flex-col items-center gap-3 bg-gray-50 rounded-2xl p-6 text-center hover:bg-electric-50 hover:shadow-md transition-all">
                        <span class="text-3xl">{{ svc.icon }}</span>
                        <span class="text-sm font-semibold text-gray-800">{{ svc.name }}</span>
                    </div>
                </div>
                <div class="text-center">
                    <Link :href="route('/our-services')" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-electric-600 to-electric-700 text-white font-bold rounded-xl hover:shadow-xl transform hover:scale-105 transition-all">
                        View All Services
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Location & Contact -->
        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <div class="inline-block px-4 py-2 bg-electric-100 rounded-full text-electric-700 font-semibold text-sm mb-4">FIND US</div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Visit Us</h2>
                    <p class="text-lg text-gray-600">Conveniently located in Rutherglen, serving all of Glasgow and Scotland</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition-all">
                        <div class="w-16 h-16 bg-electric-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-electric-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 mb-2">Address</h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ garageAddress }}<br>{{ garageCity }}<br>{{ garagePostcode }}
                        </p>
                        <a :href="'https://maps.google.com/?q=' + encodeURIComponent(garageAddress + ', ' + garageCity + ' ' + garagePostcode)"
                           target="_blank" rel="noopener"
                           class="inline-block mt-3 text-sm text-electric-600 hover:text-electric-700 font-medium transition">
                            Get Directions →
                        </a>
                    </div>

                    <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition-all">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 mb-2">Phone &amp; WhatsApp</h3>
                        <p class="text-gray-600 mb-2">Call or message us</p>
                        <a :href="garageTelHref" class="text-electric-600 font-bold text-lg hover:text-electric-700 transition block">{{ garagePhone }}</a>
                        <a :href="whatsappHref" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 mt-2 text-sm text-green-600 hover:text-green-700 font-medium transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp Us
                        </a>
                    </div>

                    <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition-all">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 mb-2">Opening Hours</h3>
                        <p class="text-gray-600 leading-relaxed">
                            <strong>{{ hoursMonFri }}</strong><br>
                            <strong>{{ hoursSat }}</strong><br>
                            <strong>{{ hoursSun }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="py-16 bg-gradient-to-r from-navy-950 to-navy-800">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">{{ ctaHeadline }}</h2>
                <p class="text-electric-200 text-lg mb-8 leading-relaxed">
                    {{ ctaSubline }}
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <Link :href="route('/book-online')" class="inline-flex items-center px-8 py-4 bg-electric-600 text-white font-bold rounded-xl hover:bg-electric-700 hover:shadow-2xl transform hover:scale-105 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Book Online Now
                    </Link>
                    <a :href="garageTelHref" class="inline-flex items-center px-8 py-4 bg-white/10 text-white font-bold rounded-xl border-2 border-white/30 hover:bg-white/20 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ garagePhone }}
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gradient-to-br from-navy-950 to-navy-900 text-gray-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-electric-600 to-electric-700 rounded-xl flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-xl">D</span>
                            </div>
                            <div>
                                <span class="text-2xl font-bold text-white">{{ garageName }}</span>
                                <div class="text-xs text-gray-400">Auto Electrical and Diagnostic Specialist</div>
                            </div>
                        </div>
                        <p class="text-gray-400 mb-4 leading-relaxed">
                            Glasgow-based precision vehicle diagnostics and technical solutions centre.
                            Serving vehicle owners and trade professionals across Scotland.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-bold text-white mb-4 text-lg">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><Link :href="route('/')" class="hover:text-electric-400 transition">Home</Link></li>
                            <li><Link :href="route('/about')" class="hover:text-electric-400 transition">About Us</Link></li>
                            <li><Link :href="route('/our-services')" class="hover:text-electric-400 transition">Our Services</Link></li>
                            <li><Link :href="route('/book-online')" class="hover:text-electric-400 transition">Book Online</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold text-white mb-4 text-lg">Contact</h4>
                        <ul class="space-y-2 text-sm">
                            <li>{{ garageAddress }}, {{ garageCity }}</li>
                            <li>{{ garagePostcode }}</li>
                            <li><a :href="garageTelHref" class="hover:text-electric-400 transition">{{ garagePhone }}</a></li>
                            <li><a :href="garageMailHref" class="hover:text-electric-400 transition break-all">{{ garageEmail }}</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-700 pt-8 text-center">
                    <p class="text-sm text-gray-400">
                        &copy; {{ new Date().getFullYear() }} {{ garageName }}. All rights reserved.
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ garageAddress }}, {{ garageCity }}, {{ garagePostcode }} | {{ garagePhone }} | Advanced Vehicle Electrical Diagnostics &amp; Repair
                    </p>
                </div>
            </div>
        </footer>

        <!-- WhatsApp Floating Button -->
        <a :href="whatsappHref" target="_blank" rel="noopener"
           title="Chat with us on WhatsApp"
           class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-[#25d366] hover:bg-[#1eba57] text-white font-semibold px-4 py-3 rounded-full shadow-2xl transition-all duration-300 hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span class="text-sm">Chat with us</span>
        </a>

    </div>
</template>
