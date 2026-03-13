<script setup lang="ts">
import { ref, inject } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    supplier: {
        id: number
        name: string
        contact_person: string | null
        email: string | null
        phone: string | null
        address: string | null
        website: string | null
        status: 'active' | 'inactive'
        notes: string | null
        parts_orders_count?: number
        created_at: string
        updated_at: string
    }
    readonly: boolean
}>()

const showDeleteConfirm = ref(false)

const form = useForm({
    name: props.supplier.name,
    contact_person: props.supplier.contact_person ?? '',
    email: props.supplier.email ?? '',
    phone: props.supplier.phone ?? '',
    address: props.supplier.address ?? '',
    website: props.supplier.website ?? '',
    status: props.supplier.status,
    notes: props.supplier.notes ?? '',
})

function submit() {
    form.put(route(`/suppliers/${props.supplier.id}`))
}

function doDelete() {
    router.delete(route(`/suppliers/${props.supplier.id}`), {
        onSuccess: () => { showDeleteConfirm.value = false },
    })
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <Head :title="readonly ? supplier.name : `Edit: ${supplier.name}`" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ readonly ? supplier.name : `Edit: ${supplier.name}` }}</h1>
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1', supplier.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        {{ supplier.status === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="readonly" :href="route(`/suppliers/${supplier.id}/edit`)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                        Edit
                    </Link>
                    <Link :href="route('/suppliers')" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                        </svg>
                        Back
                    </Link>
                </div>
            </div>

            <!-- View banner -->
            <div v-if="readonly" class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-6 text-sm text-amber-800">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                View mode — click Edit to make changes
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Supplier Details</h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span v-if="!readonly" class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                            <input
                                v-model="form.contact_person"
                                type="text"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                :class="{ 'border-red-300': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <!-- Website -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                            <input
                                v-model="form.website"
                                type="url"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                :class="{ 'border-red-300': form.errors.website }"
                            />
                            <p v-if="form.errors.website" class="mt-1 text-xs text-red-600">{{ form.errors.website }}</p>
                        </div>

                        <!-- Address -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea
                                v-model="form.address"
                                rows="2"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            ></textarea>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                v-model="form.status"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                placeholder="Payment terms, delivery notes, account numbers..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Meta info (view mode) -->
                <div v-if="readonly" class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Record Info</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div v-if="supplier.parts_orders_count !== undefined">
                            <dt class="text-gray-500">Parts Orders</dt>
                            <dd class="font-medium text-gray-900">{{ supplier.parts_orders_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Added</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(supplier.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Last Updated</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(supplier.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div v-if="!readonly" class="flex items-center justify-between">
                    <!-- Delete -->
                    <div>
                        <button v-if="!showDeleteConfirm" type="button" @click="showDeleteConfirm = true"
                            class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                            Delete Supplier
                        </button>
                        <div v-else class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <span class="text-sm text-red-700 font-medium">Confirm delete?</span>
                            <button type="button" @click="doDelete" class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">Yes, Delete</button>
                            <button type="button" @click="showDeleteConfirm = false" class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>

                    <!-- Save -->
                    <div class="flex items-center gap-3">
                        <Link :href="route('/suppliers')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
