<script setup lang="ts">
import { inject } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const route = inject<(path: string) => string>('route', (p) => p)

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
}

const props = defineProps<{
    customer: Customer
}>()

const form = useForm({
    first_name: props.customer.first_name ?? '',
    last_name: props.customer.last_name ?? '',
    email: props.customer.email ?? '',
    phone: props.customer.phone ?? '',
    mobile: props.customer.mobile ?? '',
    address: props.customer.address ?? '',
    city: props.customer.city ?? '',
    postcode: props.customer.postcode ?? '',
    notes: props.customer.notes ?? '',
    customer_type: props.customer.customer_type ?? 'individual',
    company_name: props.customer.company_name ?? '',
    vat_number: props.customer.vat_number ?? '',
})

const submit = () => {
    form.put(route(`/customers/${props.customer.id}`))
}
</script>

<template>
    <Head :title="`Edit ${customer.first_name} ${customer.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route(`/customers/${customer.id}`)" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit {{ customer.first_name }} {{ customer.last_name }}</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer Type -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Customer Type</h3>
                        <div class="flex gap-4">
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 transition"
                                :class="form.customer_type === 'individual' ? 'border-electric-600 bg-electric-50 text-electric-700' : 'border-gray-300 hover:border-gray-400'"
                            >
                                <input v-model="form.customer_type" type="radio" value="individual" class="sr-only" />
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-sm font-medium">Individual</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 transition"
                                :class="form.customer_type === 'business' ? 'border-electric-600 bg-electric-50 text-electric-700' : 'border-gray-300 hover:border-gray-400'"
                            >
                                <input v-model="form.customer_type" type="radio" value="business" class="sr-only" />
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-sm font-medium">Business</span>
                            </label>
                        </div>
                    </div>

                    <!-- Business Details (conditional) -->
                    <div v-if="form.customer_type === 'business'" class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Business Details</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name <span class="text-red-500">*</span></label>
                                <input
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">{{ form.errors.company_name }}</p>
                            </div>
                            <div>
                                <label for="vat_number" class="block text-sm font-medium text-gray-700">VAT Number</label>
                                <input
                                    id="vat_number"
                                    v-model="form.vat_number"
                                    type="text"
                                    placeholder="GB123456789"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.vat_number" class="mt-1 text-sm text-red-600">{{ form.errors.vat_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Details -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Personal Details</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <input
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <input
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile</label>
                                <input
                                    id="mobile"
                                    v-model="form.mobile"
                                    type="tel"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.mobile" class="mt-1 text-sm text-red-600">{{ form.errors.mobile }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Address</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700">Street Address</label>
                                <input
                                    id="address"
                                    v-model="form.address"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                            </div>
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
                            </div>
                            <div>
                                <label for="postcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                                <input
                                    id="postcode"
                                    v-model="form.postcode"
                                    type="text"
                                    placeholder="e.g. SW1A 1AA"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                                />
                                <p v-if="form.errors.postcode" class="mt-1 text-sm text-red-600">{{ form.errors.postcode }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Additional Notes</h3>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Any additional notes about this customer..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 sm:text-sm"
                        ></textarea>
                        <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route(`/customers/${customer.id}`)" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-lg bg-electric-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 focus:outline-none focus:ring-2 focus:ring-electric-600 focus:ring-offset-2 transition disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Update Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
