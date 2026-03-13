<script setup lang="ts">
import { ref, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Vehicle {
    id: number
    registration: string
    make: string
    model: string
    year: number
    colour: string
    mot_expiry: string | null
}

interface Invoice {
    id: number
    invoice_number: string
    status: string
    total: number
    created_at: string
    due_date: string | null
}

interface Booking {
    id: number
    service_type: string
    status: string
    scheduled_at: string
    vehicle: {
        id: number
        registration: string
        make: string
        model: string
    } | null
}

interface Customer {
    id: number
    first_name: string
    last_name: string
    email: string
    phone: string
    mobile: string
    address: string
    city: string
    postcode: string
    notes: string
    customer_type: 'individual' | 'business'
    company_name: string | null
    vat_number: string | null
    outstanding_balance: number
    created_at: string
    vehicles: Vehicle[]
    invoices: Invoice[]
    bookings: Booking[]
}

const props = defineProps<{
    customer: Customer
}>()

const activeTab = ref<'vehicles' | 'invoices' | 'bookings'>('vehicles')

const formatCurrency = (amount: number): string => {
    return '£' + Number(amount ?? 0).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const deleteCustomer = () => {
    if (confirm(`Are you sure you want to delete ${props.customer.first_name} ${props.customer.last_name}? This action cannot be undone.`)) {
        router.delete(route(`/customers/${props.customer.id}`))
    }
}

const sendingInvite = ref(false)
const sendPortalInvite = () => {
    if (!confirm(`Send a Customer Portal invite to ${props.customer.email}?`)) return
    sendingInvite.value = true
    router.post(route(`/customers/${props.customer.id}/send-portal-invite`), {}, {
        onFinish: () => { sendingInvite.value = false },
    })
}
</script>

<template>
    <Head :title="`${customer.first_name} ${customer.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('/customers')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            {{ customer.first_name }} {{ customer.last_name }}
                        </h2>
                        <p v-if="customer.company_name" class="text-sm text-gray-500">{{ customer.company_name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route(`/customers/${customer.id}/edit`)"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </Link>
                    <button
                        @click="sendPortalInvite"
                        :disabled="sendingInvite"
                        class="inline-flex items-center rounded-lg border border-indigo-300 bg-white px-3 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-50 transition disabled:opacity-50"
                        :title="'Send customer portal access link to ' + customer.email"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ sendingInvite ? 'Sending...' : 'Portal Invite' }}
                    </button>
                    <Link
                        :href="route(`/vehicles/create?customer_id=${customer.id}`)"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Vehicle
                    </Link>
                    <Link
                        :href="route(`/bookings/create?customer_id=${customer.id}`)"
                        class="inline-flex items-center rounded-lg bg-electric-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        New Booking
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Customer Info Card -->
                <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Customer Information</h3>
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ customer.first_name }} {{ customer.last_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Customer Type</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="customer.customer_type === 'business' ? 'bg-purple-50 text-purple-700' : 'bg-electric-50 text-electric-700'"
                                    >
                                        {{ customer.customer_type === 'business' ? 'Business' : 'Individual' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a v-if="customer.email" :href="`mailto:${customer.email}`" class="text-electric-600 hover:text-electric-700">{{ customer.email }}</a>
                                    <span v-else class="text-gray-400">—</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a v-if="customer.phone" :href="`tel:${customer.phone}`" class="text-electric-600 hover:text-electric-700">{{ customer.phone }}</a>
                                    <span v-else class="text-gray-400">—</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Mobile</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a v-if="customer.mobile" :href="`tel:${customer.mobile}`" class="text-electric-600 hover:text-electric-700">{{ customer.mobile }}</a>
                                    <span v-else class="text-gray-400">—</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(customer.created_at) }}</dd>
                            </div>
                            <div v-if="customer.company_name" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ customer.company_name }}
                                    <span v-if="customer.vat_number" class="ml-2 text-gray-500">(VAT: {{ customer.vat_number }})</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <template v-if="customer.address || customer.city || customer.postcode">
                                        {{ [customer.address, customer.city, customer.postcode].filter(Boolean).join(', ') }}
                                    </template>
                                    <span v-else class="text-gray-400">—</span>
                                </dd>
                            </div>
                            <div v-if="customer.notes" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ customer.notes }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Summary Card -->
                    <div class="space-y-4">
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Summary</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Vehicles</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ customer.vehicles?.length ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Invoices</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ customer.invoices?.length ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Bookings</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ customer.bookings?.length ?? 0 }}</span>
                                </div>
                                <hr class="border-gray-200" />
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Outstanding</span>
                                    <span class="text-lg font-bold" :class="customer.outstanding_balance > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(customer.outstanding_balance) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            @click="deleteCustomer"
                            class="flex w-full items-center justify-center rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 transition"
                        >
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Customer
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-6">
                    <nav class="flex gap-1 rounded-xl bg-white p-1 shadow-sm">
                        <button
                            @click="activeTab = 'vehicles'"
                            class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition"
                            :class="activeTab === 'vehicles' ? 'bg-electric-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                        >
                            Vehicles ({{ customer.vehicles?.length ?? 0 }})
                        </button>
                        <button
                            @click="activeTab = 'invoices'"
                            class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition"
                            :class="activeTab === 'invoices' ? 'bg-electric-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                        >
                            Invoices ({{ customer.invoices?.length ?? 0 }})
                        </button>
                        <button
                            @click="activeTab = 'bookings'"
                            class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition"
                            :class="activeTab === 'bookings' ? 'bg-electric-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                        >
                            Bookings ({{ customer.bookings?.length ?? 0 }})
                        </button>
                    </nav>
                </div>

                <!-- Vehicles Tab -->
                <div v-if="activeTab === 'vehicles'" class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table v-if="customer.vehicles?.length" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Registration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Make / Model</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Colour</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">MOT Expiry</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="vehicle in customer.vehicles" :key="vehicle.id" class="hover:bg-gray-50 transition">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link :href="route(`/vehicles/${vehicle.id}`)" class="inline-flex items-center rounded-md bg-yellow-100 px-2.5 py-1 font-mono text-sm font-bold text-gray-900 hover:bg-yellow-200 transition">
                                        {{ vehicle.registration }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ vehicle.make }} {{ vehicle.model }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ vehicle.year }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ vehicle.colour }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span v-if="vehicle.mot_expiry" :class="new Date(vehicle.mot_expiry) < new Date() ? 'text-red-600 font-medium' : 'text-gray-600'">
                                        {{ formatDate(vehicle.mot_expiry) }}
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <Link :href="route(`/vehicles/${vehicle.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="px-6 py-12 text-center">
                        <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0H3m10 0h2m4 0a1 1 0 001-1v-4a1 1 0 00-.293-.707l-2-2A1 1 0 0016.586 8H13" />
                        </svg>
                        <p class="text-sm font-medium text-gray-600">No vehicles registered</p>
                        <Link :href="route(`/vehicles/create?customer_id=${customer.id}`)" class="mt-2 inline-flex text-sm text-electric-600 hover:text-electric-700">Add a vehicle &rarr;</Link>
                    </div>
                </div>

                <!-- Invoices Tab -->
                <div v-if="activeTab === 'invoices'" class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table v-if="customer.invoices?.length" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="invoice in customer.invoices" :key="invoice.id" class="hover:bg-gray-50 transition">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link :href="route(`/invoices/${invoice.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">
                                        {{ invoice.invoice_number }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDate(invoice.created_at) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    <span v-if="invoice.due_date">{{ formatDate(invoice.due_date) }}</span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <StatusBadge :status="invoice.status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    {{ formatCurrency(invoice.total) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <Link :href="route(`/invoices/${invoice.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="px-6 py-12 text-center">
                        <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-600">No invoices yet</p>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div v-if="activeTab === 'bookings'" class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table v-if="customer.bookings?.length" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Scheduled</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="booking in customer.bookings" :key="booking.id" class="hover:bg-gray-50 transition">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ booking.service_type }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    <Link v-if="booking.vehicle" :href="route(`/vehicles/${booking.vehicle.id}`)" class="text-electric-600 hover:text-electric-700">
                                        {{ booking.vehicle.registration }} — {{ booking.vehicle.make }} {{ booking.vehicle.model }}
                                    </Link>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDate(booking.scheduled_at) }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <StatusBadge :status="booking.status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <Link :href="route(`/bookings/${booking.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="px-6 py-12 text-center">
                        <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-600">No bookings yet</p>
                        <Link :href="route(`/bookings/create?customer_id=${customer.id}`)" class="mt-2 inline-flex text-sm text-electric-600 hover:text-electric-700">Create a booking &rarr;</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
