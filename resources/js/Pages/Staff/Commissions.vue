<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ staff: any, commissions: any, stats: any }>()
const route = inject<(p: string) => string>('route', p => p)

function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

const selected = ref<number[]>([])
const paidDate = ref(new Date().toISOString().split('T')[0])

function toggleSelect(id: number) {
    const i = selected.value.indexOf(id)
    if (i >= 0) selected.value.splice(i, 1)
    else selected.value.push(id)
}

function approve(id: number) { router.post(route(`/commissions/${id}/approve`)) }
function paySelected() {
    if (!selected.value.length) return
    router.post(route('/commissions/pay'), { commission_ids: selected.value, paid_date: paidDate.value })
    selected.value = []
}

const statusColor: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700',
    approved: 'bg-electric-50 text-electric-700',
    paid: 'bg-green-50 text-green-700',
}
</script>

<template>
    <Head :title="`${staff.name} - Commissions`" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route(`/staff/${staff.id}`)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">{{ staff.name }} – Commissions</h1>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-xl font-bold text-yellow-600">{{ fmt(stats.pending) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Pending</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-xl font-bold text-electric-600">{{ fmt(stats.approved) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Approved</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-xl font-bold text-green-600">{{ fmt(stats.paid_this_month) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Paid This Month</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ fmt(stats.total_earned) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Earned</p>
                </div>
            </div>

            <!-- Pay Selected -->
            <div v-if="selected.length" class="bg-electric-50 border border-electric-200 rounded-xl p-4 flex items-center justify-between gap-4">
                <span class="text-sm text-electric-700">{{ selected.length }} commission(s) selected</span>
                <div class="flex items-center gap-3">
                    <input v-model="paidDate" type="date" class="px-2 py-1.5 border border-electric-200 rounded text-sm" />
                    <button @click="paySelected" class="px-4 py-1.5 bg-electric-600 text-white rounded-lg text-sm">Mark as Paid</button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 w-8"></th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Job Card</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!commissions.data?.length">
                            <td colspan="6" class="text-center text-gray-400 py-8">No commissions found.</td>
                        </tr>
                        <tr v-for="c in commissions.data" :key="c.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <input v-if="c.status === 'approved'" type="checkbox" :checked="selected.includes(c.id)" @change="toggleSelect(c.id)" class="rounded border-gray-300" />
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ fmtDate(c.created_at) }}</td>
                            <td class="px-4 py-3 font-mono text-electric-600">
                                <Link v-if="c.job_card" :href="route(`/job-cards/${c.job_card.id}`)">{{ c.job_card.job_number }}</Link>
                                <span v-else>-</span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ fmt(c.commission_amount) }}</td>
                            <td class="px-4 py-3"><span :class="['text-xs font-medium px-2 py-0.5 rounded-full', statusColor[c.status] || 'bg-gray-50 text-gray-600']">{{ c.status }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <button v-if="c.status === 'pending'" @click="approve(c.id)" class="text-electric-600 hover:underline text-xs">Approve</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
