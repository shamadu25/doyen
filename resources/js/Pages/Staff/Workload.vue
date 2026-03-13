<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps<{ technicians: any[] }>()
const route = inject<(p: string) => string>('route', p => p)
</script>

<template>
    <Head title="Technician Workload" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-6xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Technician Workload</h1>
                    <p class="text-sm text-gray-500 mt-1">Active job distribution across the team</p>
                </div>
                <Link :href="route('/staff')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">← Staff List</Link>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="!technicians.length" class="col-span-full text-center text-gray-400 py-12">No technicians found.</div>
                <div v-for="t in technicians" :key="t.id" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-electric-600 flex items-center justify-center">
                            <span class="text-white font-bold">{{ t.name?.charAt(0) }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ t.name }}</p>
                            <p class="text-xs text-gray-500">{{ t.active_jobs }} active job{{ t.active_jobs !== 1 ? 's' : '' }}</p>
                        </div>
                        <div class="ml-auto">
                            <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', t.active_jobs === 0 ? 'bg-green-50 text-green-700' : t.active_jobs < 3 ? 'bg-yellow-50 text-yellow-700' : 'bg-red-50 text-red-700']">
                                {{ t.active_jobs === 0 ? 'Free' : t.active_jobs < 3 ? 'Busy' : 'Overloaded' }}
                            </span>
                        </div>
                    </div>
                    <!-- Load bar -->
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div :style="{ width: Math.min(100, t.active_jobs * 25) + '%' }" :class="['h-2 rounded-full transition-all', t.active_jobs === 0 ? 'bg-green-400' : t.active_jobs < 3 ? 'bg-yellow-400' : 'bg-red-500']"></div>
                    </div>
                    <div v-if="t.skills?.length" class="flex flex-wrap gap-1">
                        <span v-for="s in t.skills.slice(0,3)" :key="s" class="px-1.5 py-0.5 bg-electric-50 text-electric-700 rounded text-xs">{{ s }}</span>
                        <span v-if="t.skills.length > 3" class="px-1.5 py-0.5 bg-gray-50 text-gray-500 rounded text-xs">+{{ t.skills.length - 3 }}</span>
                    </div>
                    <Link :href="route(`/staff/${t.id}`)" class="block text-center text-xs text-electric-600 hover:underline">View Profile →</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
