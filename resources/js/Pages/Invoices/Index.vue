<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps<{
    invoices: any
    filters: { search?: string; status?: string }
}>()

const route = inject<(path: string) => string>('route', (p) => p)

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

let debounce: any
watch([search, status], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('/invoices'), { search: search.value, status: status.value }, { preserveState: true, replace: true })
    }, 300)
})

function fmt(amount: any) {
    return '£' + parseFloat(amount || 0).toFixed(2)
}
</script>

<template>
    <Head title="Invoices" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage customer invoices</p>
                </div>
                <Link :href="route('/invoices/create')" class="bg-electric-600 hover:bg-electric-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">New Invoice</Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
                    <input v-model="search" type="text" placeholder="Search invoices..." class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600 flex-1 min-w-0" />
                    <select v-model="status" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="hidden md:table-cell px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="hidden md:table-cell px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">VAT</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Link :href="route(`/invoices/${inv.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">{{ inv.invoice_number }}</Link>
                                    <p class="sm:hidden text-xs text-gray-500 mt-0.5">{{ inv.invoice_date ? new Date(inv.invoice_date).toLocaleDateString('en-GB') : '-' }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ inv.customer?.first_name }} {{ inv.customer?.last_name }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ inv.invoice_date ? new Date(inv.invoice_date).toLocaleDateString('en-GB') : '-' }}</td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">{{ fmt(inv.subtotal) }}</td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">{{ fmt(inv.vat_amount || inv.tax_amount) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ fmt(inv.total) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap"><StatusBadge :status="inv.status" size="sm" /></td>
                                <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                    <Link :href="route(`/invoices/${inv.id}`)" class="text-electric-600 hover:text-electric-700 text-sm">View</Link>
                                    <a :href="route(`/invoices/${inv.id}/download`)" class="text-green-600 hover:text-green-700 text-sm">PDF</a>
                                </td>
                            </tr>
                            <tr v-if="!invoices.data?.length">
                                <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">No invoices found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="invoices.links" :from="invoices.from" :to="invoices.to" :total="invoices.total" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
