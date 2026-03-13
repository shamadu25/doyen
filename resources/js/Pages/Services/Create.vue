<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref, inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const route = inject<(path: string) => string>('route', (p) => p)

const props = defineProps<{
    categories: string[]
}>()

const newCategory = ref('')
const showNewCategoryInput = ref(false)

const form = useForm({
    name: '',
    code: '',
    description: '',
    website_description: '',
    category: '',
    default_price: '',
    cost_price: '',
    estimated_duration_minutes: '',
    vat_rate: '20',
    is_active: true,
    requires_booking: true,
    is_approved: false,
    show_on_website: false,
    icon: '',
    sort_order: '0',
})

const iconSuggestions = ['🔧', '🚗', '🛞', '⚙️', '🔍', '💡', '🛡️', '⚡', '♻️', '🛠️', '🔩', '🏎️', '🚘', '💧', '🌡️', '🔌', '📋', '✅']

function selectIcon(emoji: string) {
    form.icon = emoji
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
    form.post(route('/services'), {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Add Service" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add Service</h1>
                    <p class="mt-1 text-sm text-gray-500">Create a new service offering for your garage</p>
                </div>
                <Link :href="route('/services')" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Back to Services
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Full Service"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                                placeholder="Auto-generated if blank"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                :class="{ 'border-red-300': form.errors.code }"
                            />
                            <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
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
                                <button type="button" @click="showNewCategoryInput = false; form.category = ''" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">✕</button>
                            </div>
                            <p v-if="form.category && showNewCategoryInput === false && !showNewCategoryInput" class="mt-1 text-xs text-gray-500">Using: <strong>{{ form.category }}</strong></p>
                            <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                        </div>

                        <!-- Icon -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon (emoji)</label>
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="form.icon"
                                    type="text"
                                    placeholder="🔧"
                                    class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-2xl text-center"
                                    maxlength="10"
                                />
                                <div class="flex flex-wrap gap-1">
                                    <button
                                        v-for="emoji in iconSuggestions"
                                        :key="emoji"
                                        type="button"
                                        @click="selectIcon(emoji)"
                                        class="text-xl p-1 rounded hover:bg-gray-100 transition-colors"
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
                                placeholder="Internal notes about this service..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            ></textarea>
                        </div>

                        <!-- Website Description -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website Description</label>
                            <textarea
                                v-model="form.website_description"
                                rows="3"
                                placeholder="Description shown to customers on your website..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Price (£) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 text-sm">£</span>
                                <input
                                    v-model="form.default_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                                    placeholder="0.00"
                                    class="w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                                placeholder="60"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                        </div>
                    </div>
                </div>

                <!-- Flags Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Status & Visibility</h2>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Active</span>
                                <p class="text-xs text-gray-500">Service is available for use in job cards and bookings</p>
                            </div>
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.is_active ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.is_active ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Requires Booking</span>
                                <p class="text-xs text-gray-500">Customers must book an appointment for this service</p>
                            </div>
                            <button
                                type="button"
                                @click="form.requires_booking = !form.requires_booking"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.requires_booking ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.requires_booking ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Approved</span>
                                <p class="text-xs text-gray-500">Service has been reviewed and approved for use</p>
                            </div>
                            <button
                                type="button"
                                @click="form.is_approved = !form.is_approved"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.is_approved ? 'bg-green-600' : 'bg-gray-200'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.is_approved ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Show on Website</span>
                                <p class="text-xs text-gray-500">Display this service on your public website (requires Approved + Active)</p>
                            </div>
                            <button
                                type="button"
                                @click="form.show_on_website = !form.show_on_website"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.show_on_website ? 'bg-purple-600' : 'bg-gray-200'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.show_on_website ? 'translate-x-6' : 'translate-x-1']" />
                            </button>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
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
                        {{ form.processing ? 'Saving...' : 'Create Service' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
