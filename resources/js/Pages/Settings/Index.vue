<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

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
}>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    garage_name: props.settings.garage_name || 'Doyen Auto Services',
    garage_address: props.settings.garage_address || '59 Southcroft Rd, Rutherglen, Glasgow, G73 1UG',
    garage_city: props.settings.garage_city || 'Glasgow',
    garage_postcode: props.settings.garage_postcode || 'G73 1UG',
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
        </div>
    </AuthenticatedLayout>
</template>
