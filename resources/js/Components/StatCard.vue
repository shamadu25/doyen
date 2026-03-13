<script setup lang="ts">
const props = withDefaults(defineProps<{
    label: string
    value: string | number
    change?: number
    icon?: string
    color?: string
    prefix?: string
}>(), {
    color: 'blue',
    prefix: '',
})
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-500 truncate">{{ label }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ prefix }}{{ typeof value === 'number' ? value.toLocaleString('en-GB', { minimumFractionDigits: value % 1 !== 0 ? 2 : 0 }) : value }}</p>
                <p v-if="change !== undefined" :class="[change >= 0 ? 'text-green-600' : 'text-red-600', 'mt-1 text-xs font-medium flex items-center']">
                    <svg :class="[change >= 0 ? '' : 'rotate-180', 'w-3 h-3 mr-1']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    {{ Math.abs(change) }}% vs previous period
                </p>
            </div>
            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center', `bg-${color}-50`]">
                <slot name="icon">
                    <span :class="[`text-${color}-600`, 'text-xl']">📊</span>
                </slot>
            </div>
        </div>
    </div>
</template>
