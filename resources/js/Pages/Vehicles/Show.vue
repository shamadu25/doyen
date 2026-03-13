<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Customer {
    id: number
    name: string
    email: string
    phone: string
}

interface MotTest {
    id: number
    test_date: string
    result: string
    expiry_date: string | null
    mileage: number | null
    advisory_notes: string | null
}

interface JobCard {
    id: number
    job_number: string
    status: string
    description: string
    created_at: string
    total: number | null
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
    booking_date: string
    booking_time: string | null
    status: string
    service_type: string | null
    notes: string | null
}

interface Vehicle {
    id: number
    customer_id: number
    registration_number: string
    make: string
    model: string
    variant: string | null
    year: number | null
    vin: string | null
    fuel_type: string | null
    transmission: string | null
    engine_size: string | null
    color: string | null
    mileage: number | null
    mot_due_date: string | null
    tax_due_date: string | null
    service_due_date: string | null
    notes: string | null
    created_at: string
    customer: Customer | null
    mot_tests: MotTest[]
    job_cards: JobCard[]
    invoices: Invoice[]
    bookings: Booking[]
}

const props = defineProps<{
    vehicle: Vehicle
}>()

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

function formatCurrency(amount: number | null): string {
    if (amount === null || amount === undefined) return '—'
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(amount)
}

function isDateDueSoon(dateStr: string | null): boolean {
    if (!dateStr) return false
    const today = new Date()
    const due = new Date(dateStr)
    const diffDays = (due.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)
    return diffDays >= 0 && diffDays <= 30
}

function isDateExpired(dateStr: string | null): boolean {
    if (!dateStr) return false
    return new Date(dateStr) < new Date()
}

function dateStatusClass(dateStr: string | null): string {
    if (isDateExpired(dateStr)) return 'text-red-600 font-semibold'
    if (isDateDueSoon(dateStr)) return 'text-amber-600 font-semibold'
    return 'text-gray-900'
}

function capitalize(str: string | null): string {
    if (!str) return '—'
    return str.charAt(0).toUpperCase() + str.slice(1)
}

function deleteVehicle() {
    if (confirm(`Are you sure you want to delete ${props.vehicle.registration_number}?`)) {
        router.delete(route(`/vehicles/${props.vehicle.id}`))
    }
}
</script>

