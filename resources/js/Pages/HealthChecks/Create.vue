<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ vehicle: any, jobCard: any, vehicles: any[] }>()
const route = inject<(p: string) => string>('route', p => p)

const defaultChecks = [
    'Tyres - Front Left', 'Tyres - Front Right', 'Tyres - Rear Left', 'Tyres - Rear Right',
    'Brakes - Front', 'Brakes - Rear', 'Brake Fluid',
    'Engine Oil Level', 'Coolant Level', 'Battery Condition',
    'Lights - All', 'Windscreen Wipers', 'Suspension', 'Exhaust System', 'Steering',
]

const form = ref({
    vehicle_id: props.vehicle?.id || '',
    job_card_id: props.jobCard?.id || '',
    check_date: new Date().toISOString().split('T')[0],
    mileage: '',
    overall_notes: '',
    checks: defaultChecks.map(item => ({ item, status: 'good', notes: '' })),
})

const errors = ref<any>({})
const submitting = ref(false)

const statusOptions = [
    { value: 'good', label: '✓ Good', color: 'text-green-700 bg-green-50 border-green-200' },
    { value: 'advisory', label: '⚠ Advisory', color: 'text-yellow-700 bg-yellow-50 border-yellow-200' },
    { value: 'urgent', label: '✕ Urgent', color: 'text-red-700 bg-red-50 border-red-200' },
]

function setStatus(check: any, status: string) { check.status = status }

function submit() {
    submitting.value = true
    router.post(route('/health-checks'), form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="New Health Check" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route('/health-checks')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">New Vehicle Health Check</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Vehicle / Details -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Vehicle Details</h2>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle *</label>
                            <select v-model="form.vehicle_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none">
                                <option value="">Select vehicle...</option>
                                <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.registration_number }} – {{ v.make }} {{ v.model }}</option>
                            </select>
                            <p v-if="errors.vehicle_id" class="text-red-500 text-xs mt-1">{{ errors.vehicle_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check Date *</label>
                            <input v-model="form.check_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mileage *</label>
                            <input v-model.number="form.mileage" type="number" min="0" placeholder="e.g. 45000" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                            <p v-if="errors.mileage" class="text-red-500 text-xs mt-1">{{ errors.mileage }}</p>
                        </div>
                    </div>
                </div>

                <!-- Check Items -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Inspection Items</h2>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="flex items-center gap-1.5 text-green-700"><span class="w-2 h-2 rounded-full bg-green-500"></span>Good</span>
                            <span class="flex items-center gap-1.5 text-yellow-700"><span class="w-2 h-2 rounded-full bg-yellow-400"></span>Advisory</span>
                            <span class="flex items-center gap-1.5 text-red-700"><span class="w-2 h-2 rounded-full bg-red-500"></span>Urgent</span>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="(check, i) in form.checks" :key="i" class="px-6 py-3 grid grid-cols-12 items-center gap-3">
                            <div class="col-span-4 text-sm text-gray-900 font-medium">{{ check.item }}</div>
                            <div class="col-span-4 flex rounded-lg overflow-hidden border border-gray-200">
                                <button v-for="opt in statusOptions" :key="opt.value"
                                    type="button"
                                    @click="setStatus(check, opt.value)"
                                    :class="['flex-1 py-1.5 text-xs font-medium transition-colors', check.status === opt.value ? opt.color + ' border' : 'bg-white text-gray-500 hover:bg-gray-50']"
                                >{{ opt.value === 'good' ? '✓' : opt.value === 'advisory' ? '⚠' : '✕' }}</button>
                            </div>
                            <div class="col-span-4">
                                <input v-model="check.notes" type="text" placeholder="Notes (optional)" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-electric-400 focus:outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Notes -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Overall Notes</label>
                    <textarea v-model="form.overall_notes" rows="3" placeholder="Summary or additional observations..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('/health-checks')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Cancel</Link>
                    <button type="submit" :disabled="submitting" class="px-6 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium disabled:opacity-50">
                        {{ submitting ? 'Saving...' : 'Save Health Check' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
