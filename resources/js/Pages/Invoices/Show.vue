<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, inject, ref, nextTick } from 'vue'

const garageSettings = computed(() => (usePage().props as any).garageSettings ?? {})
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { loadStripe } from '@stripe/stripe-js'

const props = defineProps<{ invoice: any }>()
const inv = computed(() => props.invoice)
const route = inject<(path: string) => string>('route', (p) => p)

function fmt(amount: any) { return '£' + parseFloat(amount || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

const balance = computed(() => {
    const total = parseFloat(inv.value.total || inv.value.total_amount || 0)
    const paid  = parseFloat(inv.value.paid_amount || 0)
    return Math.max(0, total - paid)
})

function sendInvoice() {
    if (confirm('Send invoice to customer?')) router.post(route(`/invoices/${inv.value.id}/send`))
}
function markPaid() {
    if (confirm('Mark invoice as paid?')) router.post(route(`/invoices/${inv.value.id}/mark-paid`))
}
function creditNote() {
    if (confirm('Issue a credit note for this invoice?')) router.post(route(`/invoices/${inv.value.id}/credit-note`))
}

// ── Stripe ───────────────────────────────────────────────────────────────────
const showStripeModal = ref(false)
const stripeLoading  = ref(false)
const stripeError    = ref('')
const stripeSuccess  = ref(false)
const stripeAmount   = ref('')
const cardRef        = ref<HTMLElement | null>(null)
let stripe: any = null
let cardElement: any = null

async function openStripeModal() {
    stripeError.value   = ''
    stripeSuccess.value = false
    stripeAmount.value  = balance.value.toFixed(2)
    showStripeModal.value = true
    await nextTick()

    if (!stripe) {
        stripe = await loadStripe((import.meta as any).env.VITE_STRIPE_KEY || '')
    }
    const elements = stripe.elements()
    cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#111827',
                fontFamily: 'ui-sans-serif, system-ui, sans-serif',
                '::placeholder': { color: '#9CA3AF' },
            },
            invalid: { color: '#EF4444' },
        },
    })
    if (cardRef.value) cardElement.mount(cardRef.value)
}

async function submitStripePayment() {
    stripeLoading.value = true
    stripeError.value   = ''
    try {
        const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
        const res  = await fetch('/payments/stripe', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ invoice_id: inv.value.id, amount: parseFloat(stripeAmount.value) }),
        })
        const data = await res.json()
        if (data.error) throw new Error(data.error)

        const result = await stripe.confirmCardPayment(data.client_secret, {
            payment_method: { card: cardElement },
        })
        if (result.error) throw new Error(result.error.message)

        stripeSuccess.value = true
        cardElement.unmount()
        setTimeout(() => router.reload(), 2500)
    } catch (e: any) {
        stripeError.value = e.message || 'Payment failed. Please try again.'
    } finally {
        stripeLoading.value = false
    }
}

function closeStripeModal() {
    if (cardElement) { try { cardElement.unmount() } catch {} }
    showStripeModal.value = false
    stripeError.value = ''
    stripeSuccess.value = false
}
</script>

