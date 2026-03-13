<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{ token: string; email: string }>()

const form = ref({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})
const errors = ref<Record<string, string>>({})
const submitting = ref(false)

function submit() {
    submitting.value = true
    errors.value = {}
    router.post('/customer/set-password', form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="Set Your Password – Doyen Auto Services" />
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
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Set Your Password</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Setting password for <span class="font-medium text-gray-700">{{ email }}</span>
                    </p>
                </div>

                <div v-if="errors.password" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {{ errors.password }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New password <span class="text-red-500">*</span></label>
                        <input v-model="form.password" type="password" autocomplete="new-password" required
                            :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600', errors.password ? 'border-red-400' : 'border-gray-300']"
                            placeholder="At least 8 characters" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password <span class="text-red-500">*</span></label>
                        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required
                            :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600', errors.password ? 'border-red-400' : 'border-gray-300']" />
                    </div>

                    <button type="submit" :disabled="submitting"
                        class="w-full py-2.5 bg-electric-600 text-white rounded-lg font-medium hover:bg-electric-700 transition disabled:opacity-50">
                        {{ submitting ? 'Saving...' : 'Set Password & Sign In' }}
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500">
                    Already set up?
                    <a href="/customer/login" class="text-electric-600 hover:underline font-medium">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</template>
