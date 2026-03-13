<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const props = defineProps<{ customer: any }>()

const flash = computed(() => (usePage().props.flash as any) ?? {})

const profileForm = useForm({
    first_name: props.customer.first_name ?? '',
    last_name:  props.customer.last_name  ?? '',
    phone:      props.customer.phone      ?? '',
    mobile:     props.customer.mobile     ?? '',
    address:    props.customer.address    ?? '',
    city:       props.customer.city       ?? '',
    postcode:   props.customer.postcode   ?? '',
})

const passwordForm = useForm({
    current_password: '',
    password:         '',
    password_confirmation: '',
})

const notifForm = useForm({
    email_notifications:   props.customer.email_notifications   ?? true,
    sms_notifications:     props.customer.sms_notifications     ?? false,
    appointment_reminders: props.customer.appointment_reminders ?? true,
    mot_reminders:         props.customer.mot_reminders         ?? true,
    marketing_emails:      props.customer.marketing_emails      ?? false,
})

const activeTab = ref<'details' | 'password' | 'notifications'>('details')

function saveProfile() {
    profileForm.put('/customer/profile')
}

function savePassword() {
    passwordForm.put('/customer/profile/password', {
        onSuccess: () => passwordForm.reset(),
    })
}

function saveNotifications() {
    notifForm.put('/customer/profile/notifications')
}
</script>

<template>
    <Head title="My Profile" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-5">
            <h1 class="text-xl font-bold text-gray-900">My Profile</h1>

            <!-- Flash -->
            <div v-if="flash.success" class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ flash.success }}
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 flex">
                    <button v-for="tab in (['details', 'password', 'notifications'] as const)" :key="tab"
                        @click="activeTab = tab"
                        :class="['px-5 py-3 text-sm font-medium capitalize border-b-2 transition-colors', activeTab === tab ? 'border-electric-600 text-electric-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                        {{ tab }}
                    </button>
                </div>

                <!-- Details -->
                <form v-if="activeTab === 'details'" @submit.prevent="saveProfile" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                            <input v-model="profileForm.first_name" type="text" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600" />
                            <p v-if="profileForm.errors.first_name" class="mt-1 text-xs text-red-600">{{ profileForm.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                            <input v-model="profileForm.last_name" type="text" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="profileForm.phone" type="tel"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                            <input v-model="profileForm.mobile" type="tel"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input v-model="profileForm.address" type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City / Town</label>
                            <input v-model="profileForm.city" type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                            <input v-model="profileForm.postcode" type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600 focus:border-electric-600 uppercase" />
                        </div>
                    </div>
                    <div class="pt-2">
                        <p class="text-xs text-gray-400 mb-3">Email address: <span class="font-medium text-gray-600">{{ customer.email }}</span> — contact us to change your email.</p>
                        <button type="submit" :disabled="profileForm.processing"
                            class="rounded-lg bg-electric-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-electric-700 disabled:opacity-50">
                            {{ profileForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </form>

                <!-- Password -->
                <form v-if="activeTab === 'password'" @submit.prevent="savePassword" class="p-6 space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                        <input v-model="passwordForm.current_password" type="password" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                            :class="{ 'border-red-400': passwordForm.errors.current_password }" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input v-model="passwordForm.password" type="password" required minlength="8"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                            :class="{ 'border-red-400': passwordForm.errors.password }" />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                        <input v-model="passwordForm.password_confirmation" type="password" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600" />
                    </div>
                    <button type="submit" :disabled="passwordForm.processing"
                        class="rounded-lg bg-electric-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-electric-700 disabled:opacity-50">
                        {{ passwordForm.processing ? 'Changing…' : 'Change Password' }}
                    </button>
                </form>

                <!-- Notifications -->
                <form v-if="activeTab === 'notifications'" @submit.prevent="saveNotifications" class="p-6 space-y-4">
                    <p class="text-sm text-gray-500">Choose how you'd like to hear from us.</p>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="notifForm.email_notifications" class="h-4 w-4 rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="text-sm font-medium text-gray-800">Email notifications</p>
                                <p class="text-xs text-gray-400">Booking confirmations, invoices, updates</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="notifForm.sms_notifications" class="h-4 w-4 rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="text-sm font-medium text-gray-800">SMS notifications</p>
                                <p class="text-xs text-gray-400">Text messages for important updates</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="notifForm.appointment_reminders" class="h-4 w-4 rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="text-sm font-medium text-gray-800">Appointment reminders</p>
                                <p class="text-xs text-gray-400">48-hour reminder before your booking</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="notifForm.mot_reminders" class="h-4 w-4 rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="text-sm font-medium text-gray-800">MOT reminders</p>
                                <p class="text-xs text-gray-400">Reminder when your MOT is due (UK law requires valid MOT)</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="notifForm.marketing_emails" class="h-4 w-4 rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <div>
                                <p class="text-sm font-medium text-gray-800">Offers & promotions</p>
                                <p class="text-xs text-gray-400">Special deals and seasonal promotions</p>
                            </div>
                        </label>
                    </div>
                    <button type="submit" :disabled="notifForm.processing"
                        class="rounded-lg bg-electric-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-electric-700 disabled:opacity-50">
                        {{ notifForm.processing ? 'Saving…' : 'Save Preferences' }}
                    </button>
                </form>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
