<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps<{
    payments: any
    filters: { search?: string; method?: string }
}>()

const search = ref(props.filters.search || '')
const method = ref(props.filters.method || '')

const route = inject<(path: string) => string>('route', (p) => p)

let debounce: any
watch([search, method], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('/payments'), { search: search.value, method: method.value }, { preserveState: true, replace: true })
    }, 300)
})

function fmt(amount: any) { return '£' + parseFloat(amount || 0).toFixed(2) }
</script>

<template>
    <Head title="Payments" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
                    <p class="mt-1 text-sm text-gray-500">Record and track payments</p>
                </div>
                <Link :href="route('/payments/create')" class="bg-electric-600 hover:bg-electric-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Record Payment</Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
                    <input v-model="search" type="text" placeholder="Search payments..." class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600 w-64" />
                    <select v-model="method" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="stripe">Stripe</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="pay in payments.data" :key="pay.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Link :href="route(`/payments/${pay.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">{{ pay.payment_number || `PAY-${pay.id}` }}</Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <Link v-if="pay.invoice" :href="route(`/invoices/${pay.invoice.id}`)" class="text-electric-600 hover:text-electric-700">{{ pay.invoice.invoice_number }}</Link>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ pay.customer?.first_name }} {{ pay.customer?.last_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">{{ fmt(pay.amount) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ (pay.payment_method || '').replace('_',' ') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pay.payment_date ? new Date(pay.payment_date).toLocaleDateString('en-GB') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap"><StatusBadge :status="pay.status || 'completed'" size="sm" /></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <Link :href="route(`/payments/${pay.id}`)" class="text-electric-600 hover:text-electric-700 text-sm">View</Link>
                                </td>
                            </tr>
                            <tr v-if="!payments.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No payments found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="payments.links" :from="payments.from" :to="payments.to" :total="payments.total" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