<template>
    <Head :title="`${vehicle.registration_number} — ${vehicle.make} ${vehicle.model}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('/vehicles')" class="text-gray-500 hover:text-gray-700 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ vehicle.registration_number }}</h2>
                        <p class="text-sm text-gray-500">{{ vehicle.make }} {{ vehicle.model }} {{ vehicle.variant ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route(`/vehicles/${vehicle.id}/edit`)"
                        class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit
                    </Link>
                    <Link
                        :href="route(`/bookings/create?vehicle_id=${vehicle.id}&service_type=mot`)"
                        class="inline-flex items-center rounded-xl bg-amber-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-600 transition"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Book MOT
                    </Link>
                    <Link
                        :href="route(`/job-cards/create?vehicle_id=${vehicle.id}`)"
                        class="inline-flex items-center rounded-xl bg-electric-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        New Job Card
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Vehicle Info Card -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Registration</h4>
                            <p class="mt-1 text-lg font-bold text-gray-900">{{ vehicle.registration_number }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Make / Model</h4>
                            <p class="mt-1 text-gray-900">{{ vehicle.make }} {{ vehicle.model }} {{ vehicle.variant ?? '' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Year</h4>
                            <p class="mt-1 text-gray-900">{{ vehicle.year ?? '—' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Customer</h4>
                            <p class="mt-1">
                                <Link v-if="vehicle.customer" :href="route(`/customers/${vehicle.customer.id}`)" class="text-electric-600 hover:text-electric-700 font-medium">
                                    {{ vehicle.customer.name }}
                                </Link>
                                <span v-else class="text-gray-400">—</span>
                            </p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">VIN</h4>
                            <p class="mt-1 font-mono text-sm text-gray-900">{{ vehicle.vin ?? '—' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Fuel Type</h4>
                            <p class="mt-1 text-gray-900">{{ capitalize(vehicle.fuel_type) }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Transmission</h4>
                            <p class="mt-1 text-gray-900">{{ capitalize(vehicle.transmission) }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Engine Size</h4>
                            <p class="mt-1 text-gray-900">{{ vehicle.engine_size ?? '—' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Colour</h4>
                            <p class="mt-1 text-gray-900">{{ vehicle.color ?? '—' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Mileage</h4>
                            <p class="mt-1 text-gray-900">{{ vehicle.mileage ? vehicle.mileage.toLocaleString('en-GB') + ' mi' : '—' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Added</h4>
                            <p class="mt-1 text-gray-900">{{ formatDate(vehicle.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="vehicle.notes" class="mt-6 border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-medium uppercase tracking-wider text-gray-500">Notes</h4>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ vehicle.notes }}</p>
                    </div>
                </div>

                <!-- Key Dates -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-500">MOT Due</h4>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p :class="['mt-2 text-lg font-semibold', dateStatusClass(vehicle.mot_due_date)]">
                            {{ formatDate(vehicle.mot_due_date) }}
                        </p>
                        <p v-if="isDateExpired(vehicle.mot_due_date)" class="text-xs text-red-600">Expired</p>
                        <p v-else-if="isDateDueSoon(vehicle.mot_due_date)" class="text-xs text-amber-600">Due soon</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-500">Tax Due</h4>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p :class="['mt-2 text-lg font-semibold', dateStatusClass(vehicle.tax_due_date)]">
                            {{ formatDate(vehicle.tax_due_date) }}
                        </p>
                        <p v-if="isDateExpired(vehicle.tax_due_date)" class="text-xs text-red-600">Expired</p>
                        <p v-else-if="isDateDueSoon(vehicle.tax_due_date)" class="text-xs text-amber-600">Due soon</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-500">Service Due</h4>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <p :class="['mt-2 text-lg font-semibold', dateStatusClass(vehicle.service_due_date)]">
                            {{ formatDate(vehicle.service_due_date) }}
                        </p>
                        <p v-if="isDateExpired(vehicle.service_due_date)" class="text-xs text-red-600">Overdue</p>
                        <p v-else-if="isDateDueSoon(vehicle.service_due_date)" class="text-xs text-amber-600">Due soon</p>
                    </div>
                </div>

                <!-- MOT Tests Timeline -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">MOT Tests</h3>
                        <Link
                            :href="route(`/bookings/create?vehicle_id=${vehicle.id}&service_type=mot`)"
                            class="text-sm font-medium text-electric-600 hover:text-electric-700"
                        >
                            Book MOT &rarr;
                        </Link>
                    </div>
                    <div v-if="vehicle.mot_tests && vehicle.mot_tests.length > 0" class="space-y-3">
                        <div
                            v-for="test in vehicle.mot_tests"
                            :key="test.id"
                            class="flex items-start gap-4 rounded-lg border border-gray-100 p-4"
                        >
                            <div class="flex-shrink-0 mt-0.5">
                                <span v-if="test.result === 'pass'" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </span>
                                <span v-else class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <StatusBadge :status="test.result" />
                                    <span class="text-sm text-gray-500">{{ formatDate(test.test_date) }}</span>
                                    <span v-if="test.mileage" class="text-sm text-gray-400">{{ test.mileage.toLocaleString('en-GB') }} mi</span>
                                </div>
                                <p v-if="test.expiry_date" class="mt-1 text-sm text-gray-600">Expires: {{ formatDate(test.expiry_date) }}</p>
                                <p v-if="test.advisory_notes" class="mt-1 text-sm text-gray-500 italic">{{ test.advisory_notes }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No MOT tests recorded for this vehicle.</p>
                </div>

                <!-- Job Cards Timeline -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Job Cards</h3>
                        <Link
                            :href="route(`/job-cards/create?vehicle_id=${vehicle.id}`)"
                            class="text-sm font-medium text-electric-600 hover:text-electric-700"
                        >
                            New Job Card &rarr;
                        </Link>
                    </div>
                    <div v-if="vehicle.job_cards && vehicle.job_cards.length > 0" class="space-y-3">
                        <Link
                            v-for="job in vehicle.job_cards"
                            :key="job.id"
                            :href="route(`/job-cards/${job.id}`)"
                            class="flex items-center justify-between rounded-lg border border-gray-100 p-4 hover:bg-gray-50 transition"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ job.job_number }}</span>
                                    <StatusBadge :status="job.status" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ job.description }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ formatCurrency(job.total) }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(job.created_at) }}</p>
                            </div>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500">No job cards for this vehicle.</p>
                </div>

                <!-- Invoices Timeline -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Invoices</h3>
                    </div>
                    <div v-if="vehicle.invoices && vehicle.invoices.length > 0" class="space-y-3">
                        <Link
                            v-for="invoice in vehicle.invoices"
                            :key="invoice.id"
                            :href="route(`/invoices/${invoice.id}`)"
                            class="flex items-center justify-between rounded-lg border border-gray-100 p-4 hover:bg-gray-50 transition"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ invoice.invoice_number }}</span>
                                    <StatusBadge :status="invoice.status" />
                                </div>
                                <p v-if="invoice.due_date" class="mt-1 text-sm text-gray-500">Due: {{ formatDate(invoice.due_date) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(invoice.total) }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(invoice.created_at) }}</p>
                            </div>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500">No invoices for this vehicle.</p>
                </div>

                <!-- Bookings Timeline -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Bookings</h3>
                        <Link
                            :href="route(`/bookings/create?vehicle_id=${vehicle.id}`)"
                            class="text-sm font-medium text-electric-600 hover:text-electric-700"
                        >
                            New Booking &rarr;
                        </Link>
                    </div>
                    <div v-if="vehicle.bookings && vehicle.bookings.length > 0" class="space-y-3">
                        <div
                            v-for="booking in vehicle.bookings"
                            :key="booking.id"
                            class="flex items-center justify-between rounded-lg border border-gray-100 p-4"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ formatDate(booking.booking_date) }}</span>
                                    <span v-if="booking.booking_time" class="text-sm text-gray-500">at {{ booking.booking_time }}</span>
                                    <StatusBadge :status="booking.status" />
                                </div>
                                <p v-if="booking.service_type" class="mt-1 text-sm text-gray-500 capitalize">{{ booking.service_type }}</p>
                                <p v-if="booking.notes" class="mt-0.5 text-sm text-gray-400">{{ booking.notes }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No bookings for this vehicle.</p>
                </div>

                <!-- Danger Zone -->
                <div class="rounded-xl border border-red-200 bg-red-50 p-6">
                    <h3 class="text-lg font-medium text-red-800">Danger Zone</h3>
                    <p class="mt-1 text-sm text-red-600">Permanently delete this vehicle and all associated records.</p>
                    <button
                        @click="deleteVehicle"
                        class="mt-4 rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition"
                    >
                        Delete Vehicle
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
