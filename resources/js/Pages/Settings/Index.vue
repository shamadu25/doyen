<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import { inject, ref, reactive } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

type DayConfig = { open: boolean; start: string; end: string }
type BookingHours = Record<string, DayConfig>

const props = defineProps<{
    settings: {
        garage_name: string
        garage_address: string
        garage_city: string
        garage_postcode: string
        garage_phone: string
        garage_email: string
        garage_website: string
        vat_number: string
        vat_rate: string
        mot_station_number: string
        default_labour_rate: string
        booking_slot_duration: string
        invoice_prefix: string
        invoice_terms: string
        sms_enabled: string
        email_notifications: string
    }
    bookingHours: BookingHours
    closedDates: string[]
}>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    garage_name: props.settings.garage_name || 'Doyen Auto Services',
    garage_address: props.settings.garage_address || '',
    garage_city: props.settings.garage_city || '',
    garage_postcode: props.settings.garage_postcode || '',
    garage_phone: props.settings.garage_phone || '',
    garage_email: props.settings.garage_email || '',
    garage_website: props.settings.garage_website || '',
    vat_number: props.settings.vat_number || '',
    vat_rate: props.settings.vat_rate || '20',
    mot_station_number: props.settings.mot_station_number || '',
    default_labour_rate: props.settings.default_labour_rate || '65.00',
    booking_slot_duration: props.settings.booking_slot_duration || '60',
    invoice_prefix: props.settings.invoice_prefix || 'INV-',
    invoice_terms: props.settings.invoice_terms || 'Payment due within 30 days.',
    sms_enabled: props.settings.sms_enabled || '0',
    email_notifications: props.settings.email_notifications || '1',
})

function submit() {
    form.post(route('/settings'))
}

// ----- Booking Availability -----
const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as const

const hours = reactive<BookingHours>(
    Object.fromEntries(
        days.map(d => [d, { ...(props.bookingHours[d] ?? { open: false, start: '09:00', end: '17:00' }) }])
    )
)

const availabilityForm = useForm<BookingHours>(hours as any)

function saveAvailability() {
    availabilityForm.post('/settings/booking-availability', { preserveScroll: true })
}

// ----- Closed Dates -----
const closedDates = ref<string[]>([...(props.closedDates ?? [])])
const newClosedDate = ref('')
const closedDateForm = useForm({ date: '' })
const removeDateForm = useForm({ date: '' })

function addClosedDate() {
    if (!newClosedDate.value) return
    closedDateForm.date = newClosedDate.value
    closedDateForm.post('/settings/closed-dates/add', {
        preserveScroll: true,
        onSuccess: () => { newClosedDate.value = '' },
    })
}

function removeClosedDate(date: string) {
    removeDateForm.date = date
    removeDateForm.post('/settings/closed-dates/remove', { preserveScroll: true })
}

function formatDate(d: string) {
    return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                <p class="mt-1 text-sm text-gray-500">Configure your garage system</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Garage Details -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Garage Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Garage Name</label>
                            <input v-model="form.garage_name" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input v-model="form.garage_address" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input v-model="form.garage_city" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                            <input v-model="form.garage_postcode" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.garage_phone" type="tel" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.garage_email" type="email" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                            <input v-model="form.garage_website" type="url" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Financial Settings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Financial</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">VAT Number</label>
                            <input v-model="form.vat_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="GB 123 4567 89" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%)</label>
                            <input v-model="form.vat_rate" type="number" step="0.1" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Labour Rate (£/hr)</label>
                            <input v-model="form.default_labour_rate" type="number" step="0.01" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Prefix</label>
                            <input v-model="form.invoice_prefix" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Terms</label>
                            <textarea v-model="form.invoice_terms" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <!-- MOT & Booking -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">MOT & Booking</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">MOT Station Number</label>
                            <input v-model="form.mot_station_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Booking Slot Duration (mins)</label>
                            <input v-model="form.booking_slot_duration" type="number" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notifications</h2>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3">
                            <input v-model="form.email_notifications" type="checkbox" true-value="1" false-value="0" class="rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <span class="text-sm font-medium text-gray-900">Email Notifications</span>
                                <p class="text-xs text-gray-500">Send booking confirmations and reminders via email</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="form.sms_enabled" type="checkbox" true-value="1" false-value="0" class="rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <span class="text-sm font-medium text-gray-900">SMS Notifications</span>
                                <p class="text-xs text-gray-500">Send SMS reminders (requires Twilio configuration)</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>

            <!-- ───── Booking Availability ───── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Booking Availability</h2>
                <p class="text-sm text-gray-500 mb-5">Set your opening hours for each day of the week. Customers will only be able to book within these hours.</p>

                <div class="space-y-3">
                    <div v-for="day in days" :key="day" class="flex items-center gap-4 p-3 rounded-lg border border-gray-100 bg-gray-50">
                        <!-- Toggle -->
                        <label class="flex items-center gap-2 w-32 shrink-0 cursor-pointer select-none">
                            <input type="checkbox" v-model="hours[day].open" class="rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <span class="text-sm font-semibold capitalize" :class="hours[day].open ? 'text-gray-900' : 'text-gray-400'">{{ day }}</span>
                        </label>

                        <template v-if="hours[day].open">
                            <div class="flex items-center gap-2 flex-1">
                                <label class="text-xs text-gray-500 whitespace-nowrap">Open</label>
                                <input type="time" v-model="hours[day].start"
                                       class="rounded-md border-gray-300 text-sm py-1 px-2 w-28" />
                                <span class="text-gray-400 text-sm">to</span>
                                <input type="time" v-model="hours[day].end"
                                       class="rounded-md border-gray-300 text-sm py-1 px-2 w-28" />
                            </div>
                        </template>
                        <template v-else>
                            <span class="text-sm text-gray-400 italic">Closed — no bookings accepted</span>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button @click="saveAvailability" :disabled="availabilityForm.processing"
                            class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ availabilityForm.processing ? 'Saving…' : 'Save Opening Hours' }}
                    </button>
                </div>
            </div>

            <!-- ───── Closed / Blocked Dates ───── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Blocked Dates</h2>
                <p class="text-sm text-gray-500 mb-5">Block specific dates (bank holidays, annual leave, etc.) so no online bookings can be made on those days.</p>

                <!-- Add date -->
                <div class="flex gap-3 mb-5">
                    <input type="date" v-model="newClosedDate"
                           :min="new Date().toISOString().slice(0,10)"
                           class="rounded-lg border-gray-300 text-sm flex-1" />
                    <button @click="addClosedDate" :disabled="!newClosedDate || closedDateForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-40 whitespace-nowrap">
                        + Block Date
                    </button>
                </div>

                <!-- Blocked dates list -->
                <div v-if="$page.props.closedDates?.length || closedDates.length" class="space-y-2">
                    <div v-for="date in ($page.props.closedDates as string[])" :key="date"
                         class="flex items-center justify-between p-3 bg-red-50 border border-red-100 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🚫</span>
                            <span class="text-sm font-medium text-red-800">{{ formatDate(date) }}</span>
                        </div>
                        <button @click="removeClosedDate(date)" :disabled="removeDateForm.processing"
                                class="text-xs text-red-600 hover:text-red-800 font-medium border border-red-200 rounded px-2 py-1 hover:bg-red-100">
                            Remove
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg">
                    No dates blocked. All working days are open for bookings.
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
