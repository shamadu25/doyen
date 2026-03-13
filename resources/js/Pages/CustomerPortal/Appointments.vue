<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any, appointments: any }>()

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmtDate(d: string | null) {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}
function fmtTime(d: string | null) {
    if (!d) return ''
    const dt = new Date(d)
    return dt.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}
function fmtProposedDate(date: string | null) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}

const statusColour: Record<string, string> = {
    pending:               'bg-yellow-50 text-yellow-700',
    confirmed:             'bg-green-50 text-green-700',
    completed:             'bg-gray-100 text-gray-600',
    cancelled:             'bg-red-50 text-red-700',
    reschedule_requested:  'bg-blue-50 text-blue-700',
    in_progress:           'bg-purple-50 text-purple-700',
}
function statusLabel(s: string) {
    const map: Record<string, string> = {
        pending:              'Pending',
        confirmed:            'Confirmed',
        completed:            'Completed',
        cancelled:            'Cancelled',
        reschedule_requested: 'Reschedule Requested',
        in_progress:          'In Progress',
    }
    return map[s] ?? s
}

const CANCEL_REASONS = [
    'I no longer need this service',
    'I have found another garage',
    'The date/time no longer suits me',
    'Vehicle issue resolved',
    'Financial reasons',
    'Other',
]

const TIME_SLOTS = [
    '08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30',
    '12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30',
    '16:00','16:30','17:00',
]

const today = new Date().toISOString().split('T')[0]

// ── Cancel modal ──────────────────────────────────────────────────────────────
const cancelId       = ref<number | null>(null)
const cancelReason   = ref('')
const cancelling     = ref(false)

function openCancel(id: number) { cancelId.value = id; cancelReason.value = '' }
function closeCancel()          { cancelId.value = null; cancelReason.value = '' }
function doCancel() {
    if (!cancelId.value) return
    cancelling.value = true
    router.post(`/customer/appointments/${cancelId.value}/cancel`,
        { cancellation_reason: cancelReason.value },
        { preserveScroll: true, onFinish: () => { cancelling.value = false; closeCancel() } }
    )
}

// ── Request new time modal ────────────────────────────────────────────────────
const reschedId         = ref<number | null>(null)
const reschedDate       = ref('')
const reschedTime       = ref('')
const reschedNotes      = ref('')
const rescheduling      = ref(false)

function openReschedule(id: number) {
    reschedId.value    = id
    reschedDate.value  = ''
    reschedTime.value  = ''
    reschedNotes.value = ''
}
function closeReschedule() { reschedId.value = null }
function doRequestNewTime() {
    if (!reschedId.value) return
    rescheduling.value = true
    router.post(`/customer/appointments/${reschedId.value}/request-new-time`,
        { preferred_date: reschedDate.value, preferred_time: reschedTime.value, reschedule_notes: reschedNotes.value },
        { preserveScroll: true, onFinish: () => { rescheduling.value = false; closeReschedule() } }
    )
}

// ── Accept/keep proposed time ─────────────────────────────────────────────────
const actioning = ref<number | null>(null)

function acceptProposed(id: number) {
    actioning.value = id
    router.post(`/customer/appointments/${id}/accept-proposed`, {},
        { preserveScroll: true, onFinish: () => { actioning.value = null } }
    )
}
function keepOriginal(id: number) {
    actioning.value = id
    router.post(`/customer/appointments/${id}/keep-original`, {},
        { preserveScroll: true, onFinish: () => { actioning.value = null } }
    )
}
</script>

