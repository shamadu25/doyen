<script setup lang="ts">
import { inject, ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

interface Customer {
    id: number
    name: string
    email: string
    phone: string
    address: string | null
}

interface Vehicle {
    id: number
    registration: string
    make: string
    model: string
    year: number | null
    colour: string | null
    vin: string | null
}

interface Technician {
    id: number
    name: string
    email: string
}

interface JobCard {
    id: number
    reference: string
    status: string
}

interface QuoteSummary {
    id: number
    quote_number: string
    status: string
    total_amount: number
}

interface Booking {
    id: number
    reference: string
    customer: Customer
    vehicle: Vehicle
    assigned_to_user: Technician | null
    appointment_type: string
    scheduled_date: string
    scheduled_time: string
    duration_minutes: number
    status: string
    description: string | null
    customer_notes: string | null
    internal_notes: string | null
    job_card: JobCard | null
    quote: QuoteSummary | null
    created_at: string
    updated_at: string
    proposed_date: string | null
    proposed_time: string | null
    reschedule_proposed_at: string | null
}

interface Props {
    booking: Booking
    defaultVatRate: number
}

const props = defineProps<Props>()

const route = inject<(path: string) => string>('route', (p) => p)

function formatDate(dateStr: string): string {
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-GB', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}

function formatTime(timeStr: string): string {
    if (!timeStr) return ''
    const [h, m] = timeStr.split(':')
    const hour = parseInt(h)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    const h12 = hour % 12 || 12
    return `${h12}:${m} ${ampm}`
}

function formatDuration(minutes: number): string {
    if (minutes < 60) return `${minutes} mins`
    const hrs = Math.floor(minutes / 60)
    const mins = minutes % 60
    return mins > 0 ? `${hrs}h ${mins}m` : `${hrs} hour${hrs > 1 ? 's' : ''}`
}

function formatDateTime(dateStr: string): string {
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function confirmBooking() {
    if (confirm('Confirm this booking?')) {
        router.post(route(`/bookings/${props.booking.id}/confirm`))
    }
}

function cancelBooking() {
    if (confirm('Are you sure you want to cancel this booking? This cannot be undone.')) {
        router.post(route(`/bookings/${props.booking.id}/cancel`))
    }
}

function completeBooking() {
    if (confirm('Mark this booking as completed?')) {
        router.post(route(`/bookings/${props.booking.id}/complete`))
    }
}

function convertToJobCard() {
    if (confirm('Convert this booking into a job card?')) {
        router.post(route(`/bookings/${props.booking.id}/convert-to-job`))
    }
}

function deleteBooking() {
    if (confirm('Are you sure you want to permanently delete this booking?')) {
        router.delete(route(`/bookings/${props.booking.id}`))
    }
}

const isPending = props.booking.status === 'pending'
const isPendingQuote = props.booking.status === 'pending_quote'
const isConfirmed = props.booking.status === 'confirmed'
const isInProgress = props.booking.status === 'in_progress'
const isCancelled = props.booking.status === 'cancelled'
const isCompleted = props.booking.status === 'completed'
const isReschedulePending = props.booking.status === 'reschedule_pending'
const canConfirm = isPending || isPendingQuote
const canCancel = isPending || isPendingQuote || isConfirmed
const canComplete = isConfirmed || isInProgress
const canConvertToJobCard = (isConfirmed || isPending) && !props.booking.job_card
const canReschedule = !isCancelled && !isCompleted && !isReschedulePending
const canGenerateQuote = (isPending || isPendingQuote) && !props.booking.quote?.status?.match(/^(sent|approved|converted)$/)

// Reschedule form
const showRescheduleForm = ref(false)
const rescheduleForm = useForm({
    proposed_date: '',
    proposed_time: '',
})

function submitReschedule() {
    rescheduleForm.post(route(`/bookings/${props.booking.id}/reschedule`), {
        onSuccess: () => {
            showRescheduleForm.value = false
            rescheduleForm.reset()
        }
    })
}

// Quote generation form
const showQuoteForm = ref(false)

interface QuoteItem {
    item_type: 'service' | 'part' | 'labour'
    description: string
    quantity: number
    unit_price: number
    tax_exempt: boolean
}

const quoteForm = useForm<{
    items: QuoteItem[]
    notes: string
    validity_days: number
    discount_percentage: number
}>({
    items: [{ item_type: 'labour', description: '', quantity: 1, unit_price: 0, tax_exempt: false }],
    notes: '',
    validity_days: 14,
    discount_percentage: 0,
})

function addQuoteItem() {
    quoteForm.items.push({ item_type: 'labour', description: '', quantity: 1, unit_price: 0, tax_exempt: false })
}

function removeQuoteItem(idx: number) {
    quoteForm.items.splice(idx, 1)
}

const vatRate = computed(() => Number(props.defaultVatRate) || 20)
const quoteLineNet = (item: QuoteItem) => (Number(item.quantity) || 0) * (Number(item.unit_price) || 0)
const quoteDiscountFactor = computed(() => {
    const sub = quoteForm.items.reduce((s: number, i: QuoteItem) => s + quoteLineNet(i), 0)
    const afterDisc = sub - (sub * ((quoteForm.discount_percentage || 0) / 100))
    return sub > 0 ? (afterDisc / sub) : 1
})
const quoteLineVat = (item: QuoteItem) => item.tax_exempt ? 0 : quoteLineNet(item) * quoteDiscountFactor.value * (vatRate.value / 100)
const quoteLineGross = (item: QuoteItem) => (quoteLineNet(item) * quoteDiscountFactor.value) + quoteLineVat(item)

const quoteTotal = computed(() => {
    const rate = vatRate.value
    const sub = quoteForm.items.reduce((s: number, i: QuoteItem) => s + quoteLineNet(i), 0)
    const disc = sub * ((quoteForm.discount_percentage || 0) / 100)
    const afterDisc = sub - disc
    const vat = quoteForm.items.reduce((s: number, i: QuoteItem) => {
        if (i.tax_exempt) return s
        return s + quoteLineVat(i)
    }, 0)
    return { subtotal: sub, discount: disc, vat, total: afterDisc + vat, vatRate: rate }
})

function submitQuote() {
    const rate = vatRate.value
    const payload = {
        ...quoteForm.data(),
        items: quoteForm.items.map((i: QuoteItem) => ({
            ...i,
            vat_rate: i.tax_exempt ? 0 : rate,
            tax_exempt: !!i.tax_exempt,
        }))
    }
    quoteForm.transform(() => payload).post(route(`/bookings/${props.booking.id}/generate-quote`), {
        onSuccess: () => {
            showQuoteForm.value = false
            quoteForm.reset()
            quoteForm.items = [{ item_type: 'labour', description: '', quantity: 1, unit_price: 0, tax_exempt: false }]
        }
    })
}
</script>

<template>
    <Head :title="`Booking ${booking.reference}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('/bookings')" class="text-gray-500 hover:text-gray-700 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Booking {{ booking.reference }}
                    </h2>
                    <StatusBadge :status="booking.status" />
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="!isCancelled && !isCompleted"
                        :href="route(`/bookings/${booking.id}/edit`)"
                        class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </Link>
                    <button
                        @click="deleteBooking"
                        class="inline-flex items-center rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Booking Details -->
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Booking Details</h3>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Reference</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ booking.reference }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Appointment Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ booking.appointment_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Scheduled Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(booking.scheduled_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Scheduled Time</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatTime(booking.scheduled_time) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Technician</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ booking.assigned_to_user?.name ?? 'Unassigned' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(booking.created_at) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(booking.updated_at) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Notes -->
                        <div v-if="booking.description || booking.internal_notes || booking.customer_notes" class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Notes</h3>
                            <div class="space-y-4">
                                <div v-if="booking.description">
                                    <h4 class="text-sm font-medium text-gray-500">Work Description</h4>
                                    <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ booking.description }}</p>
                                </div>
                                <div v-if="booking.internal_notes">
                                    <h4 class="text-sm font-medium text-gray-500">Internal Notes</h4>
                                    <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ booking.internal_notes }}</p>
                                </div>
                                <div v-if="booking.customer_notes">
                                    <h4 class="text-sm font-medium text-gray-500">Customer Notes</h4>
                                    <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ booking.customer_notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Related Job Card -->
                        <div v-if="booking.job_card" class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Related Job Card</h3>
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ booking.job_card.reference }}</p>
                                    <StatusBadge :status="booking.job_card.status" class="mt-1" />
                                </div>
                                <Link
                                    :href="route(`/job-cards/${booking.job_card.id}`)"
                                    class="inline-flex items-center rounded-lg bg-electric-50 px-3 py-1.5 text-sm font-medium text-electric-700 hover:bg-electric-100 transition"
                                >
                                    View Job Card
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Related Quote -->
                        <div v-if="booking.quote" class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Quote</h3>
                            <div class="flex items-center justify-between rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ booking.quote.quote_number }}</p>
                                    <StatusBadge :status="booking.quote.status" class="mt-1" />
                                    <p class="text-sm text-gray-600 mt-1">Total: £{{ parseFloat(String(booking.quote.total_amount)).toFixed(2) }}</p>
                                </div>
                                <Link
                                    :href="route(`/quotes/${booking.quote.id}`)"
                                    class="inline-flex items-center rounded-lg bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 transition"
                                >
                                    View Quote
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Generate Quote Form (inline) -->
                        <div v-if="showQuoteForm" class="rounded-xl bg-white p-6 shadow-sm border border-indigo-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Generate Quote</h3>
                                <button @click="showQuoteForm = false" class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Add line items below. Unit prices are ex. VAT, VAT is calculated inline per line, and the quote will be emailed to the customer with a secure approval link.</p>

                            <!-- Line Items -->
                            <div class="space-y-3 mb-4">
                                <div v-for="(item, idx) in quoteForm.items" :key="idx" class="grid grid-cols-12 gap-2 items-start bg-gray-50 p-3 rounded-lg">
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">Type</label>
                                        <select v-model="item.item_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="labour">Labour</option>
                                            <option value="service">Service</option>
                                            <option value="part">Part</option>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <label class="block text-xs text-gray-500 mb-1">Description</label>
                                        <input v-model="item.description" type="text" placeholder="e.g. Oil & Filter Change" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-xs text-gray-500 mb-1">Qty</label>
                                        <input v-model.number="item.quantity" type="number" min="1" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">Unit £</label>
                                        <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                    <div class="col-span-2 flex flex-col">
                                        <label class="block text-xs text-gray-500 mb-1">VAT / Tax</label>
                                        <label :for="`qi-vat-${idx}`" class="flex items-center gap-1.5 h-5">
                                            <input type="checkbox" v-model="item.tax_exempt" :id="`qi-vat-${idx}`" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                            <span class="text-xs cursor-pointer select-none" :class="item.tax_exempt ? 'text-amber-700 font-medium' : 'text-gray-600'">Tax Exempt</span>
                                        </label>
                                        <div class="text-[11px] mt-1" :class="item.tax_exempt ? 'text-amber-700' : 'text-gray-500'">
                                            {{ item.tax_exempt ? 'VAT £0.00' : `${quoteTotal.vatRate}% · £${quoteLineVat(item).toFixed(2)}` }}
                                        </div>
                                        <div class="text-[11px] text-gray-500">Line total £{{ quoteLineGross(item).toFixed(2) }}</div>
                                    </div>
                                    <div class="col-span-1 pt-5">
                                        <button @click="removeQuoteItem(idx)" :disabled="quoteForm.items.length === 1" class="text-red-400 hover:text-red-600 disabled:opacity-30">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button @click="addQuoteItem" type="button" class="mb-4 text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Item
                            </button>

                            <!-- Options row -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Discount %</label>
                                    <input v-model.number="quoteForm.discount_percentage" type="number" min="0" max="100" step="0.5" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Valid (days)</label>
                                    <input v-model.number="quoteForm.validity_days" type="number" min="1" max="365" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Notes (optional)</label>
                                <textarea v-model="quoteForm.notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Any notes for the customer…"></textarea>
                            </div>

                            <!-- Live total preview -->
                            <div class="rounded-lg bg-indigo-50 p-3 text-sm space-y-1 mb-4">
                                <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>£{{ quoteTotal.subtotal.toFixed(2) }}</span></div>
                                <div v-if="quoteTotal.discount > 0" class="flex justify-between text-red-600"><span>Discount</span><span>−£{{ quoteTotal.discount.toFixed(2) }}</span></div>
                                <div class="flex justify-between text-gray-600"><span>VAT ({{ quoteTotal.vatRate }}%)</span><span>£{{ quoteTotal.vat.toFixed(2) }}</span></div>
                                <div class="flex justify-between font-bold text-gray-900 border-t border-indigo-200 pt-1"><span>Total</span><span>£{{ quoteTotal.total.toFixed(2) }}</span></div>
                            </div>

                            <div v-if="quoteForm.errors && Object.keys(quoteForm.errors).length" class="mb-3 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                                <p v-for="(msg, field) in quoteForm.errors" :key="field">{{ msg }}</p>
                            </div>

                            <div class="flex gap-3">
                                <button @click="submitQuote" :disabled="quoteForm.processing" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50 transition">
                                    {{ quoteForm.processing ? 'Sending…' : '📧 Send Quote to Customer' }}
                                </button>
                                <button @click="showQuoteForm = false; quoteForm.clearErrors()" type="button" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Actions -->
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Actions</h3>
                            <div class="space-y-3">
                                <button
                                    v-if="canConfirm"
                                    @click="confirmBooking"
                                    class="flex w-full items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-green-700 transition"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Confirm Booking
                                </button>

                                <button
                                    v-if="canConvertToJobCard"
                                    @click="convertToJobCard"
                                    class="flex w-full items-center justify-center rounded-xl bg-electric-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Convert to Job Card
                                </button>

                                <button
                                    v-if="canComplete"
                                    @click="completeBooking"
                                    class="flex w-full items-center justify-center rounded-xl bg-electric-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Mark Completed
                                </button>

                                <button
                                    v-if="canCancel"
                                    @click="cancelBooking"
                                    class="flex w-full items-center justify-center rounded-xl border border-red-300 bg-white px-4 py-2.5 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 transition"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancel Booking
                                </button>

                                <p v-if="isCompleted" class="text-center text-sm text-green-600 font-medium">
                                    This booking has been completed.
                                </p>
                                <p v-if="isCancelled" class="text-center text-sm text-red-600 font-medium">
                                    This booking has been cancelled.
                                </p>

                                <!-- Reschedule Pending Notice -->
                                <div v-if="isReschedulePending" class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm">
                                    <p class="font-medium text-amber-800">⏳ Awaiting customer response</p>
                                    <p class="text-amber-700 mt-1">
                                        Proposed: {{ booking.proposed_date }} at {{ booking.proposed_time }}
                                    </p>
                                </div>

                                <!-- Pending Quote Notice -->
                                <div v-if="isPendingQuote" class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-sm">
                                    <p class="font-medium text-blue-800">📋 Awaiting quote approval</p>
                                    <p class="text-blue-700 mt-1">A quote has been sent to the customer for review. Booking will be confirmed once approved.</p>
                                </div>

                                <!-- Generate & Send Quote Button -->
                                <button
                                    v-if="canGenerateQuote && !showQuoteForm"
                                    @click="showQuoteForm = true"
                                    class="flex w-full items-center justify-center rounded-xl border border-indigo-300 bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-100 transition"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Generate &amp; Send Quote
                                </button>

                                <!-- Propose New Time Button -->
                                <button
                                    v-if="canReschedule && !showRescheduleForm"
                                    @click="showRescheduleForm = true"
                                    class="flex w-full items-center justify-center rounded-xl border border-electric-200 bg-electric-50 px-4 py-2.5 text-sm font-medium text-electric-700 shadow-sm hover:bg-electric-100 transition"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Propose New Time
                                </button>

                                <!-- Reschedule Form -->
                                <div v-if="showRescheduleForm" class="rounded-lg border border-electric-200 bg-electric-50 p-4 space-y-3">
                                    <p class="text-sm font-semibold text-navy-950">Propose New Date &amp; Time</p>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">New Date</label>
                                        <input
                                            type="date"
                                            v-model="rescheduleForm.proposed_date"
                                            :min="new Date().toISOString().split('T')[0]"
                                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                        />
                                        <p v-if="rescheduleForm.errors.proposed_date" class="mt-1 text-xs text-red-600">{{ rescheduleForm.errors.proposed_date }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">New Time</label>
                                        <input
                                            type="time"
                                            v-model="rescheduleForm.proposed_time"
                                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                        />
                                        <p v-if="rescheduleForm.errors.proposed_time" class="mt-1 text-xs text-red-600">{{ rescheduleForm.errors.proposed_time }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500">An email will be sent to the customer with Accept / Decline links.</p>
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="submitReschedule"
                                            :disabled="rescheduleForm.processing"
                                            class="flex-1 rounded-lg bg-electric-600 px-3 py-2 text-xs font-medium text-white hover:bg-electric-700 disabled:opacity-50 transition"
                                        >
                                            {{ rescheduleForm.processing ? 'Sending...' : 'Send to Customer' }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="showRescheduleForm = false"
                                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 transition"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Customer</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ booking.customer.name }}</p>
                                </div>
                                <div v-if="booking.customer.email" class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ booking.customer.email }}
                                </div>
                                <div v-if="booking.customer.phone" class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ booking.customer.phone }}
                                </div>
                                <Link
                                    :href="route(`/customers/${booking.customer.id}`)"
                                    class="mt-2 inline-flex items-center text-sm font-medium text-electric-600 hover:text-electric-700 transition"
                                >
                                    View Customer Profile
                                    <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Vehicle Info -->
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Vehicle</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="inline-block rounded-lg bg-yellow-100 px-3 py-1 text-sm font-bold text-yellow-800 tracking-wider">
                                        {{ booking.vehicle.registration }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-900">
                                    {{ booking.vehicle.make }} {{ booking.vehicle.model }}
                                    <span v-if="booking.vehicle.year"> ({{ booking.vehicle.year }})</span>
                                </p>
                                <p v-if="booking.vehicle.colour" class="text-sm text-gray-600">
                                    Colour: {{ booking.vehicle.colour }}
                                </p>
                                <p v-if="booking.vehicle.vin" class="text-sm text-gray-600">
                                    VIN: {{ booking.vehicle.vin }}
                                </p>
                                <Link
                                    :href="route(`/vehicles/${booking.vehicle.id}`)"
                                    class="mt-2 inline-flex items-center text-sm font-medium text-electric-600 hover:text-electric-700 transition"
                                >
                                    View Vehicle
                                    <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
