<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler)

const props = defineProps<{
    stats: {
        totalRevenue: number
        totalJobs: number
        totalCustomers: number
        avgJobValue: number
        outstandingBalance: number
        motTests: number
    }
    revenueChart: { labels: string[]; data: number[] }
    topServices: { name: string; count: number; revenue: number }[]
    techProductivity: { name: string; jobs: number; revenue: number; hours: number }[]
    period: string
}>()

const period = ref(props.period || 'month')

const route = inject<(path: string) => string>('route', (p) => p)

function changePeriod() {
    router.get(route('/reports'), { period: period.value }, { preserveState: true, replace: true })
}

function exportCsv() {
    window.location.href = route(`/reports/export?period=${period.value}`)
}

function fmt(amount: number) { return '£' + amount.toFixed(2) }

const chartData = {
    labels: props.revenueChart.labels,
    datasets: [{
        label: 'Revenue',
        data: props.revenueChart.data,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true,
    }],
}

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { callback: (v: any) => '£' + v } },
    },
}
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
                    <p class="mt-1 text-sm text-gray-500">Business performance and analytics</p>
                </div>
                <div class="flex items-center gap-3">
                    <select v-model="period" @change="changePeriod" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                    </select>
                    <button @click="exportCsv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Export CSV</button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <StatCard label="Total Revenue" :value="`£${(stats.totalRevenue || 0).toLocaleString()}`" />
                <StatCard label="Total Jobs" :value="String(stats.totalJobs || 0)" />
                <StatCard label="Customers" :value="String(stats.totalCustomers || 0)" />
                <StatCard label="Avg Job Value" :value="fmt(stats.avgJobValue || 0)" />
                <StatCard label="Outstanding" :value="fmt(stats.outstandingBalance || 0)" />
                <StatCard label="MOT Tests" :value="String(stats.motTests || 0)" />
            </div>

            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h2>
                <div class="h-72">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Services -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Services</h2>
                    <table v-if="topServices?.length" class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-2 text-gray-500 font-medium">Service</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Jobs</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="s in topServices" :key="s.name">
                                <td class="py-2 text-gray-900">{{ s.name }}</td>
                                <td class="py-2 text-right text-gray-600">{{ s.count }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">{{ fmt(s.revenue) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-gray-400 text-center py-4">No data available</p>
                </div>

                <!-- Technician Productivity -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Technician Productivity</h2>
                    <table v-if="techProductivity?.length" class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-2 text-gray-500 font-medium">Technician</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Jobs</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Hours</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in techProductivity" :key="t.name">
                                <td class="py-2 text-gray-900">{{ t.name }}</td>
                                <td class="py-2 text-right text-gray-600">{{ t.jobs }}</td>
                                <td class="py-2 text-right text-gray-600">{{ t.hours }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">{{ fmt(t.revenue) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-gray-400 text-center py-4">No data available</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
