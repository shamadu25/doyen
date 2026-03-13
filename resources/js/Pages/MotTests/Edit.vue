<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    motTest: any
    vehicles: any[]
}>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    vehicle_id: props.motTest.vehicle_id || '',
    test_date: props.motTest.test_date?.split('T')[0] || '',
    tester_name: props.motTest.tester_name || '',
    mileage: props.motTest.mileage || '',
    result: props.motTest.result || '',
    expiry_date: props.motTest.expiry_date?.split('T')[0] || '',
    advisory_items: props.motTest.advisory_items || '',
    failure_items: props.motTest.failure_items || '',
    notes: props.motTest.notes || '',
    test_number: props.motTest.test_number || '',
})

function submit() {
    form.put(route(`/mot-tests/${props.motTest.id}`))
}
</script>

<template>
    <Head title="Edit MOT Test" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Edit MOT Test</h1>
                <Link :href="route(`/mot-tests/${motTest.id}`)" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle *</label>
                        <select v-model="form.vehicle_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                            <option value="">Select vehicle</option>
                            <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.registration_number }} - {{ v.make }} {{ v.model }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Date</label>
                        <input v-model="form.test_date" type="date" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tester Name</label>
                        <input v-model="form.tester_name" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
                        <input v-model="form.mileage" type="number" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                        <select v-model="form.result" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Pending</option>
                            <option value="pass">Pass</option>
                            <option value="fail">Fail</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input v-model="form.expiry_date" type="date" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DVSA Test Number</label>
                        <input v-model="form.test_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Advisory Items</label>
                    <textarea v-model="form.advisory_items" rows="3" class="w-full rounded-lg border-gray-300 text-sm" placeholder="One per line"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Failure Items</label>
                    <textarea v-model="form.failure_items" rows="3" class="w-full rounded-lg border-gray-300 text-sm" placeholder="One per line"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link :href="route(`/mot-tests/${motTest.id}`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Update MOT Test' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
