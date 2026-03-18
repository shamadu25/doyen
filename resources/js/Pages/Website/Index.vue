<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Testimonial {
    name: string
    location: string
    service: string
    text: string
    rating: number
}

interface ProcessStep {
    step: string
    icon: string
    title: string
    desc: string
}

interface TrustItem {
    icon: string
    title: string
    subtitle: string
}

interface ValueItem {
    icon: string
    title: string
    desc: string
}

const props = defineProps<{
    content: {
        hero: Record<string, string>
        trust: { items: string }
        testimonials: { items: string }
        process: { items: string }
        hours: Record<string, string>
        seo: Record<string, string>
        social: Record<string, string>
        about: Record<string, string>
        contact: Record<string, string>
    }
}>()

const activeTab = ref('hero')
const tabs = [
    { id: 'hero',         label: '🏠 Hero' },
    { id: 'trust',        label: '🛡️ Trust Bar' },
    { id: 'testimonials', label: '⭐ Testimonials' },
    { id: 'process',      label: '📋 How It Works' },
    { id: 'hours',        label: '🕐 Opening Hours' },
    { id: 'seo',          label: '🔍 SEO' },
    { id: 'social',       label: '📱 Social Links' },
    { id: 'about',        label: '👤 About Page' },
    { id: 'contact',      label: '📍 Contact Details' },
]

// --- Hero Form ---
const heroForm = useForm({ ...props.content.hero })

function saveHero() {
    heroForm.post(route('/website/hero'), {
        preserveScroll: true,
    })
}

// --- Trust Items ---
const trustItems = reactive<TrustItem[]>(
    JSON.parse(props.content.trust?.items || '[]')
)

function addTrustItem() {
    trustItems.push({ icon: 'tool', title: '', subtitle: '' })
}
function removeTrustItem(i: number) {
    trustItems.splice(i, 1)
}

const trustSaving = ref(false)
function saveTrust() {
    trustSaving.value = true
    router.post(route('/website/trust'), { items: JSON.stringify(trustItems) }, {
        preserveScroll: true,
        onFinish: () => { trustSaving.value = false },
    })
}

// --- Testimonials ---
const testimonials = reactive<Testimonial[]>(
    JSON.parse(props.content.testimonials?.items || '[]')
)

function addTestimonial() {
    testimonials.push({ name: '', location: '', service: '', text: '', rating: 5 })
}
function removeTestimonial(i: number) {
    testimonials.splice(i, 1)
}

const testimonialsSaving = ref(false)
function saveTestimonials() {
    testimonialsSaving.value = true
    router.post(route('/website/testimonials'), { items: JSON.stringify(testimonials) }, {
        preserveScroll: true,
        onFinish: () => { testimonialsSaving.value = false },
    })
}

// --- Process Steps ---
const processSteps = reactive<ProcessStep[]>(
    JSON.parse(props.content.process?.items || '[]')
)

function addStep() {
    processSteps.push({ step: String(processSteps.length + 1), icon: '🔧', title: '', desc: '' })
}
function removeStep(i: number) {
    processSteps.splice(i, 1)
    processSteps.forEach((s, idx) => s.step = String(idx + 1))
}

const processSaving = ref(false)
function saveProcess() {
    processSaving.value = true
    router.post(route('/website/process'), { items: JSON.stringify(processSteps) }, {
        preserveScroll: true,
        onFinish: () => { processSaving.value = false },
    })
}

// --- Hours Form ---
const hoursForm = useForm({ ...props.content.hours })

function saveHours() {
    hoursForm.post(route('/website/hours'), {
        preserveScroll: true,
    })
}

// --- SEO Form ---
const seoForm = useForm({ ...props.content.seo })

function saveSeo() {
    seoForm.post(route('/website/seo'), {
        preserveScroll: true,
    })
}

// --- Social Form ---
const socialForm = useForm({ ...props.content.social })

function saveSocial() {
    socialForm.post(route('/website/social'), {
        preserveScroll: true,
    })
}

