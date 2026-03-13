<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    jobCard: any
    customers: any[]
    vehicles: any[]
    technicians: any[]
}>()

const form = useForm({
    customer_id: props.jobCard.customer_id || '',
    vehicle_id: props.jobCard.vehicle_id || '',
    assigned_to: props.jobCard.assigned_to || '',
    priority: props.jobCard.priority || 'normal',
    status: props.jobCard.status || 'pending',
    description: props.jobCard.description || '',
    notes: props.jobCard.notes || '',
    estimated_completion: props.jobCard.estimated_completion?.split('T')[0] || '',
    mileage_in: props.jobCard.mileage_in || '',
})

const filteredVehicles = computed(() => {
    if (!form.customer_id) return props.vehicles
    return props.vehicles.filter((v: any) => v.customer_id == form.customer_id)
})

function submit() {
    form.put(route(`/job-cards/${props.jobCard.id}`))
}
</script>

<template>
    <Head title="Edit Job Card" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit {{ jobCard.job_number }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Update job card details</p>
                </div>
                <Link :href="route(`/job-cards/${jobCard.id}`)" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                        <select v-model="form.customer_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" required>
                            <option value="">Select customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle *</label>
                        <select v-model="form.vehicle_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" required>
                            <option value="">Select vehicle</option>
                            <option v-for="v in filteredVehicles" :key="v.id" :value="v.id">{{ v.registration_number }} - {{ v.make }} {{ v.model }}</option>
                        </select>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="awaiting_parts">Awaiting Parts</option>
                            <option value="completed">Completed</option>
                            <option value="invoiced">Invoiced</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Completion</label>
                        <input v-model="form.estimated_completion" type="date" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage In</label>
                        <input v-model="form.mileage_in" type="number" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Description *</label>
                    <textarea v-model="form.description" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600" required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                    <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link :href="route(`/job-cards/${jobCard.id}`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Update Job Card' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
