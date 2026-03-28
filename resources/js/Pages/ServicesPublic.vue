<script setup lang="ts">
import { computed, inject } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    websiteServices?: Record<string, Array<{
        id: number
        name: string
        icon: string | null
        category: string
        description: string | null
        price: number
        duration_minutes: number
    }>>
    websiteContent?: {
        seo?: Record<string, string>
    }
}>()

const garage = computed(() => (usePage().props as any).garageSettings ?? {})
const garageName = computed(() => garage.value.garage_name || 'Doyen Auto Services')
const garagePhone = computed(() => garage.value.phone || '+44 7760 926245')
const garageTelHref = computed(() => 'tel:+44' + garagePhone.value.replace(/\s/g, '').replace(/^0/, ''))

const groupedServices = computed(() => Object.entries(props.websiteServices ?? {}))
const seo = computed(() => props.websiteContent?.seo ?? {})
const pageTitle = computed(() => seo.value.services_title || `${garageName.value} Services | Vehicle Diagnostics & Repairs`)
const pageDescription = computed(() => seo.value.services_description || 'Explore our advanced diagnostics, ECU repair, servicing, MOT and specialist vehicle repair services.')

function fmtPrice(v: number) {
    return Number(v || 0) > 0 ? `From £${Number(v).toFixed(2)}` : 'Price on request'
}
</script>

<template>
    <Head :title="pageTitle">
        <meta name="description" :content="pageDescription" />
    </Head>

    <div class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link :href="route('/')" class="font-bold text-xl text-electric-700">{{ garageName }}</Link>
                <div class="hidden md:flex items-center gap-6 text-sm">
                    <Link :href="route('/')" class="text-gray-600 hover:text-electric-600">Home</Link>
                    <Link :href="route('/our-services')" class="text-electric-700 font-semibold">Services</Link>
                    <Link :href="route('/testimonials')" class="text-gray-600 hover:text-electric-600">Testimonials</Link>
                    <Link :href="route('/contact')" class="text-gray-600 hover:text-electric-600">Contact</Link>
                </div>
            </div>
        </nav>

        <section class="bg-gradient-to-r from-navy-900 to-navy-700 text-white py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold">Our Services</h1>
                <p class="mt-3 text-electric-100 max-w-2xl">Dealer-level diagnostics and specialist repair services for private and trade customers across Scotland.</p>
            </div>
        </section>

        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div v-for="[category, services] in groupedServices" :key="category" class="space-y-4">
                    <h2 class="text-2xl font-bold text-gray-900">{{ category }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <article v-for="service in services" :key="service.id" class="bg-white rounded-xl border border-gray-200 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-semibold text-gray-900">{{ service.icon || '🔧' }} {{ service.name }}</h3>
                                <span class="text-xs bg-electric-50 text-electric-700 px-2 py-1 rounded">{{ fmtPrice(service.price) }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">{{ service.description || 'Professional service tailored to your vehicle needs.' }}</p>
                            <p class="mt-3 text-xs text-gray-500">Estimated duration: {{ service.duration_minutes || 0 }} min</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Need help choosing the right service?</h3>
                        <p class="text-gray-600 mt-1">Call us and we’ll guide you before you book.</p>
                    </div>
                    <div class="flex gap-3">
                        <a :href="garageTelHref" class="px-5 py-2.5 bg-electric-600 text-white rounded-lg font-medium hover:bg-electric-700">Call Us</a>
                        <Link :href="route('/book-online')" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">Book Online</Link>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
