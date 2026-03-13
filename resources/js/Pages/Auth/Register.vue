<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('/register'), { onFinish: () => form.reset('password', 'password_confirmation') })
}
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-navy-950 via-navy-800 to-navy-950 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-electric-600 mb-4">
                    <span class="text-white font-bold text-2xl">DA</span>
                </div>
                <h1 class="text-3xl font-bold text-white">Doyen Auto Services</h1>
                <p class="mt-2 text-electric-200">Create your account</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5" />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input v-model="form.password" type="password" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5" />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input v-model="form.password_confirmation" type="password" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600 px-4 py-2.5" />
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full bg-electric-600 hover:bg-electric-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors disabled:opacity-50">
                        {{ form.processing ? 'Creating account...' : 'Create Account' }}
                    </button>
                </form>
            </div>
            <p class="mt-6 text-center text-sm text-electric-200">Already have an account? <Link :href="route('/login')" class="font-medium text-white hover:underline">Sign in</Link></p>
        </div>
    </div>
</template>
