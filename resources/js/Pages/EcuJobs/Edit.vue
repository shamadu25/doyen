<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    ecuJob: any
    technicians: { id: number; name: string }[]
    categories: Record<string, string>
    serviceTypes: Record<string, Record<string, string>>
    readonly?: boolean
}>()

const confirmDelete = ref(false)

const form = useForm({
    technician_id:        props.ecuJob.technician_id ?? '',
    category:             props.ecuJob.category ?? '',
    service_type:         props.ecuJob.service_type ?? '',
    service_label:        props.ecuJob.service_label ?? '',
    status:               props.ecuJob.status ?? 'booked',
    date_in:              props.ecuJob.date_in ?? '',
    date_completed:       props.ecuJob.date_completed ?? '',
    mileage:              props.ecuJob.mileage ?? '',
    ecu_part_number:      props.ecuJob.ecu_part_number ?? '',
    ecu_software_version: props.ecuJob.ecu_software_version ?? '',
    ecu_hardware_version: props.ecuJob.ecu_hardware_version ?? '',
    immo_ref:             props.ecuJob.immo_ref ?? '',
    fault_codes_found:    (props.ecuJob.fault_codes_found ?? []) as string[],
    fault_codes_cleared:  (props.ecuJob.fault_codes_cleared ?? []) as string[],
    all_codes_cleared:    props.ecuJob.all_codes_cleared ?? false,
    work_required:        props.ecuJob.work_required ?? '',
    work_performed:       props.ecuJob.work_performed ?? '',
    pre_condition:        props.ecuJob.pre_condition ?? '',
    post_condition:       props.ecuJob.post_condition ?? '',
    internal_notes:       props.ecuJob.internal_notes ?? '',
    details:              (props.ecuJob.details ?? {}) as Record<string, any>,
    price:                props.ecuJob.price ?? '',
    warranty_months:      props.ecuJob.warranty_months ?? 0,
    customer_notified:    props.ecuJob.customer_notified ?? false,
})

const serviceTypeOptions = computed<Record<string, string>>(() => {
    if (!form.category) return {}
    return props.serviceTypes[form.category] ?? {}
})

watch(() => form.category, (newVal, oldVal) => {
    if (oldVal && newVal !== oldVal) {
        form.service_type = ''
    }
})

const isRemap    = computed(() => form.category === 'remapping')
const isAirbag   = computed(() => form.category === 'airbag_srs')
const isEmission = computed(() => form.category === 'emissions')
const isMileage  = computed(() => form.category === 'mileage_correction')
const isImmo     = computed(() => form.category === 'immobiliser')

const newFaultCode = ref('')
function addFaultCode() {
    const code = newFaultCode.value.trim().toUpperCase()
    if (code && !form.fault_codes_found.includes(code)) {
        form.fault_codes_found.push(code)
    }
    newFaultCode.value = ''
}
function removeFaultCode(code: string) {
    form.fault_codes_found = form.fault_codes_found.filter(c => c !== code)
}

function submit() {
    form.put(`/ecu-jobs/${props.ecuJob.id}`)
}

function deleteJob() {
    router.delete(`/ecu-jobs/${props.ecuJob.id}`)
}

function detailVal(key: string): any {
    return form.details[key] ?? ''
}
function setDetail(key: string, val: any) {
    form.details = { ...form.details, [key]: val }
}

const statusColors: Record<string, string> = {
    booked:      'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed:   'bg-green-100 text-green-800',
    on_hold:     'bg-orange-100 text-orange-800',
    cancelled:   'bg-red-100 text-red-800',
}

