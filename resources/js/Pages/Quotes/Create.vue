<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { inject, ref, computed, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ customers: any[], services: any[], parts: any[] }>()
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
    form.value.items.push({ item_type: type, description: '', quantity: 1, unit_price: 0, service_id: null, part_id: null })
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

const vatRate = 20
const subtotal = computed(() => form.value.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0))
const discount = computed(() => subtotal.value * ((form.value.discount_percentage || 0) / 100))
const vat = computed(() => (subtotal.value - discount.value) * (vatRate / 100))
const total = computed(() => subtotal.value - discount.value + vat.value)

function fmt(v: number) { return '£' + v.toFixed(2) }

async function submit() {
    submitting.value = true
    errors.value = {}
    router.post(route('/quotes'), form.value, {
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
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Line Items</h2>
                        <div class="flex gap-2">
                            <button type="button" @click="addItem('labour')" class="px-3 py-1.5 text-xs font-medium bg-electric-50 text-electric-700 rounded-lg hover:bg-electric-100">+ Labour</button>
                            <button type="button" @click="addItem('part')" class="px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 rounded-lg hover:bg-green-100">+ Part</button>
                            <button type="button" @click="addItem('service')" class="px-3 py-1.5 text-xs font-medium bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100">+ Service</button>
                        </div>
                    </div>
                    <p v-if="errors.items" class="text-red-500 text-xs">{{ errors.items }}</p>

                    <div v-if="!form.items.length" class="text-center py-8 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg">
                        Add labour, parts or services using the buttons above
                    </div>

                    <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 items-start p-3 bg-gray-50 rounded-lg">
                        <div class="col-span-1">
                            <span :class="['inline-block px-1.5 py-0.5 rounded text-xs font-medium', item.item_type === 'labour' ? 'bg-electric-100 text-electric-700' : item.item_type === 'part' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700']">
                                {{ item.item_type[0].toUpperCase() }}
                            </span>
                        </div>
                        <div class="col-span-5">
                            <select v-if="item.item_type === 'service'" v-model="item.service_id" @change="onServiceChange(item)" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-1">
                                <option value="">Pick service...</option>
                                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <select v-else-if="item.item_type === 'part'" v-model="item.part_id" @change="onPartChange(item)" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-1">
                                <option value="">Pick part...</option>
                                <option v-for="p in parts" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <input v-model="item.description" type="text" placeholder="Description" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                        </div>
                        <div class="col-span-2">
                            <input v-model.number="item.quantity" type="number" min="1" placeholder="Qty" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" />
                        </div>
                        <div class="col-span-3">
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">£</span>
                                <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="0.00" class="w-full pl-6 pr-2 py-1.5 border border-gray-300 rounded text-sm" />
                            </div>
                        </div>
                        <div class="col-span-1 text-right">
                            <button type="button" @click="removeItem(i)" class="text-red-400 hover:text-red-600 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div v-if="form.items.length" class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span><span>{{ fmt(subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>Discount</span>
                            <div class="flex items-center gap-2">
                                <input v-model.number="form.discount_percentage" type="number" min="0" max="100" step="0.5" class="w-16 px-2 py-1 border border-gray-300 rounded text-xs text-right" />
                                <span>%</span>
                                <span class="text-red-500">-{{ fmt(discount) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>VAT (20%)</span><span>{{ fmt(vat) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 border-t pt-2">
                            <span>Total</span><span>{{ fmt(total) }}</span>
                        </div>
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
