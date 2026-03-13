<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{ ticket: any }>()

const replyForm = useForm({
    message:     '',
    is_internal: false,
    status:      props.ticket.status,
})

const statusForm = useForm({
    status:   props.ticket.status,
    priority: props.ticket.priority,
})

function submitReply() {
    replyForm.post(`/tickets/${props.ticket.id}/reply`, {
        onSuccess: () => { replyForm.reset('message') },
    })
}

function updateStatus() {
    statusForm.patch(`/tickets/${props.ticket.id}/status`)
}

function deleteTicket() {
    if (confirm('Delete this ticket? This cannot be undone.')) {
        router.delete(`/tickets/${props.ticket.id}`)
    }
}

const priorityBadge: Record<string, string> = {
    urgent: 'bg-red-100 text-red-700 border border-red-200',
    high:   'bg-orange-100 text-orange-700 border border-orange-200',
    medium: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
    low:    'bg-gray-100 text-gray-600 border border-gray-200',
}
const statusBadge: Record<string, string> = {
    open:        'bg-blue-100 text-blue-700 border border-blue-200',
    in_progress: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
    resolved:    'bg-green-100 text-green-700 border border-green-200',
    closed:      'bg-gray-100 text-gray-500 border border-gray-200',
}
function fmt(s: string) { return s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) }
function fmtDate(d: string) { return d ? new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-' }

const allReplies = computed(() => props.ticket.replies || [])
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <Link href="/tickets" class="text-sm text-blue-600 hover:underline">← Back to Tickets</Link>
                    <h1 class="text-xl font-bold text-gray-900 mt-2">{{ ticket.subject }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ ticket.ticket_number }}</p>
                </div>
                <button @click="deleteTicket" class="text-sm text-red-500 hover:text-red-700 px-3 py-1 rounded border border-red-200 hover:border-red-300 transition-colors">Delete</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main thread -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Original message -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                                    {{ ticket.customer?.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ ticket.customer?.name }}</p>
                                    <p class="text-xs text-gray-500">{{ ticket.customer?.email }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400">{{ fmtDate(ticket.created_at) }}</span>
                        </div>
                        <div class="p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ ticket.message }}</div>
                    </div>

                    <!-- Replies -->
                    <div v-for="reply in allReplies" :key="reply.id"
                        :class="['rounded-xl border overflow-hidden', reply.is_internal ? 'border-amber-200 bg-amber-50' : reply.sender_type === 'admin' ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-white']">
                        <div class="px-4 py-3 border-b flex items-center justify-between"
                            :class="reply.is_internal ? 'border-amber-200' : reply.sender_type === 'admin' ? 'border-blue-200' : 'border-gray-200'">
                            <div class="flex items-center gap-2">
                                <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm', reply.sender_type === 'admin' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">
                                    {{ reply.sender_name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ reply.sender_name }}
                                        <span v-if="reply.sender_type === 'admin'" class="ml-1.5 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Staff</span>
                                        <span v-if="reply.is_internal" class="ml-1.5 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">Internal Note</span>
                                    </p>
                                    <p class="text-xs text-gray-500">{{ reply.sender_email }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400">{{ fmtDate(reply.created_at) }}</span>
                        </div>
                        <div class="p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ reply.message }}</div>
                    </div>

                    <!-- Reply Form -->
                    <div v-if="ticket.status !== 'closed'" class="bg-white rounded-xl border border-gray-200 p-4 space-y-4">
                        <h3 class="font-semibold text-gray-900">Reply to Customer</h3>
                        <div v-if="replyForm.errors.message" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ replyForm.errors.message }}</div>
                        <textarea v-model="replyForm.message" rows="5" placeholder="Type your reply…"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y" />

                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" v-model="replyForm.is_internal" class="rounded border-gray-300" />
                                    Internal note (not sent to customer)
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <select v-model="replyForm.status" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="open">Keep Open</option>
                                    <option value="in_progress">Mark In Progress</option>
                                    <option value="resolved">Mark Resolved</option>
                                    <option value="closed">Close Ticket</option>
                                </select>
                                <button @click="submitReply" :disabled="replyForm.processing || !replyForm.message.trim()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ replyForm.processing ? 'Sending…' : 'Send Reply' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-center text-sm text-gray-500">
                        This ticket is <strong>closed</strong>. Update the status to re-open it.
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Ticket Info -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                        <h3 class="font-semibold text-gray-900 text-sm">Ticket Details</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Status</span>
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', statusBadge[ticket.status] || '']">{{ fmt(ticket.status) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Priority</span>
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold capitalize', priorityBadge[ticket.priority] || '']">{{ ticket.priority }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Category</span>
                                <span class="text-gray-700 capitalize">{{ ticket.category }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Submitted</span>
                                <span class="text-gray-700">{{ fmtDate(ticket.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Update Status/Priority -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                        <h3 class="font-semibold text-gray-900 text-sm">Update Ticket</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select v-model="statusForm.status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                                <select v-model="statusForm.priority" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <button @click="updateStatus" :disabled="statusForm.processing"
                                class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 disabled:opacity-50">
                                Update
                            </button>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
                        <h3 class="font-semibold text-gray-900 text-sm">Customer</h3>
                        <p class="text-sm font-medium text-gray-900">{{ ticket.customer?.name }}</p>
                        <p class="text-sm text-gray-500">{{ ticket.customer?.email }}</p>
                        <p class="text-sm text-gray-500">{{ ticket.customer?.phone || 'No phone' }}</p>
                        <Link :href="`/customers/${ticket.customer?.id}`" class="text-sm text-blue-600 hover:underline block mt-1">View customer profile →</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
