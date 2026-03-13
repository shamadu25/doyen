<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps<{
    parts: any
    filters: { search?: string; category?: string; low_stock?: string }
    categories: string[]
}>()

const search = ref(props.filters.search || '')
const category = ref(props.filters.category || '')
const lowStock = ref(props.filters.low_stock || '')

const route = inject<(path: string) => string>('route', (p) => p)

let debounce: any
watch([search, category, lowStock], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('/inventory'), { search: search.value, category: category.value, low_stock: lowStock.value }, { preserveState: true, replace: true })
    }, 300)
})

function fmt(amount: any) { return '£' + parseFloat(amount || 0).toFixed(2) }
</script>

<template>
    <Head title="Inventory" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage parts and stock levels</p>
                </div>
                <Link :href="route('/inventory/create')" class="bg-electric-600 hover:bg-electric-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Add Part</Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
                    <input v-model="search" type="text" placeholder="Search parts..." class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600 w-64" />
                    <select v-model="category" class="rounded-lg border-gray-300 text-sm focus:border-electric-600 focus:ring-electric-600">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input v-model="lowStock" type="checkbox" true-value="1" false-value="" class="rounded border-gray-300 text-electric-600 focus:ring-electric-600" />
                        Low Stock Only
                    </label>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sell Price</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Min Level</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="part in parts.data" :key="part.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Link :href="route(`/inventory/${part.id}`)" class="text-sm font-medium text-electric-600 hover:text-electric-700">{{ part.name }}</Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">{{ part.part_number || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ part.category || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">{{ fmt(part.cost_price || part.purchase_price) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">{{ fmt(part.selling_price || part.price) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        (part.stock_quantity || 0) <= (part.minimum_stock || part.reorder_level || 0) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                    ]">{{ part.stock_quantity || 0 }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ part.minimum_stock || part.reorder_level || 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    <Link :href="route(`/inventory/${part.id}`)" class="text-electric-600 hover:text-electric-700 text-sm">View</Link>
                                    <Link :href="route(`/inventory/${part.id}/edit`)" class="text-gray-600 hover:text-gray-700 text-sm">Edit</Link>
                                </td>
                            </tr>
                            <tr v-if="!parts.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No parts found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="parts.links" :from="parts.from" :to="parts.to" :total="parts.total" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
