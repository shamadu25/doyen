<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ part: any; categories: string[] }>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    name: props.part.name || '',
    part_number: props.part.part_number || '',
    category: props.part.category || '',
    description: props.part.description || '',
    cost_price: props.part.cost_price || props.part.purchase_price || '',
    selling_price: props.part.selling_price || props.part.price || '',
    minimum_stock: props.part.minimum_stock || props.part.reorder_level || 5,
    supplier: props.part.supplier || '',
    location: props.part.location || '',
})

function submit() { form.put(route(`/inventory/${props.part.id}`)) }
</script>

<template>
    <Head title="Edit Part" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Edit {{ part.name }}</h1>
                <Link :href="route(`/inventory/${part.id}`)" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Part Name *</label>
                        <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 text-sm" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Part Number</label>
                        <input v-model="form.part_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input v-model="form.category" type="text" class="w-full rounded-lg border-gray-300 text-sm" list="cats" />
                        <datalist id="cats"><option v-for="c in categories" :key="c" :value="c" /></datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">£</span>
                            <input v-model="form.cost_price" type="number" step="0.01" class="w-full pl-7 rounded-lg border-gray-300 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">£</span>
                            <input v-model="form.selling_price" type="number" step="0.01" class="w-full pl-7 rounded-lg border-gray-300 text-sm" />
                        </div>
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
                        <input v-model="form.location" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link :href="route(`/inventory/${part.id}`)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Update Part' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
