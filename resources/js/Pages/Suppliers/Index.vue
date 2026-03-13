<script setup lang="ts">
import { ref, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    suppliers: {
        data: Array<{
            id: number
            name: string
            contact_person: string | null
            email: string | null
            phone: string | null
            address: string | null
            website: string | null
            status: 'active' | 'inactive'
            notes: string | null
            parts_orders_count: number
            created_at: string
        }>
        links: Array<{ url: string | null; label: string; active: boolean }>
        meta: { current_page: number; last_page: number; total: number; per_page: number }
    }
    stats: { total: number; active: number; inactive: number }
    filters: { search?: string; status?: string }
}>()

const search = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? '')
const deleteTarget = ref<number | null>(null)

function applyFilters() {
    router.get(route('/suppliers'), { search: search.value, status: statusFilter.value }, { preserveState: true, replace: true })
}

function clearFilters() {
    search.value = ''
    statusFilter.value = ''
    router.get(route('/suppliers'), {}, { preserveState: true, replace: true })
}

function confirmDelete(id: number) {
    deleteTarget.value = id
}

function doDelete() {
    if (!deleteTarget.value) return
    router.delete(route(`/suppliers/${deleteTarget.value}`), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<template>
    <Head title="Suppliers" />
    <AuthenticatedLayout>
        <div class="px-4 py-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Suppliers</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage your parts and service suppliers</p>
                </div>
                <Link :href="route('/suppliers/create')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Add Supplier
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
                    <p class="text-xs text-gray-500 mt-1">Active</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-400">{{ stats.inactive }}</p>
                    <p class="text-xs text-gray-500 mt-1">Inactive</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Name, contact, email, phone..."
                        class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select v-model="statusFilter" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Filter</button>
                    <button v-if="filters.search || filters.status" @click="clearFilters" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Clear</button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Supplier</th>
                            <th class="hidden md:table-cell text-left px-4 py-3 font-medium text-gray-600">Contact</th>
                            <th class="hidden sm:table-cell text-left px-4 py-3 font-medium text-gray-600">Phone / Email</th>
                            <th class="hidden md:table-cell text-center px-4 py-3 font-medium text-gray-600">Orders</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="suppliers.data.length === 0">
                            <td colspan="6" class="text-center py-12 text-gray-400">
                                <svg class="mx-auto w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                                </svg>
                                No suppliers found
                            </td>
                        </tr>
                        <tr v-for="s in suppliers.data" :key="s.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ s.name }}</div>
                                <div v-if="s.website" class="text-xs text-blue-500 truncate max-w-48">
                                    <a :href="s.website" target="_blank" rel="noopener" class="hover:underline">{{ s.website }}</a>
                                </div>
                                <p class="sm:hidden text-xs text-gray-500 mt-0.5">{{ s.phone ?? '' }}</p>
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-gray-700">{{ s.contact_person ?? '—' }}</td>
                            <td class="hidden sm:table-cell px-4 py-3">
                                <div class="text-gray-700">{{ s.phone ?? '—' }}</div>
                                <div class="text-gray-500 text-xs">{{ s.email ?? '' }}</div>
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-center">
                                <span class="text-gray-700 font-medium">{{ s.parts_orders_count }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', s.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                    {{ s.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route(`/suppliers/${s.id}/edit`)" class="text-xs px-2.5 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 font-medium">Edit</Link>
                                    <button @click="confirmDelete(s.id)" class="text-xs px-2.5 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div><!-- end overflow-x-auto -->

                <!-- Pagination -->
                <div v-if="suppliers.meta && suppliers.meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 bg-gray-50">
                    <p class="text-sm text-gray-600">
                        Showing {{ (suppliers.meta.current_page - 1) * suppliers.meta.per_page + 1 }}–{{ Math.min(suppliers.meta.current_page * suppliers.meta.per_page, suppliers.meta.total) }} of {{ suppliers.meta.total }}
                    </p>
                    <div class="flex gap-1">
                        <template v-for="link in suppliers.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="['px-3 py-1.5 text-xs rounded-lg font-medium', link.active ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50']"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-xs rounded-lg text-gray-300 bg-gray-50" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirm Modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Supplier</h3>
                <p class="text-sm text-gray-600 mb-5">Are you sure you want to delete this supplier? This cannot be undone.</p>
                <div class="flex gap-3 justify-end">
                    <button @click="deleteTarget = null" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button @click="doDelete" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
