<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { inject } from 'vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface Service {
    id: number
    name: string
    code: string
    category: string
    default_price: string
    cost_price: string | null
    estimated_duration_minutes: number | null
    vat_rate: string
    is_active: boolean
    is_approved: boolean
    show_on_website: boolean
    requires_booking: boolean
    icon: string | null
    sort_order: number
    description: string | null
}

interface PaginatedServices {
    data: Service[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    links: Array<{ url: string | null; label: string; active: boolean }>
}

const props = defineProps<{
    services: PaginatedServices
    categories: string[]
    stats: { total: number; active: number; approved: number; on_website: number }
    filters: { search?: string; category?: string; status?: string }
}>()

// Filters
const search   = ref(props.filters.search   ?? '')
const category = ref(props.filters.category ?? '')
const status   = ref(props.filters.status   ?? '')

function applyFilters() {
    router.get(route('/services'), {
        search:   search.value   || undefined,
        category: category.value || undefined,
        status:   status.value   || undefined,
    }, { preserveState: true, replace: true })
}

function clearFilters() {
    search.value = ''
    category.value = ''
    status.value = ''
    router.get(route('/services'), {}, { preserveState: false })
}

// Selection
const selected = ref<number[]>([])
const allSelected = computed(() => props.services.data.length > 0 &&
    props.services.data.every(s => selected.value.includes(s.id)))

function toggleAll() {
    if (allSelected.value) {
        selected.value = []
    } else {
        selected.value = props.services.data.map(s => s.id)
    }
}

function toggleOne(id: number) {
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter(x => x !== id)
    } else {
        selected.value.push(id)
    }
}

// Bulk action
const bulkAction = ref('')
const bulkSaving = ref(false)

function runBulkAction() {
    if (!bulkAction.value || selected.value.length === 0) return
    if (bulkAction.value === 'delete' && !confirm(`Delete ${selected.value.length} service(s)? This cannot be undone.`)) return
    bulkSaving.value = true
    router.post(route('/services/bulk-action'), {
        action: bulkAction.value,
        service_ids: selected.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            bulkSaving.value = false
            bulkAction.value = ''
            selected.value = []
        },
    })
}

// Delete single
function deleteSingle(service: Service) {
    if (!confirm(`Delete "${service.name}"? This cannot be undone.`)) return
    router.delete(route(`/services/${service.id}`), { preserveScroll: true })
}

// Toggle helpers
function toggleApprove(service: Service) {
    router.post(route(`/services/${service.id}/toggle-approve`), {}, { preserveScroll: true })
}
function toggleWebsite(service: Service) {
    router.post(route(`/services/${service.id}/toggle-website`), {}, { preserveScroll: true })
}
function toggleActive(service: Service) {
    router.post(route(`/services/${service.id}/toggle-active`), {}, { preserveScroll: true })
}

const categoryColors: Record<string, string> = {
    'Maintenance & MOT': 'blue',
    'General Vehicle Repairs': 'indigo',
    'Advanced Diagnostics': 'purple',
    'Airbag & Safety': 'red',
    'ECU Remapping': 'amber',
    'Emission Services': 'green',
    'Mileage Correction': 'slate',
    'Electrical': 'cyan',
    'Commercial & Fleet': 'gray',
}

