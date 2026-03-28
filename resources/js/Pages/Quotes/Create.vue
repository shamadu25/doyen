<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref, computed, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ customers: any[], services: any[], parts: any[], defaultVatRate: number }>()
const route = inject<(p: string) => string>('route', p => p)

const form = ref({
    customer_id: '',
    vehicle_id: '',
    quote_date: new Date().toISOString().split('T')[0],
    validity_days: 30,
    description: '',
    notes: '',
    discount_percentage: 0,
    items: [] as any[],
})
const customerVehicles = ref<any[]>([])
const errors = ref<any>({})
const submitting = ref(false)

watch(() => form.value.customer_id, (id) => {
    form.value.vehicle_id = ''
    if (id) {
        const c = props.customers.find(c => c.id == id)
        customerVehicles.value = c?.vehicles || []
    }
})

function addItem(type: string) {
    form.value.items.push({ item_type: type, description: '', quantity: 1, unit_price: 0, service_id: null, part_id: null, tax_exempt: false })
}

function removeItem(i: number) {
    form.value.items.splice(i, 1)
}

function onServiceChange(item: any) {
    const s = props.services.find(s => s.id == item.service_id)
    if (s) { item.description = s.name; item.unit_price = s.price ?? 0 }
}

function onPartChange(item: any) {
    const p = props.parts.find(p => p.id == item.part_id)
    if (p) { item.description = p.name; item.unit_price = p.sale_price ?? p.cost_price ?? 0 }
}

const vatRate = computed(() => props.defaultVatRate ?? 20)
const subtotal = computed(() => form.value.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0))
const discount = computed(() => subtotal.value * ((form.value.discount_percentage || 0) / 100))
const discountFactor = computed(() => 1 - ((form.value.discount_percentage || 0) / 100))
const lineNet = (item: any) => (Number(item.quantity) || 0) * (Number(item.unit_price) || 0)
const lineVat = (item: any) => item.tax_exempt ? 0 : lineNet(item) * discountFactor.value * (vatRate.value / 100)
const lineGross = (item: any) => (lineNet(item) * discountFactor.value) + lineVat(item)
const vat = computed(() => form.value.items.reduce((s, i) => {
    if (i.tax_exempt) return s
    return s + lineVat(i)
}, 0))
const total = computed(() => subtotal.value - discount.value + vat.value)

function fmt(v: number) { return '£' + v.toFixed(2) }

