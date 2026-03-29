<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    jobCard: any
    availableParts: any[]
    availableServices: any[]
    documentTypes?: Record<string, string>
}>()

const job = computed(() => props.jobCard)

const labourForm = useForm({
    description: '',
    hours: '',
    rate: '65.00',   // default labour rate
})

const labourLineTotal = computed(() => {
    const h = parseFloat(labourForm.hours as string) || 0
    const r = parseFloat(labourForm.rate as string) || 0
    return h * r
})

const quickLabourItems = [
    'Diagnostic scan & report',
    'ECU repair & recoding',
    'Airbag / SRS reset',
    'Brake pads & discs (axle)',
    'Suspension / steering repair',
    'Engine repair',
    'General labour',
]

const partForm = useForm({
    part_id: '',
    description: '',
    quantity: 1,
    unit_price: '',
})

// Toggle between inventory part and manual entry
const manualPart = ref(false)

const showLabourForm = ref(false)
const showPartForm = ref(false)

function addLabour() {
    labourForm.post(route(`/job-cards/${job.value.id}/add-labour`), {
        preserveScroll: true,
        onSuccess: () => {
            labourForm.reset()
            labourForm.rate = '65.00'
            showLabourForm.value = false
        },
    })
}

function addPart() {
    partForm.post(route(`/job-cards/${job.value.id}/add-part`), {
        preserveScroll: true,
        onSuccess: () => {
            partForm.reset()
            showPartForm.value = false
        },
    })
}

function completeJob() {
    if (confirm('Mark this job as completed?')) {
        router.post(route(`/job-cards/${job.value.id}/complete`))
    }
}

function generateInvoice() {
    if (confirm('Generate an invoice for this job?')) {
        router.post(route(`/job-cards/${job.value.id}/generate-invoice`))
    }
}

function selectedPartPrice() {
    const part = props.availableParts.find((p: any) => p.id == partForm.part_id)
    if (part) {
        if (!partForm.unit_price) partForm.unit_price = part.selling_price || part.price || ''
        if (!partForm.description) partForm.description = part.name || ''
    }
}

const labourTotal = computed(() => (job.value.services || []).reduce((sum: number, l: any) => sum + (parseFloat(l.quantity) * parseFloat(l.unit_price)), 0))
const partsTotal = computed(() => (job.value.parts || []).reduce((sum: number, p: any) => sum + (parseFloat(p.quantity) * parseFloat(p.unit_price)), 0))
const subtotal = computed(() => labourTotal.value + partsTotal.value)
const vat = computed(() => subtotal.value * 0.2)
const total = computed(() => subtotal.value + vat.value)

function fmt(amount: number) {
    return '£' + amount.toFixed(2)
}

// ── Documents / Diagnostic Reports ─────────────────────────────────────────
const showUploadForm = ref(false)
const docForm = useForm({
    title: '',
    document_type: 'diagnostic_report',
    description: '',
    visible_to_customer: true,
    file: null as File | null,
})

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement
    if (target.files && target.files[0]) {
        docForm.file = target.files[0]
    }
}

function uploadDocument() {
    docForm.post(route(`/job-cards/${job.value.id}/documents`), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            docForm.reset()
            showUploadForm.value = false
        },
    })
}

function deleteDocument(docId: number) {
    if (confirm('Delete this document?')) {
        router.delete(route(`/documents/${docId}`), { preserveScroll: true })
    }
}

function toggleVisibility(docId: number) {
    router.patch(route(`/documents/${docId}/toggle-visibility`), {}, { preserveScroll: true })
}

function humanSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}
</script>

