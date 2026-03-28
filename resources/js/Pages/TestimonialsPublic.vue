<script setup lang="ts">
import { computed, inject } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const route = inject<(path: string) => string>('route', (p) => p)
const props = defineProps<{
    websiteContent?: {
        testimonials?: { items?: string }
    }
}>()

const garage = computed(() => (usePage().props as any).garageSettings ?? {})
const garageName = computed(() => garage.value.garage_name || 'Doyen Auto Services')

const fallbackTestimonials = [
    { name: 'Mark D.', location: 'Glasgow', service: 'ECU Repair', text: 'Diagnosed and fixed in one day. Saved me a lot versus main dealer.', rating: 5 },
    { name: 'Tariq H.', location: 'Rutherglen', service: 'Airbag Reset', text: 'Crash data cleared and SRS sorted quickly. Transparent pricing.', rating: 5 },
    { name: 'Carolyn P.', location: 'Cambuslang', service: 'Diagnostics', text: 'Professional and clear communication. Correct fault resolved first time.', rating: 5 },
]

const testimonials = computed(() => {
    const raw = props.websiteContent?.testimonials?.items
    if (!raw) return fallbackTestimonials
    try {
        const parsed = JSON.parse(raw)
        return Array.isArray(parsed) ? parsed : fallbackTestimonials
    } catch {
        return fallbackTestimonials
    }
})
</script>

<template>
    <Head :title="`${garageName} Reviews | Customer Testimonials`">
        <meta name="description" content="Read what customers say about our diagnostics, ECU repair and specialist vehicle services in Glasgow." />
    </Head>

    <div class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link :href="route('/')" class="font-bold text-xl text-electric-700">{{ garageName }}</Link>
                <div class="hidden md:flex items-center gap-6 text-sm">
                    <Link :href="route('/')" class="text-gray-600 hover:text-electric-600">Home</Link>
                    <Link :href="route('/our-services')" class="text-gray-600 hover:text-electric-600">Services</Link>
                    <Link :href="route('/testimonials')" class="text-electric-700 font-semibold">Testimonials</Link>
                    <Link :href="route('/contact')" class="text-gray-600 hover:text-electric-600">Contact</Link>
                </div>
            </div>
        </nav>

        <section class="bg-gradient-to-r from-navy-900 to-navy-700 text-white py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold">Customer Testimonials</h1>
                <p class="mt-3 text-electric-100 max-w-2xl">Real feedback from customers across Glasgow and surrounding areas.</p>
            </div>
        </section>

        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <article v-for="(item, idx) in testimonials" :key="idx" class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">{{ item.name }}</h2>
                        <span class="text-amber-500 text-sm">{{ '★'.repeat(item.rating || 5) }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ item.location }} · {{ item.service }}</p>
                    <p class="mt-4 text-sm text-gray-700 leading-relaxed">“{{ item.text }}”</p>
                </article>
            </div>
        </section>

        <section class="pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Ready to experience it yourself?</h3>
                        <p class="text-gray-600 mt-1">Book your diagnostic or repair appointment online.</p>
                    </div>
                    <Link :href="route('/book-online')" class="px-5 py-2.5 bg-electric-600 text-white rounded-lg font-medium hover:bg-electric-700">Book Online</Link>
                </div>
            </div>
        </section>
    </div>
</template>