async function submit() {
    submitting.value = true
    errors.value = {}
    router.post(route('/quotes'), {
        ...form.value,
        items: form.value.items.map((i: any) => ({
            ...i,
            vat_rate: i.tax_exempt ? 0 : vatRate.value,
            tax_exempt: !!i.tax_exempt,
        }))
    }, {
        onError: (e) => { errors.value = e; submitting.value = false },
        onSuccess: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="New Quote" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route('/quotes')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">New Quote</h1>
            </div>

            <div v-if="errors.general" class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ errors.general }}</div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer & Vehicle -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Customer & Vehicle</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                            <select v-model="form.customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600">
                                <option value="">Select customer...</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="errors.customer_id" class="text-red-500 text-xs mt-1">{{ errors.customer_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                            <select v-model="form.vehicle_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" :disabled="!form.customer_id">
                                <option value="">No vehicle / TBC</option>
                                <option v-for="v in customerVehicles" :key="v.id" :value="v.id">{{ v.registration_number }} – {{ v.make }} {{ v.model }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quote Date *</label>
                            <input v-model="form.quote_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid for (days)</label>
                            <input v-model.number="form.validity_days" type="number" min="1" max="365" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input v-model="form.description" type="text" placeholder="Brief description of work..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600" />
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                        <button type="button" @click="addItem('labour')" class="text-sm text-electric-600 hover:text-electric-700 font-medium">+ Add Item</button>
                    </div>
                    <p v-if="errors.items" class="text-red-500 text-xs mb-2">{{ errors.items }}</p>
                    <p class="text-xs text-gray-500 mb-3">UK VAT display: unit prices are ex. VAT, VAT is shown per line, and line totals are inc. VAT.</p>

                    <!-- Column headers -->
                    <div class="grid grid-cols-12 gap-3 text-xs font-medium text-gray-500 mb-1 px-0.5">
                        <div class="col-span-4">Description</div>
                        <div class="col-span-2">Type</div>
                        <div class="col-span-1">Qty</div>
                        <div class="col-span-2">Unit Price</div>
                        <div class="col-span-2 text-center">VAT / Tax Status</div>
                        <div class="col-span-1"></div>
                    </div>

                    <div v-if="!form.items.length" class="text-center py-8 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg">
                        Click <strong>+ Add Item</strong> to get started
                    </div>

                    <div class="space-y-3">
                        <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-3 items-center">
                            <div class="col-span-4">
                                <select v-if="item.item_type === 'service'" v-model="item.service_id" @change="onServiceChange(item)" class="w-full rounded-lg border-gray-300 text-sm mb-1">
                                    <option value="">Pick service...</option>
                                    <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                                <select v-else-if="item.item_type === 'part'" v-model="item.part_id" @change="onPartChange(item)" class="w-full rounded-lg border-gray-300 text-sm mb-1">
                                    <option value="">Pick part...</option>
                                    <option v-for="p in parts" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <input v-model="item.description" type="text" placeholder="Description" class="w-full rounded-lg border-gray-300 text-sm" />
                            </div>
                            <div class="col-span-2">
                                <select v-model="item.item_type" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="labour">Labour</option>
                                    <option value="part">Part</option>
                                    <option value="service">Service</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <input v-model.number="item.quantity" type="number" min="1" step="0.01" placeholder="Qty" class="w-full rounded-lg border-gray-300 text-sm" />
                            </div>
                            <div class="col-span-2">
                                <input v-model.number="item.unit_price" type="number" step="0.01" placeholder="£0.00" class="w-full rounded-lg border-gray-300 text-sm" />
                            </div>
                            <div class="col-span-2">
                                <label :for="`vat-${i}`" class="flex items-center justify-center gap-1.5 h-5">
                                    <input type="checkbox" v-model="item.tax_exempt" :id="`vat-${i}`" class="rounded border-gray-300 text-electric-600 focus:ring-electric-500" />
                                    <span class="text-xs cursor-pointer select-none" :class="item.tax_exempt ? 'text-amber-700 font-medium' : 'text-gray-600'">Tax Exempt</span>
                                </label>
                                <div class="text-[11px] text-center mt-1" :class="item.tax_exempt ? 'text-amber-700' : 'text-gray-500'">
                                    {{ item.tax_exempt ? 'VAT £0.00' : `${vatRate}% · ${fmt(lineVat(item))}` }}
                                </div>
                                <div class="text-[11px] text-center text-gray-500">Line total {{ fmt(lineGross(item)) }}</div>
                            </div>
                            <div class="col-span-1 flex items-center justify-center">
                                <button type="button" @click="removeItem(i)" class="text-red-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 border-t pt-4 space-y-2 max-w-xs ml-auto text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal:</span><span class="font-medium">{{ fmt(subtotal) }}</span></div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-600">Discount:</span>
                            <div class="flex items-center gap-1.5">
                                <input v-model.number="form.discount_percentage" type="number" min="0" max="100" step="0.5" class="w-14 rounded border-gray-300 text-xs text-right" />
                                <span class="text-xs text-gray-500">%</span>
                                <span class="font-medium text-red-500">-{{ fmt(discount) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between"><span class="text-gray-600">VAT:</span><span class="font-medium">{{ fmt(vat) }}</span></div>
                        <div class="flex justify-between border-t pt-2"><span class="font-semibold">Total:</span><span class="font-bold text-electric-600 text-lg">{{ fmt(total) }}</span></div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Internal Notes</label>
                    <textarea v-model="form.notes" rows="3" placeholder="Notes visible to staff only..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-electric-600"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <Link :href="route('/quotes')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</Link>
                    <button type="submit" :disabled="submitting" class="px-6 py-2 bg-electric-600 text-white rounded-lg hover:bg-electric-700 text-sm font-medium disabled:opacity-50">
                        {{ submitting ? 'Creating...' : 'Create Quote' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