const categoryColors: Record<string, string> = {
    diagnostics:        'bg-purple-100 text-purple-800',
    remapping:          'bg-indigo-100 text-indigo-800',
    airbag_srs:         'bg-rose-100 text-rose-800',
    emissions:          'bg-emerald-100 text-emerald-800',
    immobiliser:        'bg-cyan-100 text-cyan-800',
    mileage_correction: 'bg-amber-100 text-amber-800',
    electrical:         'bg-orange-100 text-orange-800',
    other:              'bg-gray-100 text-gray-800',
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/ecu-jobs" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ ecuJob.job_number }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ ecuJob.customer ? `${ecuJob.customer.first_name} ${ecuJob.customer.last_name}` : '' }}
                            <span v-if="ecuJob.vehicle"> · {{ ecuJob.vehicle.registration_number }} {{ ecuJob.vehicle.make }} {{ ecuJob.vehicle.model }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold', statusColors[ecuJob.status] ?? 'bg-gray-100']">
                        {{ ecuJob.status_label ?? ecuJob.status }}
                    </span>
                    <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold', categoryColors[ecuJob.category] ?? 'bg-gray-100']">
                        {{ categories[ecuJob.category] ?? ecuJob.category }}
                    </span>
                    <template v-if="readonly">
                        <Link :href="`/ecu-jobs/${ecuJob.id}/edit`"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Edit Job
                        </Link>
                    </template>
                </div>
            </div>
        </template>

        <!-- Readonly view -->
        <div v-if="readonly" class="space-y-6 max-w-4xl">
            <!-- Summary card -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Job Summary</h2>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Date In</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ ecuJob.date_in ? new Date(ecuJob.date_in).toLocaleDateString('en-GB') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Date Completed</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ ecuJob.date_completed ? new Date(ecuJob.date_completed).toLocaleDateString('en-GB') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Technician</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ ecuJob.technician?.name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Price</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ ecuJob.price ? `£${parseFloat(ecuJob.price).toFixed(2)}` : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Category</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ categories[ecuJob.category] ?? ecuJob.category }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Service Type</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ ecuJob.service_label || ecuJob.service_type }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Mileage</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ ecuJob.mileage ? ecuJob.mileage.toLocaleString() + ' mi' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Warranty</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ ecuJob.warranty_months ? `${ecuJob.warranty_months} months` : 'None' }}</p>
                    </div>
                </div>
            </div>

            <!-- ECU Info -->
            <div v-if="ecuJob.ecu_part_number || ecuJob.ecu_software_version || ecuJob.ecu_hardware_version || ecuJob.immo_ref"
                class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">ECU / Module Info</h2>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div v-if="ecuJob.ecu_part_number">
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Part Number</p>
                        <p class="mt-1 font-mono font-semibold text-gray-900">{{ ecuJob.ecu_part_number }}</p>
                    </div>
                    <div v-if="ecuJob.ecu_software_version">
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">SW Version</p>
                        <p class="mt-1 font-mono font-semibold text-gray-900">{{ ecuJob.ecu_software_version }}</p>
                    </div>
                    <div v-if="ecuJob.ecu_hardware_version">
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">HW Version</p>
                        <p class="mt-1 font-mono font-semibold text-gray-900">{{ ecuJob.ecu_hardware_version }}</p>
                    </div>
                    <div v-if="ecuJob.immo_ref">
                        <p class="text-xs font-medium uppercase text-gray-400 tracking-wide">Immo Ref</p>
                        <p class="mt-1 font-mono font-semibold text-gray-900">{{ ecuJob.immo_ref }}</p>
                    </div>
                </div>
            </div>

            <!-- Remap results -->
            <div v-if="ecuJob.category === 'remapping' && ecuJob.details"
                class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Remap Results</h2>
                </div>
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs font-medium uppercase text-gray-400 border-b border-gray-100">
                                <th class="text-left pb-2">Metric</th>
                                <th class="text-center pb-2">Before</th>
                                <th class="text-center pb-2">After</th>
                                <th class="text-center pb-2">Gain</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="ecuJob.details.bhp_before || ecuJob.details.bhp_after">
                                <td class="py-2 font-medium">Power (BHP)</td>
                                <td class="py-2 text-center">{{ ecuJob.details.bhp_before ?? '—' }}</td>
                                <td class="py-2 text-center font-semibold text-green-700">{{ ecuJob.details.bhp_after ?? '—' }}</td>
                                <td class="py-2 text-center text-green-600 font-semibold">
                                    <template v-if="ecuJob.details.bhp_before && ecuJob.details.bhp_after">
                                        +{{ (parseFloat(ecuJob.details.bhp_after) - parseFloat(ecuJob.details.bhp_before)).toFixed(1) }}
                                    </template>
                                    <template v-else>—</template>
                                </td>
                            </tr>
                            <tr v-if="ecuJob.details.torque_before || ecuJob.details.torque_after">
                                <td class="py-2 font-medium">Torque (Nm)</td>
                                <td class="py-2 text-center">{{ ecuJob.details.torque_before ?? '—' }}</td>
                                <td class="py-2 text-center font-semibold text-green-700">{{ ecuJob.details.torque_after ?? '—' }}</td>
                                <td class="py-2 text-center text-green-600 font-semibold">
                                    <template v-if="ecuJob.details.torque_before && ecuJob.details.torque_after">
                                        +{{ (parseFloat(ecuJob.details.torque_after) - parseFloat(ecuJob.details.torque_before)).toFixed(1) }}
                                    </template>
                                    <template v-else>—</template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="ecuJob.details.stage || ecuJob.details.tune_file_ref" class="mt-4 flex gap-6 text-sm">
                        <div v-if="ecuJob.details.stage">
                            <p class="text-xs uppercase text-gray-400 font-medium tracking-wide">Stage</p>
                            <p class="mt-1 font-semibold capitalize">{{ ecuJob.details.stage }}</p>
                        </div>
                        <div v-if="ecuJob.details.tune_file_ref">
                            <p class="text-xs uppercase text-gray-400 font-medium tracking-wide">Tune File</p>
                            <p class="mt-1 font-mono text-blue-700">{{ ecuJob.details.tune_file_ref }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fault Codes -->
            <div v-if="ecuJob.fault_codes_found?.length"
                class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Fault Codes</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span v-for="code in ecuJob.fault_codes_found" :key="code"
                            class="rounded-full bg-red-50 border border-red-200 px-3 py-1 text-xs font-mono text-red-700">
                            {{ code }}
                        </span>
                    </div>
                    <p v-if="ecuJob.all_codes_cleared" class="text-sm text-green-700 font-medium mt-2">
                        ✓ All fault codes cleared after work
                    </p>
                </div>
            </div>

            <!-- Work Notes -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Work Notes</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div v-if="ecuJob.pre_condition">
                        <p class="text-xs uppercase text-gray-400 font-medium tracking-wide mb-1">Condition Before</p>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ecuJob.pre_condition }}</p>
                    </div>
                    <div v-if="ecuJob.work_required">
                        <p class="text-xs uppercase text-gray-400 font-medium tracking-wide mb-1">Work Required</p>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ecuJob.work_required }}</p>
                    </div>
                    <div v-if="ecuJob.work_performed">
                        <p class="text-xs uppercase text-gray-400 font-medium tracking-wide mb-1">Work Performed</p>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ecuJob.work_performed }}</p>
                    </div>
                    <div v-if="ecuJob.post_condition">
                        <p class="text-xs uppercase text-gray-400 font-medium tracking-wide mb-1">Condition After</p>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ecuJob.post_condition }}</p>
                    </div>
                    <div v-if="ecuJob.internal_notes">
                        <p class="text-xs uppercase text-gray-400 font-medium tracking-wide mb-1">Internal Notes</p>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ ecuJob.internal_notes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit form -->
        <form v-else @submit.prevent="submit" class="space-y-6 max-w-4xl">

            <!-- Job Details -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Job Details</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select v-model="form.category" required
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                            :class="form.errors.category ? 'border-red-400' : 'border-gray-300'">
                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Type <span class="text-red-500">*</span></label>
                        <select v-model="form.service_type" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option v-for="(label, key) in serviceTypeOptions" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.service_type" class="mt-1 text-xs text-red-600">{{ form.errors.service_type }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Description</label>
                        <input v-model="form.service_label" type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select v-model="form.status" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="booked">Booked</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Technician</label>
                        <select v-model="form.technician_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Unassigned</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date In <span class="text-red-500">*</span></label>
                        <input v-model="form.date_in" type="date" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Completed</label>
                        <input v-model="form.date_completed" type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
                        <input v-model="form.mileage" type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            <!-- ECU Technical Details -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">ECU / Module Information</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ECU Part Number</label>
                        <input v-model="form.ecu_part_number" type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Software Version</label>
                        <input v-model="form.ecu_software_version" type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hardware Version</label>
                        <input v-model="form.ecu_hardware_version" type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div v-if="isImmo">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Immobiliser Ref / PIN</label>
                        <input v-model="form.immo_ref" type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <!-- Remap -->
                <div v-if="isRemap" class="px-6 pb-6 grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BHP Before</label>
                        <input :value="detailVal('bhp_before')" @input="setDetail('bhp_before', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BHP After</label>
                        <input :value="detailVal('bhp_after')" @input="setDetail('bhp_after', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Torque Before (Nm)</label>
                        <input :value="detailVal('torque_before')" @input="setDetail('torque_before', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Torque After (Nm)</label>
                        <input :value="detailVal('torque_after')" @input="setDetail('torque_after', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tune File Reference</label>
                        <input :value="detailVal('tune_file_ref')" @input="setDetail('tune_file_ref', ($event.target as HTMLInputElement).value)"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                        <select :value="detailVal('stage')" @change="setDetail('stage', ($event.target as HTMLSelectElement).value)"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Select…</option>
                            <option value="stage1">Stage 1</option>
                            <option value="stage2">Stage 2</option>
                            <option value="eco">Eco / Economy</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>

                <!-- Airbag -->
                <div v-if="isAirbag" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="crash_data_cleared_edit"
                            :checked="detailVal('crash_data_cleared') === true || detailVal('crash_data_cleared') === 'true'"
                            @change="setDetail('crash_data_cleared', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label for="crash_data_cleared_edit" class="text-sm text-gray-700">Crash data cleared</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="srs_light_cleared_edit"
                            :checked="detailVal('srs_light_cleared') === true || detailVal('srs_light_cleared') === 'true'"
                            @change="setDetail('srs_light_cleared', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label for="srs_light_cleared_edit" class="text-sm text-gray-700">SRS warning light cleared</label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modules Reset</label>
                        <input :value="detailVal('modules_reset')" @input="setDetail('modules_reset', ($event.target as HTMLInputElement).value)"
                            type="text" placeholder="e.g. Driver Airbag, Passenger Airbag"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <!-- Emissions -->
                <div v-if="isEmission" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DPF Status Before</label>
                        <input :value="detailVal('dpf_before')" @input="setDetail('dpf_before', ($event.target as HTMLInputElement).value)"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DPF Status After</label>
                        <input :value="detailVal('dpf_after')" @input="setDetail('dpf_after', ($event.target as HTMLInputElement).value)"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">EGR Status</label>
                        <select :value="detailVal('egr_status')" @change="setDetail('egr_status', ($event.target as HTMLSelectElement).value)"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">N/A</option>
                            <option value="repaired">Repaired</option>
                            <option value="removed_software">Removed (Software)</option>
                            <option value="physical_delete">Physical Delete</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">AdBlue Status</label>
                        <select :value="detailVal('adblue_status')" @change="setDetail('adblue_status', ($event.target as HTMLSelectElement).value)"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">N/A</option>
                            <option value="repaired">Repaired</option>
                            <option value="emulated">Emulated</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                </div>

                <!-- Mileage correction -->
                <div v-if="isMileage" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage Before</label>
                        <input :value="detailVal('mileage_before')" @input="setDetail('mileage_before', ($event.target as HTMLInputElement).value)"
                            type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage After</label>
                        <input :value="detailVal('mileage_after')" @input="setDetail('mileage_after', ($event.target as HTMLInputElement).value)"
                            type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="cluster_replaced_edit"
                            :checked="detailVal('cluster_replaced') === true || detailVal('cluster_replaced') === 'true'"
                            @change="setDetail('cluster_replaced', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label for="cluster_replaced_edit" class="text-sm text-gray-700">Instrument cluster replaced</label>
                    </div>
                </div>
            </div>

            <!-- Fault Codes -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Fault Codes Found</h2>
                </div>
                <div class="p-6">
                    <div class="flex gap-2 mb-3">
                        <input v-model="newFaultCode" type="text" placeholder="e.g. P0401…"
                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                            @keyup.enter="addFaultCode" />
                        <button type="button" @click="addFaultCode"
                            class="rounded-lg bg-gray-100 hover:bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700">
                            Add
                        </button>
                    </div>
                    <div v-if="form.fault_codes_found.length" class="flex flex-wrap gap-2">
                        <span v-for="code in form.fault_codes_found" :key="code"
                            class="inline-flex items-center gap-1 rounded-full bg-red-50 border border-red-200 px-3 py-1 text-xs font-mono font-medium text-red-700">
                            {{ code }}
                            <button type="button" @click="removeFaultCode(code)" class="ml-1 text-red-400 hover:text-red-600">&times;</button>
                        </span>
                    </div>
                    <p v-else class="text-sm text-gray-400 italic">No fault codes.</p>
                    <div v-if="form.fault_codes_found.length" class="mt-3 flex items-center gap-2">
                        <input type="checkbox" id="all_codes_cleared_edit" v-model="form.all_codes_cleared"
                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                        <label for="all_codes_cleared_edit" class="text-sm text-gray-700">All fault codes cleared after work</label>
                    </div>
                </div>
            </div>

            <!-- Work Notes -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Work Notes</h2>
                </div>
                <div class="p-6 grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Condition Before Work</label>
                        <textarea v-model="form.pre_condition" rows="2"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work Required</label>
                        <textarea v-model="form.work_required" rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work Performed</label>
                        <textarea v-model="form.work_performed" rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Condition After Work</label>
                        <textarea v-model="form.post_condition" rows="2"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                        <textarea v-model="form.internal_notes" rows="2"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Pricing & Warranty</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (£)</label>
                        <input v-model="form.price" type="number" min="0" step="0.01"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warranty (months)</label>
                        <input v-model="form.warranty_months" type="number" min="0" max="36"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="flex items-end pb-1">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="customer_notified_edit" v-model="form.customer_notified"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="customer_notified_edit" class="text-sm text-gray-700">Customer notified</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pb-8">
                <div>
                    <button v-if="!confirmDelete" type="button" @click="confirmDelete = true"
                        class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                        Delete Job
                    </button>
                    <div v-else class="flex items-center gap-2">
                        <span class="text-sm text-red-600 font-medium">Confirm delete?</span>
                        <button type="button" @click="deleteJob"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            Yes, Delete
                        </button>
                        <button type="button" @click="confirmDelete = false"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link :href="`/ecu-jobs/${ecuJob.id}`"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
