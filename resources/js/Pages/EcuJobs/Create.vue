<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    customers: { id: number; first_name: string; last_name: string; phone: string; email: string }[]
    vehicles: { id: number; customer_id: number; registration_number: string; make: string; model: string; year: number }[]
    technicians: { id: number; name: string }[]
    categories: Record<string, string>
    serviceTypes: Record<string, Record<string, string>>
    preVehicleId?: number | null
    preCustomerId?: number | null
    preJobCardId?: number | null
}>()

const form = useForm({
    customer_id:          props.preCustomerId ?? '',
    vehicle_id:           props.preVehicleId  ?? '',
    job_card_id:          props.preJobCardId  ?? '',
    technician_id:        '',
    category:             '',
    service_type:         '',
    service_label:        '',
    status:               'booked',
    date_in:              new Date().toISOString().split('T')[0],
    date_completed:       '',
    mileage:              '',
    ecu_part_number:      '',
    ecu_software_version: '',
    ecu_hardware_version: '',
    immo_ref:             '',
    fault_codes_found:    [] as string[],
    fault_codes_cleared:  [] as string[],
    all_codes_cleared:    false,
    work_required:        '',
    work_performed:       '',
    pre_condition:        '',
    post_condition:       '',
    internal_notes:       '',
    details:              {} as Record<string, any>,
    price:                '',
    warranty_months:      0,
    customer_notified:    false,
})

// Filtered vehicles by customer
const filteredVehicles = computed(() => {
    if (!form.customer_id) return props.vehicles
    return props.vehicles.filter(v => v.customer_id === Number(form.customer_id))
})

// Service type options based on selected category
const serviceTypeOptions = computed<Record<string, string>>(() => {
    if (!form.category) return {}
    return props.serviceTypes[form.category] ?? {}
})

// Reset service_type when category changes
watch(() => form.category, () => {
    form.service_type = ''
    form.service_label = ''
})

// Auto-fill service_label when service_type is selected
watch(() => form.service_type, (val) => {
    if (val && serviceTypeOptions.value[val]) {
        form.service_label = serviceTypeOptions.value[val]
    }
})

// Show category-specific detail fields
const isRemap     = computed(() => form.category === 'remapping')
const isAirbag    = computed(() => form.category === 'airbag_srs')
const isEmission  = computed(() => form.category === 'emissions')
const isMileage   = computed(() => form.category === 'mileage_correction')
const isImmo      = computed(() => form.category === 'immobiliser')

// Fault codes
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
    form.post('/ecu-jobs')
}

