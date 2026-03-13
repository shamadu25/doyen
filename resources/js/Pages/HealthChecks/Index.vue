<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps<{ checks: any }>()
const route = inject<(p: string) => string>('route', p => p)
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }
</script>

<template>
    <Head title="Vehicle Health Checks" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Vehicle Health Checks</h1>
                    <p class="text-sm text-gray-500 mt-1">Digital inspection reports</p>
                </div>
                <Link :href="route('/health-checks/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Health Check
                </Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Vehicle</th>
                            <th class="hidden sm:table-cell text-left px-4 py-3 font-medium text-gray-600">Customer</th>
                            <th class="hidden md:table-cell text-right px-4 py-3 font-medium text-gray-600">Mileage</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Good</th>
                            <th class="hidden md:table-cell text-center px-4 py-3 font-medium text-gray-600">Advisory</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Urgent</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!checks.data?.length">
                            <td colspan="8" class="text-center text-gray-400 py-12">No health checks found.</td>
                        </tr>
                        <tr v-for="c in checks.data" :key="c.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ fmtDate(c.check_date) }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ c.vehicle?.registration_number }}
                                <p class="sm:hidden text-xs text-gray-500 mt-0.5">{{ c.vehicle?.customer?.name }}</p>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 text-gray-600">{{ c.vehicle?.customer?.name }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-right text-gray-600">{{ c.mileage?.toLocaleString() }}</td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">{{ c.good_count || 0 }}</span></td>
                            <td class="hidden md:table-cell px-4 py-3 text-center"><span class="px-2 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">{{ c.advisory_count || 0 }}</span></td>
                            <td class="px-4 py-3 text-center"><span :class="['px-2 py-0.5 rounded-full text-xs font-medium', (c.urgent_count || 0) > 0 ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-500']">{{ c.urgent_count || 0 }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route(`/health-checks/${c.id}`)" class="text-electric-600 hover:underline text-sm">View</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div><!-- end overflow-x-auto -->
                <!-- Pagination -->
                <div v-if="checks.links?.length > 3" class="flex justify-center gap-1 p-4 border-t border-gray-100">
                    <component v-for="link in checks.links" :key="link.label"
                        :is="link.url ? Link : 'span'"
                        :href="link.url || undefined"
                        v-html="link.label"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-electric-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 cursor-default' : '']"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