<template>
    <Head title="My Appointments" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">My Appointments</h1>
                <Link href="/customer/appointments/book"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-electric-600 px-3 py-2 text-sm font-semibold text-white hover:bg-electric-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Book
                </Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div v-if="!appointments.data?.length" class="text-center text-gray-400 py-10 text-sm">
                    No appointments found.
                    <div class="mt-3"><Link href="/customer/appointments/book" class="text-electric-600 hover:underline">Book your first appointment →</Link></div>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div v-for="a in appointments.data" :key="a.id" class="px-5 py-4">
                        <!-- Main row: info + status badge -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ a.appointment_type || 'Vehicle Service' }}</p>
                                <p class="text-sm text-gray-600 mt-0.5">
                                    {{ fmtDate(a.scheduled_date) }}
                                    <span v-if="a.scheduled_date"> at {{ fmtTime(a.scheduled_date) }}</span>
                                </p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ a.vehicle?.registration_number }} – {{ a.vehicle?.make }} {{ a.vehicle?.model }}
                                </p>
                                <p v-if="a.customer_notes && a.status !== 'reschedule_requested'" class="text-xs text-gray-400 mt-1 truncate max-w-xs">{{ a.customer_notes }}</p>

                                <!-- Reschedule requested info -->
                                <div v-if="a.status === 'reschedule_requested'" class="mt-2 rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-800 space-y-0.5">
                                    <p class="font-semibold">Reschedule request sent to garage</p>
                                    <p v-if="a.reschedule_requested_date">Preferred: {{ fmtProposedDate(a.reschedule_requested_date) }}<span v-if="a.reschedule_requested_time"> at {{ a.reschedule_requested_time }}</span></p>
                                    <p v-if="a.reschedule_notes" class="text-blue-600">Note: {{ a.reschedule_notes }}</p>
                                </div>

                                <!-- Cancellation reason -->
                                <p v-if="a.status === 'cancelled' && a.cancellation_reason" class="text-xs text-red-500 mt-1">
                                    Reason: {{ a.cancellation_reason }}
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <span :class="['text-xs px-2 py-1 rounded-full font-medium', statusColour[a.status] ?? 'bg-gray-50 text-gray-600']">
                                    {{ statusLabel(a.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Garage proposed a new time ─────────────────────── -->
                        <div v-if="a.proposed_date && a.proposed_time && !['cancelled','completed'].includes(a.status)"
                             class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3">
                            <p class="text-sm font-semibold text-amber-800">
                                The garage has proposed a new appointment time:
                            </p>
                            <p class="text-sm text-amber-700 mt-0.5">
                                {{ fmtProposedDate(a.proposed_date) }} at {{ a.proposed_time }}
                            </p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <button @click="acceptProposed(a.id)" :disabled="actioning === a.id"
                                    class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 disabled:opacity-60">
                                    {{ actioning === a.id ? 'Processing…' : 'Confirm New Appointment Date/Time' }}
                                </button>
                                <button @click="openReschedule(a.id)" :disabled="actioning === a.id"
                                    class="rounded-lg bg-electric-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-electric-700 disabled:opacity-60">
                                    Request an Alternative Date/Time
                                </button>
                                <button @click="keepOriginal(a.id)" :disabled="actioning === a.id"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-60">
                                    Keep Original Appointment Date/Time
                                </button>
                            </div>
                        </div>

                        <!-- Standard action buttons ────────────────────────── -->
                        <div v-else-if="['pending','confirmed','reschedule_requested'].includes(a.status)"
                             class="flex gap-3 mt-3">
                            <button @click="openReschedule(a.id)"
                                class="text-xs rounded-lg border border-electric-300 px-3 py-1.5 font-medium text-electric-700 hover:bg-electric-50">
                                Request an Alternative Date/Time
                            </button>
                            <button @click="openCancel(a.id)"
                                class="text-xs rounded-lg border border-red-200 px-3 py-1.5 font-medium text-red-600 hover:bg-red-50">
                                Cancel Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="appointments.links?.length > 3" class="flex justify-center gap-1">
                <template v-for="link in appointments.links" :key="link.label">
                    <component :is="link.url ? 'a' : 'span'" :href="link.url || undefined"
                        v-html="link.label"
                        :class="['px-3 py-1 text-sm rounded border', link.active ? 'bg-electric-600 text-white border-electric-600' : link.url ? 'border-gray-300 text-gray-600 hover:bg-gray-50' : 'border-gray-200 text-gray-400 cursor-default']" />
                </template>
            </div>
        </div>

        <!-- Cancel Modal ─────────────────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="cancelId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full">
                    <h3 class="font-bold text-gray-900 text-lg">Cancel booking</h3>
                    <p class="text-sm text-gray-500 mt-1">Please let us know why you're cancelling.</p>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-gray-400 font-normal">(optional)</span></label>
                        <select v-model="cancelReason"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-500">
                            <option value="">Select a reason…</option>
                            <option v-for="r in CANCEL_REASONS" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>
                    <div class="flex gap-3 mt-5">
                        <button @click="closeCancel" class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Keep it
                        </button>
                        <button @click="doCancel" :disabled="cancelling"
                            class="flex-1 rounded-lg bg-red-600 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">
                            {{ cancelling ? 'Cancelling…' : 'Yes, cancel' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Request New Date/Time Modal ──────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="reschedId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full">
                    <h3 class="font-bold text-gray-900 text-lg">Request an Alternative Date/Time</h3>
                    <p class="text-sm text-gray-500 mt-1">Let us know your preferred time and we'll be in touch to confirm.</p>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date <span class="text-red-500">*</span></label>
                            <input v-model="reschedDate" type="date" :min="today" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Time <span class="text-red-500">*</span></label>
                            <select v-model="reschedTime" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-500">
                                <option value="">Select a time…</option>
                                <option v-for="t in TIME_SLOTS" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea v-model="reschedNotes" rows="2" maxlength="500"
                                placeholder="Any other information for the garage…"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-electric-500 resize-none" />
                        </div>
                    </div>
                    <div class="flex gap-3 mt-5">
                        <button @click="closeReschedule" class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="doRequestNewTime" :disabled="rescheduling || !reschedDate || !reschedTime"
                            class="flex-1 rounded-lg bg-electric-600 py-2 text-sm font-semibold text-white hover:bg-electric-700 disabled:opacity-60">
                            {{ rescheduling ? 'Sending…' : 'Send Request' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </CustomerPortalLayout>
</template>