<template>
    <Head :title="`Invoice ${inv.invoice_number}`" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ inv.invoice_number }}</h1>
                        <StatusBadge :status="inv.status" />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Issued {{ fmtDate(inv.invoice_date) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route(`/invoices/${inv.id}/edit`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button v-if="inv.status === 'draft'" @click="sendInvoice" class="px-4 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700">Send</button>
                    <button v-if="inv.status === 'sent' || inv.status === 'overdue' || inv.status === 'partial'" @click="openStripeModal" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        Take Card Payment
                    </button>
                    <button v-if="inv.status === 'sent' || inv.status === 'overdue'" @click="markPaid" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Mark Paid</button>
                    <a :href="route(`/invoices/${inv.id}/download`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Download PDF</a>
                    <button v-if="inv.status === 'paid'" @click="creditNote" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Credit Note</button>
                    <Link :href="route('/invoices')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
                </div>
            </div>

            <!-- Invoice Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
                <!-- Header -->
                <div class="flex justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ garageSettings.garage_name || 'Doyen Auto Services' }}</h2>
                        <p class="text-sm text-gray-500">{{ garageSettings.address || '59 Southcroft Road' }}, {{ garageSettings.city || 'Rutherglen, Glasgow' }}</p>
                        <p class="text-sm text-gray-500">{{ garageSettings.postcode || 'G73 1UG' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900">INVOICE</p>
                        <p class="text-sm text-gray-600">{{ inv.invoice_number }}</p>
                        <p class="text-sm text-gray-500 mt-1">Date: {{ fmtDate(inv.invoice_date) }}</p>
                        <p v-if="inv.due_date" class="text-sm text-gray-500">Due: {{ fmtDate(inv.due_date) }}</p>
                    </div>
                </div>

                <!-- Bill To -->
                <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Bill To</p>
                    <p class="font-medium text-gray-900">{{ inv.customer?.first_name }} {{ inv.customer?.last_name }}</p>
                    <p v-if="inv.customer?.email" class="text-sm text-gray-600">{{ inv.customer?.email }}</p>
                    <p v-if="inv.customer?.phone" class="text-sm text-gray-600">{{ inv.customer?.phone }}</p>
                    <p v-if="inv.customer?.address" class="text-sm text-gray-600">{{ inv.customer?.address }}</p>
                </div>

                <!-- Vehicle -->
                <div v-if="inv.vehicle" class="mb-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Vehicle</p>
                    <p class="text-sm text-gray-900">{{ inv.vehicle?.registration_number }} - {{ inv.vehicle?.make }} {{ inv.vehicle?.model }} {{ inv.vehicle?.year }}</p>
                </div>

                <!-- Items Table  -->
                <table class="min-w-full divide-y divide-gray-200 text-sm mb-6">
                    <thead>
                        <tr>
                            <th class="text-left py-3 font-medium text-gray-500">Description</th>
                            <th class="text-center py-3 font-medium text-gray-500">Type</th>
                            <th class="text-right py-3 font-medium text-gray-500">Qty</th>
                            <th class="text-right py-3 font-medium text-gray-500">Unit Price</th>
                            <th class="text-right py-3 font-medium text-gray-500">VAT</th>
                            <th class="text-right py-3 font-medium text-gray-500">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in (inv.items || inv.invoice_items || [])" :key="item.id">
                            <td class="py-3 text-gray-900">{{ item.description }}</td>
                            <td class="py-3 text-center text-gray-500 capitalize">{{ item.type || '-' }}</td>
                            <td class="py-3 text-right text-gray-600">{{ item.quantity }}</td>
                            <td class="py-3 text-right text-gray-600">{{ fmt(item.unit_price) }}</td>
                            <td class="py-3 text-right text-gray-500">{{ fmt(parseFloat(item.vat_amount ?? 0)) }}</td>
                            <td class="py-3 text-right font-medium text-gray-900">{{ fmt(parseFloat(item.line_total ?? (parseFloat(item.quantity) * parseFloat(item.unit_price))) + parseFloat(item.vat_amount ?? 0)) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="border-t pt-4 space-y-2 max-w-xs ml-auto text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal:</span><span class="font-medium">{{ fmt(inv.subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">VAT (20%):</span><span class="font-medium">{{ fmt(inv.vat_amount || inv.tax_amount) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="font-bold text-gray-900">Total:</span><span class="font-bold text-electric-600 text-xl">{{ fmt(inv.total) }}</span></div>
                </div>

                <!-- Notes -->
                <div v-if="inv.notes" class="mt-8 pt-4 border-t">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Notes</p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ inv.notes }}</p>
                </div>
            </div>

            <!-- Payments -->
            <div v-if="(inv.payments || []).length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payments</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr>
                            <th class="text-left py-2 text-gray-500 font-medium">Date</th>
                            <th class="text-left py-2 text-gray-500 font-medium">Method</th>
                            <th class="text-right py-2 text-gray-500 font-medium">Amount</th>
                            <th class="text-left py-2 text-gray-500 font-medium">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in inv.payments" :key="p.id">
                            <td class="py-2 text-gray-900">{{ fmtDate(p.payment_date) }}</td>
                            <td class="py-2 text-gray-600 capitalize">{{ p.payment_method }}</td>
                            <td class="py-2 text-right font-medium text-green-600">{{ fmt(p.amount) }}</td>
                            <td class="py-2 text-gray-500">{{ p.reference || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ── Stripe Payment Modal ──────────────────────────────────────────── -->
    <Teleport to="body">
        <div v-if="showStripeModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50" @click="closeStripeModal"></div>

            <!-- Modal panel -->
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8">
                <!-- Success state -->
                <div v-if="stripeSuccess" class="text-center py-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-9 h-9 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Payment Successful!</h3>
                    <p class="text-gray-500 text-sm">{{ fmt(stripeAmount) }} received. Refreshing&hellip;</p>
                </div>

                <!-- Payment form -->
                <template v-else>
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Card Payment</h3>
                            <p class="text-sm text-gray-500 mt-0.5">{{ inv.invoice_number }}</p>
                        </div>
                        <button @click="closeStripeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Amount -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (&pound;)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">&pound;</span>
                            <input
                                v-model="stripeAmount"
                                type="number"
                                step="0.01"
                                min="0.50"
                                class="w-full pl-7 rounded-lg border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500"
                            />
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Balance outstanding: {{ fmt(balance) }}</p>
                    </div>

                    <!-- Stripe Card Element -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Card Details</label>
                        <div ref="cardRef" class="p-3 border border-gray-300 rounded-lg bg-white focus-within:border-purple-500 focus-within:ring-1 focus-within:ring-purple-500"></div>
                    </div>

                    <!-- Error -->
                    <div v-if="stripeError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700 flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ stripeError }}
                        </p>
                    </div>

                    <!-- Secure badge + Submit -->
                    <button
                        @click="submitStripePayment"
                        :disabled="stripeLoading"
                        class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 disabled:opacity-60 text-white font-bold rounded-xl transition flex items-center justify-center gap-2"
                    >
                        <svg v-if="!stripeLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ stripeLoading ? 'Processing&hellip;' : `Charge ${fmt(stripeAmount)}` }}
                    </button>
                    <p class="text-center text-xs text-gray-400 mt-3">
                        <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                        Secured by Stripe &mdash; card details never touch our server
                    </p>
                </template>
            </div>
        </div>
    </Teleport>
</template>
