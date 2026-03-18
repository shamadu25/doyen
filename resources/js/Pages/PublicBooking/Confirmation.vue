<script setup lang="ts">
import { inject, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

interface Booking {
    reference: string
    customer_name: string
    email: string
    phone: string
    vehicle: string
    appointment_type: string
    service_requested: string | null
    quote_request: string | null
    scheduled_date: string
    scheduled_time: string
    description: string | null
    status: string
}

interface Props {
    booking: Booking
    reference: string
    account_created?: boolean
    portal_url?: string
}

const props = defineProps<Props>()

const route = inject<(path: string) => string>('route', (p) => p)

// Garage contact details from shared props
const garagePhone    = computed(() => ((usePage().props as any).garageSettings?.phone)    || '+44 141 482 0726')
const garageEmail    = computed(() => ((usePage().props as any).garageSettings?.email)    || 'info@doyenautos.co.uk')
const garageAddress  = computed(() => ((usePage().props as any).garageSettings?.address)  || '59 Southcroft Road')
const garageCity     = computed(() => ((usePage().props as any).garageSettings?.city)     || 'Rutherglen')
const garagePostcode = computed(() => ((usePage().props as any).garageSettings?.postcode) || 'G73 1UG')
const garageName     = computed(() => ((usePage().props as any).garageSettings?.garage_name) || 'Doyen Auto Services')
const garageTelHref  = computed(() => 'tel:+44' + garagePhone.value.replace(/\s/g, '').replace(/^0/, ''))
const garageMailHref = computed(() => 'mailto:' + garageEmail.value)

const appointmentTypeLabels: Record<string, string> = {
    'mot': 'MOT Test',
    'service': 'Service',
    'repair': 'Repair',
    'diagnosis': 'Diagnostics'
}
</script>

<template>
    <Head title="Booking Confirmed - Doyen Auto Services" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <Link :href="route('/')" class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-electric-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">D</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ garageName }}</span>
                </Link>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Booking Confirmed!</h1>
                <p class="text-lg text-gray-600">
                    Thank you for booking with Doyen Auto Services
                </p>
            </div>

            <!-- Booking Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="bg-electric-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-electric-200 text-sm font-medium">Booking Reference</div>
                            <div class="text-white text-2xl font-bold mt-1">{{ reference }}</div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Pending Confirmation
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Customer Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Customer Details</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Name</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">{{ booking.customer_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Email</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">{{ booking.email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Phone</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">{{ booking.phone }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="border-t border-gray-200"></div>

                    <!-- Vehicle Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Vehicle</h3>
                        <p class="text-gray-900 font-medium">{{ booking.vehicle }}</p>
                    </div>

                    <div class="border-t border-gray-200"></div>

                    <!-- Appointment Info -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Appointment Details</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Service Type</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">
                                    {{ appointmentTypeLabels[booking.appointment_type] || booking.appointment_type }}
                                </dd>
                            </div>
                            <div v-if="booking.service_requested">
                                <dt class="text-sm text-gray-500">Service Requested</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">{{ booking.service_requested }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Date & Time</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">
                                    {{ booking.scheduled_date }}<br>
                                    <span class="text-electric-600">{{ booking.scheduled_time }}</span>
                                </dd>
                            </div>
                            <div v-if="booking.quote_request" class="sm:col-span-2">
                                <dt class="text-sm text-gray-500">Quote Request</dt>
                                <dd class="text-sm text-gray-700 mt-1">{{ booking.quote_request }}</dd>
                            </div>
                            <div v-if="booking.description" class="sm:col-span-2">
                                <dt class="text-sm text-gray-500">Details</dt>
                                <dd class="text-sm text-gray-700 mt-1">{{ booking.description }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- What Happens Next -->
            <div class="bg-electric-50 border border-electric-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-electric-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    What Happens Next?
                </h3>
                <ol class="space-y-3 text-sm text-gray-700">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-electric-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">1</span>
                        <span><strong>Confirmation Email:</strong> You'll receive a confirmation email at {{ booking.email }} within the next few minutes.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-electric-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">2</span>
                        <span><strong>We'll Call You:</strong> Our team will contact you on {{ booking.phone }} to confirm your appointment and discuss any specific requirements.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 bg-electric-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3">3</span>
                        <span><strong>Bring Your Vehicle:</strong> Arrive at our garage at {{ booking.scheduled_time }} on {{ booking.scheduled_date }}.</span>
                    </li>
                </ol>
            </div>

            <!-- Customer Portal Access (shown when account was created) -->
            <div v-if="account_created && portal_url" class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Your Customer Portal Account is Ready!
                </h3>
                <p class="text-sm text-gray-700 mb-4">You can now log in to your customer portal to track this booking, view invoices, check quotes, and manage your vehicles.</p>
                <a
                    :href="portal_url"
                    class="inline-flex items-center px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Go to My Portal
                </a>
            </div>

            <!-- Important Info -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Important Information
                </h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Please save your booking reference <strong class="text-gray-900">{{ reference }}</strong> for future reference
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        If you need to cancel or reschedule, please call us at least 24 hours in advance
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        For MOT tests, please ensure you bring your V5C registration document
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl mb-2">📞</div>
                        <div class="font-medium text-gray-900">Call Us</div>
                        <a :href="garageTelHref" class="text-electric-600 font-medium mt-1 hover:underline block">{{ garagePhone }}</a>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl mb-2">📧</div>
                        <div class="font-medium text-gray-900">Email Us</div>
                        <a :href="garageMailHref" class="text-electric-600 font-medium mt-1 hover:underline block break-all">{{ garageEmail }}</a>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl mb-2">📍</div>
                        <div class="font-medium text-gray-900">Visit Us</div>
                        <div class="text-gray-600 mt-1 text-xs">{{ garageAddress }}<br>{{ garageCity }}, {{ garagePostcode }}</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    :href="route('/')"
                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Return to Homepage
                </Link>
                <button
                    onclick="window.print()"
                    class="inline-flex items-center justify-center px-6 py-3 bg-electric-600 rounded-lg text-sm font-medium text-white hover:bg-electric-700 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Confirmation
                </button>
            </div>
        </div>
    </div>
</template>
