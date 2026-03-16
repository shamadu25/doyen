<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const garagePhone   = computed(() => ((usePage().props as any).garageSettings?.phone)   || '+44 141 482 0726')
const garageAddress = computed(() => ((usePage().props as any).garageSettings?.address) || '59 Southcroft Road')
const garageCity    = computed(() => ((usePage().props as any).garageSettings?.city)    || 'Rutherglen, Glasgow')

const props = defineProps<{
    status: 'accepted' | 'declined'
    message: string
    booking: {
        reference_number?: string
        scheduled_date?: string
        scheduled_time?: string
    }
}>()
</script>

<template>
    <Head title="Booking Update — Doyen Auto Services" />
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div v-if="status === 'accepted'" class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div v-else class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ status === 'accepted' ? 'Booking Confirmed!' : 'Reschedule Declined' }}
                </h1>
                <p class="text-gray-600">{{ message }}</p>
            </div>

            <div v-if="status === 'accepted' && booking.scheduled_date" class="bg-electric-50 rounded-lg p-4 mb-6 text-left">
                <p class="text-sm font-medium text-electric-700">Your appointment details:</p>
                <p class="text-sm text-electric-700 mt-1">📅 {{ booking.scheduled_date }}</p>
                <p class="text-sm text-electric-700">🕐 {{ booking.scheduled_time }}</p>
                <p class="text-sm text-electric-700">Ref: {{ booking.reference_number }}</p>
            </div>

            <div class="border-t pt-6">
                <p class="text-sm text-gray-500 mb-3">Questions? Contact us:</p>
                <p class="font-semibold text-gray-700">{{ garagePhone }}</p>
                <p class="text-sm text-gray-500">Doyen Auto Services, {{ garageCity }}</p>
            </div>
        </div>
    </div>
</template>
