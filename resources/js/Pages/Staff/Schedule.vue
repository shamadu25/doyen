<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ staff: any, schedules: any[], date: string }>()
const route = inject<(p: string) => string>('route', p => p)

const form = ref({ date: new Date().toISOString().split('T')[0], start_time: '08:00', end_time: '17:00', break_start: '12:00', break_end: '13:00', notes: '' })
const submitting = ref(false)
const errors = ref<any>({})

function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

const statusColor: Record<string, string> = {
    scheduled: 'bg-electric-50 text-electric-700',
    clocked_in: 'bg-green-50 text-green-700',
    clocked_out: 'bg-gray-50 text-gray-700',
    absent: 'bg-red-50 text-red-700',
}

function submit() {
    submitting.value = true
    router.post(route(`/staff/${props.staff.id}/schedule`), form.value, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false; form.value.notes = '' },
    })
}
</script>

<template>
    <Head :title="`${staff.name} - Schedule`" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route(`/staff/${staff.id}`)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ staff.name }}'s Schedule</h1>
                    <p class="text-sm text-gray-500">Manage shifts and time tracking</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Add Shift -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Add Shift</h2>
                    <form @submit.prevent="submit" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date *</label>
                            <input v-model="form.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                            <p v-if="errors.date" class="text-red-500 text-xs mt-1">{{ errors.date }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Start *</label>
                                <input v-model="form.start_time" type="time" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">End *</label>
                                <input v-model="form.end_time" type="time" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Break Start</label>
                                <input v-model="form.break_start" type="time" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Break End</label>
                                <input v-model="form.break_end" type="time" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                            <input v-model="form.notes" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-electric-600 focus:outline-none" />
                        </div>
                        <button type="submit" :disabled="submitting" class="w-full py-2 bg-electric-600 text-white rounded-lg text-sm font-medium hover:bg-electric-700 disabled:opacity-50">
                            {{ submitting ? 'Adding...' : 'Add Shift' }}
                        </button>
                    </form>
                </div>

                <!-- Schedule List -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Recent Shifts</h2>
                    </div>
                    <div v-if="!schedules.length" class="text-center text-gray-400 py-8 text-sm">No shifts scheduled.</div>
                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="s in schedules" :key="s.id" class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ fmtDate(s.date) }}</p>
                                <p class="text-xs text-gray-500">{{ s.start_time }} – {{ s.end_time }}<span v-if="s.hours_worked"> · {{ s.hours_worked }}h worked</span></p>
                            </div>
                            <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', statusColor[s.status] || 'bg-gray-50 text-gray-600']">{{ s.status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
