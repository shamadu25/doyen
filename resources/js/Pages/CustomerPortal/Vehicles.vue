<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import CustomerPortalLayout from '@/Layouts/CustomerPortalLayout.vue'

defineProps<{ customer: any, vehicles: any[] }>()

function motStatus(expiry: string | null) {
    if (!expiry) return null
    const d = new Date(expiry)
    const now = new Date()
    const in30 = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
    if (d < now) return 'expired'
    if (d <= in30) return 'due-soon'
    return 'ok'
}
function fmtDate(d: string) { return d ? new Date(d).toLocaleDateString('en-GB') : '' }
</script>

<template>
    <Head title="My Vehicles" />
    <CustomerPortalLayout :customer="customer">
        <div class="space-y-4">
            <h1 class="text-xl font-bold text-gray-900">My Vehicles</h1>
            <div v-if="!vehicles.length" class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">No vehicles on record.</div>
            <div v-for="v in vehicles" :key="v.id" class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-electric-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-electric-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-900 text-lg">{{ v.registration_number }}</p>
                            <!-- MOT badge -->
                            <span v-if="motStatus(v.mot_expiry) === 'expired'"
                                class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">MOT Expired</span>
                            <span v-else-if="motStatus(v.mot_expiry) === 'due-soon'"
                                class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 font-medium">MOT Due Soon</span>
                        </div>
                        <p class="text-gray-600">{{ v.make }} {{ v.model }} {{ v.year }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-1 mt-3 text-sm">
                            <div v-if="v.colour"><span class="text-gray-400">Colour</span><br><span class="font-medium text-gray-800">{{ v.colour }}</span></div>
                            <div v-if="v.fuel_type"><span class="text-gray-400">Fuel</span><br><span class="font-medium text-gray-800 capitalize">{{ v.fuel_type }}</span></div>
                            <div v-if="v.engine_size"><span class="text-gray-400">Engine</span><br><span class="font-medium text-gray-800">{{ v.engine_size }}</span></div>
                            <div v-if="v.mot_expiry">
                                <span class="text-gray-400">MOT Expiry</span><br>
                                <span :class="['font-medium', motStatus(v.mot_expiry) === 'expired' ? 'text-red-600' : motStatus(v.mot_expiry) === 'due-soon' ? 'text-orange-600' : 'text-gray-800']">
                                    {{ fmtDate(v.mot_expiry) }}
                                </span>
                            </div>
                        </div>

                        <!-- Book MOT CTA when expired or due soon -->
                        <div v-if="motStatus(v.mot_expiry) !== 'ok' && v.mot_expiry" class="mt-3">
                            <Link href="/customer/appointments/book"
                                class="inline-flex items-center gap-1.5 text-sm text-electric-600 hover:text-electric-700 font-medium hover:underline">
                                Book MOT appointment →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CustomerPortalLayout>
</template>
