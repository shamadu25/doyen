<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const props = defineProps<{
    customer: any
    vehicles: any[]
    services: { id: number; name: string; category: string; estimated_duration_minutes: number | null; price: string | null }[]
}>()

// Generate 30-min slots between startHour and endHour (exclusive of endHour)
function makeSlots(startHour: number, endHour: number) {
    const slots: string[] = []
    for (let h = startHour; h < endHour; h++) {
        slots.push(`${String(h).padStart(2, '0')}:00`)
        slots.push(`${String(h).padStart(2, '0')}:30`)
    }
    return slots
}

const WEEKDAY_SLOTS = makeSlots(9, 18)  // Mon–Fri 09:00–17:30
const SATURDAY_SLOTS = makeSlots(10, 15) // Sat 10:00–14:30

// Min date = tomorrow
const tomorrow = new Date()
tomorrow.setDate(tomorrow.getDate() + 1)
const minDate = tomorrow.toISOString().split('T')[0]

// Returns day-of-week for a YYYY-MM-DD string (0=Sun, 6=Sat)
function dayOfWeek(dateStr: string) {
    if (!dateStr) return -1
    const [y, m, d] = dateStr.split('-').map(Number)
    return new Date(y, m - 1, d).getDay()
}

const isSunday = computed(() => dayOfWeek(form.appointment_date) === 0)

const timeSlots = computed(() => {
    const dow = dayOfWeek(form.appointment_date)
    if (dow === 0) return []          // Sunday – closed
    if (dow === 6) return SATURDAY_SLOTS
    return WEEKDAY_SLOTS
})

// Clear time if date changes and current time is no longer available
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
                    <h2 class="font-semibold text-gray-800 mb-4">Select Vehicle</h2>
                    <div v-if="!vehicles.length" class="text-sm text-gray-400 italic">
                        No vehicles on file. <a href="/customer/vehicles" class="text-electric-600 hover:underline">Add a vehicle</a> or contact us.
                    </div>
                    <div v-else class="space-y-2">
                        <label v-for="v in vehicles" :key="v.id"
                            :class="['flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors', Number(form.vehicle_id) === v.id ? 'border-electric-600 bg-electric-50' : 'border-gray-200 hover:bg-gray-50']">
                            <input type="radio" v-model="form.vehicle_id" :value="v.id" class="text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ v.registration_number }}</p>
                                <p class="text-xs text-gray-500">{{ v.make }} {{ v.model }} {{ v.year }}</p>
                            </div>
                            <div v-if="v.mot_expiry" class="ml-auto text-right">
                                <p class="text-xs text-gray-400">MOT</p>
                                <p :class="['text-xs font-medium', new Date(v.mot_expiry) < new Date() ? 'text-red-600' : new Date(v.mot_expiry) < new Date(Date.now() + 30*24*60*60*1000) ? 'text-orange-600' : 'text-green-600']">
                                    {{ new Date(v.mot_expiry).toLocaleDateString('en-GB') }}
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
                                :class="{ 'border-red-400': form.errors.appointment_date }" />
                            <p v-if="form.errors.appointment_date" class="mt-1 text-xs text-red-600">{{ form.errors.appointment_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred time <span class="text-red-500">*</span></label>
                            <div v-if="isSunday" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
                                We are closed on Sundays. Please choose another day.
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
                        Mon–Fri 09:00–18:00 &bull; Sat 10:00–15:00 &bull; Sun closed.
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
