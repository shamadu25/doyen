<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    jobs: any
    stats: { total: number; in_progress: number; completed: number; booked: number }
    categoryCounts: Record<string, number>
    categories: Record<string, string>
    statusLabels: Record<string, string>
    technicians: { id: number; name: string }[]
    filters: { search?: string; category?: string; status?: string; technician?: string }
}>()

const search     = ref(props.filters.search     ?? '')
const category   = ref(props.filters.category   ?? '')
const status     = ref(props.filters.status     ?? '')
const technician = ref(props.filters.technician ?? '')

function applyFilters() {
    router.get('/ecu-jobs', {
        search:     search.value     || undefined,
        category:   category.value   || undefined,
        status:     status.value     || undefined,
        technician: technician.value || undefined,
    }, { preserveState: true, replace: true })
}

function clearFilters() {
    search.value = ''
    category.value = ''
    status.value = ''
    technician.value = ''
    router.get('/ecu-jobs', {}, { preserveState: true, replace: true })
}

const statusColors: Record<string, string> = {
    booked:      'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed:   'bg-green-100 text-green-800',
    on_hold:     'bg-orange-100 text-orange-800',
    cancelled:   'bg-red-100 text-red-800',
}

const categoryColors: Record<string, string> = {
    diagnostics:        'bg-purple-100 text-purple-800',
    remapping:          'bg-indigo-100 text-indigo-800',
    airbag_srs:         'bg-rose-100 text-rose-800',
    emissions:          'bg-emerald-100 text-emerald-800',
    immobiliser:        'bg-cyan-100 text-cyan-800',
    mileage_correction: 'bg-amber-100 text-amber-800',
    electrical:         'bg-orange-100 text-orange-800',
    other:              'bg-gray-100 text-gray-800',
}

const flash = computed(() => (usePage().props.flash as any) ?? {})
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">ECU Jobs</h1>
                <Link href="/ecu-jobs/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New ECU Job
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash -->
            <div v-if="flash.success" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ flash.success }}
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Jobs</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                </div>
                <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs text-blue-500 uppercase tracking-wide font-medium">Booked</p>
                    <p class="mt-1 text-3xl font-bold text-blue-600">{{ stats.booked }}</p>
                </div>
                <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs text-yellow-500 uppercase tracking-wide font-medium">In Progress</p>
                    <p class="mt-1 text-3xl font-bold text-yellow-600">{{ stats.in_progress }}</p>
                </div>
                <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                    <p class="text-xs text-green-500 uppercase tracking-wide font-medium">Completed</p>
                    <p class="mt-1 text-3xl font-bold text-green-600">{{ stats.completed }}</p>
                </div>
            </div>

            <!-- Category breakdown -->
            <div v-if="Object.keys(categoryCounts).length" class="flex flex-wrap gap-2">
                <span v-for="(count, key) in categoryCounts" :key="key"
                    :class="['inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium', categoryColors[key] ?? 'bg-gray-100 text-gray-700']">
                    {{ categories[key] ?? key }}: {{ count }}
                </span>
            </div>

            <!-- Filters -->
            <div class="rounded-xl bg-white border border-gray-200 p-4 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <input v-model="search" type="text" placeholder="Search job number, reg, customer…"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keyup.enter="applyFilters" />

                    <select v-model="category" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                    </select>

                    <select v-model="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                    </select>

                    <select v-model="technician" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">All Technicians</option>
                        <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
                <div class="mt-3 flex gap-2">
                    <button @click="applyFilters"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Apply Filters
                    </button>
                    <button @click="clearFilters"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div v-if="jobs.data.length === 0" class="px-6 py-12 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z"/>
                    </svg>
                    <p class="font-medium">No ECU jobs found</p>
                    <p class="text-sm">Try adjusting your filters or <Link href="/ecu-jobs/create" class="text-blue-600 hover:underline">add a new job</Link>.</p>
                </div>
                <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Job #</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Customer / Vehicle</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Service</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Technician</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Price</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date In</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="job in jobs.data" :key="job.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-medium text-blue-700">
                                <Link :href="`/ecu-jobs/${job.id}`" class="hover:underline">{{ job.job_number }}</Link>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    {{ job.customer ? `${job.customer.first_name} ${job.customer.last_name}` : '—' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ job.vehicle ? `${job.vehicle.registration_number} · ${job.vehicle.make} ${job.vehicle.model}` : '—' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', categoryColors[job.category] ?? 'bg-gray-100 text-gray-700']">
                                    {{ categories[job.category] ?? job.category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 max-w-[160px] truncate" :title="job.service_label || job.service_type">
                                {{ job.service_label || job.service_type }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', statusColors[job.status] ?? 'bg-gray-100']">
                                    {{ statusLabels[job.status] ?? job.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ job.technician?.name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ job.price ? `£${parseFloat(job.price).toFixed(2)}` : '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ job.date_in ? new Date(job.date_in).toLocaleDateString('en-GB') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/ecu-jobs/${job.id}`"
                                        class="rounded px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        View
                                    </Link>
                                    <Link :href="`/ecu-jobs/${job.id}/edit`"
                                        class="rounded px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200">
                                        Edit
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="jobs.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Showing {{ jobs.from }}–{{ jobs.to }} of {{ jobs.total }} jobs
                </p>
                <div class="flex gap-1">
                    <Link v-for="link in jobs.links" :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 text-sm rounded border',
                            link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                            !link.url ? 'opacity-40 pointer-events-none' : ''
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
