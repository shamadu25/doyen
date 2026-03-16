<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const props = defineProps<{
    customer: any
    vehicles: any[]
    services: { id: number; name: string; category: string; estimated_duration_minutes: number | null; price: string | null }[]
    bookingHours: Record<string, { open: boolean; start: string; end: string }>
    closedDates: string[]
    slotDuration: number
}>()

// ── Add-vehicle inline form ───────────────────────────────────────────────────
const showAddVehicle = ref(false)
const addVehicleForm = useForm({
    registration_number: '',
    make: '',
    model: '',
    year: '',
    color: '',
    fuel_type: '',
})

function submitAddVehicle() {
    addVehicleForm.post('/customer/vehicles', {
        preserveScroll: true,
        onSuccess: () => {
            showAddVehicle.value = false
            addVehicleForm.reset()
            // Reload the page so the new vehicle appears in props.vehicles
            router.reload({ only: ['vehicles'] })
        },
    })
}
// ── Booking availability from admin settings ─────────────────────────────────
const DAY_NAMES = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as const

function getDayName(dateStr: string): string {
    if (!dateStr) return ''
    const [y, m, d] = dateStr.split('-').map(Number)
    return DAY_NAMES[new Date(y, m - 1, d).getDay()]
}

function makeSlotsFromConfig(config: { open: boolean; start: string; end: string }, slotMins: number): string[] {
    if (!config?.open) return []
    const [startH, startM] = config.start.split(':').map(Number)
    const [endH, endM]     = config.end.split(':').map(Number)
    const startTotal = startH * 60 + startM
    const endTotal   = endH * 60 + endM
    const slots: string[] = []
    for (let t = startTotal; t + slotMins <= endTotal; t += slotMins) {
        const h = Math.floor(t / 60)
        const m = t % 60
        slots.push(`${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`)
    }
    return slots
}

// Min date = tomorrow
const tomorrow = new Date()
tomorrow.setDate(tomorrow.getDate() + 1)
const minDate = tomorrow.toISOString().split('T')[0]

/** True when the selected date is a closed day or a blocked date */
const isDateClosed = computed(() => {
    if (!form.appointment_date) return false
    if (props.closedDates?.includes(form.appointment_date)) return true
    const dayName  = getDayName(form.appointment_date)
    return !(props.bookingHours?.[dayName]?.open ?? true)
})

/** Time slots for the selected date based on admin opening hours */
const timeSlots = computed(() => {
    if (!form.appointment_date) return []
    const dayName  = getDayName(form.appointment_date)
    const config   = props.bookingHours?.[dayName]
    return makeSlotsFromConfig(config ?? { open: false, start: '09:00', end: '17:00' }, props.slotDuration || 30)
})

/** Human-readable opening hours summary built from the booking hours settings */
const openingHintLines = computed(() => {
    const bh = props.bookingHours
    if (!bh) return []
    const groups: string[] = []
    const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as const
    const shortDay: Record<string, string> = {
        monday: 'Mon', tuesday: 'Tue', wednesday: 'Wed', thursday: 'Thu',
        friday: 'Fri', saturday: 'Sat', sunday: 'Sun',
    }
    // Group consecutive open days with same hours
    let start = 0
    while (start < days.length) {
        const d = bh[days[start]]
        if (!d?.open) { start++; continue }
        let end = start
        while (end + 1 < days.length && bh[days[end + 1]]?.open
               && bh[days[end + 1]].start === d.start && bh[days[end + 1]].end === d.end) {
            end++
        }
        const label = start === end ? shortDay[days[start]] : `${shortDay[days[start]]}–${shortDay[days[end]]}`
        groups.push(`${label} ${d.start}–${d.end}`)
        start = end + 1
    }
    const closedDays = days.filter(d => !bh[d]?.open).map(d => shortDay[d])
    if (closedDays.length) groups.push(`${closedDays.join(', ')} closed`)
    return groups
})

