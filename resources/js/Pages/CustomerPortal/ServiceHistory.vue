<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any, jobCards: any }>()
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }
function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }

const openJob = ref<number | null>(null)
function toggle(id: number) { openJob.value = openJob.value === id ? null : id }

function serviceTotal(s: any) {
    return parseFloat(s.unit_price || 0) * parseFloat(s.quantity || 1) - parseFloat(s.discount || 0)
}
function partTotal(p: any) {
    return parseFloat(p.unit_price || 0) * parseFloat(p.quantity || 1) - parseFloat(p.discount || 0)
}
function jobTotal(job: any) {
    const s = (job.services || []).reduce((sum: number, sv: any) => sum + serviceTotal(sv), 0)
    const p = (job.parts    || []).reduce((sum: number, pt: any) => sum + partTotal(pt),    0)
    return s + p
}
</script>

<template>
    <Head title="Service History" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <h1 class="text-xl font-bold text-gray-900">Service History</h1>

            <div v-if="!jobCards.data?.length"
                class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
                No service history found.
            </div>

            <div v-for="job in jobCards.data" :key="job.id"
                class="bg-white rounded-xl border border-gray-200 overflow-hidden">

                <!-- Header row — always visible, click to expand -->
                <button type="button" @click="toggle(job.id)"
                    class="w-full text-left px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 transition-colors">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">{{ job.job_number }}</p>
                        <p class="text-sm text-gray-500 mt-0.5 truncate">
                            {{ fmtDate(job.created_at) }}
                            <template v-if="job.vehicle"> · {{ job.vehicle.registration_number }} – {{ job.vehicle.make }} {{ job.vehicle.model }}</template>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span :class="['text-xs px-2 py-1 rounded-full font-medium capitalize',
                            job.status === 'completed' || job.status === 'invoiced'
                                ? 'bg-green-50 text-green-700'
                                : 'bg-electric-50 text-electric-700']">
                            {{ job.status }}
                        </span>
                        <!-- Chevron -->
                        <svg :class="['w-4 h-4 text-gray-400 transition-transform', openJob === job.id ? 'rotate-180' : '']"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                <!-- Expanded detail panel -->
                <div v-if="openJob === job.id" class="border-t border-gray-100 px-5 py-4 space-y-4">

                    <!-- Description / complaint -->
                    <div v-if="job.description || job.customer_complaint">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Complaint / Description</p>
                        <p class="text-sm text-gray-700">{{ job.customer_complaint || job.description }}</p>
                    </div>

                    <!-- Work done / diagnosis -->
                    <div v-if="job.work_performed || job.diagnosis">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Work Performed</p>
                        <p class="text-sm text-gray-700">{{ job.work_performed || job.diagnosis }}</p>
                    </div>

                    <!-- Labour / services -->
                    <div v-if="job.services?.length">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Labour</p>
                        <div class="space-y-1.5">
                            <div v-for="s in job.services" :key="s.id"
                                class="flex items-center justify-between text-sm gap-2">
                                <span class="text-gray-700">{{ s.description || s.service?.name || 'Labour' }}</span>
                                <span class="text-gray-900 font-medium flex-shrink-0">{{ fmt(serviceTotal(s)) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Parts -->
                    <div v-if="job.parts?.length">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Parts</p>
                        <div class="space-y-1.5">
                            <div v-for="p in job.parts" :key="p.id"
                                class="flex items-center justify-between text-sm gap-2">
                                <span class="text-gray-700">
                                    {{ p.description || p.part?.name || 'Part' }}
                                    <span v-if="p.quantity > 1" class="text-gray-400"> × {{ p.quantity }}</span>
                                </span>
                                <span class="text-gray-900 font-medium flex-shrink-0">{{ fmt(partTotal(p)) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div v-if="job.services?.length || job.parts?.length"
                        class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <span class="text-sm font-semibold text-gray-700">Total</span>
                        <span class="text-base font-bold text-gray-900">{{ fmt(jobTotal(job)) }}</span>
                    </div>

                    <!-- Technician notes (if exists and not sensitive) -->
                    <div v-if="job.technician_notes">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Technician Notes</p>
                        <p class="text-sm text-gray-700">{{ job.technician_notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
