<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any }>()

const form = useForm({
    subject:  '',
    message:  '',
    category: 'general',
    priority: 'medium',
})

function submit() {
    form.post('/customer/tickets')
}
</script>

<template>
    <Head title="New Support Ticket" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4 max-w-2xl">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Open a Support Ticket</h1>
                <p class="text-sm text-gray-500 mt-0.5">Describe your issue and we'll get back to you as soon as possible.</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                    <input v-model="form.subject" type="text" placeholder="Brief description of your issue"
                        :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.subject ? 'border-red-400' : 'border-gray-200']" />
                    <p v-if="form.errors.subject" class="mt-1 text-xs text-red-600">{{ form.errors.subject }}</p>
                </div>

                <!-- Category & Priority row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select v-model="form.category"
                            :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.category ? 'border-red-400' : 'border-gray-200']">
                            <option value="general">General Enquiry</option>
                            <option value="billing">Billing & Payments</option>
                            <option value="service">Vehicle Service</option>
                            <option value="technical">Technical Issue</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                        <select v-model="form.priority"
                            :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500', form.errors.priority ? 'border-red-400' : 'border-gray-200']">
                            <option value="low">Low – General question</option>
                            <option value="medium">Medium – Needs attention</option>
                            <option value="high">High – Urgent matter</option>
                            <option value="urgent">Urgent – Critical issue</option>
                        </select>
                    </div>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea v-model="form.message" rows="7" placeholder="Please describe your issue in detail. Include any relevant dates, vehicle registration numbers, invoice references, etc."
                        :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y', form.errors.message ? 'border-red-400' : 'border-gray-200']" />
                    <div class="flex items-center justify-between mt-1">
                        <p v-if="form.errors.message" class="text-xs text-red-600">{{ form.errors.message }}</p>
                        <p class="text-xs text-gray-400 ml-auto">{{ form.message.length }}/5000</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-2">
                    <a href="/customer/tickets" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <button @click="submit" :disabled="form.processing"
                        class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ form.processing ? 'Submitting…' : 'Submit Ticket' }}
                    </button>
                </div>
            </div>

            <!-- Info box -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                <div class="flex gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold">What to expect:</p>
                        <ul class="mt-1 space-y-0.5 text-blue-700">
                            <li>• You'll receive an email confirmation immediately</li>
                            <li>• Our team typically responds within 1–2 business days</li>
                            <li>• You can track and reply to your ticket from this portal</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
