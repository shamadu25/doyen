<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ part: any; transactions: any[] }>()

const route = inject<(path: string) => string>('route', (p) => p)

function fmt(amount: any) { return '£' + parseFloat(amount || 0).toFixed(2) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '-' }

const showAdjust = ref(false)
const adjustForm = useForm({
    type: 'in',
    quantity: 1,
    reason: '',
})

function adjustStock() {
    adjustForm.post(route(`/inventory/${props.part.id}/adjust-stock`), {
        preserveScroll: true,
        onSuccess: () => { adjustForm.reset(); showAdjust.value = false },
    })
}

const margin = ((parseFloat(props.part.selling_price || props.part.price || 0) - parseFloat(props.part.cost_price || props.part.purchase_price || 0)) / parseFloat(props.part.selling_price || props.part.price || 1) * 100).toFixed(1)
</script>

<template>
    <Head :title="part.name" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ part.name }}</h1>
                    <p class="mt-1 text-sm text-gray-500 font-mono">{{ part.part_number || '-' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route(`/inventory/${part.id}/edit`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button @click="showAdjust = !showAdjust" class="px-4 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700">Adjust Stock</button>
                    <Link :href="route('/inventory')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Part Details -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Part Details</h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><span class="text-gray-500">Category:</span><span class="ml-2 text-gray-900">{{ part.category || '-' }}</span></div>
                            <div><span class="text-gray-500">Supplier:</span><span class="ml-2 text-gray-900">{{ part.supplier || '-' }}</span></div>
                            <div><span class="text-gray-500">Location:</span><span class="ml-2 text-gray-900">{{ part.location || '-' }}</span></div>
                            <div><span class="text-gray-500">Cost Price:</span><span class="ml-2 text-gray-900">{{ fmt(part.cost_price || part.purchase_price) }}</span></div>
                            <div><span class="text-gray-500">Selling Price:</span><span class="ml-2 text-gray-900 font-medium">{{ fmt(part.selling_price || part.price) }}</span></div>
                            <div><span class="text-gray-500">Margin:</span><span class="ml-2 text-gray-900">{{ margin }}%</span></div>
                        </div>
                        <div v-if="part.description" class="mt-4 pt-4 border-t">
                            <span class="text-sm text-gray-500">Description:</span>
                            <p class="mt-1 text-sm text-gray-700">{{ part.description }}</p>
                        </div>
                    </div>

                    <!-- Adjust Stock Form -->
                    <div v-if="showAdjust" class="bg-white rounded-xl border border-electric-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Adjust Stock</h2>
                        <form @submit.prevent="adjustStock" class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select v-model="adjustForm.type" class="w-full rounded-lg border-gray-300 text-sm">
                                        <option value="in">Stock In</option>
                                        <option value="out">Stock Out</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input v-model="adjustForm.quantity" type="number" min="1" class="w-full rounded-lg border-gray-300 text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                                    <input v-model="adjustForm.reason" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Purchase, Return" />
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showAdjust = false" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                                <button type="submit" :disabled="adjustForm.processing" class="px-4 py-2 text-sm text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">Adjust</button>
                            </div>
                        </form>
                    </div>

                    <!-- Transaction History -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Stock History</h2>
                        <table v-if="transactions?.length" class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left py-2 text-gray-500 font-medium">Date</th>
                                    <th class="text-left py-2 text-gray-500 font-medium">Type</th>
                                    <th class="text-right py-2 text-gray-500 font-medium">Qty</th>
                                    <th class="text-left py-2 text-gray-500 font-medium">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="t in transactions" :key="t.id">
                                    <td class="py-2 text-gray-600">{{ fmtDate(t.created_at) }}</td>
                                    <td class="py-2"><span :class="t.type === 'in' ? 'text-green-600' : 'text-red-600'" class="font-medium capitalize">{{ t.type }}</span></td>
                                    <td class="py-2 text-right font-medium" :class="t.type === 'in' ? 'text-green-600' : 'text-red-600'">{{ t.type === 'in' ? '+' : '-' }}{{ t.quantity }}</td>
                                    <td class="py-2 text-gray-600">{{ t.reason || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-gray-400 text-center py-4">No stock transactions recorded</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-center">
                        <p class="text-sm text-gray-500 mb-1">Current Stock</p>
                        <div :class="['text-4xl font-bold', (part.stock_quantity || 0) <= (part.minimum_stock || part.reorder_level || 0) ? 'text-red-600' : 'text-green-600']">
                            {{ part.stock_quantity || 0 }}
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Min level: {{ part.minimum_stock || part.reorder_level || 0 }}</p>
                        <div v-if="(part.stock_quantity || 0) <= (part.minimum_stock || part.reorder_level || 0)" class="mt-3 p-2 bg-red-50 rounded-lg">
                            <p class="text-xs font-medium text-red-700">Low Stock Warning</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Stock Value</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">At Cost:</span>
                                <span class="font-medium">{{ fmt((part.stock_quantity || 0) * parseFloat(part.cost_price || part.purchase_price || 0)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">At Sale:</span>
                                <span class="font-medium text-green-600">{{ fmt((part.stock_quantity || 0) * parseFloat(part.selling_price || part.price || 0)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
