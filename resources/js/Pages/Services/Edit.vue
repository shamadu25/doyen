<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { ref, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    service: {
        id: number
        name: string
        code: string | null
        description: string | null
        website_description: string | null
        category: string
        default_price: string | number
        cost_price: string | number | null
        estimated_duration_minutes: number | null
        vat_rate: string | number
        is_active: boolean
        requires_booking: boolean
        is_approved: boolean
        show_on_website: boolean
        icon: string | null
        sort_order: number
        created_at: string
        updated_at: string
    }
    categories: string[]
    readonly: boolean
}>()

const newCategory = ref('')
const showNewCategoryInput = ref(false)
const showDeleteConfirm = ref(false)

const form = useForm({
    name: props.service.name,
    code: props.service.code ?? '',
    description: props.service.description ?? '',
    website_description: props.service.website_description ?? '',
    category: props.service.category,
    default_price: String(props.service.default_price),
    cost_price: props.service.cost_price != null ? String(props.service.cost_price) : '',
    estimated_duration_minutes: props.service.estimated_duration_minutes != null ? String(props.service.estimated_duration_minutes) : '',
    vat_rate: String(props.service.vat_rate),
    is_active: props.service.is_active,
    requires_booking: props.service.requires_booking,
    is_approved: props.service.is_approved,
    show_on_website: props.service.show_on_website,
    icon: props.service.icon ?? '',
    sort_order: String(props.service.sort_order),
})

const iconSuggestions = ['🔧', '🚗', '🛞', '⚙️', '🔍', '💡', '🛡️', '⚡', '♻️', '🛠️', '🔩', '🏎️', '🚘', '💧', '🌡️', '🔌', '📋', '✅']

function selectIcon(emoji: string) {
    if (!props.readonly) form.icon = emoji
}

function onCategoryChange(val: string) {
    if (val === '__new__') {
        showNewCategoryInput.value = true
        form.category = ''
    } else {
        showNewCategoryInput.value = false
    }
}

function applyNewCategory() {
    if (newCategory.value.trim()) {
        form.category = newCategory.value.trim()
        showNewCategoryInput.value = false
    }
}

function submit() {
    form.put(route(`/services/${props.service.id}`))
}

