<script setup lang="ts">
import { ref, watch, computed, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

interface Customer {
    id: number
    name: string
    email: string
    phone: string
}

interface Vehicle {
    id: number
    registration: string
    make: string
    model: string
}

interface Technician {
    id: number
    name: string
}

interface Booking {
    id: number
    reference: string
    customer: Customer
    vehicle: Vehicle
    assigned_to_user: Technician | null
    appointment_type: string
    scheduled_date: string
    scheduled_time: string
    duration_minutes: number
    status: string
    description: string | null
    customer_notes: string | null
    job_card_id: number | null
    created_at: string
}

interface PaginatedBookings {
    data: Booking[]
    links: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

interface Props {
    bookings: PaginatedBookings
    filters: {
        search?: string
        status?: string
        appointment_type?: string
        date?: string
    }
}

const props = defineProps<Props>()

const route = inject<(path: string) => string>('route', (p) => p)

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const appointmentType = ref(props.filters.appointment_type ?? '')
const date = ref(props.filters.date ?? '')

const serviceTypes = [
    { value: '', label: 'All Types' },
    { value: 'mot', label: 'MOT' },
    { value: 'service', label: 'Service' },
    { value: 'repair', label: 'Repair' },
    { value: 'diagnosis', label: 'Diagnostics' },
]

const statuses = [
    { value: '', label: 'All Statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'pending_quote', label: 'Pending Quote' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'reschedule_pending', label: 'Reschedule Pending' },
]

function applyFilters() {
    router.get(route('/bookings'), {
        search: search.value || undefined,
        status: status.value || undefined,
        appointment_type: appointmentType.value || undefined,
        date: date.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

let searchTimeout: ReturnType<typeof setTimeout>
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 400)
})

watch([status, appointmentType, date], () => {
    applyFilters()
})

function clearFilters() {
    search.value = ''
    status.value = ''
    appointmentType.value = ''
    date.value = ''
    router.get(route('/bookings'), {}, { preserveState: true, replace: true })
}

function formatDate(dateStr: string): string {
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatTime(timeStr: string): string {
    if (!timeStr) return ''
    const [h, m] = timeStr.split(':')
    const hour = parseInt(h)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    const h12 = hour % 12 || 12
    return `${h12}:${m} ${ampm}`
}

function deleteBooking(id: number) {
    if (confirm('Are you sure you want to delete this booking?')) {
        router.delete(route(`/bookings/${id}`))
    }
}

const hasFilters = computed(() => search.value || status.value || appointmentType.value || date.value)
</script>

<template>
    <Head title="Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Bookings</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="route('/bookings/calendar')"
                        class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="hidden sm:inline">Calendar View</span>
                        <span class="sm:hidden">Calendar</span>
                    </Link>
                    <Link
                        :href="route('/bookings/create')"
                        class="inline-flex items-center rounded-xl bg-electric-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Booking
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Reference, customer, vehicle..."
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                            <select
                                v-model="status"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                            >
                                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Appointment Type</label>
                            <select
                                v-model="appointmentType"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                            >
                                <option v-for="st in serviceTypes" :key="st.value" :value="st.value">{{ st.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                            <input
                                v-model="date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                v-if="hasFilters"
                                @click="clearFilters"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results summary -->
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ bookings.data.length }}</span> of
                        <span class="font-medium">{{ bookings.total }}</span> bookings
                    </p>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Vehicle</th>
                                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date / Time</th>
                                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Technician</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50 transition">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <Link :href="route(`/bookings/${booking.id}`)" class="font-medium text-electric-600 hover:text-electric-700 text-sm">
                                            {{ booking.reference }}
                                        </Link>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                        {{ booking.customer?.name ?? '—' }}
                                    </td>
                                    <td class="hidden md:table-cell whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                        <span v-if="booking.vehicle">
                                            {{ booking.vehicle.registration }} — {{ booking.vehicle.make }} {{ booking.vehicle.model }}
                                        </span>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="hidden lg:table-cell whitespace-nowrap px-4 py-3 text-sm text-gray-900 capitalize">{{ booking.appointment_type }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                        {{ formatDate(booking.scheduled_date) }}<br />
                                        <span class="text-gray-500">{{ formatTime(booking.scheduled_time) }}</span>
                                    </td>
                                    <td class="hidden lg:table-cell whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                        {{ booking.assigned_to_user?.name ?? 'Unassigned' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <StatusBadge :status="booking.status" />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        <div class="flex items-center justify-end gap-1">
                                            <Link
                                                :href="route(`/bookings/${booking.id}`)"
                                                class="text-gray-500 hover:text-electric-600 transition"
                                                title="View"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Link>
                                            <Link
                                                :href="route(`/bookings/${booking.id}/edit`)"
                                                class="text-gray-500 hover:text-yellow-600 transition"
                                                title="Edit"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Link>
                                            <button
                                                @click="deleteBooking(booking.id)"
                                                class="text-gray-500 hover:text-red-600 transition"
                                                title="Delete"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="bookings.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="font-medium">No bookings found</p>
                                            <p class="mt-1 text-gray-400">Try adjusting your filters or create a new booking.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="bookings.last_page > 1" class="border-t border-gray-200 px-6 py-4">
                        <Pagination :links="bookings.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
