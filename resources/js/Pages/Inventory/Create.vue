<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps<{ categories: string[] }>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    name: '',
    part_number: '',
    category: '',
    description: '',
    cost_price: '',
    selling_price: '',
    stock_quantity: 0,
    minimum_stock: 5,
    supplier: '',
    location: '',
})

function submit() { form.post(route('/inventory')) }
</script>

<template>
    <Head title="Add Part" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Add New Part</h1>
                <Link :href="route('/inventory')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Part Name *</label>
                        <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 text-sm" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Part Number</label>
                        <input v-model="form.part_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. BP-001" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input v-model="form.category" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Brakes, Filters, Oil" list="cats" />
                        <datalist id="cats"><option v-for="c in categories" :key="c" :value="c" /></datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">£</span>
                            <input v-model="form.cost_price" type="number" step="0.01" class="w-full pl-7 rounded-lg border-gray-300 text-sm" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">£</span>
                            <input v-model="form.selling_price" type="number" step="0.01" class="w-full pl-7 rounded-lg border-gray-300 text-sm" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock</label>
                        <input v-model="form.stock_quantity" type="number" min="0" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level</label>
                        <input v-model="form.minimum_stock" type="number" min="0" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <input v-model="form.supplier" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage Location</label>
                        <input v-model="form.location" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Bay A, Shelf 3" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link :href="route('/inventory')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Add Part' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