function deleteService() {
    router.delete(route(`/services/${props.service.id}`), {
        onSuccess: () => { showDeleteConfirm.value = false },
    })
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <Head :title="readonly ? `Service: ${service.name}` : `Edit: ${service.name}`" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <span v-if="service.icon" class="text-3xl">{{ service.icon }}</span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ readonly ? service.name : `Edit: ${service.name}` }}
                        </h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500">{{ service.code }}</span>
                            <span class="text-gray-300">·</span>
                            <span class="text-xs text-gray-500">{{ service.category }}</span>
                            <span class="text-gray-300">·</span>
                            <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', service.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
                                {{ service.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="readonly" :href="route(`/services/${service.id}/edit`)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                        Edit
                    </Link>
                    <Link :href="route('/services')" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                        </svg>
                        Back
                    </Link>
                </div>
            </div>

            <!-- Readonly Banner -->
            <div v-if="readonly" class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-6 text-sm text-amber-800">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.58-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                View mode — click Edit to make changes
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name <span v-if="!readonly" class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Code</label>
                            <input
                                v-model="form.code"
                                type="text"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                :class="{ 'border-red-300': form.errors.code }"
                            />
                            <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span v-if="!readonly" class="text-red-500">*</span></label>
                            <div v-if="readonly">
                                <p class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500">{{ form.category }}</p>
                            </div>
                            <template v-else>
                                <select
                                    v-if="!showNewCategoryInput"
                                    v-model="form.category"
                                    @change="onCategoryChange(form.category)"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.category }"
                                >
                                    <option value="">Select category...</option>
                                    <option v-for="cat in props.categories" :key="cat" :value="cat">{{ cat }}</option>
                                    <option value="__new__">+ Add new category</option>
                                </select>
                                <div v-else class="flex gap-2">
                                    <input
                                        v-model="newCategory"
                                        type="text"
                                        placeholder="New category name"
                                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        @keydown.enter.prevent="applyNewCategory"
                                    />
                                    <button type="button" @click="applyNewCategory" class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">OK</button>
                                    <button type="button" @click="showNewCategoryInput = false; form.category = props.service.category" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">✕</button>
                                </div>
                            </template>
                            <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                        </div>

                        <!-- Icon -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon (emoji)</label>
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="form.icon"
                                    type="text"
                                    :disabled="readonly"
                                    class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-2xl text-center disabled:bg-gray-50"
                                    maxlength="10"
                                />
                                <div class="flex flex-wrap gap-1">
                                    <button
                                        v-for="emoji in iconSuggestions"
                                        :key="emoji"
                                        type="button"
                                        :disabled="readonly"
                                        @click="selectIcon(emoji)"
                                        class="text-xl p-1 rounded hover:bg-gray-100 transition-colors disabled:cursor-default"
                                        :class="{ 'bg-blue-100 ring-2 ring-blue-400': form.icon === emoji }"
                                    >{{ emoji }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Internal Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            ></textarea>
                        </div>

                        <!-- Website Description -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website Description</label>
                            <textarea
                                v-model="form.website_description"
                                rows="3"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Pricing & Duration</h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Default Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Price (£) <span v-if="!readonly" class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 text-sm">£</span>
                                <input
                                    v-model="form.default_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :disabled="readonly"
                                    class="w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                    :class="{ 'border-red-300': form.errors.default_price }"
                                />
                            </div>
                            <p v-if="form.errors.default_price" class="mt-1 text-xs text-red-600">{{ form.errors.default_price }}</p>
                        </div>

                        <!-- Cost Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (£)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 text-sm">£</span>
                                <input
                                    v-model="form.cost_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :disabled="readonly"
                                    class="w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                                />
                            </div>
                        </div>

                        <!-- VAT Rate -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%)</label>
                            <input
                                v-model="form.vat_rate"
                                type="number"
                                step="0.1"
                                min="0"
                                max="100"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            />
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Duration (minutes)</label>
                            <input
                                v-model="form.estimated_duration_minutes"
                                type="number"
                                min="0"
                                step="15"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            />
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                :disabled="readonly"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-gray-50 disabled:text-gray-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                        </div>
                    </div>
                </div>

                <!-- Flags Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Status & Visibility</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Active</span>
                                <p class="text-xs text-gray-500">Service is available for use in job cards and bookings</p>
                            </div>
                            <button
                                type="button"
                                :disabled="readonly"
                                @click="!readonly && (form.is_active = !form.is_active)"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.is_active ? 'bg-blue-600' : 'bg-gray-200',
                                    readonly ? 'cursor-default opacity-75' : 'cursor-pointer'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.is_active ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Requires Booking</span>
                                <p class="text-xs text-gray-500">Customers must book an appointment for this service</p>
                            </div>
                            <button
                                type="button"
                                :disabled="readonly"
                                @click="!readonly && (form.requires_booking = !form.requires_booking)"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.requires_booking ? 'bg-blue-600' : 'bg-gray-200',
                                    readonly ? 'cursor-default opacity-75' : 'cursor-pointer'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.requires_booking ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Approved</span>
                                <p class="text-xs text-gray-500">Service has been reviewed and approved for use</p>
                            </div>
                            <button
                                type="button"
                                :disabled="readonly"
                                @click="!readonly && (form.is_approved = !form.is_approved)"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.is_approved ? 'bg-green-600' : 'bg-gray-200',
                                    readonly ? 'cursor-default opacity-75' : 'cursor-pointer'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.is_approved ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Show on Website</span>
                                <p class="text-xs text-gray-500">Display this service on your public website</p>
                            </div>
                            <button
                                type="button"
                                :disabled="readonly"
                                @click="!readonly && (form.show_on_website = !form.show_on_website)"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.show_on_website ? 'bg-purple-600' : 'bg-gray-200',
                                    readonly ? 'cursor-default opacity-75' : 'cursor-pointer'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.show_on_website ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Meta Info (readonly) -->
                <div v-if="readonly" class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Record Info</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Created</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(service.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Last Updated</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(service.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div v-if="!readonly" class="flex items-center justify-between">
                    <!-- Delete -->
                    <div>
                        <button
                            v-if="!showDeleteConfirm"
                            type="button"
                            @click="showDeleteConfirm = true"
                            class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100"
                        >
                            Delete Service
                        </button>
                        <div v-else class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <span class="text-sm text-red-700 font-medium">Confirm delete?</span>
                            <button type="button" @click="deleteService" class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">Yes, Delete</button>
                            <button type="button" @click="showDeleteConfirm = false" class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>

                    <!-- Save -->
                    <div class="flex items-center gap-3">
                        <Link :href="route('/services')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
