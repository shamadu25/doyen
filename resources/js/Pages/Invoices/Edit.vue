<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ invoice: any; customers: any[]; vehicles: any[] }>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    customer_id: props.invoice.customer_id || '',
    vehicle_id: props.invoice.vehicle_id || '',
    invoice_date: props.invoice.invoice_date?.split('T')[0] || '',
    due_date: props.invoice.due_date?.split('T')[0] || '',
    notes: props.invoice.notes || '',
    items: (props.invoice.items || props.invoice.invoice_items || []).map((i: any) => ({
        id: i.id,
        description: i.description,
        quantity: i.quantity,
        unit_price: i.unit_price,
        type: i.type || 'labour',
    })),
})

if (!form.items.length) form.items.push({ id: null, description: '', quantity: 1, unit_price: '', type: 'labour' })

const filteredVehicles = computed(() => {
    if (!form.customer_id) return props.vehicles
    return props.vehicles.filter((v: any) => v.customer_id == form.customer_id)
})

function addItem() {
    form.items.push({ id: null, description: '', quantity: 1, unit_price: '', type: 'labour' })
}
function removeItem(idx: number) {
    if (form.items.length > 1) form.items.splice(idx, 1)
}

const subtotal = computed(() => form.items.reduce((s: number, i: any) => s + (parseFloat(i.quantity || 0) * parseFloat(i.unit_price || 0)), 0))
const vat = computed(() => subtotal.value * 0.2)
const total = computed(() => subtotal.value + vat.value)
function fmt(n: number) { return '£' + n.toFixed(2) }

function submit() { form.put(route(`/invoices/${props.invoice.id}`)) }
</script>

<template>
    <Head title="Edit Invoice" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Edit {{ invoice.invoice_number }}</h1>
                <Link :href="route(`/invoices/${invoice.id}`)" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                            <select v-model="form.customer_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="">Select customer</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                            <select v-model="form.vehicle_id" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Select vehicle</option>
                                <option v-for="v in filteredVehicles" :key="v.id" :value="v.id">{{ v.registration_number }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                            <input v-model="form.invoice_date" type="date" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input v-model="form.due_date" type="date" class="w-full rounded-lg border-gray-300 text-sm" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                        <button type="button" @click="addItem" class="text-sm text-electric-600 hover:text-electric-700 font-medium">+ Add Item</button>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-3 items-start">
                            <div class="col-span-5"><input v-model="item.description" type="text" placeholder="Description" class="w-full rounded-lg border-gray-300 text-sm" required /></div>
                            <div class="col-span-2">
                                <select v-model="item.type" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="labour">Labour</option>
                                    <option value="part">Part</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-span-2"><input v-model="item.quantity" type="number" min="1" step="0.01" class="w-full rounded-lg border-gray-300 text-sm" required /></div>
                            <div class="col-span-2"><input v-model="item.unit_price" type="number" step="0.01" class="w-full rounded-lg border-gray-300 text-sm" required /></div>
                            <div class="col-span-1 flex items-center justify-center pt-1">
                                <button v-if="form.items.length > 1" type="button" @click="removeItem(idx)" class="text-red-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 border-t pt-4 space-y-2 max-w-xs ml-auto text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal:</span><span>{{ fmt(subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">VAT (20%):</span><span>{{ fmt(vat) }}</span></div>
                        <div class="flex justify-between border-t pt-2"><span class="font-semibold">Total:</span><span class="font-bold text-electric-600 text-lg">{{ fmt(total) }}</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route(`/invoices/${invoice.id}`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Update Invoice' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