// Category-specific detail helpers
function detailVal(key: string): any {
    return form.details[key] ?? ''
}
function setDetail(key: string, val: any) {
    form.details = { ...form.details, [key]: val }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link href="/ecu-jobs" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">New ECU Job</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-6 max-w-4xl">

            <!-- Customer & Vehicle -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Customer & Vehicle</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                        <select v-model="form.customer_id" required
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                            :class="form.errors.customer_id ? 'border-red-400' : 'border-gray-300'">
                            <option value="">Select customer…</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.first_name }} {{ c.last_name }} — {{ c.phone }}
                            </option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle <span class="text-red-500">*</span></label>
                        <select v-model="form.vehicle_id" required
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                            :class="form.errors.vehicle_id ? 'border-red-400' : 'border-gray-300'">
                            <option value="">Select vehicle…</option>
                            <option v-for="v in filteredVehicles" :key="v.id" :value="v.id">
                                {{ v.registration_number }} — {{ v.make }} {{ v.model }} {{ v.year }}
                            </option>
                        </select>
                        <p v-if="form.errors.vehicle_id" class="mt-1 text-xs text-red-600">{{ form.errors.vehicle_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Linked Job Card (optional)</label>
                        <input v-model="form.job_card_id" type="number" placeholder="Job card ID"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage at Drop-off</label>
                        <input v-model="form.mileage" type="number" min="0" placeholder="e.g. 45000"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

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
                            <option value="">Select category…</option>
                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Type <span class="text-red-500">*</span></label>
                        <select v-model="form.service_type" required :disabled="!form.category"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 disabled:bg-gray-50 disabled:text-gray-400"
                            :class="form.errors.service_type ? 'border-red-400' : 'border-gray-300'">
                            <option value="">{{ form.category ? 'Select service…' : 'Choose category first' }}</option>
                            <option v-for="(label, key) in serviceTypeOptions" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.service_type" class="mt-1 text-xs text-red-600">{{ form.errors.service_type }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Description / Label</label>
                        <input v-model="form.service_label" type="text" placeholder="Describe the specific work…"
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
                        <input v-model="form.ecu_part_number" type="text" placeholder="e.g. 0281020044"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Software Version</label>
                        <input v-model="form.ecu_software_version" type="text" placeholder="e.g. 1037503181"
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

                <!-- Remap-specific -->
                <div v-if="isRemap" class="px-6 pb-6 grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BHP Before</label>
                        <input :value="detailVal('bhp_before')" @input="setDetail('bhp_before', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BHP After</label>
                        <input :value="detailVal('bhp_after')" @input="setDetail('bhp_after', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Torque Before (Nm)</label>
                        <input :value="detailVal('torque_before')" @input="setDetail('torque_before', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Torque After (Nm)</label>
                        <input :value="detailVal('torque_after')" @input="setDetail('torque_after', ($event.target as HTMLInputElement).value)"
                            type="number" step="0.1" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tune File Reference</label>
                        <input :value="detailVal('tune_file_ref')" @input="setDetail('tune_file_ref', ($event.target as HTMLInputElement).value)"
                            type="text" placeholder="File name or ref number"
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

                <!-- Airbag-specific -->
                <div v-if="isAirbag" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="crash_data_cleared"
                            :checked="detailVal('crash_data_cleared') === true || detailVal('crash_data_cleared') === 'true'"
                            @change="setDetail('crash_data_cleared', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label for="crash_data_cleared" class="text-sm text-gray-700">Crash data cleared</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="srs_light_cleared"
                            :checked="detailVal('srs_light_cleared') === true || detailVal('srs_light_cleared') === 'true'"
                            @change="setDetail('srs_light_cleared', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label for="srs_light_cleared" class="text-sm text-gray-700">SRS warning light cleared</label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modules Reset</label>
                        <input :value="detailVal('modules_reset')" @input="setDetail('modules_reset', ($event.target as HTMLInputElement).value)"
                            type="text" placeholder="e.g. Driver Airbag, Passenger Airbag, BCM"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <!-- Emissions-specific -->
                <div v-if="isEmission" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DPF Status Before</label>
                        <input :value="detailVal('dpf_before')" @input="setDetail('dpf_before', ($event.target as HTMLInputElement).value)"
                            type="text" placeholder="e.g. 90% blocked"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DPF Status After</label>
                        <input :value="detailVal('dpf_after')" @input="setDetail('dpf_after', ($event.target as HTMLInputElement).value)"
                            type="text" placeholder="e.g. Removed / Cleared"
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

                <!-- Mileage-specific -->
                <div v-if="isMileage" class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage Before Correction</label>
                        <input :value="detailVal('mileage_before')" @input="setDetail('mileage_before', ($event.target as HTMLInputElement).value)"
                            type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mileage After Correction</label>
                        <input :value="detailVal('mileage_after')" @input="setDetail('mileage_after', ($event.target as HTMLInputElement).value)"
                            type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="cluster_replaced"
                            :checked="detailVal('cluster_replaced') === true || detailVal('cluster_replaced') === 'true'"
                            @change="setDetail('cluster_replaced', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label for="cluster_replaced" class="text-sm text-gray-700">Instrument cluster replaced</label>
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
                        <input v-model="newFaultCode" type="text" placeholder="e.g. P0401, C0034…"
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
                    <p v-else class="text-sm text-gray-400 italic">No fault codes added yet.</p>
                    <div v-if="form.fault_codes_found.length" class="mt-3 flex items-center gap-2">
                        <input type="checkbox" id="all_codes_cleared" v-model="form.all_codes_cleared"
                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                        <label for="all_codes_cleared" class="text-sm text-gray-700">All fault codes cleared after work</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Condition Before Work</label>
                        <textarea v-model="form.pre_condition" rows="2" placeholder="Describe condition / symptoms…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work Required / Customer Request</label>
                        <textarea v-model="form.work_required" rows="3" placeholder="Describe the work requested…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work Performed</label>
                        <textarea v-model="form.work_performed" rows="3" placeholder="Describe work actually carried out…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Condition After Work</label>
                        <textarea v-model="form.post_condition" rows="2" placeholder="Describe outcome / final condition…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                        <textarea v-model="form.internal_notes" rows="2" placeholder="Staff-only notes…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            <!-- Pricing & Warranty -->
            <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Pricing & Warranty</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (£)</label>
                        <input v-model="form.price" type="number" min="0" step="0.01" placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warranty (months)</label>
                        <input v-model="form.warranty_months" type="number" min="0" max="36" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="flex flex-col justify-end gap-3">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="customer_notified" v-model="form.customer_notified"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="customer_notified" class="text-sm text-gray-700">Customer notified</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pb-8">
                <Link href="/ecu-jobs"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </Link>
                <button type="submit" :disabled="form.processing"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-50">
                    {{ form.processing ? 'Saving…' : 'Create ECU Job' }}
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
