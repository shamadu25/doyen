<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('/login'), { onFinish: () => form.reset('password') })
}
</script>

<template>
    <Head title="Staff Login | Doyen Auto Services">
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
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Sign in to your account</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <input
                            id="email" type="email" v-model="form.email" required autofocus
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5"
                            placeholder="you@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            id="password" type="password" v-model="form.password" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5"
                            placeholder="••••••••"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <Link :href="route('/forgot-password')" class="text-sm font-medium text-electric-600 hover:text-electric-700">
                            Forgot password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-electric-600 hover:bg-electric-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-electric-200">
                Don't have an account? <Link :href="route('/register')" class="font-medium text-white hover:underline">Register</Link>
            </p>
        </div>
    </div>
</template>
