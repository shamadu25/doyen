<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler)

const route = inject<(path: string) => string>('route', (p) => p)

interface Props {
    stats: Record<string, number>
    revenueChart: { labels: string[]; revenue: number[] }
    recentJobs: any[]
    recentBookings: any[]
    upcomingMots: any[]
}

const props = defineProps<Props>()

const chartData = {
    labels: props.revenueChart.labels,
    datasets: [{
        label: 'Revenue (£)',
        data: props.revenueChart.revenue,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 0,
        pointHoverRadius: 4,
    }]
}

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { mode: 'index' as const, intersect: false } },
    scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
        y: { beginAtZero: true, ticks: { callback: (v: any) => '£' + v.toLocaleString() } }
    }
}

const formatCurrency = (v: number) => '£' + v.toLocaleString('en-GB', { minimumFractionDigits: 2 })
</script>

<template>
    <Head title="Dashboard | Doyen Auto Services">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Overview of your workshop performance</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Today's Revenue" :value="stats.daily_revenue" prefix="£" />
                <StatCard label="Monthly Revenue" :value="stats.monthly_revenue" prefix="£" />
                <StatCard label="MOT Revenue" :value="stats.mot_revenue" prefix="£" />
                <StatCard label="Outstanding" :value="stats.outstanding_amount" prefix="£" />
                <StatCard label="Jobs In Progress" :value="stats.jobs_in_progress" />
                <StatCard label="Bookings Today" :value="stats.bookings_today" />
                <StatCard label="Total Customers" :value="stats.customers_total" />
                <StatCard label="Low Stock Items" :value="stats.low_stock_count" />
            </div>

            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue (Last 30 Days)</h3>
                <div style="height: 300px">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Jobs -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Job Cards</h3>
                        <Link :href="route('/job-cards')" class="text-sm text-electric-600 hover:text-electric-700 font-medium">View all</Link>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="job in recentJobs" :key="job.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                            <div>
                                <Link :href="route(`/job-cards/${job.id}`)" class="text-sm font-medium text-gray-900 hover:text-electric-600">{{ job.job_number }}</Link>
                                <p class="text-xs text-gray-500">{{ job.customer?.first_name }} {{ job.customer?.last_name }} · {{ job.vehicle?.registration_number }}</p>
                            </div>
                            <StatusBadge :status="job.status" size="sm" />
                        </div>
                        <div v-if="!recentJobs.length" class="px-6 py-8 text-center text-sm text-gray-500">No recent job cards</div>
                    </div>
                </div>

                <!-- Upcoming MOTs -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Upcoming MOT Expiries</h3>
                        <Link :href="route('/mot-tests')" class="text-sm text-electric-600 hover:text-electric-700 font-medium">View all</Link>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="vehicle in upcomingMots" :key="vehicle.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                            <div>
                                <Link :href="route(`/vehicles/${vehicle.id}`)" class="text-sm font-medium text-gray-900 hover:text-electric-600">{{ vehicle.registration_number }}</Link>
                                <p class="text-xs text-gray-500">{{ vehicle.make }} {{ vehicle.model }} · {{ vehicle.customer?.first_name }} {{ vehicle.customer?.last_name }}</p>
                            </div>
                            <span class="text-xs font-medium text-red-600">{{ new Date(vehicle.mot_due_date).toLocaleDateString('en-GB') }}</span>
                        </div>
                        <div v-if="!upcomingMots.length" class="px-6 py-8 text-center text-sm text-gray-500">No upcoming MOT expiries</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    <Link :href="route('/customers/create')" class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-electric-200 hover:bg-electric-50 transition-colors">
                        <span class="text-2xl mb-2">👤</span>
                        <span class="text-xs font-medium text-gray-700">New Customer</span>
                    </Link>
                    <Link :href="route('/vehicles/create')" class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-electric-200 hover:bg-electric-50 transition-colors">
                        <span class="text-2xl mb-2">🚗</span>
                        <span class="text-xs font-medium text-gray-700">Add Vehicle</span>
                    </Link>
                    <Link :href="route('/bookings/create')" class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-electric-200 hover:bg-electric-50 transition-colors">
                        <span class="text-2xl mb-2">📅</span>
                        <span class="text-xs font-medium text-gray-700">New Booking</span>
                    </Link>
                    <Link :href="route('/job-cards/create')" class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-electric-200 hover:bg-electric-50 transition-colors">
                        <span class="text-2xl mb-2">📋</span>
                        <span class="text-xs font-medium text-gray-700">New Job Card</span>
                    </Link>
                    <Link :href="route('/mot-tests/create')" class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-electric-200 hover:bg-electric-50 transition-colors">
                        <span class="text-2xl mb-2">🛡️</span>
                        <span class="text-xs font-medium text-gray-700">MOT Test</span>
                    </Link>
                    <Link :href="route('/invoices/create')" class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-electric-200 hover:bg-electric-50 transition-colors">
                        <span class="text-2xl mb-2">💷</span>
                        <span class="text-xs font-medium text-gray-700">New Invoice</span>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
