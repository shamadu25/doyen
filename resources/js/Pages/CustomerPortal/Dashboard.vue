<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const props = defineProps<{ customer: any, appointments: any[], vehicles: any[], invoices: any[], jobCards: any[], pendingQuotes?: number }>()

function fmt(v: any) { return '£' + parseFloat(v || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

const outstanding = computed(() =>
    props.invoices.filter(i => ['sent','overdue','partial'].includes(i.status))
        .reduce((s, i) => s + parseFloat(i.total_amount || 0) - parseFloat(i.paid_amount || 0), 0)
)

const now = new Date()
const in30 = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
const motAlerts = computed(() => props.vehicles.filter(v => {
    if (!v.mot_expiry) return false
    const d = new Date(v.mot_expiry)
    return d <= in30
}))
</script>

<template>
    <Head title="My Dashboard" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Welcome back, {{ customer.name.split(' ')[0] }}</h1>
                <p class="text-sm text-gray-500 mt-1">Your vehicle service overview</p>
            </div>

            <!-- Alerts -->
            <div class="space-y-2">
                <!-- MOT alerts -->
                <div v-for="v in motAlerts" :key="v.id"
                    :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium', new Date(v.mot_expiry) < now ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-orange-50 border border-orange-200 text-orange-800']">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span>
                        <strong>{{ v.registration_number }}</strong>
                        {{ new Date(v.mot_expiry) < now ? ' MOT has expired!' : ` MOT expires ${fmtDate(v.mot_expiry)}` }}
                        — <Link href="/customer/appointments/book" class="underline hover:no-underline">Book now</Link>
                    </span>
                </div>

                <!-- Pending quotes alert -->
                <div v-if="pendingQuotes && pendingQuotes > 0"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-electric-50 border border-electric-200 text-sm font-medium text-electric-800">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>
                        You have <strong>{{ pendingQuotes }} quote{{ pendingQuotes > 1 ? 's' : '' }}</strong> waiting for your approval.
                        <Link href="/customer/quotes" class="underline hover:no-underline">View quotes →</Link>
                    </span>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-electric-600">{{ vehicles.length }}</p>
                    <p class="text-xs text-gray-500 mt-1">Vehicle{{ vehicles.length !== 1 ? 's' : '' }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ appointments.length }}</p>
                    <p class="text-xs text-gray-500 mt-1">Appointments</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ jobCards.length }}</p>
                    <p class="text-xs text-gray-500 mt-1">Services</p>
                </div>
                <div :class="['rounded-xl border p-4 text-center', outstanding > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200']">
                    <p :class="['text-xl font-bold', outstanding > 0 ? 'text-red-700' : 'text-green-700']">{{ fmt(outstanding) }}</p>
                    <p :class="['text-xs mt-1', outstanding > 0 ? 'text-red-500' : 'text-green-500']">Outstanding</p>
                </div>
            </div>

            <!-- Book Appointment CTA -->
            <Link href="/customer/appointments/book"
                class="flex items-center gap-4 bg-electric-600 text-white rounded-xl p-5 hover:bg-electric-700 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold">Book an Appointment</p>
                    <p class="text-sm text-electric-100 mt-0.5">Service, MOT, repairs and more</p>
                </div>
                <svg class="w-5 h-5 text-electric-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>

            <!-- Recent Invoices -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Invoices</h2>
                    <Link href="/customer/invoices" class="text-sm text-electric-600 hover:underline">View all</Link>
                </div>
                <div v-if="!invoices.length" class="text-center text-gray-400 py-6 text-sm">No invoices yet.</div>
                <div v-else class="divide-y divide-gray-100">
                    <div v-for="inv in invoices.slice(0,5)" :key="inv.id" class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ inv.invoice_number }}</p>
                            <p class="text-xs text-gray-500">{{ fmtDate(inv.invoice_date) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ fmt(inv.total_amount) }}</p>
                            <span :class="['text-xs px-1.5 py-0.5 rounded-full font-medium', inv.status === 'paid' ? 'bg-green-50 text-green-700' : inv.status === 'overdue' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700']">{{ inv.status }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Upcoming Appointments</h2>
                    <Link href="/customer/appointments" class="text-sm text-electric-600 hover:underline">View all</Link>
                </div>
                <div v-if="!appointments.length" class="text-center text-gray-400 py-6 text-sm">No appointments.</div>
                <div v-else class="divide-y divide-gray-100">
                    <div v-for="a in appointments.slice(0,3)" :key="a.id" class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ a.service_type || 'Service' }}</p>
                            <p class="text-xs text-gray-500">{{ fmtDate(a.appointment_date) }} · {{ a.vehicle?.registration_number }}</p>
                        </div>
                        <span :class="['text-xs px-1.5 py-0.5 rounded-full font-medium capitalize', a.status === 'confirmed' ? 'bg-green-50 text-green-700' : a.status === 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-electric-50 text-electric-700']">{{ a.status }}</span>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
