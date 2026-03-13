<script setup lang="ts">
const props = withDefaults(defineProps<{
    status: string
    size?: 'sm' | 'md'
}>(), {
    size: 'md'
})

const statusConfig: Record<string, { bg: string; text: string; label: string }> = {
    // Booking statuses
    pending: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pending' },
    confirmed: { bg: 'bg-electric-100', text: 'text-electric-700', label: 'Confirmed' },
    in_progress: { bg: 'bg-indigo-100', text: 'text-indigo-800', label: 'In Progress' },
    completed: { bg: 'bg-green-100', text: 'text-green-800', label: 'Completed' },
    cancelled: { bg: 'bg-red-100', text: 'text-red-800', label: 'Cancelled' },
    // Job Card statuses
    awaiting_parts: { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Awaiting Parts' },
    invoiced: { bg: 'bg-purple-100', text: 'text-purple-800', label: 'Invoiced' },
    // Invoice statuses
    draft: { bg: 'bg-gray-100', text: 'text-gray-800', label: 'Draft' },
    sent: { bg: 'bg-electric-100', text: 'text-electric-700', label: 'Sent' },
    paid: { bg: 'bg-green-100', text: 'text-green-800', label: 'Paid' },
    partial: { bg: 'bg-amber-100', text: 'text-amber-800', label: 'Partial' },
    overdue: { bg: 'bg-red-100', text: 'text-red-800', label: 'Overdue' },
    refunded: { bg: 'bg-purple-100', text: 'text-purple-800', label: 'Refunded' },
    // MOT statuses
    booked: { bg: 'bg-electric-100', text: 'text-electric-700', label: 'Booked' },
    passed: { bg: 'bg-green-100', text: 'text-green-800', label: 'Passed' },
    failed: { bg: 'bg-red-100', text: 'text-red-800', label: 'Failed' },
    advisory: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Advisory' },
    retest: { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Retest' },
    // Priority
    low: { bg: 'bg-gray-100', text: 'text-gray-800', label: 'Low' },
    normal: { bg: 'bg-electric-100', text: 'text-electric-700', label: 'Normal' },
    high: { bg: 'bg-orange-100', text: 'text-orange-800', label: 'High' },
    urgent: { bg: 'bg-red-100', text: 'text-red-800', label: 'Urgent' },
}

const config = statusConfig[props.status] || { bg: 'bg-gray-100', text: 'text-gray-800', label: props.status }
</script>

<template>
    <span :class="[
        config.bg, config.text,
        size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs',
        'inline-flex items-center font-medium rounded-full capitalize'
    ]">
        {{ config.label }}
    </span>
</template>
