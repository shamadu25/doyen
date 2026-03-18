<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any, invoices: any }>()
function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }
const statusColor: Record<string, string> = {
    draft: 'bg-gray-50 text-gray-600', sent: 'bg-electric-50 text-electric-700',
    paid: 'bg-green-50 text-green-700', overdue: 'bg-red-50 text-red-700',
    partial: 'bg-yellow-50 text-yellow-700', cancelled: 'bg-gray-50 text-gray-500',
}
</script>

<template>
    <Head title="My Invoices" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <h1 class="text-xl font-bold text-gray-900">My Invoices</h1>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div v-if="!invoices.data?.length" class="text-center text-gray-400 py-10 text-sm">No invoices found.</div>
                <div v-else class="divide-y divide-gray-100">
                    <div v-for="inv in invoices.data" :key="inv.id" class="px-5 py-4 flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ inv.invoice_number }}</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ fmtDate(inv.invoice_date) }} · Due {{ fmtDate(inv.due_date) }}</p>
                            <div v-if="parseFloat(inv.paid_amount) > 0 && inv.status !== 'paid'" class="text-xs text-gray-500 mt-1">
                                Paid {{ fmt(inv.paid_amount) }} of {{ fmt(inv.total_amount) }}
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 space-y-1.5">
                            <p class="font-semibold text-gray-900">{{ fmt(inv.total_amount) }}</p>
                            <span :class="['inline-block text-xs px-2 py-0.5 rounded-full font-medium capitalize', statusColor[inv.status] || 'bg-gray-50 text-gray-600']">{{ inv.status }}</span>
                            <div>
                                <a :href="`/customer/invoices/${inv.id}/download`" target="_blank"
                                    class="inline-flex items-center gap-1 text-xs text-electric-600 hover:text-electric-700 font-medium hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
