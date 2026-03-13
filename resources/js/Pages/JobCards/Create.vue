<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    customers: any[]
    vehicles: any[]
    technicians: any[]
}>()

const form = useForm({
    customer_id: '',
    vehicle_id: '',
    assigned_to: '',
    priority: 'normal',
    description: '',
    notes: '',
    estimated_completion: '',
    mileage_in: '',
})

const filteredVehicles = computed(() => {
    if (!form.customer_id) return props.vehicles
    return props.vehicles.filter((v: any) => v.customer_id == form.customer_id)
})

import { computed, inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

function submit() {
    form.post(route('/job-cards'))
}
</script>

<template>
    <Head title="Create Job Card" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">New Job Card</h1>
                    <p class="mt-1 text-sm text-gray-500">Create a new workshop job</p>
                </div>
                <Link :href="route('/job-cards')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                        <select v-model="form.customer_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" required>
                            <option value="">Select customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle *</label>
                        <select v-model="form.vehicle_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" required>
                            <option value="">Select vehicle</option>
                            <option v-for="v in filteredVehicles" :key="v.id" :value="v.id">{{ v.registration_number }} - {{ v.make }} {{ v.model }}</option>
                        </select>
                        <p v-if="form.errors.vehicle_id" class="mt-1 text-xs text-red-600">{{ form.errors.vehicle_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                        <select v-model="form.assigned_to" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                            <option value="">Unassigned</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select v-model="form.priority" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Completion</label>
                        <input v-model="form.estimated_completion" type="date" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage In</label>
                        <input v-model="form.mileage_in" type="number" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" placeholder="e.g. 45000" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Description *</label>
                    <textarea v-model="form.description" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" placeholder="Describe the work to be done..." required></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                    <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" placeholder="Internal notes..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link :href="route('/job-cards')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create Job Card' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
