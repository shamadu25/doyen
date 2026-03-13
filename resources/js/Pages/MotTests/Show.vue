<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps<{ motTest: any }>()

const route = inject<(path: string) => string>('route', (p) => p)

const mot = props.motTest

function markPass() {
    if (confirm('Mark this MOT as PASSED?')) router.post(route(`/mot-tests/${mot.id}/pass`))
}
function markFail() {
    if (confirm('Mark this MOT as FAILED?')) router.post(route(`/mot-tests/${mot.id}/fail`))
}
function retest() {
    if (confirm('Record a retest for this vehicle?')) router.post(route(`/mot-tests/${mot.id}/retest`))
}

function fmt(date: string) {
    return date ? new Date(date).toLocaleDateString('en-GB') : '-'
}
</script>

<template>
    <Head :title="`MOT Test ${mot.test_number || mot.id}`" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">MOT Test {{ mot.test_number || `#${mot.id}` }}</h1>
                        <StatusBadge :status="mot.result || mot.status || 'pending'" />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Tested {{ fmt(mot.test_date) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route(`/mot-tests/${mot.id}/edit`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button v-if="!mot.result || mot.result === 'pending'" @click="markPass" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Pass</button>
                    <button v-if="!mot.result || mot.result === 'pending'" @click="markFail" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Fail</button>
                    <button v-if="mot.result === 'fail'" @click="retest" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">Retest</button>
                    <Link :href="route('/mot-tests')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Test Details -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Details</h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><span class="text-gray-500">Test Date:</span><span class="ml-2 text-gray-900">{{ fmt(mot.test_date) }}</span></div>
                            <div><span class="text-gray-500">Expiry Date:</span><span class="ml-2 text-gray-900">{{ fmt(mot.expiry_date) }}</span></div>
                            <div><span class="text-gray-500">Tester:</span><span class="ml-2 text-gray-900">{{ mot.tester_name || '-' }}</span></div>
                            <div><span class="text-gray-500">Mileage:</span><span class="ml-2 text-gray-900">{{ mot.mileage ? Number(mot.mileage).toLocaleString() : '-' }}</span></div>
                            <div><span class="text-gray-500">DVSA Test Number:</span><span class="ml-2 text-gray-900">{{ mot.test_number || '-' }}</span></div>
                        </div>
                    </div>

                    <!-- Failure Items -->
                    <div v-if="mot.failure_items" class="bg-white rounded-xl border border-red-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-red-700 mb-3">Failure Items</h2>
                        <ul class="space-y-2">
                            <li v-for="(item, i) in (typeof mot.failure_items === 'string' ? mot.failure_items.split('\n').filter(Boolean) : mot.failure_items)" :key="i" class="flex items-start gap-2 text-sm">
                                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                <span class="text-gray-900">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Advisory Items -->
                    <div v-if="mot.advisory_items" class="bg-white rounded-xl border border-yellow-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-yellow-700 mb-3">Advisory Items</h2>
                        <ul class="space-y-2">
                            <li v-for="(item, i) in (typeof mot.advisory_items === 'string' ? mot.advisory_items.split('\n').filter(Boolean) : mot.advisory_items)" :key="i" class="flex items-start gap-2 text-sm">
                                <svg class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                <span class="text-gray-900">{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Notes -->
                    <div v-if="mot.notes" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ mot.notes }}</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Result Card -->
                    <div :class="[
                        'rounded-xl border shadow-sm p-6 text-center',
                        mot.result === 'pass' ? 'bg-green-50 border-green-200' : mot.result === 'fail' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'
                    ]">
                        <div :class="['text-4xl font-bold', mot.result === 'pass' ? 'text-green-600' : mot.result === 'fail' ? 'text-red-600' : 'text-gray-400']">
                            {{ mot.result === 'pass' ? 'PASS' : mot.result === 'fail' ? 'FAIL' : 'PENDING' }}
                        </div>
                        <p v-if="mot.expiry_date && mot.result === 'pass'" class="mt-2 text-sm text-green-700">Valid until {{ fmt(mot.expiry_date) }}</p>
                    </div>

                    <!-- Vehicle Card -->
                    <div v-if="mot.vehicle" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Vehicle</h3>
                        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-md px-3 py-1 text-center font-bold text-gray-900 text-lg tracking-wider mb-3">{{ mot.vehicle.registration_number }}</div>
                        <div class="space-y-1 text-sm">
                            <p class="text-gray-700">{{ mot.vehicle.make }} {{ mot.vehicle.model }} {{ mot.vehicle.year }}</p>
                            <p v-if="mot.vehicle.colour" class="text-gray-500">{{ mot.vehicle.colour }}</p>
                            <p v-if="mot.vehicle.fuel_type" class="text-gray-500">{{ mot.vehicle.fuel_type }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