// Clear time if date changes and the current time is no longer in the available slots
watch(() => form.appointment_date, () => {
    if (form.appointment_time && !timeSlots.value.includes(form.appointment_time)) {
        form.appointment_time = ''
    }
})

const form = useForm({
    vehicle_id:       props.vehicles.length === 1 ? props.vehicles[0].id : '',
    service_id:       '',
    service_type:     '',
    appointment_date: '',
    appointment_time: '',
    notes:            '',
})

const selectedService = computed(() => props.services.find(s => s.id === Number(form.service_id)))

// Auto-fill service_type from selected service
watch(() => form.service_id, val => {
    if (val) {
        const svc = props.services.find(s => s.id === Number(val))
        form.service_type = svc?.name ?? ''
    }
})

// Group services by category
const groupedServices = computed(() => {
    const groups: Record<string, typeof props.services> = {}
    for (const s of props.services) {
        const cat = s.category || 'Other'
        if (!groups[cat]) groups[cat] = []
        groups[cat].push(s)
    }
    return groups
})

function submit() {
    form.post('/customer/appointments/book')
}
</script>

<template>
    <Head title="Book an Appointment" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link href="/customer/appointments" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Book an Appointment</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Vehicle -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-800">Select Vehicle</h2>
                        <button type="button" @click="showAddVehicle = !showAddVehicle"
                            class="text-xs text-electric-600 hover:text-electric-700 font-medium">
                            + Add vehicle
                        </button>
                    </div>

                    <!-- Inline add-vehicle form -->
                    <div v-if="showAddVehicle" class="mb-4 rounded-lg border border-electric-200 bg-electric-50 p-4 space-y-3">
                        <p class="text-sm font-medium text-gray-700">Add a vehicle to your account</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Registration *</label>
                                <input v-model="addVehicleForm.registration_number" type="text" required placeholder="e.g. AB12 CDE"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-electric-600"
                                    :class="{ 'border-red-400': addVehicleForm.errors.registration_number }" />
                                <p v-if="addVehicleForm.errors.registration_number" class="mt-1 text-xs text-red-600">{{ addVehicleForm.errors.registration_number }}</p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Make *</label>
                                <input v-model="addVehicleForm.make" type="text" required placeholder="e.g. Ford"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                    :class="{ 'border-red-400': addVehicleForm.errors.make }" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Model *</label>
                                <input v-model="addVehicleForm.model" type="text" required placeholder="e.g. Focus"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                    :class="{ 'border-red-400': addVehicleForm.errors.model }" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Year</label>
                                <input v-model="addVehicleForm.year" type="number" placeholder="e.g. 2019"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600" />
                            </div>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="showAddVehicle = false; addVehicleForm.reset()"
                                class="flex-1 rounded-lg border border-gray-300 py-2 text-xs font-medium text-gray-600 hover:bg-white">
                                Cancel
                            </button>
                            <button type="button" @click="submitAddVehicle" :disabled="addVehicleForm.processing"
                                class="flex-1 rounded-lg bg-electric-600 py-2 text-xs font-semibold text-white hover:bg-electric-700 disabled:opacity-50">
                                {{ addVehicleForm.processing ? 'Saving…' : 'Save Vehicle' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="!vehicles.length && !showAddVehicle" class="text-sm text-gray-400 italic">
                        No vehicles on file. Add a vehicle above or <a href="/customer/vehicles" class="text-electric-600 hover:underline">manage vehicles</a>.
                    </div>
                    <div v-else-if="vehicles.length" class="space-y-2">
                        <label v-for="v in vehicles" :key="v.id"
                            :class="['flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors', Number(form.vehicle_id) === v.id ? 'border-electric-600 bg-electric-50' : 'border-gray-200 hover:bg-gray-50']">
                            <input type="radio" v-model="form.vehicle_id" :value="v.id" class="text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ v.registration_number }}</p>
                                <p class="text-xs text-gray-500">{{ v.make }} {{ v.model }} {{ v.year }}</p>
                            </div>
                            <div v-if="v.mot_due_date" class="ml-auto text-right">
                                <p class="text-xs text-gray-400">MOT</p>
                                <p :class="['text-xs font-medium', new Date(v.mot_due_date) < new Date() ? 'text-red-600' : new Date(v.mot_due_date) < new Date(Date.now() + 30*24*60*60*1000) ? 'text-orange-600' : 'text-green-600']">
                                    {{ new Date(v.mot_due_date).toLocaleDateString('en-GB') }}
                                </p>
                            </div>
                        </label>
                        <p v-if="form.errors.vehicle_id" class="text-xs text-red-600">{{ form.errors.vehicle_id }}</p>
                    </div>
                </div>

                <!-- Service -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">What do you need?</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span></label>
                            <select v-model="form.service_id"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600">
                                <option value="">Select a service…</option>
                                <optgroup v-for="(svcs, cat) in groupedServices" :key="cat" :label="cat">
                                    <option v-for="s in svcs" :key="s.id" :value="s.id">
                                        {{ s.name }}{{ s.price ? ` — £${parseFloat(s.price).toFixed(2)}` : '' }}
                                    </option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Duration hint -->
                        <div v-if="selectedService?.estimated_duration_minutes" class="text-xs text-gray-400">
                            ⏱ Estimated duration: {{ selectedService.estimated_duration_minutes }} minutes
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Description / additional details <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.service_type" type="text" required
                                placeholder="e.g. Full service, oil leak repair, tyre change…"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                :class="{ 'border-red-400': form.errors.service_type }" />
                            <p v-if="form.errors.service_type" class="mt-1 text-xs text-red-600">{{ form.errors.service_type }}</p>
                        </div>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Choose a Date & Time</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred date <span class="text-red-500">*</span></label>
                            <input v-model="form.appointment_date" type="date" required :min="minDate"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                :class="{ 'border-red-400': form.errors.appointment_date || isDateClosed }" />
                            <p v-if="isDateClosed && form.appointment_date" class="mt-1 text-xs text-amber-600">
                                We are not available on this day. Please choose another date.
                            </p>
                            <p v-if="form.errors.appointment_date" class="mt-1 text-xs text-red-600">{{ form.errors.appointment_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred time <span class="text-red-500">*</span></label>
                            <div v-if="!form.appointment_date" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-400 italic">
                                Select a date first
                            </div>
                            <div v-else-if="isDateClosed" class="w-full rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                                Closed — please choose another day.
                            </div>
                            <select v-else v-model="form.appointment_time" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                :class="{ 'border-red-400': form.errors.appointment_time }">
                                <option value="">Select time…</option>
                                <option v-for="t in timeSlots" :key="t" :value="t">{{ t }}</option>
                            </select>
                            <p v-if="form.errors.appointment_time" class="mt-1 text-xs text-red-600">{{ form.errors.appointment_time }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">
                        We'll confirm your exact appointment time by phone or email within 24 hours.
                        <template v-if="openingHintLines.length">
                            {{ openingHintLines.join(' &bull; ') }}
                        </template>
                    </p>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional notes (optional)</label>
                    <textarea v-model="form.notes" rows="3" maxlength="500"
                        placeholder="Any symptoms, warning lights, or specific concerns…"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 resize-none" />
                    <p class="text-xs text-gray-400 mt-1 text-right">{{ (form.notes || '').length }}/500</p>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pb-8 sm:pb-4">
                    <Link href="/customer/appointments"
                        class="flex-1 rounded-lg border border-gray-300 py-3 text-sm font-medium text-gray-700 text-center hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing || !vehicles.length"
                        class="flex-1 rounded-lg bg-electric-600 py-3 text-sm font-semibold text-white hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Submitting…' : 'Request Appointment' }}
                    </button>
                </div>
            </form>
        </div>
    </CustomerPortalLayout>
</template>
