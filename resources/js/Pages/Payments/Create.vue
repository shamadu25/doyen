<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    invoices: any[]
    customers: any[]
}>()

const route = inject<(path: string) => string>('route', (p) => p)

const form = useForm({
    invoice_id: '',
    customer_id: '',
    amount: '',
    payment_method: 'card',
    payment_date: new Date().toISOString().split('T')[0],
    reference: '',
    notes: '',
})

function selectInvoice() {
    const inv = props.invoices.find((i: any) => i.id == form.invoice_id)
    if (inv) {
        form.customer_id = inv.customer_id
        const balance = parseFloat(inv.total || 0) - parseFloat(inv.amount_paid || 0)
        form.amount = balance > 0 ? String(balance.toFixed(2)) : inv.total
    }
}

function fmt(amount: any) { return '£' + parseFloat(amount || 0).toFixed(2) }

function submit() { form.post(route('/payments')) }
</script>

<template>
    <Head title="Record Payment" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Record Payment</h1>
                    <p class="mt-1 text-sm text-gray-500">Record a payment against an invoice</p>
                </div>
                <Link :href="route('/payments')" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice</label>
                        <select v-model="form.invoice_id" @change="selectInvoice" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Select invoice (optional)</option>
                            <option v-for="inv in invoices" :key="inv.id" :value="inv.id">{{ inv.invoice_number }} - {{ inv.customer?.first_name }} {{ inv.customer?.last_name }} ({{ fmt(inv.total) }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                        <select v-model="form.customer_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                            <option value="">Select customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">£</span>
                            <input v-model="form.amount" type="number" step="0.01" min="0.01" class="w-full pl-7 rounded-lg border-gray-300 text-sm" required />
                        </div>
                        <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                        <select v-model="form.payment_method" class="w-full rounded-lg border-gray-300 text-sm" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                        <input v-model="form.payment_date" type="date" class="w-full rounded-lg border-gray-300 text-sm" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                        <input v-model="form.reference" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Transaction reference" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link :href="route('/payments')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium text-white bg-electric-600 rounded-lg hover:bg-electric-700 disabled:opacity-50">
                        {{ form.processing ? 'Recording...' : 'Record Payment' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
