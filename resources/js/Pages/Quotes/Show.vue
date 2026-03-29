<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps<{ quote: any }>()
const route = inject<(p: string) => string>('route', p => p)

const q = computed(() => props.quote)
const discountFactor = computed(() => {
    const subtotal = parseFloat(String(q.value.subtotal || 0))
    const discount = parseFloat(String(q.value.discount_amount || 0))
    const discountedSubtotal = subtotal - discount
    return subtotal > 0 ? (discountedSubtotal / subtotal) : 1
})

function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }
function lineNet(item: any) { return (Number(item.quantity) || 0) * (Number(item.unit_price) || 0) }
function lineVat(item: any) {
    if (item.tax_exempt) return 0
    const itemVatRate = Number(item.vat_rate ?? q.value.vat_rate ?? 20)
    return lineNet(item) * discountFactor.value * (itemVatRate / 100)
}
function lineGross(item: any) { return (lineNet(item) * discountFactor.value) + lineVat(item) }

const statusColor: Record<string, string> = {
    draft: 'text-gray-600',
    sent: 'text-electric-600',
    approved: 'text-green-600',
    declined: 'text-red-600',
    converted: 'text-purple-600',
    expired: 'text-orange-600',
}

function sendQuote() { if (confirm('Send quote to customer?')) router.post(route(`/quotes/${q.value.id}/send`)) }
function sendForReview() { if (confirm('Send this quote to the customer for review and approval? They will receive an email with a secure link to approve or decline.')) router.post(route(`/quotes/${q.value.id}/send-for-review`)) }
function approveQuote() { if (confirm('Mark as approved?')) router.post(route(`/quotes/${q.value.id}/approve`)) }
function declineQuote() { if (confirm('Mark as declined?')) router.post(route(`/quotes/${q.value.id}/decline`)) }
function convertQuote() { if (confirm('Convert to Job Card?')) router.post(route(`/quotes/${q.value.id}/convert`)) }
function deleteQuote() { if (confirm('Delete this quote?')) router.delete(route(`/quotes/${q.value.id}`)) }
</script>

<template>
    <Head :title="`Quote ${q.quote_number}`" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('/quotes')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Quote {{ q.quote_number }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Created {{ fmtDate(q.quote_date) }} · Valid until {{ fmtDate(q.valid_until) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap justify-end">
                    <StatusBadge :status="q.status" />
                    <button v-if="q.status === 'draft' || q.status === 'sent'" @click="sendForReview" class="px-3 py-1.5 text-sm bg-electric-600 text-white rounded-lg hover:bg-electric-700">📧 Send to Customer for Approval</button>
                    <button v-if="q.status === 'sent'" @click="approveQuote" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
                    <button v-if="q.status === 'sent'" @click="declineQuote" class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Decline</button>
                    <button v-if="q.status === 'approved'" @click="convertQuote" class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">Convert to Job Card</button>
                    <Link v-if="q.status === 'converted' && q.converted_to_job_card_id" :href="route(`/job-cards/${q.converted_to_job_card_id}`)" class="px-3 py-1.5 text-sm bg-purple-100 text-purple-800 rounded-lg hover:bg-purple-200">
                        Open Job Card
                    </Link>
                    <a :href="route(`/quotes/${q.id}/download`)" target="_blank" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 inline-flex items-center gap-1">⬇ Download PDF</a>
                    <Link v-if="!['approved','converted'].includes(q.status)" :href="route(`/quotes/${q.id}/edit`)" class="px-3 py-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button v-if="q.status !== 'converted'" @click="deleteQuote" class="px-3 py-1.5 text-sm text-red-600 hover:text-red-800">Delete</button>
                </div>
            </div>

            <!-- Customer & Vehicle -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Customer</p>
                    <p class="font-semibold text-gray-900">{{ q.customer?.name }}</p>
                    <p class="text-sm text-gray-600">{{ q.customer?.email }}</p>
                    <p class="text-sm text-gray-600">{{ q.customer?.phone }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Vehicle</p>
                    <template v-if="q.vehicle">
                        <p class="font-semibold text-gray-900">{{ q.vehicle.registration_number }}</p>
                        <p class="text-sm text-gray-600">{{ q.vehicle.make }} {{ q.vehicle.model }} {{ q.vehicle.year }}</p>
                    </template>
                    <p v-else class="text-sm text-gray-400">No vehicle attached</p>
                </div>
            </div>

            <!-- Description -->
            <div v-if="q.description" class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Description</p>
                <p class="text-gray-700 text-sm">{{ q.description }}</p>
            </div>

            <!-- Line Items -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Line Items</h2>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Type</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Description</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">Qty</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">Unit Price</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">VAT</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">Net (ex. VAT)</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">Total (inc. VAT)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in q.items" :key="item.id">
                            <td class="px-4 py-3">
                                <span :class="['inline-block px-2 py-0.5 rounded text-xs font-medium', item.item_type === 'labour' ? 'bg-electric-50 text-electric-700' : item.item_type === 'part' ? 'bg-green-50 text-green-700' : 'bg-purple-50 text-purple-700']">{{ item.item_type }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ item.description }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ item.quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ fmt(item.unit_price) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                <template v-if="item.tax_exempt"><span class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Exempt</span></template>
                                <template v-else>{{ item.vat_rate ?? q.vat_rate }}% / {{ fmt(lineVat(item)) }}</template>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ fmt(lineNet(item)) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ fmt(lineGross(item)) }}</td>
                        </tr>
                    </tbody>
                </table>
                <!-- Totals -->
                <div class="border-t border-gray-100 px-4 py-4 space-y-2 bg-gray-50">
                    <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>{{ fmt(q.subtotal) }}</span></div>
                    <div v-if="q.discount_amount > 0" class="flex justify-between text-sm text-red-600"><span>Discount ({{ q.discount_percentage }}%)</span><span>-{{ fmt(q.discount_amount) }}</span></div>
                    <div class="flex justify-between text-sm text-gray-600"><span>VAT</span><span>{{ fmt(q.vat_amount) }}</span></div>
                    <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-200 pt-2"><span>Total</span><span>{{ fmt(q.total_amount) }}</span></div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="q.notes" class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                <p class="text-gray-700 text-sm whitespace-pre-line">{{ q.notes }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
