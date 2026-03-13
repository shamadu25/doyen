<script setup lang="ts">
import { ref, watch, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Customer {
    id: number
    first_name: string
    last_name: string
    email: string
    phone: string
    vehicles_count: number
    outstanding_balance: number
}

interface PaginatedCustomers {
    data: Customer[]
    links: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

const props = defineProps<{
    customers: PaginatedCustomers
    filters: {
        search?: string
    }
}>()

const search = ref(props.filters.search ?? '')

watch(search, (value) => {
    router.get(route('/customers'), { search: value || undefined }, {
        preserveState: true,
        replace: true,
    })
})

const formatCurrency = (amount: number): string => {
    return '£' + Number(amount ?? 0).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const deleteCustomer = (customer: Customer) => {
    if (confirm(`Are you sure you want to delete ${customer.first_name} ${customer.last_name}?`)) {
        router.delete(route(`/customers/${customer.id}`))
    }
}
</script>

<template>
    <Head title="Customers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Customers</h2>
                <Link
                    :href="route('/customers/create')"
                    class="inline-flex items-center rounded-lg bg-electric-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 focus:outline-none focus:ring-2 focus:ring-electric-600 focus:ring-offset-2 transition"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Customer
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search customers by name, email or phone..."
                                class="w-full rounded-lg border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-electric-600 focus:ring-electric-600"
                            />
                        </div>
                        <span class="text-sm text-gray-500">{{ customers.total }} customer{{ customers.total !== 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Vehicles</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Outstanding Balance</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="customer in customers.data"
                                :key="customer.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link :href="route(`/customers/${customer.id}`)" class="font-medium text-electric-600 hover:text-electric-700">
                                        {{ customer.first_name }} {{ customer.last_name }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ customer.email }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ customer.phone }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-600">
                                    <span class="inline-flex items-center rounded-full bg-electric-50 px-2.5 py-0.5 text-xs font-medium text-electric-700">
                                        {{ customer.vehicles_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                    :class="customer.outstanding_balance > 0 ? 'text-red-600' : 'text-green-600'"
                                >
                                    {{ formatCurrency(customer.outstanding_balance) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route(`/customers/${customer.id}`)"
                                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"
                                            title="View"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route(`/customers/${customer.id}/edit`)"
                                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-electric-600 transition"
                                            title="Edit"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deleteCustomer(customer)"
                                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-red-600 transition"
                                            title="Delete"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="customers.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="font-medium text-gray-600">No customers found</p>
                                        <p class="mt-1 text-gray-400">Try adjusting your search or add a new customer.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="customers.last_page > 1" class="border-t border-gray-200 bg-gray-50 px-6 py-3">
                        <Pagination :links="customers.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
