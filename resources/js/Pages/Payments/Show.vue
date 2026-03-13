<script setup lang="ts">
import { inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{ payment: any }>()
const pay = props.payment

function fmt(amount: any) { return '£' + parseFloat(amount || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

function refund() {
    if (confirm('Refund this payment?')) router.post(route(`/payments/${pay.id}/refund`))
}
</script>

<template>
    <Head :title="`Payment ${pay.payment_number || pay.id}`" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ pay.payment_number || `Payment #${pay.id}` }}</h1>
                        <StatusBadge :status="pay.status || 'completed'" />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Recorded {{ fmtDate(pay.payment_date) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="pay.status !== 'refunded'" @click="refund" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Refund</button>
                    <Link :href="route('/payments')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="text-center mb-6">
                    <div class="text-4xl font-bold text-green-600">{{ fmt(pay.amount) }}</div>
                    <p class="text-sm text-gray-500 mt-1 capitalize">{{ (pay.payment_method || '').replace('_', ' ') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                    <div>
                        <span class="text-gray-500">Customer:</span>
                        <Link v-if="pay.customer" :href="route(`/customers/${pay.customer.id}`)" class="ml-2 text-electric-600 hover:text-electric-700 font-medium">{{ pay.customer?.first_name }} {{ pay.customer?.last_name }}</Link>
                    </div>
                    <div>
                        <span class="text-gray-500">Invoice:</span>
                        <Link v-if="pay.invoice" :href="route(`/invoices/${pay.invoice.id}`)" class="ml-2 text-electric-600 hover:text-electric-700 font-medium">{{ pay.invoice?.invoice_number }}</Link>
                        <span v-else class="ml-2 text-gray-400">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Date:</span>
                        <span class="ml-2 text-gray-900">{{ fmtDate(pay.payment_date) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Reference:</span>
                        <span class="ml-2 text-gray-900">{{ pay.reference || '-' }}</span>
                    </div>
                    <div v-if="pay.stripe_payment_id">
                        <span class="text-gray-500">Stripe ID:</span>
                        <span class="ml-2 text-gray-900 font-mono text-xs">{{ pay.stripe_payment_id }}</span>
                    </div>
                </div>

                <div v-if="pay.notes" class="mt-4 pt-4 border-t">
                    <span class="text-sm text-gray-500">Notes:</span>
                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ pay.notes }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
