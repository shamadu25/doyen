<script setup lang="ts">
import { ref, watch, computed, inject } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

interface Customer {
    id: number
    name: string
    email: string
    phone: string
}

interface Vehicle {
    id: number
    customer_id: number
    registration_number: string
    make: string
    model: string
    year: number | null
}

interface Technician {
    id: number
    name: string
}

interface Props {
    customers: Customer[]
    vehicles: Vehicle[]
    technicians: Technician[]
}

const props = defineProps<Props>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    customer_id: '' as string | number,
    vehicle_id: '' as string | number,
    assigned_to: '' as string | number,
    appointment_type: '',
    scheduled_date: '',
    scheduled_time: '',
    duration_minutes: 60,
    description: '',
    customer_notes: '',
    internal_notes: '',
})

const serviceTypes = [
    { value: 'mot', label: 'MOT' },
    { value: 'service', label: 'Service' },
    { value: 'repair', label: 'Repair' },
    { value: 'diagnosis', label: 'Diagnostics' },
]

const durations = [
    { value: 30, label: '30 minutes' },
    { value: 60, label: '1 hour' },
    { value: 90, label: '1.5 hours' },
    { value: 120, label: '2 hours' },
    { value: 180, label: '3 hours' },
    { value: 240, label: '4 hours' },
    { value: 480, label: 'Full day (8 hours)' },
]

const filteredVehicles = computed(() => {
    if (!form.customer_id) return []
    return props.vehicles.filter(v => v.customer_id === Number(form.customer_id))
})

watch(() => form.customer_id, () => {
    form.vehicle_id = ''
})

function submit() {
    // Combine date and time into datetime for scheduled_date
    const dateTime = form.scheduled_date && form.scheduled_time 
        ? `${form.scheduled_date} ${form.scheduled_time}:00`
        : form.scheduled_date
    
    form.transform((data) => ({
        ...data,
        scheduled_date: dateTime,
        scheduled_time: undefined, // Remove this field before submission
    })).post(route('/bookings'))
}
</script>

<template>
    <Head title="New Booking" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('/bookings')" class="text-gray-500 hover:text-gray-700 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">New Booking</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer & Vehicle -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Customer &amp; Vehicle</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer <span class="text-red-500">*</span></label>
                                <select
                                    id="customer_id"
                                    v-model="form.customer_id"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                    :class="{ 'border-red-500': form.errors.customer_id }"
                                >
                                    <option value="">Select customer...</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">
                                        {{ c.name }} ({{ c.phone }})
                                    </option>
                                </select>
                                <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                            </div>

                            <div>
                                <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Vehicle <span class="text-red-500">*</span></label>
                                <select
                                    id="vehicle_id"
                                    v-model="form.vehicle_id"
                                    :disabled="!form.customer_id"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    :class="{ 'border-red-500': form.errors.vehicle_id }"
                                >
                                    <option value="">{{ form.customer_id ? 'Select vehicle...' : 'Select a customer first' }}</option>
                                    <option v-for="v in filteredVehicles" :key="v.id" :value="v.id">
                                        {{ v.registration_number }} — {{ v.make }} {{ v.model }}{{ v.year ? ` (${v.year})` : '' }}
                                    </option>
                                </select>
                                <p v-if="form.errors.vehicle_id" class="mt-1 text-sm text-red-600">{{ form.errors.vehicle_id }}</p>
                                <p v-if="form.customer_id && filteredVehicles.length === 0" class="mt-1 text-sm text-amber-600">
                                    No vehicles found for this customer.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Service Details</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="appointment_type" class="block text-sm font-medium text-gray-700">Service Type <span class="text-red-500">*</span></label>
                                <select
                                    id="appointment_type"
                                    v-model="form.appointment_type"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                    :class="{ 'border-red-500': form.errors.appointment_type }"
                                >
                                    <option value="">Select service type...</option>
                                    <option v-for="st in serviceTypes" :key="st.value" :value="st.value">{{ st.label }}</option>
                                </select>
                                <p v-if="form.errors.appointment_type" class="mt-1 text-sm text-red-600">{{ form.errors.appointment_type }}</p>
                            </div>

                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Technician</label>
                                <select
                                    id="assigned_to"
                                    v-model="form.assigned_to"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                >
                                    <option value="">Unassigned</option>
                                    <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                                <p v-if="form.errors.assigned_to" class="mt-1 text-sm text-red-600">{{ form.errors.assigned_to }}</p>
                            </div>

                            <div>
                                <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Date <span class="text-red-500">*</span></label>
                                <input
                                    id="scheduled_date"
                                    v-model="form.scheduled_date"
                                    type="date"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                    :class="{ 'border-red-500': form.errors.scheduled_date }"
                                />
                                <p v-if="form.errors.scheduled_date" class="mt-1 text-sm text-red-600">{{ form.errors.scheduled_date }}</p>
                            </div>

                            <div>
                                <label for="scheduled_time" class="block text-sm font-medium text-gray-700">Time <span class="text-red-500">*</span></label>
                                <input
                                    id="scheduled_time"
                                    v-model="form.scheduled_time"
                                    type="time"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                    :class="{ 'border-red-500': form.errors.scheduled_time }"
                                />
                                <p v-if="form.errors.scheduled_time" class="mt-1 text-sm text-red-600">{{ form.errors.scheduled_time }}</p>
                            </div>


                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Notes</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Work Description</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Describe the work to be done..."
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                />
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <div>
                                <label for="internal_notes" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                                <textarea
                                    id="internal_notes"
                                    v-model="form.internal_notes"
                                    rows="3"
                                    placeholder="Notes visible to staff only..."
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                />
                                <p v-if="form.errors.internal_notes" class="mt-1 text-sm text-red-600">{{ form.errors.internal_notes }}</p>
                            </div>

                            <div>
                                <label for="customer_notes" class="block text-sm font-medium text-gray-700">Customer Notes</label>
                                <textarea
                                    id="customer_notes"
                                    v-model="form.customer_notes"
                                    rows="3"
                                    placeholder="Notes or special requests from the customer..."
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                />
                                <p v-if="form.errors.customer_notes" class="mt-1 text-sm text-red-600">{{ form.errors.customer_notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('/bookings')" class="rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-electric-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-electric-700 disabled:opacity-50 transition"
                        >
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Booking</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
