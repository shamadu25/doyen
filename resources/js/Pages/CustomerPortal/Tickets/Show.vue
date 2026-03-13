<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

const props = defineProps<{ customer: any; ticket: any }>()

const replyForm = useForm({ message: '' })

function submitReply() {
    replyForm.post(`/customer/tickets/${props.ticket.id}/reply`, {
        onSuccess: () => replyForm.reset('message'),
    })
}

const statusBadge: Record<string, string> = {
    open:        'bg-blue-100 text-blue-700',
    in_progress: 'bg-yellow-100 text-yellow-700',
    resolved:    'bg-green-100 text-green-700',
    closed:      'bg-gray-100 text-gray-500',
}
const priorityBadge: Record<string, string> = {
    urgent: 'bg-red-100 text-red-700',
    high:   'bg-orange-100 text-orange-700',
    medium: 'bg-yellow-100 text-yellow-700',
    low:    'bg-gray-100 text-gray-600',
}
function fmt(s: string) { return s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-' }

const replies = props.ticket.public_replies || []
const isClosed = ['resolved', 'closed'].includes(props.ticket.status)
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4 max-w-2xl">
            <!-- Header -->
            <div>
                <Link href="/customer/tickets" class="text-sm text-blue-600 hover:underline">← Back to Tickets</Link>
                <div class="flex items-start justify-between mt-3 gap-2">
                    <h1 class="text-xl font-bold text-gray-900 flex-1">{{ ticket.subject }}</h1>
                    <span :class="['inline-flex px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0 mt-0.5', statusBadge[ticket.status] || 'bg-gray-100 text-gray-600']">{{ fmt(ticket.status) }}</span>
                </div>
                <div class="flex items-center gap-3 mt-1.5 flex-wrap text-xs text-gray-500">
                    <span class="font-semibold text-blue-600">{{ ticket.ticket_number }}</span>
                    <span class="capitalize">{{ ticket.category }}</span>
                    <span :class="['px-1.5 py-0.5 rounded text-xs font-medium capitalize', priorityBadge[ticket.priority] || '']">{{ ticket.priority }} priority</span>
                    <span>Opened {{ fmtDate(ticket.created_at) }}</span>
                </div>
            </div>

            <!-- Original message -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                            {{ customer.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ customer.name }}</span>
                        <span class="text-xs text-gray-400">(you)</span>
                    </div>
                    <span class="text-xs text-gray-400">{{ fmtDate(ticket.created_at) }}</span>
                </div>
                <div class="p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ ticket.message }}</div>
            </div>

            <!-- Replies thread -->
            <div v-for="reply in replies" :key="reply.id"
                :class="['rounded-xl border overflow-hidden', reply.sender_type === 'admin' ? 'border-blue-200' : 'border-gray-200']">
                <div :class="['px-4 py-3 border-b flex items-center justify-between', reply.sender_type === 'admin' ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200']">
                    <div class="flex items-center gap-2">
                        <div :class="['w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs', reply.sender_type === 'admin' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">
                            {{ reply.sender_name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ reply.sender_type === 'admin' ? 'Doyen Auto Services' : customer.name }}
                                <span v-if="reply.sender_type === 'admin'" class="ml-1.5 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Support Team</span>
                                <span v-else class="ml-1.5 text-xs text-gray-400">(you)</span>
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ fmtDate(reply.created_at) }}</span>
                </div>
                <div :class="['p-4 text-sm leading-relaxed whitespace-pre-wrap', reply.sender_type === 'admin' ? 'bg-blue-50 text-blue-900' : 'bg-white text-gray-700']">
                    {{ reply.message }}
                </div>
            </div>

            <!-- Closed notice -->
            <div v-if="isClosed" class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-sm text-gray-600">
                    This ticket is <strong>{{ fmt(ticket.status) }}</strong>.
                    If you need further assistance, please
                    <Link href="/customer/tickets/create" class="text-blue-600 hover:underline">open a new ticket</Link>.
                </p>
            </div>

            <!-- Reply form -->
            <div v-if="!isClosed" class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">Send a Reply</h3>
                <div v-if="replyForm.errors.message" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ replyForm.errors.message }}</div>
                <textarea v-model="replyForm.message" rows="5"
                    placeholder="Add more details or follow up on the response…"
                    :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y', replyForm.errors.message ? 'border-red-400' : 'border-gray-200']" />
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400">{{ replyForm.message.length }}/5000 characters</p>
                    <button @click="submitReply" :disabled="replyForm.processing || !replyForm.message.trim()"
                        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <svg v-if="replyForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ replyForm.processing ? 'Sending…' : 'Send Reply' }}
                    </button>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
