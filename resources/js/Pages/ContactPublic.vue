<script setup lang="ts">
import { computed, inject } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const route = inject<(path: string) => string>('route', (p) => p)
const props = defineProps<{ websiteContent?: { hours?: Record<string, string> } }>()

const garage = computed(() => (usePage().props as any).garageSettings ?? {})
const garageName = computed(() => garage.value.garage_name || 'Doyen Auto Services')
const garagePhone = computed(() => garage.value.phone || '+44 7760 926245')
const garageEmail = computed(() => garage.value.email || 'info@doyenautos.co.uk')
const garageAddress = computed(() => garage.value.address || '59 Southcroft Road')
const garageCity = computed(() => garage.value.city || 'Rutherglen, Glasgow')
const garagePostcode = computed(() => garage.value.postcode || 'G73 1UG')

const telHref = computed(() => 'tel:+44' + garagePhone.value.replace(/\s/g, '').replace(/^0/, ''))
const mailHref = computed(() => 'mailto:' + garageEmail.value)
const mapsHref = computed(() => `https://maps.google.com/?q=${encodeURIComponent(`${garageAddress.value}, ${garageCity.value}, ${garagePostcode.value}`)}`)

const hours = computed(() => props.websiteContent?.hours ?? {})
</script>

<template>
    <Head :title="`${garageName} Contact | Book & Enquiries`">
        <meta name="description" content="Contact Doyen Auto Services for diagnostics, ECU repairs, MOT and specialist services. Call, email, or book online." />
    </Head>

    <div class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link :href="route('/')" class="font-bold text-xl text-electric-700">{{ garageName }}</Link>
                <div class="hidden md:flex items-center gap-6 text-sm">
                    <Link :href="route('/')" class="text-gray-600 hover:text-electric-600">Home</Link>
                    <Link :href="route('/our-services')" class="text-gray-600 hover:text-electric-600">Services</Link>
                    <Link :href="route('/testimonials')" class="text-gray-600 hover:text-electric-600">Testimonials</Link>
                    <Link :href="route('/contact')" class="text-electric-700 font-semibold">Contact</Link>
                </div>
            </div>
        </nav>

        <section class="bg-gradient-to-r from-navy-900 to-navy-700 text-white py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold">Contact Us</h1>
                <p class="mt-3 text-electric-100 max-w-2xl">Speak with our team for diagnostics, repairs, or booking support.</p>
            </div>
        </section>

        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900">Phone</h2>
                    <p class="mt-2 text-gray-600">Call us directly for urgent diagnostics and bookings.</p>
                    <a :href="telHref" class="mt-4 inline-block text-electric-700 font-semibold">{{ garagePhone }}</a>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900">Email</h2>
                    <p class="mt-2 text-gray-600">Send your enquiry and vehicle details.</p>
                    <a :href="mailHref" class="mt-4 inline-block text-electric-700 font-semibold">{{ garageEmail }}</a>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900">Address</h2>
                    <p class="mt-2 text-gray-600">{{ garageAddress }}, {{ garageCity }}, {{ garagePostcode }}</p>
                    <a :href="mapsHref" target="_blank" class="mt-4 inline-block text-electric-700 font-semibold">Open in Google Maps</a>
                </div>
            </div>
        </section>

        <section class="pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-900">Opening Hours</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="bg-slate-50 rounded-lg p-3"><span class="font-medium">Mon–Fri</span><p class="text-gray-600 mt-1">{{ hours.mon_fri || 'Mon–Fri: 9:00 am – 6:00 pm' }}</p></div>
                    <div class="bg-slate-50 rounded-lg p-3"><span class="font-medium">Saturday</span><p class="text-gray-600 mt-1">{{ hours.saturday || 'Saturday: 10:00 am – 3:00 pm' }}</p></div>
                    <div class="bg-slate-50 rounded-lg p-3"><span class="font-medium">Sunday</span><p class="text-gray-600 mt-1">{{ hours.sunday || 'Sunday: Closed' }}</p></div>
                </div>
                <div class="mt-6">
                    <Link :href="route('/book-online')" class="inline-flex items-center px-5 py-2.5 bg-electric-600 text-white rounded-lg font-medium hover:bg-electric-700">Book Online</Link>
                </div>
            </div>
        </section>
    </div>
</template>
