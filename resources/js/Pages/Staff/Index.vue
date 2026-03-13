<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps<{ staff: any, stats: any }>()
const route = inject<(p: string) => string>('route', p => p)

const roleColor: Record<string, string> = {
    admin: 'bg-red-100 text-red-700',
    manager: 'bg-orange-100 text-orange-700',
    technician: 'bg-electric-100 text-electric-700',
    receptionist: 'bg-green-100 text-green-700',
}
</script>

<template>
    <Head title="Staff" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Staff Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your team members</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('/workload')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Workload</Link>
                    <Link :href="route('/staff/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Staff
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Staff</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
                    <p class="text-xs text-gray-500 mt-1">Active</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-electric-600">{{ stats.technicians }}</p>
                    <p class="text-xs text-gray-500 mt-1">Technicians</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ stats.working_today }}</p>
                    <p class="text-xs text-gray-500 mt-1">Working Today</p>
                </div>
            </div>

            <!-- Staff Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="!staff.data?.length" class="col-span-full text-center text-gray-400 py-12">No staff members found.</div>
                <div v-for="s in staff.data" :key="s.id" class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-electric-600 flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">{{ s.name?.charAt(0)?.toUpperCase() }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ s.name }}</p>
                                <p class="text-xs text-gray-500">{{ s.employee_id }}</p>
                            </div>
                        </div>
                        <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', roleColor[s.role] || 'bg-gray-100 text-gray-600']">{{ s.role }}</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ s.email }}</p>
                    <div class="flex items-center justify-between">
                        <span :class="['text-xs font-medium', s.is_active ? 'text-green-600' : 'text-red-500']">
                            {{ s.is_active ? '● Active' : '○ Inactive' }}
                        </span>
                        <span class="text-xs text-gray-500">{{ s.assigned_jobs_count || s.assigned_jobs?.length || 0 }} active jobs</span>
                    </div>
                    <div class="flex gap-2 pt-1 border-t border-gray-100">
                        <Link :href="route(`/staff/${s.id}`)" class="flex-1 text-center px-2 py-1.5 text-xs text-electric-600 hover:bg-electric-50 rounded">View</Link>
                        <Link :href="route(`/staff/${s.id}/edit`)" class="flex-1 text-center px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-50 rounded">Edit</Link>
                        <Link :href="route(`/staff/${s.id}/schedule`)" class="flex-1 text-center px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-50 rounded">Schedule</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
