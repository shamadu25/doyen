<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any; tickets: any }>()

const statusBadge: Record<string, string> = {
    open:        'bg-blue-50 text-blue-700',
    in_progress: 'bg-yellow-50 text-yellow-700',
    resolved:    'bg-green-50 text-green-700',
    closed:      'bg-gray-100 text-gray-500',
}
function fmt(s: string) { return s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }
</script>

<template>
    <Head title="Support Tickets" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Submit and track your support requests</p>
                </div>
                <Link href="/customer/tickets/create"
                    class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Ticket
                </Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div v-if="!tickets.data?.length" class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="font-medium text-gray-600">No support tickets yet</p>
                    <p class="text-sm mt-1">Need help? Open a new ticket and we'll get back to you.</p>
                    <Link href="/customer/tickets/create" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700">Open a Ticket</Link>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <Link v-for="ticket in tickets.data" :key="ticket.id"
                        :href="`/customer/tickets/${ticket.id}`"
                        class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors block">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-semibold text-blue-600">{{ ticket.ticket_number }}</span>
                                <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-medium', statusBadge[ticket.status] || 'bg-gray-100 text-gray-600']">{{ fmt(ticket.status) }}</span>
                                <span class="text-xs text-gray-400 capitalize">{{ ticket.category }}</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900 mt-0.5 truncate">{{ ticket.subject }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ fmtDate(ticket.created_at) }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="tickets.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500">{{ tickets.from }}–{{ tickets.to }} of {{ tickets.total }}</p>
                    <div class="flex gap-2">
                        <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="px-3 py-1 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">← Prev</Link>
                        <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="px-3 py-1 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Next →</Link>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
