<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    token: string
    email: string
}>()

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('/reset-password'))
}
</script>

<template>
    <Head title="Reset Password | Doyen Auto Services">
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-navy-950 via-navy-800 to-navy-950 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-electric-600 mb-4">
                    <span class="text-white font-bold text-2xl">DA</span>
                </div>
                <h1 class="text-3xl font-bold text-white">Doyen Auto Services</h1>
                <p class="mt-2 text-electric-200">Auto Electrical and Diagnostic Specialist</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Reset your password</h2>

                <!-- Error Messages -->
                <div v-if="form.errors.email" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">{{ form.errors.email }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <input
                            id="email" 
                            type="email" 
                            v-model="form.email" 
                            required 
                            readonly
                            class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm px-4 py-2.5"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input
                            id="password" 
                            type="password" 
                            v-model="form.password" 
                            required
                            autofocus
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input
                            id="password_confirmation" 
                            type="password" 
                            v-model="form.password_confirmation" 
                            required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5"
                            placeholder="••••••••"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-electric-600 hover:bg-electric-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Resetting...' : 'Reset Password' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
