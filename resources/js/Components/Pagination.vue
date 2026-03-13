<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface Props {
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
    from?: number
    to?: number
    total?: number
}

defineProps<Props>()
</script>

<template>
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-if="links[0]?.url"
                :href="links[0].url"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >Previous</Link>
            <Link
                v-if="links[links.length - 1]?.url"
                :href="links[links.length - 1].url"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >Next</Link>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700" v-if="from && to && total">
                    Showing <span class="font-medium">{{ from }}</span> to <span class="font-medium">{{ to }}</span> of <span class="font-medium">{{ total }}</span> results
                </p>
            </div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                <template v-for="(link, idx) in links" :key="idx">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            link.active ? 'z-10 bg-electric-600 text-white focus-visible:outline-blue-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
                            'relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20'
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        :class="[
                            'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 ring-1 ring-inset ring-gray-300'
                        ]"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </div>
</template>
