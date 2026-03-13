<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ staff: any, stats: any }>()
const route = inject<(p: string) => string>('route', p => p)

const roleColor: Record<string, string> = {
    admin: 'bg-red-100 text-red-700', manager: 'bg-orange-100 text-orange-700',
    technician: 'bg-electric-100 text-electric-700', receptionist: 'bg-green-100 text-green-700',
}
function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

function clockIn() { router.post(route(`/staff/${props.staff.id}/clock-in`)) }
function clockOut() { router.post(route(`/staff/${props.staff.id}/clock-out`)) }
</script>

<template>
    <Head :title="staff.name" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('/staff')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div class="w-14 h-14 rounded-full bg-electric-600 flex items-center justify-center">
                        <span class="text-white font-bold text-xl">{{ staff.name?.charAt(0)?.toUpperCase() }}</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ staff.name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', roleColor[staff.role] || 'bg-gray-100 text-gray-600']">{{ staff.role }}</span>
                            <span class="text-sm text-gray-500">{{ staff.employee_id }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap justify-end">
                    <button @click="clockIn" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">Clock In</button>
                    <button @click="clockOut" class="px-3 py-1.5 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600">Clock Out</button>
                    <Link :href="route(`/staff/${staff.id}/schedule`)" class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Schedule</Link>
                    <Link :href="route(`/staff/${staff.id}/commissions`)" class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Commissions</Link>
                    <Link :href="route(`/staff/${staff.id}/edit`)" class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Edit</Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-electric-600">{{ stats.active_jobs }}</p>
                    <p class="text-xs text-gray-500 mt-1">Active Jobs</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ stats.completed_jobs_month }}</p>
                    <p class="text-xs text-gray-500 mt-1">Jobs This Month</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ fmt(stats.total_commission_month) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Commission (MTD)</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ stats.hours_worked_month || 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">Hours (MTD)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Contact Info -->
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Contact Information</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>{{ staff.email }}</div>
                        <div v-if="staff.phone" class="flex items-center gap-2 text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>{{ staff.phone }}</div>
                        <div class="flex justify-between"><span class="text-gray-400">Hire Date</span><span>{{ fmtDate(staff.hire_date) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-400">Hourly Rate</span><span>{{ staff.hourly_rate ? fmt(staff.hourly_rate) + '/hr' : '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-400">Commission</span><span>{{ staff.commission_rate ? staff.commission_rate + '%' : '-' }}</span></div>
                    </div>
                </div>

                <!-- Skills -->
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Skills & Certifications</h2>
                    <div v-if="staff.skills?.length" class="flex flex-wrap gap-1 mb-3">
                        <span v-for="s in staff.skills" :key="s" class="px-2 py-0.5 bg-electric-50 text-electric-700 rounded text-xs">{{ s }}</span>
                    </div>
                    <div v-if="staff.certifications?.length" class="flex flex-wrap gap-1">
                        <span v-for="c in staff.certifications" :key="c" class="px-2 py-0.5 bg-green-50 text-green-700 rounded text-xs">{{ c }}</span>
                    </div>
                    <p v-if="!staff.skills?.length && !staff.certifications?.length" class="text-gray-400 text-sm">No skills or certifications recorded.</p>
                </div>
            </div>

            <!-- Active Jobs -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Active Job Cards</h2>
                </div>
                <div v-if="!staff.assigned_jobs?.length" class="text-center text-gray-400 py-8 text-sm">No active jobs assigned.</div>
                <table v-else class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Job #</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Vehicle</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Status</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="job in staff.assigned_jobs" :key="job.id">
                            <td class="px-4 py-3 font-mono text-electric-600">{{ job.job_number }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ job.vehicle?.registration_number }} – {{ job.vehicle?.customer?.name }}</td>
                            <td class="px-4 py-3"><span class="capitalize text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full">{{ job.status }}</span></td>
                            <td class="px-4 py-3 text-right"><Link :href="route(`/job-cards/${job.id}`)" class="text-electric-600 hover:underline text-xs">View</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
