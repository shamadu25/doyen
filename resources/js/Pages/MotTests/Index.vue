<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import StatCard from '@/Components/StatCard.vue'

const props = defineProps<{
    motTests: any
    stats: { totalTests: number; passed: number; failed: number; passRate: number }
    filters: { search?: string; result?: string }
}>()

const route = inject<(path: string) => string>('route', (p) => p)

const search = ref(props.filters.search || '')
const result = ref(props.filters.result || '')

let debounce: any
watch([search, result], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('/mot-tests'), { search: search.value, result: result.value }, { preserveState: true, replace: true })
    }, 300)
})
</script>

<template>
    <Head title="MOT Tests" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">MOT Tests</h1>
                    <p class="mt-1 text-sm text-gray-500">Record and track MOT test results</p>
                </div>
                <Link :href="route('/mot-tests/create')" class="bg-electric-600 hover:bg-electric-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">New MOT Test</Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <StatCard label="Total Tests" :value="String(stats.totalTests)" />
                <StatCard label="Passed" :value="String(stats.passed)" />
                <StatCard label="Failed" :value="String(stats.failed)" />
                <StatCard label="Pass Rate" :value="`${stats.passRate}%`" />
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
                    <input v-model="search" type="text" placeholder="Search by reg or customer..." class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600 w-64" />
                    <select v-model="result" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="">All Results</option>
                        <option value="pass">Pass</option>
                        <option value="fail">Fail</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="mot in motTests.data" :key="mot.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Link :href="route(`/mot-tests/${mot.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">{{ mot.test_number || `MOT-${mot.id}` }}</Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ mot.vehicle?.registration_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ mot.vehicle?.customer?.first_name }} {{ mot.vehicle?.customer?.last_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ mot.tester_name || mot.tester?.name || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap"><StatusBadge :status="mot.result || mot.status || 'pending'" size="sm" /></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ mot.test_date ? new Date(mot.test_date).toLocaleDateString('en-GB') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ mot.expiry_date ? new Date(mot.expiry_date).toLocaleDateString('en-GB') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    <Link :href="route(`/mot-tests/${mot.id}`)" class="text-electric-600 hover:text-electric-700 text-sm">View</Link>
                                    <Link :href="route(`/mot-tests/${mot.id}/edit`)" class="text-gray-600 hover:text-gray-700 text-sm">Edit</Link>
                                </td>
                            </tr>
                            <tr v-if="!motTests.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No MOT tests found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="motTests.links" :from="motTests.from" :to="motTests.to" :total="motTests.total" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
