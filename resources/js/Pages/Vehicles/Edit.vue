<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Customer {
    id: number
    name: string
}

interface Vehicle {
    id: number
    customer_id: number | null
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
}

const props = defineProps<{
    vehicle: Vehicle
    customers: Customer[]
}>()

const form = useForm({
    customer_id: props.vehicle.customer_id ?? '',
    registration_number: props.vehicle.registration_number ?? '',
    make: props.vehicle.make ?? '',
    model: props.vehicle.model ?? '',
    variant: props.vehicle.variant ?? '',
    year: props.vehicle.year ?? '',
    vin: props.vehicle.vin ?? '',
    fuel_type: props.vehicle.fuel_type ?? '',
    transmission: props.vehicle.transmission ?? '',
    engine_size: props.vehicle.engine_size ?? '',
    color: props.vehicle.color ?? '',
    mileage: props.vehicle.mileage ?? '',
    mot_due_date: props.vehicle.mot_due_date ?? '',
    tax_due_date: props.vehicle.tax_due_date ?? '',
    service_due_date: props.vehicle.service_due_date ?? '',
    notes: props.vehicle.notes ?? '',
})

const fuelTypes = [
    { value: 'petrol', label: 'Petrol' },
    { value: 'diesel', label: 'Diesel' },
    { value: 'electric', label: 'Electric' },
    { value: 'hybrid', label: 'Hybrid' },
    { value: 'lpg', label: 'LPG' },
    { value: 'other', label: 'Other' },
]

function forceUppercase() {
    form.registration_number = form.registration_number.toUpperCase()
}

function submit() {
    form.put(route(`/vehicles/${props.vehicle.id}`))
}
</script>

<template>
    <Head :title="`Edit ${vehicle.registration_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route(`/vehicles/${vehicle.id}`)" class="text-gray-500 hover:text-gray-700 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Vehicle</h2>
                    <p class="text-sm text-gray-500">{{ vehicle.registration_number }} — {{ vehicle.make }} {{ vehicle.model }}</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer & Registration -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Owner & Registration</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer <span class="text-red-500">*</span></label>
                                <select
                                    id="customer_id"
                                    v-model="form.customer_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                >
                                    <option value="">Select customer...</option>
                                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                        {{ customer.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                            </div>
                            <div>
                                <label for="registration_number" class="block text-sm font-medium text-gray-700">Registration Number <span class="text-red-500">*</span></label>
                                <input
                                    id="registration_number"
                                    v-model="form.registration_number"
                                    @input="forceUppercase"
                                    type="text"
                                    placeholder="e.g. AB12 CDE"
                                    class="mt-1 block w-full rounded-lg border-gray-300 uppercase shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                />
                                <p v-if="form.errors.registration_number" class="mt-1 text-sm text-red-600">{{ form.errors.registration_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Details -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Vehicle Details</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="make" class="block text-sm font-medium text-gray-700">Make <span class="text-red-500">*</span></label>
                                <input id="make" v-model="form.make" type="text" placeholder="e.g. Ford" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.make" class="mt-1 text-sm text-red-600">{{ form.errors.make }}</p>
                            </div>
                            <div>
                                <label for="model" class="block text-sm font-medium text-gray-700">Model <span class="text-red-500">*</span></label>
                                <input id="model" v-model="form.model" type="text" placeholder="e.g. Focus" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.model" class="mt-1 text-sm text-red-600">{{ form.errors.model }}</p>
                            </div>
                            <div>
                                <label for="variant" class="block text-sm font-medium text-gray-700">Variant</label>
                                <input id="variant" v-model="form.variant" type="text" placeholder="e.g. Zetec" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.variant" class="mt-1 text-sm text-red-600">{{ form.errors.variant }}</p>
                            </div>
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                                <input id="year" v-model="form.year" type="number" min="1900" max="2099" placeholder="e.g. 2022" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.year" class="mt-1 text-sm text-red-600">{{ form.errors.year }}</p>
                            </div>
                            <div>
                                <label for="vin" class="block text-sm font-medium text-gray-700">VIN</label>
                                <input id="vin" v-model="form.vin" type="text" placeholder="Vehicle Identification Number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.vin" class="mt-1 text-sm text-red-600">{{ form.errors.vin }}</p>
                            </div>
                            <div>
                                <label for="fuel_type" class="block text-sm font-medium text-gray-700">Fuel Type</label>
                                <select id="fuel_type" v-model="form.fuel_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600">
                                    <option value="">Select fuel type...</option>
                                    <option v-for="ft in fuelTypes" :key="ft.value" :value="ft.value">{{ ft.label }}</option>
                                </select>
                                <p v-if="form.errors.fuel_type" class="mt-1 text-sm text-red-600">{{ form.errors.fuel_type }}</p>
                            </div>
                            <div>
                                <label for="transmission" class="block text-sm font-medium text-gray-700">Transmission</label>
                                <select id="transmission" v-model="form.transmission" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600">
                                    <option value="">Select...</option>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                    <option value="semi-automatic">Semi-Automatic</option>
                                </select>
                                <p v-if="form.errors.transmission" class="mt-1 text-sm text-red-600">{{ form.errors.transmission }}</p>
                            </div>
                            <div>
                                <label for="engine_size" class="block text-sm font-medium text-gray-700">Engine Size</label>
                                <input id="engine_size" v-model="form.engine_size" type="text" placeholder="e.g. 1.6L" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.engine_size" class="mt-1 text-sm text-red-600">{{ form.errors.engine_size }}</p>
                            </div>
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700">Colour</label>
                                <input id="color" v-model="form.color" type="text" placeholder="e.g. Midnight Black" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">{{ form.errors.color }}</p>
                            </div>
                            <div>
                                <label for="mileage" class="block text-sm font-medium text-gray-700">Mileage</label>
                                <input id="mileage" v-model="form.mileage" type="number" min="0" placeholder="e.g. 45000" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.mileage" class="mt-1 text-sm text-red-600">{{ form.errors.mileage }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Important Dates -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Important Dates</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div>
                                <label for="mot_due_date" class="block text-sm font-medium text-gray-700">MOT Due Date</label>
                                <input id="mot_due_date" v-model="form.mot_due_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.mot_due_date" class="mt-1 text-sm text-red-600">{{ form.errors.mot_due_date }}</p>
                            </div>
                            <div>
                                <label for="tax_due_date" class="block text-sm font-medium text-gray-700">Tax Due Date</label>
                                <input id="tax_due_date" v-model="form.tax_due_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.tax_due_date" class="mt-1 text-sm text-red-600">{{ form.errors.tax_due_date }}</p>
                            </div>
                            <div>
                                <label for="service_due_date" class="block text-sm font-medium text-gray-700">Service Due Date</label>
                                <input id="service_due_date" v-model="form.service_due_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600" />
                                <p v-if="form.errors.service_due_date" class="mt-1 text-sm text-red-600">{{ form.errors.service_due_date }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Additional Notes</h3>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="Any additional notes about this vehicle..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                        ></textarea>
                        <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route(`/vehicles/${vehicle.id}`)" class="rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-electric-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition disabled:opacity-50"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Update Vehicle</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
