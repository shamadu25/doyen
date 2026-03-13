<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps<{ quotes: any }>()
const route = inject<(p: string) => string>('route', p => p)
const search = ref('')
const status = ref('')

function filter() {
    router.get(route('/quotes'), { search: search.value, status: status.value }, { preserveState: true })
}
function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }
</script>

<template>
    <Head title="Quotes" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quotes & Estimates</h1>
                    <p class="text-sm text-gray-500 mt-1">Create and manage customer quotes</p>
                </div>
                <Link :href="route('/quotes/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Quote
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex gap-3 flex-wrap">
                <input v-model="search" @input="filter" type="text" placeholder="Search quote # or customer..." class="flex-1 min-w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" />
                <select v-model="status" @change="filter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="approved">Approved</option>
                    <option value="declined">Declined</option>
                    <option value="converted">Converted</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Quote #</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Customer</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Vehicle</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Valid Until</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!quotes.data?.length">
                            <td colspan="8" class="text-center text-gray-400 py-12">No quotes found.</td>
                        </tr>
                        <tr v-for="q in quotes.data" :key="q.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-medium text-electric-600">{{ q.quote_number }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ q.customer?.name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ q.vehicle?.registration_number || '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ fmtDate(q.quote_date) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ fmtDate(q.valid_until) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ fmt(q.total_amount) }}</td>
                            <td class="px-4 py-3"><StatusBadge :status="q.status" /></td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route(`/quotes/${q.id}`)" class="text-electric-600 hover:underline text-sm">View</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div v-if="quotes.links?.length > 3" class="flex justify-center gap-1 p-4 border-t border-gray-100">
                    <component
                        v-for="link in quotes.links" :key="link.label"
                        :is="link.url ? Link : 'span'"
                        :href="link.url || undefined"
                        v-html="link.label"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-electric-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 cursor-default' : '']"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
