<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ staff: any }>()
const route = inject<(p: string) => string>('route', p => p)

const form = ref({
    name: props.staff.name, email: props.staff.email,
    employee_id: props.staff.employee_id || '',
    role: props.staff.role, phone: props.staff.phone || '',
    date_of_birth: props.staff.date_of_birth?.split('T')[0] || '',
    hire_date: props.staff.hire_date?.split('T')[0] || '',
    hourly_rate: props.staff.hourly_rate || '',
    commission_rate: props.staff.commission_rate || '',
    skills: props.staff.skills || [],
    certifications: props.staff.certifications || [],
    emergency_contact_name: props.staff.emergency_contact_name || '',
    emergency_contact_phone: props.staff.emergency_contact_phone || '',
    notes: props.staff.notes || '',
    is_active: props.staff.is_active,
})
const errors = ref<any>({})
const submitting = ref(false)
const skillInput = ref('')
const certInput = ref('')
function addSkill() { if (skillInput.value.trim()) { form.value.skills.push(skillInput.value.trim()); skillInput.value = '' } }
function addCert() { if (certInput.value.trim()) { form.value.certifications.push(certInput.value.trim()); certInput.value = '' } }

function submit() {
    submitting.value = true
    router.put(route(`/staff/${props.staff.id}`), form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="Edit Staff" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route(`/staff/${staff.id}`)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Edit {{ staff.name }}</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Details</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                            <select v-model="form.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none">
                                <option value="technician">Technician</option>
                                <option value="receptionist">Receptionist</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                            <input v-model="form.hire_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (£)</label>
                            <input v-model="form.hourly_rate" type="number" min="0" step="0.50" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (%)</label>
                            <input v-model="form.commission_rate" type="number" min="0" max="100" step="0.5" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-gray-300" />
                            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Skills & Certifications</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Skills</label>
                            <div class="flex gap-2 mb-2">
                                <input v-model="skillInput" @keydown.enter.prevent="addSkill" type="text" placeholder="Add skill..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                                <button type="button" @click="addSkill" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="(s, i) in form.skills" :key="i" class="inline-flex items-center gap-1 px-2 py-0.5 bg-electric-50 text-electric-700 rounded text-xs">
                                    {{ s }}<button type="button" @click="form.skills.splice(i,1)">×</button>
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Certifications</label>
                            <div class="flex gap-2 mb-2">
                                <input v-model="certInput" @keydown.enter.prevent="addCert" type="text" placeholder="Add cert..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                                <button type="button" @click="addCert" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="(c, i) in form.certifications" :key="i" class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded text-xs">
                                    {{ c }}<button type="button" @click="form.certifications.splice(i,1)">×</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route(`/staff/${staff.id}`)" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Cancel</Link>
                    <button type="submit" :disabled="submitting" class="px-6 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium disabled:opacity-50">
                        {{ submitting ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