// --- Contact Form ---
const contactForm = useForm({
    address:         props.content.contact?.address         || '',
    city:            props.content.contact?.city            || '',
    postcode:        props.content.contact?.postcode        || '',
    phone:           props.content.contact?.phone           || '',
    email:           props.content.contact?.email           || '',
    whatsapp_number: props.content.contact?.whatsapp_number || '',
})

function saveContact() {
    contactForm.post(route('/website/contact'), { preserveScroll: true })
}

// --- About Form ---
const aboutForm = useForm({
    hero_badge:             props.content.about?.hero_badge            || 'About Doyen Auto Services',
    hero_headline:          props.content.about?.hero_headline         || 'Glasgow-based Precision',
    hero_headline_gradient: props.content.about?.hero_headline_gradient || 'Vehicle Diagnostics',
    hero_subline:           props.content.about?.hero_subline          || '',
    stat_1_value:           props.content.about?.stat_1_value          || '16+',
    stat_1_label:           props.content.about?.stat_1_label          || 'Years Experience',
    stat_2_value:           props.content.about?.stat_2_value          || 'Glasgow',
    stat_2_label:           props.content.about?.stat_2_label          || 'Based in Rutherglen',
    stat_3_value:           props.content.about?.stat_3_value          || 'ECU',
    stat_3_label:           props.content.about?.stat_3_label          || 'Diagnostics Specialist',
    stat_4_value:           props.content.about?.stat_4_value          || 'Scotland',
    stat_4_label:           props.content.about?.stat_4_label          || 'Wide Coverage',
    who_intro_1:            props.content.about?.who_intro_1           || '',
    who_intro_2:            props.content.about?.who_intro_2           || '',
    who_intro_3:            props.content.about?.who_intro_3           || '',
    founder_name:           props.content.about?.founder_name          || '',
    founder_title:          props.content.about?.founder_title         || '',
    founder_experience:     props.content.about?.founder_experience    || '',
    founder_quote:          props.content.about?.founder_quote         || '',
    cta_headline:           props.content.about?.cta_headline          || '',
    cta_subline:            props.content.about?.cta_subline           || '',
})

const aboutValues = reactive<ValueItem[]>(
    (() => { try { return JSON.parse(props.content.about?.values || '[]') } catch { return [] } })()
)

function addAboutValue() {
    aboutValues.push({ icon: '⭐', title: '', desc: '' })
}
function removeAboutValue(i: number) {
    aboutValues.splice(i, 1)
}

