<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any, jobCards: any }>()
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }
</script>

<template>
    <Head title="Service History" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <h1 class="text-xl font-bold text-gray-900">Service History</h1>
            <div v-if="!jobCards.data?.length" class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">No service history found.</div>
            <div v-for="job in jobCards.data" :key="job.id" class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900">{{ job.job_number }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ fmtDate(job.created_at) }} · {{ job.vehicle?.registration_number }} – {{ job.vehicle?.make }} {{ job.vehicle?.model }}</p>
                    </div>
                    <span :class="['text-xs px-2 py-1 rounded-full font-medium capitalize flex-shrink-0', job.status === 'completed' || job.status === 'invoiced' ? 'bg-green-50 text-green-700' : 'bg-electric-50 text-electric-700']">{{ job.status }}</span>
                </div>
                <div v-if="job.description" class="text-sm text-gray-600">{{ job.description }}</div>
                <div v-if="job.services?.length || job.parts?.length" class="space-y-1">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Work Carried Out</p>
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="s in job.services" :key="s.id" class="px-2 py-0.5 bg-electric-50 text-electric-700 rounded text-xs">{{ s.description || s.service?.name || 'Labour' }}</span>
                        <span v-for="p in job.parts" :key="p.id" class="px-2 py-0.5 bg-green-50 text-green-700 rounded text-xs">{{ p.description || p.part?.name || 'Part' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
