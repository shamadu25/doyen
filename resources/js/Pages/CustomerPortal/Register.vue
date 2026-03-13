<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
})
const errors = ref<Record<string, string>>({})
const submitting = ref(false)
const showPassword = ref(false)
const showConfirm = ref(false)

// Password strength
const strength = computed(() => {
    const p = form.value.password
    if (!p) return 0
    let score = 0
    if (p.length >= 8) score++
    if (p.length >= 12) score++
    if (/[A-Z]/.test(p)) score++
    if (/[0-9]/.test(p)) score++
    if (/[^A-Za-z0-9]/.test(p)) score++
    return score
})
const strengthLabel = computed(() => ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'][strength.value] ?? '')
const strengthColor = computed(() => ['', 'bg-red-500', 'bg-orange-500', 'bg-yellow-400', 'bg-green-500', 'bg-green-600'][strength.value] ?? '')
const strengthWidth = computed(() => `${(strength.value / 5) * 100}%`)

const passwordsMatch = computed(() =>
    form.value.password_confirmation.length > 0 && form.value.password === form.value.password_confirmation
)
const passwordsMismatch = computed(() =>
    form.value.password_confirmation.length > 0 && form.value.password !== form.value.password_confirmation
)

function submit() {
    submitting.value = true
    errors.value = {}
    router.post('/customer/register', form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="Create Account – Doyen Auto Services" />
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
                    <h2 class="text-xl font-semibold text-gray-900">Create Account</h2>
                    <p class="text-sm text-gray-500 mt-1">Access your bookings, invoices and service history online.</p>
                </div>

                <!-- Field-level errors -->
                <div v-if="Object.keys(errors).length" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm space-y-1">
                    <p v-for="(msg, field) in errors" :key="field">{{ msg }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
                            <input v-model="form.first_name" type="text" autocomplete="given-name" required
                                :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600', errors.first_name ? 'border-red-400' : 'border-gray-300']" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last name <span class="text-red-500">*</span></label>
                            <input v-model="form.last_name" type="text" autocomplete="family-name" required
                                :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600', errors.last_name ? 'border-red-400' : 'border-gray-300']" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email address <span class="text-red-500">*</span></label>
                        <input v-model="form.email" type="email" autocomplete="email" required
                            :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600', errors.email ? 'border-red-400' : 'border-gray-300']" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone number</label>
                        <input v-model="form.phone" type="tel" autocomplete="tel"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600"
                            placeholder="Optional" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                                :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600 pr-10', errors.password ? 'border-red-400' : 'border-gray-300']"
                                placeholder="At least 8 characters" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Strength meter -->
                        <div v-if="form.password" class="mt-2 space-y-1">
                            <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div :class="['h-full rounded-full transition-all duration-300', strengthColor]" :style="{ width: strengthWidth }" />
                            </div>
                            <p class="text-xs" :class="strength >= 4 ? 'text-green-600' : strength >= 3 ? 'text-yellow-600' : 'text-red-500'">
                                {{ strengthLabel }}
                                <span v-if="form.password.length < 8" class="text-gray-400"> — minimum 8 characters</span>
                            </p>
                        </div>
                        <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input v-model="form.password_confirmation" :type="showConfirm ? 'text' : 'password'" autocomplete="new-password" required
                                :class="['w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600 pr-10', passwordsMismatch ? 'border-red-400' : passwordsMatch ? 'border-green-400' : 'border-gray-300']" />
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg v-if="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="passwordsMatch" class="mt-1 text-xs text-green-600">✓ Passwords match</p>
                        <p v-else-if="passwordsMismatch" class="mt-1 text-xs text-red-600">Passwords do not match</p>
                    </div>

                    <button type="submit" :disabled="submitting || passwordsMismatch || form.password.length < 8"
                        class="w-full py-2.5 bg-electric-600 text-white rounded-lg font-medium hover:bg-electric-700 transition disabled:opacity-50">
                        {{ submitting ? 'Creating account...' : 'Create Account' }}
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500">
                    Already have an account?
                    <a href="/customer/login" class="text-electric-600 hover:underline font-medium">Sign in</a>
                </p>
            </div>

            <p class="text-center text-xs text-electric-400 mt-6">
                Questions? Call <a href="tel:+441414820726" class="underline">+44 141 482 0726</a>
            </p>
        </div>
    </div>
</template>
