<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'

const route = inject<(p: string) => string>('route', p => p)
const form = ref({ email: '', password: '' })
const errors = ref<any>({})
const submitting = ref(false)

function submit() {
    submitting.value = true
    errors.value = {}
    router.post(route('/customer/login'), form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="Customer Portal Login – Doyen Auto Services" />
    <div class="min-h-screen bg-gradient-to-br from-navy-950 to-navy-800 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-electric-600 flex items-center justify-center mx-auto mb-3">
                    <span class="text-white font-bold text-xl">DA</span>
                </div>
                <h1 class="text-2xl font-bold text-white">Doyen Auto Services</h1>
                <p class="text-electric-400 mt-1">Customer Portal</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 space-y-5">
                <h2 class="text-xl font-semibold text-gray-900">Sign In</h2>

                <div v-if="errors.email" class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ errors.email }}</div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <input v-model="form.email" type="email" autocomplete="email" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input v-model="form.password" type="password" autocomplete="current-password" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" />
                    </div>
                    <button type="submit" :disabled="submitting" class="w-full py-2.5 bg-electric-600 text-white rounded-lg font-medium hover:bg-electric-700 transition disabled:opacity-50">
                        {{ submitting ? 'Signing in...' : 'Sign In' }}
                    </button>
                </form>

                <p class="text-center text-xs text-gray-500">
                    Need help? Call us on <a href="tel:+441414820726" class="text-electric-600 hover:underline">+44 141 482 0726</a>
                </p>

                <div class="border-t pt-4 space-y-2 text-center">
                    <p class="text-sm text-gray-500">
                        New customer?
                        <a href="/customer/register" class="text-electric-600 hover:underline font-medium">Create an account</a>
                    </p>
                    <p class="text-xs text-gray-400">
                        Booked before but haven't set a password?
                        <a href="/customer/register" class="text-electric-600 hover:underline">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
