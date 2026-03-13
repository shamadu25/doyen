<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    tickets: any
    stats: { open: number; in_progress: number; resolved: number; total: number }
    filters: Record<string, string>
}>()

const search   = ref(props.filters.search   || '')
const status   = ref(props.filters.status   || '')
const priority = ref(props.filters.priority || '')
const category = ref(props.filters.category || '')

function applyFilters() {
    router.get('/tickets', { search: search.value, status: status.value, priority: priority.value, category: category.value }, { preserveState: true, replace: true })
}

const priorityBadge: Record<string, string> = {
    urgent: 'bg-red-100 text-red-700',
    high:   'bg-orange-100 text-orange-700',
    medium: 'bg-yellow-100 text-yellow-700',
    low:    'bg-gray-100 text-gray-600',
}
const statusBadge: Record<string, string> = {
    open:        'bg-blue-100 text-blue-700',
    in_progress: 'bg-yellow-100 text-yellow-700',
    resolved:    'bg-green-100 text-green-700',
    closed:      'bg-gray-100 text-gray-500',
}
function fmt(s: string) { return s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }
</script>

<template>
    <Head title="Support Tickets" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage customer support requests</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ stats.open }}</p>
                    <p class="text-xs text-gray-500 mt-1">Open</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ stats.in_progress }}</p>
                    <p class="text-xs text-gray-500 mt-1">In Progress</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ stats.resolved }}</p>
                    <p class="text-xs text-gray-500 mt-1">Resolved</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-700">{{ stats.total }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                        <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Ticket #, subject, customer…"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select v-model="status" @change="applyFilters" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                        <select v-model="priority" @change="applyFilters" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Priorities</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <select v-model="category" @change="applyFilters" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <option value="general">General</option>
                            <option value="billing">Billing</option>
                            <option value="technical">Technical</option>
                            <option value="service">Service</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>
                    <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Search</button>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div v-if="!tickets.data?.length" class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="font-medium">No support tickets found</p>
                    <p class="text-sm mt-1">Try adjusting your filters</p>
                </div>
                <table v-else class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-blue-700 text-sm">{{ ticket.ticket_number }}</p>
                                <p class="text-sm text-gray-700 mt-0.5 max-w-xs truncate">{{ ticket.subject }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ ticket.customer?.name }}</p>
                                <p class="text-xs text-gray-500">{{ ticket.customer?.email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-600 capitalize">{{ ticket.category }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold capitalize', priorityBadge[ticket.priority] || 'bg-gray-100 text-gray-600']">
                                    {{ ticket.priority }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold', statusBadge[ticket.status] || 'bg-gray-100 text-gray-600']">
                                    {{ fmt(ticket.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ fmtDate(ticket.created_at) }}</td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="`/tickets/${ticket.id}`" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View →</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="tickets.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500">Showing {{ tickets.from }}–{{ tickets.to }} of {{ tickets.total }}</p>
                    <div class="flex gap-2">
                        <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-50">← Prev</Link>
                        <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-50">Next →</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
