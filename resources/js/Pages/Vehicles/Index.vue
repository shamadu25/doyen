<script setup lang="ts">
import { ref, watch, computed, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Customer {
    id: number
    name: string
}

interface Vehicle {
    id: number
    registration_number: string
    make: string
    model: string
    customer: Customer | null
    mileage: number | null
    mot_due_date: string | null
}

interface PaginatedVehicles {
    data: Vehicle[]
    links: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

const props = defineProps<{
    vehicles: PaginatedVehicles
    filters?: {
        search?: string
        mot_due_soon?: boolean
    }
}>()

const search = ref(props.filters?.search ?? '')
const motDueSoon = ref(props.filters?.mot_due_soon ?? false)

let debounceTimer: ReturnType<typeof setTimeout>

watch([search, motDueSoon], () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        router.get(route('/vehicles'), {
            search: search.value || undefined,
            mot_due_soon: motDueSoon.value || undefined,
        }, {
            preserveState: true,
            replace: true,
        })
    }, 300)
})

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

function isMotDueSoon(dateStr: string | null): boolean {
    if (!dateStr) return false
    const today = new Date()
    const due = new Date(dateStr)
    const diffMs = due.getTime() - today.getTime()
    const diffDays = diffMs / (1000 * 60 * 60 * 24)
    return diffDays >= 0 && diffDays <= 30
}

function isMotExpired(dateStr: string | null): boolean {
    if (!dateStr) return false
    return new Date(dateStr) < new Date()
}

function deleteVehicle(vehicle: Vehicle) {
    if (confirm(`Are you sure you want to delete ${vehicle.registration_number}?`)) {
        router.delete(route(`/vehicles/${vehicle.id}`))
    }
}
</script>

<template>
    <Head title="Vehicles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Vehicles</h2>
                <Link
                    :href="route('/vehicles/create')"
                    class="inline-flex items-center rounded-xl bg-electric-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Vehicle
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by registration, make, model or customer..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                            />
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input
                                v-model="motDueSoon"
                                type="checkbox"
                                class="rounded border-gray-300 text-electric-600 focus:ring-electric-600"
                            />
                            MOT due within 30 days
                        </label>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Registration</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Make / Model</th>
                                    <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mileage</th>
                                    <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">MOT Expiry</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="vehicle in vehicles.data" :key="vehicle.id" class="hover:bg-gray-50 transition">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <Link :href="route(`/vehicles/${vehicle.id}`)" class="font-semibold text-electric-600 hover:text-electric-700 text-sm">
                                            {{ vehicle.registration_number }}
                                        </Link>
                                        <p class="sm:hidden text-xs text-gray-500 mt-0.5">{{ vehicle.make }} {{ vehicle.model }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ vehicle.make }} {{ vehicle.model }}
                                    </td>
                                    <td class="hidden sm:table-cell whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        <Link
                                            v-if="vehicle.customer"
                                            :href="route(`/customers/${vehicle.customer.id}`)"
                                            class="text-electric-600 hover:text-electric-700"
                                        >
                                            {{ vehicle.customer.name }}
                                        </Link>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                    <td class="hidden md:table-cell whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ vehicle.mileage ? vehicle.mileage.toLocaleString('en-GB') : '—' }}
                                    </td>
                                    <td class="hidden sm:table-cell whitespace-nowrap px-4 py-3 text-sm">
                                        <span
                                            :class="{
                                                'text-red-600 font-semibold': isMotExpired(vehicle.mot_due_date) || isMotDueSoon(vehicle.mot_due_date),
                                                'text-gray-700': !isMotExpired(vehicle.mot_due_date) && !isMotDueSoon(vehicle.mot_due_date),
                                            }"
                                        >
                                            {{ formatDate(vehicle.mot_due_date) }}
                                            <span v-if="isMotExpired(vehicle.mot_due_date)" class="ml-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Expired</span>
                                            <span v-else-if="isMotDueSoon(vehicle.mot_due_date)" class="ml-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Due soon</span>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        <div class="flex items-center justify-end gap-1">
                                            <Link :href="route(`/vehicles/${vehicle.id}`)" class="text-gray-500 hover:text-electric-600" title="View">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </Link>
                                            <Link :href="route(`/vehicles/${vehicle.id}/edit`)" class="text-gray-500 hover:text-electric-600" title="Edit">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </Link>
                                            <button @click="deleteVehicle(vehicle)" class="text-gray-500 hover:text-red-600" title="Delete">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="vehicles.data.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                        No vehicles found. <Link :href="route('/vehicles/create')" class="text-electric-600 hover:underline">Add your first vehicle</Link>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="vehicles.last_page > 1" class="border-t border-gray-200 px-6 py-4">
                        <Pagination :links="vehicles.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
