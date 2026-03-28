<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { inject, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    smsConfig: {
        enabled: boolean
        twilio_sid_configured: boolean
        twilio_token_configured: boolean
        twilio_from: string
        twilio_sid_preview: string
    }
}>()

const route = inject<(path: string) => string>('route', (p) => p)
const flash = computed(() => (usePage().props.flash as any) ?? {})

const form = useForm({
    phone: '',
    message: 'Test SMS from Doyen Auto Services. If you received this, Twilio SMS is working correctly.',
})

function submit() {
    form.post(route('/settings/sms-test'))
}
</script>

<template>
    <Head title="SMS Test" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">SMS Test</h1>
                    <p class="mt-1 text-sm text-gray-500">Send a test SMS using Twilio credentials from `.env`.</p>
                </div>
                <Link :href="route('/settings')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    Back to Settings
                </Link>
            </div>

            <div v-if="flash.success" class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                {{ flash.error }}
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Twilio Configuration Status</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
                        <span class="text-gray-600">SMS_ENABLED</span>
                        <span :class="props.smsConfig.enabled ? 'text-green-700 font-semibold' : 'text-red-700 font-semibold'">
                            {{ props.smsConfig.enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
                        <span class="text-gray-600">TWILIO_SID</span>
                        <span :class="props.smsConfig.twilio_sid_configured ? 'text-green-700 font-semibold' : 'text-red-700 font-semibold'">
                            {{ props.smsConfig.twilio_sid_configured ? props.smsConfig.twilio_sid_preview : 'Missing' }}
                        </span>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
                        <span class="text-gray-600">TWILIO_TOKEN</span>
                        <span :class="props.smsConfig.twilio_token_configured ? 'text-green-700 font-semibold' : 'text-red-700 font-semibold'">
                            {{ props.smsConfig.twilio_token_configured ? 'Configured' : 'Missing' }}
                        </span>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 flex items-center justify-between">
                        <span class="text-gray-600">TWILIO_FROM</span>
                        <span class="text-gray-800 font-medium">
                            {{ props.smsConfig.twilio_from || 'Not set' }}
                        </span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Send Test SMS</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Phone Number</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        placeholder="+447900123456"
                        class="w-full rounded-lg border-gray-300 text-sm"
                    />
                    <p class="mt-1 text-xs text-gray-500">Use full international format, e.g. `+447...` for UK mobile.</p>
                    <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea
                        v-model="form.message"
                        rows="4"
                        maxlength="480"
                        class="w-full rounded-lg border-gray-300 text-sm"
                    />
                    <p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium disabled:opacity-50"
                    >
                        {{ form.processing ? 'Sending...' : 'Send Test SMS' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