<template>
    <Head :title="`Job ${job.job_number}`" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ job.job_number }}</h1>
                        <StatusBadge :status="job.status" />
                        <StatusBadge :status="job.priority" />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Created {{ new Date(job.created_at).toLocaleDateString('en-GB') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route(`/job-cards/${job.id}/edit`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button v-if="job.status !== 'completed' && job.status !== 'invoiced'" @click="completeJob" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Complete</button>
                    <button v-if="job.status === 'completed'" @click="generateInvoice" class="px-4 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700">Generate Invoice</button>
                    <Link :href="route('/job-cards')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <div v-if="job.source_quote" class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-purple-700">Converted From Quote</p>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-purple-900">{{ job.source_quote.quote_number }}</p>
                                <p class="text-sm text-purple-800">This job card was created from the approved quote and carries over its scope and line items.</p>
                            </div>
                            <Link :href="route(`/quotes/${job.source_quote.id}`)" class="shrink-0 rounded-lg border border-purple-300 bg-white px-3 py-2 text-sm font-medium text-purple-800 hover:bg-purple-100">
                                View Quote
                            </Link>
                        </div>
                    </div>

                    <!-- Job Details -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Details</h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Customer:</span>
                                <Link :href="route(`/customers/${job.customer?.id}`)" class="ml-2 text-electric-600 hover:text-electric-700 font-medium">{{ job.customer?.first_name }} {{ job.customer?.last_name }}</Link>
                            </div>
                            <div>
                                <span class="text-gray-500">Vehicle:</span>
                                <Link :href="route(`/vehicles/${job.vehicle?.id}`)" class="ml-2 text-electric-600 hover:text-electric-700 font-medium">{{ job.vehicle?.registration_number }}</Link>
                            </div>
                            <div>
                                <span class="text-gray-500">Assigned To:</span>
                                <span class="ml-2 text-gray-900">{{ job.assigned_to_user?.name || 'Unassigned' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Mileage In:</span>
                                <span class="ml-2 text-gray-900">{{ job.mileage_in ? Number(job.mileage_in).toLocaleString() : '-' }}</span>
                            </div>
                            <div v-if="job.estimated_completion">
                                <span class="text-gray-500">Est. Completion:</span>
                                <span class="ml-2 text-gray-900">{{ new Date(job.estimated_completion).toLocaleDateString('en-GB') }}</span>
                            </div>
                        </div>
                        <div v-if="job.customer_complaint" class="mt-4">
                            <span class="text-sm text-gray-500">Customer Complaint:</span>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ job.customer_complaint }}</p>
                        </div>
                        <div v-if="job.work_required" class="mt-4">
                            <span class="text-sm text-gray-500">Work Required:</span>
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ job.work_required }}</p>
                        </div>
                        <div v-if="job.technician_notes" class="mt-4">
                            <span class="text-sm text-gray-500">Technician Notes:</span>
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ job.technician_notes }}</p>
                        </div>
                    </div>

                    <!-- Labour Lines -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Labour</h2>
                            <button v-if="job.status !== 'completed' && job.status !== 'invoiced'" @click="showLabourForm = !showLabourForm" class="text-sm text-electric-600 hover:text-electric-700 font-medium">
                                {{ showLabourForm ? 'Cancel' : '+ Add Labour' }}
                            </button>
                        </div>

                        <!-- Add Labour Form -->
                        <form v-if="showLabourForm" @submit.prevent="addLabour" class="mb-4 p-4 bg-electric-50 border border-electric-200 rounded-xl space-y-3">
                            <p class="text-xs font-semibold text-electric-700 uppercase tracking-wide">New Labour Line</p>
                            <!-- Quick-fill chips -->
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="item in quickLabourItems" :key="item"
                                    type="button"
                                    @click="labourForm.description = item"
                                    class="px-2 py-1 text-xs bg-white border border-electric-200 rounded-full text-electric-700 hover:bg-electric-100 transition"
                                >{{ item }}</button>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                                <input v-model="labourForm.description" type="text" placeholder="e.g. ECU repair & recoding" class="w-full rounded-lg border-gray-300 text-sm" required />
                                <p v-if="labourForm.errors.description" class="text-xs text-red-500 mt-1">{{ labourForm.errors.description }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Hours <span class="text-red-500">*</span></label>
                                    <input v-model="labourForm.hours" type="number" step="0.25" min="0.25" placeholder="e.g. 1.5" class="w-full rounded-lg border-gray-300 text-sm" required />
                                    <p v-if="labourForm.errors.hours" class="text-xs text-red-500 mt-1">{{ labourForm.errors.hours }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Hourly Rate (£) <span class="text-red-500">*</span></label>
                                    <input v-model="labourForm.rate" type="number" step="0.01" min="0" placeholder="65.00" class="w-full rounded-lg border-gray-300 text-sm" required />
                                    <p v-if="labourForm.errors.rate" class="text-xs text-red-500 mt-1">{{ labourForm.errors.rate }}</p>
                                </div>
                            </div>
                            <!-- Live total preview -->
                            <div v-if="labourLineTotal > 0" class="flex items-center justify-between bg-white rounded-lg px-4 py-2 border border-electric-200">
                                <span class="text-sm text-gray-600">Line total (ex VAT):</span>
                                <span class="font-bold text-electric-700 text-sm">{{ fmt(labourLineTotal) }}</span>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="showLabourForm = false; labourForm.reset(); labourForm.rate = '65.00'" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                                <button type="submit" :disabled="labourForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                                    {{ labourForm.processing ? 'Adding…' : 'Add Labour Line' }}
                                </button>
                            </div>
                        </form>

                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left py-2 text-gray-500 font-medium">Description</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Hours</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Rate</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="line in (job.services || [])" :key="line.id">
                                    <td class="py-2 text-gray-900">{{ line.description }}</td>
                                    <td class="py-2 text-right text-gray-600">{{ line.quantity }}</td>
                                    <td class="py-2 text-right text-gray-600">{{ fmt(parseFloat(line.unit_price)) }}</td>
                                    <td class="py-2 text-right font-medium text-gray-900">{{ fmt(parseFloat(line.quantity) * parseFloat(line.unit_price)) }}</td>
                                </tr>
                                <tr v-if="!(job.services || []).length">
                                    <td colspan="4" class="py-4 text-center text-gray-400">No labour lines added yet — click + Add Labour</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t">
                                    <td colspan="3" class="py-2 text-right font-medium text-gray-700">Labour Total:</td>
                                    <td class="py-2 text-right font-bold text-gray-900">{{ fmt(labourTotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Parts Lines -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Parts</h2>
                            <button v-if="job.status !== 'completed' && job.status !== 'invoiced'" @click="showPartForm = !showPartForm" class="text-sm text-electric-600 hover:text-electric-700 font-medium">
                                {{ showPartForm ? 'Cancel' : '+ Add Part' }}
                            </button>
                        </div>

                        <!-- Add Part Form -->
                        <form v-if="showPartForm" @submit.prevent="addPart" class="mb-4 p-4 bg-gray-50 rounded-lg space-y-3">
                            <div class="flex items-center gap-3 mb-2">
                                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" v-model="manualPart" @change="partForm.part_id = ''; partForm.description = ''; partForm.unit_price = ''" class="rounded border-gray-300" />
                                    Enter part manually (not from inventory)
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Inventory select OR manual name -->
                                <div class="col-span-2">
                                    <select v-if="!manualPart" v-model="partForm.part_id" @change="selectedPartPrice" class="w-full rounded-lg border-gray-300 text-sm" :required="!manualPart">
                                        <option value="">Select from inventory...</option>
                                        <option v-for="p in availableParts" :key="p.id" :value="p.id">{{ p.name }} — {{ p.part_number || '' }} ({{ p.stock_quantity }} in stock)</option>
                                    </select>
                                    <input v-else v-model="partForm.description" type="text" placeholder="Part name / description" class="w-full rounded-lg border-gray-300 text-sm" required />
                                </div>
                                <!-- Description override (inventory mode) -->
                                <div class="col-span-2" v-if="!manualPart && partForm.part_id">
                                    <input v-model="partForm.description" type="text" placeholder="Description (auto-filled, edit if needed)" class="w-full rounded-lg border-gray-300 text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                                    <input v-model="partForm.quantity" type="number" min="1" placeholder="Qty" class="w-full rounded-lg border-gray-300 text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Unit Price (£)</label>
                                    <input v-model="partForm.unit_price" type="number" step="0.01" placeholder="0.00" class="w-full rounded-lg border-gray-300 text-sm" required />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" :disabled="partForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">Add Part</button>
                            </div>
                        </form>

                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left py-2 text-gray-500 font-medium">Part</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Qty</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Unit Price</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="line in (job.parts || [])" :key="line.id">
                                    <td class="py-2 text-gray-900">{{ line.part?.name || line.part_name || line.description }}</td>
                                    <td class="py-2 text-right text-gray-600">{{ line.quantity }}</td>
                                    <td class="py-2 text-right text-gray-600">{{ fmt(parseFloat(line.unit_price)) }}</td>
                                    <td class="py-2 text-right font-medium text-gray-900">{{ fmt(parseFloat(line.quantity) * parseFloat(line.unit_price)) }}</td>
                                </tr>
                                <tr v-if="!(job.parts || []).length">
                                    <td colspan="4" class="py-4 text-center text-gray-400">No parts added yet — click + Add Part</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t">
                                    <td colspan="3" class="py-2 text-right font-medium text-gray-700">Parts Total:</td>
                                    <td class="py-2 text-right font-bold text-gray-900">{{ fmt(partsTotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Sidebar: Totals -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Totals</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Labour:</span>
                                <span class="font-medium text-gray-900">{{ fmt(labourTotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Parts:</span>
                                <span class="font-medium text-gray-900">{{ fmt(partsTotal) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-3">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium text-gray-900">{{ fmt(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">VAT (20%):</span>
                                <span class="font-medium text-gray-900">{{ fmt(vat) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-3">
                                <span class="text-gray-900 font-semibold text-base">Total:</span>
                                <span class="font-bold text-electric-600 text-lg">{{ fmt(total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Info Card -->
                    <div v-if="job.vehicle" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Vehicle</h3>
                        <div class="space-y-2 text-sm">
                            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-md px-3 py-1 text-center font-bold text-gray-900 text-lg tracking-wider">{{ job.vehicle.registration_number }}</div>
                            <p class="text-gray-700">{{ job.vehicle.make }} {{ job.vehicle.model }} {{ job.vehicle.year }}</p>
                            <p v-if="job.vehicle.colour" class="text-gray-500">Colour: {{ job.vehicle.colour }}</p>
                            <p v-if="job.vehicle.fuel_type" class="text-gray-500">Fuel: {{ job.vehicle.fuel_type }}</p>
                        </div>
                    </div>

                    <!-- Customer Info Card -->
                    <div v-if="job.customer" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Customer</h3>
                        <div class="space-y-1 text-sm">
                            <p class="font-medium text-gray-900">{{ job.customer.first_name }} {{ job.customer.last_name }}</p>
                            <p v-if="job.customer.phone" class="text-gray-600">{{ job.customer.phone }}</p>
                            <p v-if="job.customer.email" class="text-gray-600">{{ job.customer.email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Diagnostic Reports & Documents ───────────────────────── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Diagnostic Reports &amp; Documents</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload reports, photos or files. Toggle "Visible to Customer" to share via the customer portal.</p>
                    </div>
                    <button @click="showUploadForm = !showUploadForm"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-electric-600 text-white text-sm font-semibold rounded-lg hover:bg-electric-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Upload Document
                    </button>
                </div>

                <!-- Upload Form -->
                <div v-if="showUploadForm" class="px-6 py-5 bg-gray-50 border-b border-gray-100">
                    <form @submit.prevent="uploadDocument" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                                <input v-model="docForm.title" type="text" required placeholder="e.g. Diagnostic Report"
                                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-electric-500 focus:border-electric-500" />
                                <p v-if="docForm.errors.title" class="text-xs text-red-600 mt-1">{{ docForm.errors.title }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                                <select v-model="docForm.document_type" required
                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-electric-500 focus:border-electric-500">
                                    <option v-for="(label, key) in (documentTypes || {})" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input v-model="docForm.description" type="text" placeholder="Optional note"
                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-electric-500 focus:border-electric-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                            <input type="file" required @change="onFileChange"
                                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt"
                                   class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-electric-50 file:text-electric-700 hover:file:bg-electric-100" />
                            <p class="text-xs text-gray-500 mt-1">PDF, images, Office docs, CSV or TXT — max 20 MB</p>
                            <p v-if="docForm.errors.file" class="text-xs text-red-600 mt-1">{{ docForm.errors.file }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input v-model="docForm.visible_to_customer" type="checkbox"
                                       class="w-4 h-4 rounded border-gray-300 text-electric-600 focus:ring-electric-500" />
                                <span class="text-sm font-medium text-gray-700">Visible to Customer</span>
                            </label>
                            <span class="text-xs text-gray-500">(customer can download this from their portal)</span>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" :disabled="docForm.processing"
                                    class="px-5 py-2 bg-electric-600 text-white text-sm font-semibold rounded-lg hover:bg-electric-700 disabled:opacity-50 transition">
                                {{ docForm.processing ? 'Uploading…' : 'Upload' }}
                            </button>
                            <button type="button" @click="showUploadForm = false"
                                    class="px-5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Documents List -->
                <div class="divide-y divide-gray-100">
                    <div v-if="!job.documents || job.documents.length === 0" class="px-6 py-8 text-center text-gray-400 text-sm">
                        No documents uploaded yet. Click "Upload Document" to add a diagnostic report or file.
                    </div>
                    <div v-for="doc in job.documents" :key="doc.id"
                         class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- File type icon -->
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                 :class="doc.mime_type?.includes('pdf') ? 'bg-red-100' : doc.mime_type?.includes('image') ? 'bg-blue-100' : 'bg-gray-100'">
                                <svg v-if="doc.mime_type?.includes('pdf')" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else-if="doc.mime_type?.includes('image')" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <svg v-else class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ doc.title }}</p>
                                <p class="text-xs text-gray-500">{{ doc.document_type?.replace(/_/g, ' ') }} · {{ doc.file_name }} · {{ humanSize(doc.file_size) }}</p>
                                <p v-if="doc.description" class="text-xs text-gray-400 truncate">{{ doc.description }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <!-- Customer visible badge / toggle -->
                            <button @click="toggleVisibility(doc.id)"
                                    :title="doc.visible_to_customer ? 'Click to hide from customer' : 'Click to share with customer'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition"
                                    :class="doc.visible_to_customer ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                {{ doc.visible_to_customer ? 'Customer can see' : 'Hidden' }}
                            </button>
                            <!-- Download -->
                            <a :href="`/storage/${doc.file_path}`" download
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-electric-50 text-electric-700 text-xs font-semibold rounded-lg hover:bg-electric-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </a>
                            <!-- Delete -->
                            <button @click="deleteDocument(doc.id)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
