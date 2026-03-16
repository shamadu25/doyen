<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any, vehicles: any[] }>()

const showAddForm = ref(false)

const form = useForm({
    registration_number: '',
    make: '',
    model: '',
    year: '',
    color: '',
    fuel_type: '',
    mileage: '',
})

function submitAdd() {
    form.post('/customer/vehicles', {
        onSuccess: () => {
            showAddForm.value = false
            form.reset()
        },
    })
}

function motStatus(expiry: string | null) {
    if (!expiry) return null
    const d = new Date(expiry)
    const now = new Date()
    const in30 = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
    if (d < now) return 'expired'
    if (d <= in30) return 'due-soon'
    return 'ok'
}
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '' }

function confirmDelete(vehicleId: number) {
    if (confirm('Remove this vehicle from your account?')) {
        router.delete(`/customer/vehicles/${vehicleId}`)
    }
}
</script>

<template>
    <Head title="My Vehicles" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">My Vehicles</h1>
                <button @click="showAddForm = !showAddForm"
                    class="flex items-center gap-2 rounded-lg bg-electric-600 px-4 py-2 text-sm font-semibold text-white hover:bg-electric-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Vehicle
                </button>
            </div>

            <!-- Add Vehicle Form -->
            <div v-if="showAddForm" class="bg-white rounded-xl border border-electric-200 p-5 shadow-sm">
                <h2 class="font-semibold text-gray-800 mb-4">Add a New Vehicle</h2>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Registration Number <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.registration_number" type="text" required placeholder="e.g. AB12 CDE"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-electric-600"
                                :class="{ 'border-red-400': form.errors.registration_number }" />
                            <p v-if="form.errors.registration_number" class="mt-1 text-xs text-red-600">{{ form.errors.registration_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Make <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.make" type="text" required placeholder="e.g. Ford"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                :class="{ 'border-red-400': form.errors.make }" />
                            <p v-if="form.errors.make" class="mt-1 text-xs text-red-600">{{ form.errors.make }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Model <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.model" type="text" required placeholder="e.g. Focus"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600"
                                :class="{ 'border-red-400': form.errors.model }" />
                            <p v-if="form.errors.model" class="mt-1 text-xs text-red-600">{{ form.errors.model }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <input v-model="form.year" type="number" placeholder="e.g. 2019" min="1960" :max="new Date().getFullYear() + 1"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Colour</label>
                            <input v-model="form.color" type="text" placeholder="e.g. Silver"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
                            <select v-model="form.fuel_type"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600">
                                <option value="">Select…</option>
                                <option value="petrol">Petrol</option>
                                <option value="diesel">Diesel</option>
                                <option value="electric">Electric</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="lpg">LPG</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Mileage</label>
                            <input v-model="form.mileage" type="number" placeholder="e.g. 45000" min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-electric-600" />
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showAddForm = false; form.reset()"
                            class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 rounded-lg bg-electric-600 py-2.5 text-sm font-semibold text-white hover:bg-electric-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving…' : 'Save Vehicle' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Vehicle List -->
            <div v-if="!vehicles.length && !showAddForm"
                class="bg-white rounded-xl border border-gray-200 p-10 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
                <p class="text-gray-500 text-sm mb-3">No vehicles on record yet.</p>
                <button @click="showAddForm = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-electric-600 px-4 py-2 text-sm font-semibold text-white hover:bg-electric-700">
                    Add your first vehicle
                </button>
            </div>

            <div v-for="v in vehicles" :key="v.id" class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-electric-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-electric-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-900 text-lg">{{ v.registration_number }}</p>
                            <span v-if="motStatus(v.mot_due_date) === 'expired'"
                                class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">MOT Expired</span>
                            <span v-else-if="motStatus(v.mot_due_date) === 'due-soon'"
                                class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 font-medium">MOT Due Soon</span>
                        </div>
                        <p class="text-gray-600">{{ v.make }} {{ v.model }} {{ v.year }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-1 mt-3 text-sm">
                            <div v-if="v.color"><span class="text-gray-400">Colour</span><br><span class="font-medium text-gray-800">{{ v.color }}</span></div>
                            <div v-if="v.fuel_type"><span class="text-gray-400">Fuel</span><br><span class="font-medium text-gray-800 capitalize">{{ v.fuel_type }}</span></div>
                            <div v-if="v.engine_size"><span class="text-gray-400">Engine</span><br><span class="font-medium text-gray-800">{{ v.engine_size }}</span></div>
                            <div v-if="v.mot_due_date">
                                <span class="text-gray-400">MOT Expiry</span><br>
                                <span :class="['font-medium', motStatus(v.mot_due_date) === 'expired' ? 'text-red-600' : motStatus(v.mot_due_date) === 'due-soon' ? 'text-orange-600' : 'text-gray-800']">
                                    {{ fmtDate(v.mot_due_date) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-3">
                            <a v-if="motStatus(v.mot_due_date) !== 'ok' && v.mot_due_date"
                                href="/customer/appointments/book"
                                class="text-sm text-electric-600 hover:text-electric-700 font-medium hover:underline">
                                Book MOT appointment →
                            </a>
                            <button @click="confirmDelete(v.id)"
                                class="ml-auto text-xs text-red-400 hover:text-red-600 hover:underline">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
