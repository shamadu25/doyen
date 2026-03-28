<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const props = defineProps<{ customer: any; quotes: any }>()

const flash = computed(() => (usePage().props.flash as any) ?? {})

function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '—' }
function quoteDiscountFactor(quote: any) {
    const subtotal = parseFloat(String(quote?.subtotal || 0))
    const discount = parseFloat(String(quote?.discount_amount || 0))
    return subtotal > 0 ? ((subtotal - discount) / subtotal) : 1
}
function lineNet(item: any, quote: any) {
    const rawNet = parseFloat(String(item?.total_price ?? ((item?.quantity || 0) * (item?.unit_price || 0))))
    return rawNet * quoteDiscountFactor(quote)
}
function lineVat(item: any, quote: any) {
    if (item?.tax_exempt) return 0
    const rate = parseFloat(String(item?.vat_rate ?? quote?.vat_rate ?? 20))
    return lineNet(item, quote) * (rate / 100)
}
function lineGross(item: any, quote: any) { return lineNet(item, quote) + lineVat(item, quote) }

const confirmAction = ref<{ quoteId: number; action: 'approve' | 'reject' } | null>(null)
const submitting = ref(false)

function confirmApprove(id: number) { confirmAction.value = { quoteId: id, action: 'approve' } }
function confirmReject(id: number)  { confirmAction.value = { quoteId: id, action: 'reject'  } }

function executeAction() {
    if (!confirmAction.value) return
    submitting.value = true
    const { quoteId, action } = confirmAction.value
    router.post(`/customer/quotes/${quoteId}/${action}`, {}, {
        onFinish: () => { submitting.value = false; confirmAction.value = null },
    })
}

const statusLabels: Record<string, string> = {
    draft: 'Draft', sent: 'Awaiting Approval', approved: 'Approved',
    declined: 'Declined', expired: 'Expired', converted: 'Converted',
}
const statusColors: Record<string, string> = {
    draft:    'bg-gray-50 text-gray-600',
    sent:     'bg-electric-50 text-electric-700 ring-1 ring-electric-200',
    approved: 'bg-green-50 text-green-700',
    declined: 'bg-red-50 text-red-700',
    expired:  'bg-orange-50 text-orange-700',
    converted: 'bg-teal-50 text-teal-700',
}

function isExpired(q: any) {
    return q.valid_until && new Date(q.valid_until) < new Date()
}
</script>

<template>
    <Head title="My Quotes" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <h1 class="text-xl font-bold text-gray-900">My Quotes</h1>

            <!-- Flash -->
            <div v-if="flash.success" class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ flash.success }}
            </div>

            <div v-if="!quotes.data?.length"
                class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
                No quotes on record yet.
            </div>

            <!-- Quote cards -->
            <div v-for="quote in quotes.data" :key="quote.id"
                class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-5 py-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900">{{ quote.quote_number }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ quote.vehicle?.registration_number }}
                            <span v-if="quote.vehicle"> · {{ quote.vehicle.make }} {{ quote.vehicle.model }}</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Issued {{ fmtDate(quote.quote_date) }}
                            <span v-if="quote.valid_until"> · Valid until {{ fmtDate(quote.valid_until) }}</span>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-lg font-bold text-gray-900">{{ fmt(quote.total_amount) }}</p>
                        <span :class="['inline-block text-xs px-2 py-0.5 rounded-full font-medium mt-1', statusColors[quote.status] ?? 'bg-gray-50 text-gray-600']">
                            {{ statusLabels[quote.status] ?? quote.status }}
                        </span>
                    </div>
                </div>

                <!-- Line items -->
                <div v-if="quote.items?.length" class="border-t border-gray-100 px-5 py-3">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="text-left font-medium py-1.5">Description</th>
                                <th class="text-right font-medium py-1.5">Qty</th>
                                <th class="text-right font-medium py-1.5">Unit</th>
                                <th class="text-right font-medium py-1.5">VAT</th>
                                <th class="text-right font-medium py-1.5">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in quote.items" :key="item.id">
                                <td class="py-1.5 text-gray-700">{{ item.description }}</td>
                                <td class="py-1.5 text-right text-gray-500 text-xs">{{ item.quantity ?? 1 }}×</td>
                                <td class="py-1.5 text-right text-gray-700">{{ fmt(item.unit_price) }}</td>
                                <td class="py-1.5 text-right text-gray-500 text-xs">
                                    <template v-if="item.tax_exempt">Exempt</template>
                                    <template v-else>{{ item.vat_rate ?? quote.vat_rate }}% / {{ fmt(lineVat(item, quote)) }}</template>
                                </td>
                                <td class="py-1.5 text-right font-medium text-gray-900 pl-4">{{ fmt(lineGross(item, quote)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Description (no items) -->
                <div v-else-if="quote.description" class="border-t border-gray-100 px-5 py-3 text-sm text-gray-600">
                    {{ quote.description }}
                </div>

                <!-- Expired warning -->
                <div v-if="isExpired(quote) && quote.status === 'sent'"
                    class="border-t border-orange-100 bg-orange-50 px-5 py-2.5 text-xs text-orange-700 font-medium">
                    ⚠ This quote has expired. Contact us for an updated quote.
                </div>

                <!-- Approve / Reject (only for sent + not expired) -->
                <div v-if="quote.status === 'sent' && !isExpired(quote)"
                    class="border-t border-gray-100 bg-gray-50 px-5 py-3 flex gap-3">
                    <button @click="confirmApprove(quote.id)"
                        class="flex-1 rounded-lg bg-electric-600 py-2 text-sm font-semibold text-white hover:bg-electric-700 transition">
                        ✓ Approve Quote
                    </button>
                    <button @click="confirmReject(quote.id)"
                        class="flex-1 rounded-lg border border-red-200 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                        Decline
                    </button>
                </div>

                <!-- Approved banner -->
                <div v-if="quote.status === 'approved'"
                    class="border-t border-green-100 bg-green-50 px-5 py-2.5 text-xs text-green-700 font-medium">
                    ✓ You approved this quote{{ quote.approved_at ? ' on ' + fmtDate(quote.approved_at) : '' }}. We'll be in touch to schedule the work.
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="quotes.last_page > 1" class="flex justify-center gap-1 pt-2">
                <template v-for="link in quotes.links" :key="link.label">
                    <a v-if="link.url" :href="link.url"
                        :class="['px-3 py-1.5 text-sm rounded border', link.active ? 'bg-electric-600 text-white border-electric-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']"
                        v-html="link.label" />
                </template>
            </div>
        </div>

        <!-- Confirmation modal -->
        <Teleport to="body">
            <div v-if="confirmAction" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">
                        {{ confirmAction.action === 'approve' ? 'Approve this quote?' : 'Decline this quote?' }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">
                        {{ confirmAction.action === 'approve'
                            ? 'By approving, you authorise us to carry out the work described. We will contact you to arrange a booking.'
                            : 'You can contact us if you have any questions about this quote.' }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="confirmAction = null"
                            class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="executeAction" :disabled="submitting"
                            :class="['flex-1 rounded-lg py-2.5 text-sm font-semibold text-white disabled:opacity-50', confirmAction.action === 'approve' ? 'bg-electric-600 hover:bg-electric-700' : 'bg-red-600 hover:bg-red-700']">
                            {{ submitting ? 'Processing…' : confirmAction.action === 'approve' ? 'Yes, Approve' : 'Yes, Decline' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </CustomerPortalLayout>
</template>
