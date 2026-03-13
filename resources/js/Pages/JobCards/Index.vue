<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    jobCards: any
    filters: { search?: string; status?: string; priority?: string; technician?: string }
    technicians: any[]
}>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const priority = ref(props.filters.priority || '')

let debounce: any
watch([search, status, priority], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('/job-cards'), { search: search.value, status: status.value, priority: priority.value }, { preserveState: true, replace: true })
    }, 300)
})
</script>

<template>
    <Head title="Job Cards" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Job Cards</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage workshop job cards</p>
                </div>
                <Link :href="route('/job-cards/create')" class="bg-electric-600 hover:bg-electric-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">New Job Card</Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
                    <input v-model="search" type="text" placeholder="Search jobs..." class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600 w-64" />
                    <select v-model="status" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="awaiting_parts">Awaiting Parts</option>
                        <option value="completed">Completed</option>
                        <option value="invoiced">Invoiced</option>
                    </select>
                    <select v-model="priority" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="">All Priority</option>
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                                <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technician</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                                <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="job in jobCards.data" :key="job.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Link :href="route(`/job-cards/${job.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">{{ job.job_number }}</Link>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ job.customer?.first_name }} {{ job.customer?.last_name }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ job.vehicle?.registration_number }}</td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ job.assigned_to_user?.name || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap"><StatusBadge :status="job.status" size="sm" /></td>
                                <td class="hidden sm:table-cell px-4 py-3 whitespace-nowrap"><StatusBadge :status="job.priority" size="sm" /></td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ new Date(job.created_at).toLocaleDateString('en-GB') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                    <Link :href="route(`/job-cards/${job.id}`)" class="text-electric-600 hover:text-electric-700 text-sm">View</Link>
                                    <Link :href="route(`/job-cards/${job.id}/edit`)" class="text-gray-600 hover:text-gray-700 text-sm">Edit</Link>
                                </td>
                            </tr>
                            <tr v-if="!jobCards.data?.length">
                                <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">No job cards found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="jobCards.links" :from="jobCards.from" :to="jobCards.to" :total="jobCards.total" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
