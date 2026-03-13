<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ healthCheck: any }>()
const route = inject<(p: string) => string>('route', p => p)
const hc = computed(() => props.healthCheck)

function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

const itemColor = (status: string) => ({
    good: 'bg-green-50 border-green-200 text-green-800',
    advisory: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    urgent: 'bg-red-50 border-red-200 text-red-800',
}[status] || 'bg-gray-50 text-gray-700')

const itemIcon = (status: string) => ({ good: '✓', advisory: '⚠', urgent: '✕' }[status] || '•')

function emailReport() { router.post(route(`/health-checks/${hc.value.id}/email`)) }
function deleteCheck() { if (confirm('Delete this health check?')) router.delete(route(`/health-checks/${hc.value.id}`)) }
</script>

<template>
    <Head title="Health Check Report" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('/health-checks')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Health Check Report</h1>
                        <p class="text-sm text-gray-500">{{ fmtDate(hc.check_date) }} · {{ hc.vehicle?.registration_number }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="emailReport" class="px-3 py-1.5 text-sm bg-electric-600 text-white rounded-lg hover:bg-electric-700">Email to Customer</button>
                    <button @click="deleteCheck" class="px-3 py-1.5 text-sm text-red-600 hover:text-red-800">Delete</button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-green-700">{{ hc.good_count || 0 }}</p>
                    <p class="text-sm text-green-600 mt-1">Good</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-yellow-700">{{ hc.advisory_count || 0 }}</p>
                    <p class="text-sm text-yellow-600 mt-1">Advisory</p>
                </div>
                <div :class="['border rounded-xl p-4 text-center', (hc.urgent_count||0)>0 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200']">
                    <p :class="['text-3xl font-bold', (hc.urgent_count||0)>0 ? 'text-red-700' : 'text-gray-500']">{{ hc.urgent_count || 0 }}</p>
                    <p :class="['text-sm mt-1', (hc.urgent_count||0)>0 ? 'text-red-600' : 'text-gray-500']">Urgent</p>
                </div>
            </div>

            <!-- Vehicle & Job -->
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Vehicle</p>
                    <p class="font-semibold text-gray-900">{{ hc.vehicle?.registration_number }}</p>
                    <p class="text-sm text-gray-600">{{ hc.vehicle?.make }} {{ hc.vehicle?.model }} {{ hc.vehicle?.year }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ hc.vehicle?.customer?.name }}</p>
                    <p class="text-sm text-gray-500 mt-1">Mileage at check: {{ hc.mileage?.toLocaleString() }} mi</p>
                </div>
                <div v-if="hc.job_card" class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Job Card</p>
                    <Link :href="route(`/job-cards/${hc.job_card.id}`)" class="font-semibold text-electric-600 hover:underline">{{ hc.job_card.job_number }}</Link>
                </div>
            </div>

            <!-- Check Items -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Inspection Results</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(item, i) in hc.check_items" :key="i" class="px-5 py-3 flex items-start gap-4">
                        <span :class="['inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold border flex-shrink-0 mt-0.5', itemColor(item.status)]">
                            {{ itemIcon(item.status) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ item.item }}</p>
                            <p v-if="item.notes" class="text-xs text-gray-500 mt-0.5">{{ item.notes }}</p>
                        </div>
                        <span :class="['text-xs font-medium px-2 py-0.5 rounded-full border capitalize', itemColor(item.status)]">{{ item.status }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="hc.overall_notes" class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Overall Notes</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ hc.overall_notes }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