const aboutSaving = ref(false)
function saveAbout() {
    aboutSaving.value = true
    router.post(route('/website/about'), {
        ...aboutForm.data(),
        values: JSON.stringify(aboutValues),
    }, {
        preserveScroll: true,
        onFinish: () => { aboutSaving.value = false },
    })
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Website Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Edit the content shown on your public website</p>
                </div>
                <a :href="route('/')" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-electric-600 border border-electric-300 rounded-lg hover:bg-electric-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Preview Website
                </a>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex gap-1 overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'whitespace-nowrap px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition',
                            activeTab === tab.id
                                ? 'border-electric-600 text-electric-600 bg-electric-50'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- ===================== HERO TAB ===================== -->
            <div v-if="activeTab === 'hero'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Hero Section</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Badge Text (top chip)</label>
                            <input v-model="heroForm.badge_text" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Headline (main line)</label>
                            <input v-model="heroForm.headline" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Headline Gradient Line (second line in blue gradient)</label>
                            <input v-model="heroForm.headline_gradient" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sub-headline paragraph</label>
                            <textarea v-model="heroForm.subline" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CTA Primary Button</label>
                                <input v-model="heroForm.cta_primary" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CTA Secondary Button</label>
                                <input v-model="heroForm.cta_secondary" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <p class="text-sm font-medium text-gray-700 mb-3">Stats Cards</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                    <label class="text-xs font-medium text-gray-500">Stat 1 Value</label>
                                    <input v-model="heroForm.stat_1_value" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    <label class="text-xs font-medium text-gray-500">Stat 1 Label</label>
                                    <input v-model="heroForm.stat_1_label" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                    <label class="text-xs font-medium text-gray-500">Stat 2 Value</label>
                                    <input v-model="heroForm.stat_2_value" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    <label class="text-xs font-medium text-gray-500">Stat 2 Label</label>
                                    <input v-model="heroForm.stat_2_label" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                    <label class="text-xs font-medium text-gray-500">Stat 3 Value</label>
                                    <input v-model="heroForm.stat_3_value" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    <label class="text-xs font-medium text-gray-500">Stat 3 Label</label>
                                    <input v-model="heroForm.stat_3_label" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveHero"
                            :disabled="heroForm.processing"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ heroForm.processing ? 'Saving…' : 'Save Hero Section' }}
                        </button>
                        <span v-if="heroForm.wasSuccessful" class="text-sm text-green-600 font-medium">✓ Saved!</span>
                    </div>
                </div>
            </div>

            <!-- ===================== TRUST BAR TAB ===================== -->
            <div v-if="activeTab === 'trust'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Trust Bar Items</h2>
                            <p class="text-sm text-gray-500 mt-0.5">The 4 highlight badges shown below the hero section</p>
                        </div>
                        <button @click="addTrustItem"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-electric-600 border border-electric-300 rounded-lg hover:bg-electric-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Item
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(item, i) in trustItems" :key="i"
                             class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Icon Name</label>
                                        <input v-model="item.icon" type="text" placeholder="tool / clock / bolt"
                                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Title</label>
                                        <input v-model="item.title" type="text"
                                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Subtitle</label>
                                        <input v-model="item.subtitle" type="text"
                                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    </div>
                                </div>
                                <button @click="removeTrustItem(i)"
                                        class="mt-5 p-1.5 text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="trustItems.length === 0" class="text-sm text-gray-400 text-center py-4">No items yet — click Add Item.</p>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveTrust" :disabled="trustSaving"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ trustSaving ? 'Saving…' : 'Save Trust Bar' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================== TESTIMONIALS TAB ===================== -->
            <div v-if="activeTab === 'testimonials'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Customer Testimonials</h2>
                            <p class="text-sm text-gray-500 mt-0.5">Displayed in the Reviews section of the homepage</p>
                        </div>
                        <button @click="addTestimonial"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-electric-600 border border-electric-300 rounded-lg hover:bg-electric-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Testimonial
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(t, i) in testimonials" :key="i"
                             class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-700">Testimonial #{{ i + 1 }}</span>
                                <button @click="removeTestimonial(i)"
                                        class="p-1.5 text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer Name</label>
                                    <input v-model="t.name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
                                    <input v-model="t.location" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Service Used</label>
                                    <input v-model="t.service" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Rating (1–5)</label>
                                    <select v-model.number="t.rating" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} Stars</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Review Text</label>
                                <textarea v-model="t.text" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm text-sm"></textarea>
                            </div>
                        </div>
                        <p v-if="testimonials.length === 0" class="text-sm text-gray-400 text-center py-4">No testimonials yet — click Add Testimonial.</p>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveTestimonials" :disabled="testimonialsSaving"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ testimonialsSaving ? 'Saving…' : 'Save Testimonials' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================== PROCESS TAB ===================== -->
            <div v-if="activeTab === 'process'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">How It Works / Process Steps</h2>
                            <p class="text-sm text-gray-500 mt-0.5">The numbered step cards shown on the homepage</p>
                        </div>
                        <button @click="addStep"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-electric-600 border border-electric-300 rounded-lg hover:bg-electric-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Step
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(step, i) in processSteps" :key="i"
                             class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-electric-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                    {{ step.step }}
                                </div>
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Icon (emoji)</label>
                                        <input v-model="step.icon" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Title</label>
                                        <input v-model="step.title" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                        <input v-model="step.desc" type="text" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                    </div>
                                </div>
                                <button @click="removeStep(i)"
                                        class="mt-5 p-1.5 text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="processSteps.length === 0" class="text-sm text-gray-400 text-center py-4">No steps yet — click Add Step.</p>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveProcess" :disabled="processSaving"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ processSaving ? 'Saving…' : 'Save Process Steps' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================== HOURS TAB ===================== -->
            <div v-if="activeTab === 'hours'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Opening Hours</h2>

                    <div class="space-y-4 max-w-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Top Bar Hours (compact, shown in the site header)</label>
                            <input v-model="hoursForm.topbar" type="text"
                                   placeholder="Mon-Fri: 8:00-17:30 | Sat: 8:00-12:30"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            <p class="text-xs text-gray-400 mt-1">Keep it short — displayed in the narrow top bar</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monday – Friday</label>
                            <input v-model="hoursForm.mon_fri" type="text"
                                   placeholder="Mon–Fri: 9:00 am – 6:00 pm"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Saturday</label>
                            <input v-model="hoursForm.saturday" type="text"
                                   placeholder="Saturday: 10:00 am – 3:00 pm"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sunday</label>
                            <input v-model="hoursForm.sunday" type="text"
                                   placeholder="Sunday: Closed"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional, e.g. emergency line)</label>
                            <input v-model="hoursForm.note" type="text"
                                   placeholder="Emergency calls available — phone for availability"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveHours" :disabled="hoursForm.processing"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ hoursForm.processing ? 'Saving…' : 'Save Opening Hours' }}
                        </button>
                        <span v-if="hoursForm.wasSuccessful" class="text-sm text-green-600 font-medium">✓ Saved!</span>
                    </div>
                </div>
            </div>

            <!-- ===================== SEO TAB ===================== -->
            <div v-if="activeTab === 'seo'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">SEO Settings</h2>
                    <p class="text-sm text-gray-500 mb-5">Controls web page title and meta tags for search engines</p>

                    <div class="space-y-4 max-w-2xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input v-model="seoForm.meta_title" type="text"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            <p class="text-xs text-gray-400 mt-1">Shown in the browser tab and Google search results (50–60 chars ideal)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea v-model="seoForm.meta_description" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Shown under the title in Google results (150–160 chars ideal)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">OG Image URL (optional)</label>
                            <input v-model="seoForm.og_image" type="url"
                                   placeholder="https://yoursite.com/og-image.jpg"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            <p class="text-xs text-gray-400 mt-1">Image shown when sharing on social media (recommended 1200 × 630)</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveSeo" :disabled="seoForm.processing"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ seoForm.processing ? 'Saving…' : 'Save SEO Settings' }}
                        </button>
                        <span v-if="seoForm.wasSuccessful" class="text-sm text-green-600 font-medium">✓ Saved!</span>
                    </div>
                </div>
            </div>

            <!-- ===================== SOCIAL TAB ===================== -->
            <div v-if="activeTab === 'social'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Social Media Links</h2>
                    <p class="text-sm text-gray-500 mb-5">Links shown in the website footer. Leave blank to hide.</p>

                    <div class="space-y-4 max-w-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                            <input v-model="socialForm.facebook_url" type="url"
                                   placeholder="https://facebook.com/yourpage"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                            <input v-model="socialForm.instagram_url" type="url"
                                   placeholder="https://instagram.com/yourhandle"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">TikTok URL</label>
                            <input v-model="socialForm.tiktok_url" type="url"
                                   placeholder="https://tiktok.com/@yourhandle"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveSocial" :disabled="socialForm.processing"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ socialForm.processing ? 'Saving…' : 'Save Social Links' }}
                        </button>
                        <span v-if="socialForm.wasSuccessful" class="text-sm text-green-600 font-medium">✓ Saved!</span>
                    </div>
                </div>
            </div>

            <!-- ===================== ABOUT TAB ===================== -->
            <div v-if="activeTab === 'about'" class="space-y-6">

                <!-- Hero -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Hero Section</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Badge Text</label>
                            <input v-model="aboutForm.hero_badge" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Headline Line 1</label>
                                <input v-model="aboutForm.hero_headline" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Headline Line 2 (gradient)</label>
                                <input v-model="aboutForm.hero_headline_gradient" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sub-line Paragraph</label>
                            <textarea v-model="aboutForm.hero_subline" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Quick Stats Bar</h2>
                    <p class="text-sm text-gray-500 mb-5">These 4 stat badges appear on the About Us page hero banner (e.g. <strong>16+</strong> Years Experience, <strong>ECU</strong> Diagnostics Specialist). Edit the value and label for each.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="n in 4" :key="n" class="border border-gray-100 rounded-lg p-4 space-y-2">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Stat {{ n }}</label>
                            <input v-model="(aboutForm as any)[`stat_${n}_value`]" type="text" placeholder="Value (e.g. 16+)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                            <input v-model="(aboutForm as any)[`stat_${n}_label`]" type="text" placeholder="Label (e.g. Years Experience)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                        </div>
                    </div>
                </div>

                <!-- Who We Are -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Who We Are — Intro Paragraphs</h2>
                    <div class="space-y-4">
                        <div v-for="n in 3" :key="n">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Paragraph {{ n }}</label>
                            <textarea v-model="(aboutForm as any)[`who_intro_${n}`]" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Founder -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Founder Card</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Founder Name</label>
                                <input v-model="aboutForm.founder_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title / Role</label>
                                <input v-model="aboutForm.founder_title" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Experience Label</label>
                            <input v-model="aboutForm.founder_experience" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quote (without quotation marks)</label>
                            <textarea v-model="aboutForm.founder_quote" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Values -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-semibold text-gray-900">Why Choose Us — Value Cards</h2>
                        <button @click="addAboutValue" type="button"
                            class="text-sm text-electric-600 border border-electric-300 px-3 py-1.5 rounded-lg hover:bg-electric-50 transition">
                            + Add Card
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div v-for="(val, i) in aboutValues" :key="i"
                             class="border border-gray-100 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Card {{ i + 1 }}</span>
                                <button @click="removeAboutValue(i)" type="button" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Icon (emoji)</label>
                                    <input v-model="val.icon" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Title</label>
                                    <input v-model="val.title" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Description</label>
                                    <input v-model="val.desc" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Banner -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">CTA Banner (bottom of page)</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label>
                            <input v-model="aboutForm.cta_headline" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sub-line</label>
                            <textarea v-model="aboutForm.cta_subline" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-electric-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="saveAbout" :disabled="aboutSaving"
                        class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                        {{ aboutSaving ? 'Saving…' : 'Save About Page' }}
                    </button>
                </div>

            </div>

            <!-- ===================== CONTACT TAB ===================== -->
            <div v-if="activeTab === 'contact'" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Contact Details</h2>
                    <p class="text-sm text-gray-500 mb-5">
                        These details are shown in the website header, footer, and contact sections.
                        The WhatsApp number is used for the floating green chat button — you can set a
                        different number here if your WhatsApp business line differs from your main phone.
                    </p>

                    <div class="space-y-4 max-w-2xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line</label>
                            <input v-model="contactForm.address" type="text"
                                   placeholder="e.g. 59 Southcroft Rd, Rutherglen"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City / Town</label>
                                <input v-model="contactForm.city" type="text"
                                       placeholder="e.g. Glasgow"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                                <input v-model="contactForm.postcode" type="text"
                                       placeholder="e.g. G73 1UG"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input v-model="contactForm.phone" type="tel"
                                       placeholder="e.g. +44 7760 926245"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                                <p class="text-xs text-gray-400 mt-1">Shown in the header and footer click-to-call link</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input v-model="contactForm.email" type="email"
                                       placeholder="e.g. info@doyenautos.co.uk"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-500 focus:ring-electric-500" />
                            </div>
                        </div>

                        <!-- WhatsApp number — highlighted -->
                        <div class="border border-green-200 rounded-xl p-4 bg-green-50">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <label class="block text-sm font-medium text-green-800">WhatsApp Number (floating chat button)</label>
                            </div>
                            <input v-model="contactForm.whatsapp_number" type="tel"
                                   placeholder="e.g. +44 7760 926245"
                                   class="w-full rounded-lg border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white" />
                            <p class="text-xs text-green-700 mt-1.5">
                                This number powers the green WhatsApp floating button on every page.
                                Leave blank to use the Phone Number above.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button @click="saveContact" :disabled="contactForm.processing"
                            class="px-6 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition disabled:opacity-50">
                            {{ contactForm.processing ? 'Saving…' : 'Save Contact Details' }}
                        </button>
                        <span v-if="contactForm.wasSuccessful" class="text-sm text-green-600 font-medium">✓ Saved! Changes are live on the website.</span>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
