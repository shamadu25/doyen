<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { inject, ref, reactive, computed } from 'vue'
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
        invoice_due_days: string
        invoice_bank_name: string
        invoice_sort_code: string
        invoice_account_number: string
        invoice_account_name: string
        invoice_company_number: string
        invoice_footer_note: string
        invoice_late_payment: string
        invoice_header_name: string
        invoice_header_address: string
        invoice_header_city: string
        invoice_header_postcode: string
        invoice_header_phone: string
        invoice_header_email: string
        invoice_header_website: string
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
    invoice_terms: props.settings.invoice_terms || 'Payment due within 30 days of invoice date.',
    invoice_due_days: props.settings.invoice_due_days || '30',
    invoice_bank_name: props.settings.invoice_bank_name || '',
    invoice_sort_code: props.settings.invoice_sort_code || '',
    invoice_account_number: props.settings.invoice_account_number || '',
    invoice_account_name: props.settings.invoice_account_name || '',
    invoice_company_number: props.settings.invoice_company_number || '',
    invoice_footer_note: props.settings.invoice_footer_note || 'Thank you for your custom.',
    invoice_late_payment: props.settings.invoice_late_payment || '',
    invoice_header_name: props.settings.invoice_header_name || '',
    invoice_header_address: props.settings.invoice_header_address || '',
    invoice_header_city: props.settings.invoice_header_city || '',
    invoice_header_postcode: props.settings.invoice_header_postcode || '',
    invoice_header_phone: props.settings.invoice_header_phone || '',
    invoice_header_email: props.settings.invoice_header_email || '',
    invoice_header_website: props.settings.invoice_header_website || '',
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

const flash = computed(() => (usePage().props.flash as any) ?? {})
</script>

<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                <p class="mt-1 text-sm text-gray-500">Configure your garage system</p>
            </div>

            <!-- Flash messages -->
            <div v-if="flash.success" class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                {{ flash.error }}
            </div>
            <div v-if="Object.keys(form.errors).length" class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm space-y-1">
                <p v-for="(msg, field) in form.errors" :key="String(field)">{{ msg }}</p>
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
                            <input v-model="form.garage_website" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="https://example.com" />
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

                <!-- Invoice & VAT Settings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Invoice &amp; VAT Receipt Settings</h2>
                    <p class="text-sm text-gray-500 mb-5">These details appear on every invoice and VAT receipt sent to customers.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Invoice Header Business Details -->
                        <div class="md:col-span-2">
                            <div class="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3 mb-4">
                                <p class="text-sm font-semibold text-blue-800 mb-1">Invoice Header Details</p>
                                <p class="text-xs text-blue-600">These override the general garage settings on the printed invoice / VAT receipt header. Leave blank to use the Garage Details section above.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Business / Trading Name</label>
                                    <input v-model="form.invoice_header_name" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Doyen Auto Services Ltd" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line</label>
                                    <input v-model="form.invoice_header_address" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 12 High Street, Industrial Estate" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City / Town</label>
                                    <input v-model="form.invoice_header_city" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Glasgow" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                                    <input v-model="form.invoice_header_postcode" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. G1 1AA" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input v-model="form.invoice_header_phone" type="tel" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 0141 000 0000" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input v-model="form.invoice_header_email" type="email" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. accounts@yourgarage.co.uk" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                    <input v-model="form.invoice_header_website" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. https://www.yourgarage.co.uk" />
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t pt-5">
                            <p class="text-sm font-semibold text-gray-700 mb-4">VAT &amp; Compliance</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Companies House Number</label>
                                    <input v-model="form.invoice_company_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 12345678" />
                                    <p class="text-xs text-gray-400 mt-1">Required for limited companies</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Due (days)</label>
                                    <input v-model="form.invoice_due_days" type="number" min="0" max="365" class="w-full rounded-lg border-gray-300 text-sm" placeholder="30" />
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-sm font-semibold text-gray-700 mb-3 mt-1 border-t pt-4">Bank / Payment Details</p>
                            <p class="text-xs text-gray-500 mb-3">Printed on invoices so customers can pay by BACS transfer.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                    <input v-model="form.invoice_bank_name" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Barclays" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                    <input v-model="form.invoice_account_name" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Doyen Auto Services Ltd" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Code</label>
                                    <input v-model="form.invoice_sort_code" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 20-00-00" maxlength="8" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <input v-model="form.invoice_account_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 12345678" maxlength="8" />
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Footer Note</label>
                            <input v-model="form.invoice_footer_note" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Thank you for your custom." />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Late Payment Notice</label>
                            <textarea v-model="form.invoice_late_payment" rows="2" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Interest may be charged on overdue invoices…"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Leave blank to hide from invoice. Statutory rate: 8% above Bank of England base rate.</p>
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