function categoryBadgeClass(cat: string) {
    const c = categoryColors[cat] ?? 'gray'
    return `bg-${c}-100 text-${c}-700`
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6 px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Service Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage, approve and publish services to your website</p>
                </div>
                <Link :href="route('/services/create')"
                      class="inline-flex items-center gap-2 px-5 py-2.5 bg-electric-600 hover:bg-electric-700 text-white font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Service
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Total Services</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Active</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-electric-600">{{ stats.approved }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Approved</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ stats.on_website }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">On Website</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <input v-model="search" @keyup.enter="applyFilters" type="text"
                               placeholder="Name, code or category…"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                        <select v-model="category" class="rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select v-model="status" class="rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending Approval</option>
                            <option value="website">On Website</option>
                        </select>
                    </div>
                    <button @click="applyFilters"
                            class="px-4 py-2 bg-electric-600 hover:bg-electric-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <button @click="clearFilters"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm border border-gray-300 rounded-lg transition">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div v-if="selected.length > 0"
                 class="bg-electric-50 border border-electric-200 rounded-xl p-3 mb-4 flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-electric-700">{{ selected.length }} selected</span>
                <select v-model="bulkAction" class="rounded-lg border-electric-300 shadow-sm text-sm bg-white">
                    <option value="">Choose action…</option>
                    <option value="approve">Approve</option>
                    <option value="unapprove">Unapprove</option>
                    <option value="enable">Enable</option>
                    <option value="disable">Disable</option>
                    <option value="show_website">Publish to Website</option>
                    <option value="hide_website">Hide from Website</option>
                    <option value="delete">Delete</option>
                </select>
                <button @click="runBulkAction" :disabled="!bulkAction || bulkSaving"
                        class="px-4 py-1.5 bg-electric-600 hover:bg-electric-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                    {{ bulkSaving ? 'Working…' : 'Apply' }}
                </button>
                <button @click="selected = []" class="text-sm text-gray-500 hover:text-gray-700">Deselect all</button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="w-10 px-4 py-3">
                                    <input type="checkbox" :checked="allSelected" @change="toggleAll"
                                           class="rounded border-gray-300 text-electric-600" />
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Service</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden md:table-cell">Category</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Price</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 hidden lg:table-cell">Active</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 hidden lg:table-cell">Approved</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 hidden xl:table-cell">Website</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="services.data.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                    No services found. <Link :href="route('/services/create')" class="text-electric-600 font-medium">Add the first one →</Link>
                                </td>
                            </tr>
                            <tr v-for="service in services.data" :key="service.id"
                                :class="['hover:bg-gray-50 transition', selected.includes(service.id) ? 'bg-electric-50' : '']">
                                <td class="px-4 py-3">
                                    <input type="checkbox" :checked="selected.includes(service.id)"
                                           @change="toggleOne(service.id)"
                                           class="rounded border-gray-300 text-electric-600" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span v-if="service.icon" class="text-xl">{{ service.icon }}</span>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ service.name }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ service.code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ service.category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">
                                    £{{ Number(service.default_price).toFixed(2) }}
                                </td>
                                <!-- Active toggle -->
                                <td class="px-4 py-3 text-center hidden lg:table-cell">
                                    <button @click="toggleActive(service)"
                                            :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition',
                                                     service.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']">
                                        {{ service.is_active ? '✓ Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <!-- Approved toggle -->
                                <td class="px-4 py-3 text-center hidden lg:table-cell">
                                    <button @click="toggleApprove(service)"
                                            :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition',
                                                     service.is_approved ? 'bg-electric-100 text-electric-700 hover:bg-electric-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200']">
                                        {{ service.is_approved ? '✓ Approved' : 'Pending' }}
                                    </button>
                                </td>
                                <!-- Website toggle -->
                                <td class="px-4 py-3 text-center hidden xl:table-cell">
                                    <button @click="toggleWebsite(service)"
                                            :disabled="!service.is_approved || !service.is_active"
                                            :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition disabled:opacity-40 disabled:cursor-not-allowed',
                                                     service.show_on_website ? 'bg-purple-100 text-purple-700 hover:bg-purple-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']">
                                        {{ service.show_on_website ? '🌐 Live' : 'Hidden' }}
                                    </button>
                                </td>
                                <!-- Actions -->
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route(`/services/${service.id}/edit`)"
                                              class="p-1.5 text-gray-400 hover:text-electric-600 transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button @click="deleteSingle(service)"
                                                class="p-1.5 text-gray-400 hover:text-red-600 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="services.last_page > 1" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500">
                    <span>Showing {{ services.from }}–{{ services.to }} of {{ services.total }}</span>
                    <div class="flex gap-1">
                        <template v-for="link in services.links" :key="link.label">
                            <Link v-if="link.url"
                                  :href="link.url"
                                  :class="['px-3 py-1 rounded-lg border transition',
                                           link.active ? 'bg-electric-600 text-white border-electric-600' : 'border-gray-300 hover:bg-gray-50']"
                                  v-html="link.label" />
                            <span v-else class="px-3 py-1 text-gray-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
