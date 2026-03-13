<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref, computed, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ quote: any, customers: any[], services: any[], parts: any[] }>()
const route = inject<(p: string) => string>('route', p => p)

const form = ref({
    customer_id: props.quote.customer_id,
    vehicle_id: props.quote.vehicle_id || '',
    quote_date: props.quote.quote_date?.split('T')[0] || '',
    validity_days: props.quote.validity_days || 30,
    description: props.quote.description || '',
    notes: props.quote.notes || '',
    discount_percentage: props.quote.discount_percentage || 0,
})
const customerVehicles = ref<any[]>([])
const errors = ref<any>({})
const submitting = ref(false)

const currentCustomer = props.customers.find(c => c.id == props.quote.customer_id)
if (currentCustomer) customerVehicles.value = currentCustomer.vehicles || []

watch(() => form.value.customer_id, (id) => {
    form.value.vehicle_id = ''
    const c = props.customers.find(c => c.id == id)
    customerVehicles.value = c?.vehicles || []
})

function submit() {
    submitting.value = true
    errors.value = {}
    router.put(route(`/quotes/${props.quote.id}`), form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="Edit Quote" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route(`/quotes/${quote.id}`)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Edit Quote {{ quote.quote_number }}</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                            <select v-model="form.customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none">
                                <option value="">Select customer...</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="errors.customer_id" class="text-red-500 text-xs mt-1">{{ errors.customer_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                            <select v-model="form.vehicle_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" :disabled="!form.customer_id">
                                <option value="">No vehicle / TBC</option>
                                <option v-for="v in customerVehicles" :key="v.id" :value="v.id">{{ v.registration_number }} – {{ v.make }} {{ v.model }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quote Date *</label>
                            <input v-model="form.quote_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid for (days)</label>
                            <input v-model.number="form.validity_days" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input v-model="form.description" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Discount %</label>
                            <input v-model.number="form.discount_percentage" type="number" min="0" max="100" step="0.5" class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Link :href="route(`/quotes/${quote.id}`)" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Cancel</Link>
                    <button type="submit" :disabled="submitting" class="px-6 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium disabled:opacity-50">
                        {{ submitting ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
