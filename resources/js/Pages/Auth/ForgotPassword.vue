<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('/forgot-password'))
}
</script>

<template>
    <Head title="Forgot Password | Doyen Auto Services">
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
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Forgot your password?</h2>
                <p class="text-sm text-gray-600 mb-6">
                    No problem. Just let us know your email address and we'll email you a password reset link that will allow you to choose a new one.
                </p>

                <!-- Success Message -->
                <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">{{ $page.props.flash.success }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <input
                            id="email" 
                            type="email" 
                            v-model="form.email" 
                            required 
                            autofocus
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5"
                            placeholder="you@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-electric-600 hover:bg-electric-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Sending...' : 'Email Password Reset Link' }}
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-electric-200">
                Remember your password? <Link :href="route('/login')" class="font-medium text-white hover:underline">Sign in</Link>
            </p>
        </div>
    </div>
</template>
