<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const route = inject<(path: string) => string>('route', (p) => p)

interface PortalDocument {
    id: number
    title: string
    document_type: string
    description: string | null
    file_name: string
    mime_type: string
    file_size: string
    is_image: boolean
    is_pdf: boolean
    download_url: string
    job_number: string | null
    vehicle: string | null
    created_at: string
}

const props = defineProps<{
    customer: any
    documents: PortalDocument[]
}>()

const docsByType = computed(() => {
    const grouped: Record<string, PortalDocument[]> = {}
    for (const doc of props.documents) {
        const key = doc.document_type.replace(/_/g, ' ')
        if (!grouped[key]) grouped[key] = []
        grouped[key].push(doc)
    }
    return grouped
})

function typeLabel(type: string): string {
    return type.charAt(0).toUpperCase() + type.slice(1)
}
</script>

<template>
    <Head title="My Documents" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Documents</h1>
                <p class="text-gray-500 text-sm mt-1">Diagnostic reports and files shared by Doyen Auto Services.</p>
            </div>

            <!-- Empty state -->
            <div v-if="documents.length === 0"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Documents Yet</h3>
                <p class="text-gray-500 text-sm">
                    When your diagnostic report or inspection results are ready, they'll appear here for you to download.
                </p>
                <Link :href="route('/customer/bookings/create')"
                      class="inline-flex items-center mt-6 px-5 py-2.5 bg-electric-600 text-white text-sm font-semibold rounded-lg hover:bg-electric-700 transition">
                    Book a Diagnostic
                </Link>
            </div>

            <!-- Documents grid -->
            <template v-else>
                <div v-for="(docs, type) in docsByType" :key="type" class="space-y-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide px-1">
                        {{ typeLabel(type) }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="doc in docs" :key="doc.id"
                             class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-4 hover:shadow-md transition">
                            <!-- Icon -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl"
                                 :class="doc.is_pdf ? 'bg-red-50' : doc.is_image ? 'bg-blue-50' : 'bg-gray-100'">
                                <span v-if="doc.is_pdf">📄</span>
                                <span v-else-if="doc.is_image">🖼️</span>
                                <span v-else>📋</span>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-base font-semibold text-gray-900 truncate">{{ doc.title }}</p>
                                <p v-if="doc.description" class="text-xs text-gray-500 mt-0.5 truncate">{{ doc.description }}</p>
                                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2 text-xs text-gray-500">
                                    <span v-if="doc.job_number" class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ doc.job_number }}
                                    </span>
                                    <span v-if="doc.vehicle" class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h8l2-2z" />
                                        </svg>
                                        {{ doc.vehicle }}
                                    </span>
                                    <span>{{ doc.file_size }}</span>
                                    <span>{{ doc.created_at }}</span>
                                </div>
                            </div>

                            <!-- Download button -->
                            <a :href="doc.download_url"
                               class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-electric-600 text-white text-xs font-semibold rounded-lg hover:bg-electric-700 transition self-start">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </CustomerPortalLayout>
</template>
