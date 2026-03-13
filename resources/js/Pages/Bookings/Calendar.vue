<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

interface Technician {
    id: number
    name: string
}

interface CalendarEvent {
    id: number
    reference: string
    customer_name: string
    vehicle_registration: string
    appointment_type: string
    scheduled_date: string
    scheduled_time: string
    duration_minutes: number
    status: string
    assigned_to: number | null
    technician_name: string | null
}

interface Props {
    events: CalendarEvent[]
    technicians: Technician[]
    start: string
    end: string
}

const props = defineProps<Props>()

const route = inject<(path: string) => string>('route', (p) => p)

const timeSlots = [
    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
    '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
    '17:00', '17:30',
]

const selectedTechnician = ref<number | ''>('')

const startDate = computed(() => new Date(props.start + 'T00:00:00'))

const weekDays = computed(() => {
    const days: Date[] = []
    const start = new Date(startDate.value)
    for (let i = 0; i < 7; i++) {
        const d = new Date(start)
        d.setDate(start.getDate() + i)
        days.push(d)
    }
    return days
})

const filteredEvents = computed(() => {
    if (!selectedTechnician.value) return props.events
    return props.events.filter(e => e.assigned_to === Number(selectedTechnician.value))
})

function formatDayHeader(date: Date): string {
    return date.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short' })
}

function formatDateKey(date: Date): string {
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const d = String(date.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
}

function getEventsForSlot(date: Date, time: string): CalendarEvent[] {
    const dateKey = formatDateKey(date)
    return filteredEvents.value.filter(e => {
        if (e.scheduled_date !== dateKey) return false
        const eventTime = e.scheduled_time?.substring(0, 5)
        return eventTime === time
    })
}

function hasEventSpanning(date: Date, time: string): boolean {
    const dateKey = formatDateKey(date)
    const [slotH, slotM] = time.split(':').map(Number)
    const slotMinutes = slotH * 60 + slotM

    return filteredEvents.value.some(e => {
        if (e.scheduled_date !== dateKey) return false
        const [eH, eM] = (e.scheduled_time || '00:00').split(':').map(Number)
        const eventStart = eH * 60 + eM
        const eventEnd = eventStart + (e.duration_minutes || 60)
        return slotMinutes > eventStart && slotMinutes < eventEnd
    })
}

const appointmentTypeColors: Record<string, string> = {
    'mot': 'bg-electric-100 border-electric-200 text-electric-700',
    'service': 'bg-green-100 border-green-300 text-green-800',
    'repair': 'bg-red-100 border-red-300 text-red-800',
    'diagnosis': 'bg-yellow-100 border-yellow-300 text-yellow-800',
}

function getEventColor(appointmentType: string): string {
    return appointmentTypeColors[appointmentType] ?? 'bg-gray-100 border-gray-300 text-gray-800'
}

function getEventHeight(minutes: number): string {
    const slots = Math.max(1, Math.ceil(minutes / 30))
    return `${slots * 2.5}rem`
}

function isToday(date: Date): boolean {
    const today = new Date()
    return date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
}

function navigateWeek(direction: number) {
    const newStart = new Date(startDate.value)
    newStart.setDate(newStart.getDate() + (direction * 7))
    router.get(route('/bookings/calendar'), {
        start: formatDateKey(newStart),
        technician: selectedTechnician.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function goToToday() {
    const today = new Date()
    const dayOfWeek = today.getDay()
    const monday = new Date(today)
    monday.setDate(today.getDate() - ((dayOfWeek + 6) % 7))
    router.get(route('/bookings/calendar'), {
        start: formatDateKey(monday),
        technician: selectedTechnician.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function filterByTechnician() {
    router.get(route('/bookings/calendar'), {
        start: props.start,
        technician: selectedTechnician.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

const weekLabel = computed(() => {
    const start = weekDays.value[0]
    const end = weekDays.value[6]
    const startStr = start.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
    const endStr = end.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    return `${startStr} — ${endStr}`
})
</script>

<template>
    <Head title="Booking Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('/bookings')" class="text-gray-500 hover:text-gray-700 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Booking Calendar</h2>
                </div>
                <Link
                    :href="route('/bookings/create')"
                    class="inline-flex items-center rounded-xl bg-electric-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-electric-700 transition"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Booking
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Controls -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            @click="navigateWeek(-1)"
                            class="rounded-lg border border-gray-300 bg-white p-2 text-gray-600 shadow-sm hover:bg-gray-50 transition"
                            title="Previous week"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            @click="goToToday"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition"
                        >
                            Today
                        </button>
                        <button
                            @click="navigateWeek(1)"
                            class="rounded-lg border border-gray-300 bg-white p-2 text-gray-600 shadow-sm hover:bg-gray-50 transition"
                            title="Next week"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <h3 class="text-lg font-semibold text-gray-900">{{ weekLabel }}</h3>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-600">Technician:</label>
                        <select
                            v-model="selectedTechnician"
                            @change="filterByTechnician"
                            class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-electric-600 focus:ring-electric-600"
                        >
                            <option value="">All Technicians</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Colour Legend -->
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="text-xs font-medium text-gray-500 uppercase">Appointment Types:</span>
                    <div v-for="(cls, type) in appointmentTypeColors" :key="type" class="flex items-center gap-1.5">
                        <span :class="cls" class="inline-block h-3 w-3 rounded border"></span>
                        <span class="text-xs text-gray-600 capitalize">{{ type }}</span>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
                    <div class="min-w-[900px]">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-[80px_repeat(7,1fr)] border-b border-gray-200">
                            <div class="border-r border-gray-200 bg-gray-50 p-3"></div>
                            <div
                                v-for="day in weekDays"
                                :key="day.toISOString()"
                                class="border-r border-gray-200 p-3 text-center last:border-r-0"
                                :class="{ 'bg-electric-50': isToday(day), 'bg-gray-50': !isToday(day) }"
                            >
                                <p class="text-sm font-semibold" :class="isToday(day) ? 'text-electric-700' : 'text-gray-900'">
                                    {{ formatDayHeader(day) }}
                                </p>
                            </div>
                        </div>

                        <!-- Time Slots -->
                        <div class="grid grid-cols-[80px_repeat(7,1fr)]">
                            <template v-for="time in timeSlots" :key="time">
                                <!-- Time Label -->
                                <div class="border-b border-r border-gray-100 px-3 py-2 text-right">
                                    <span class="text-xs font-medium text-gray-400">{{ time }}</span>
                                </div>

                                <!-- Day Cells -->
                                <div
                                    v-for="day in weekDays"
                                    :key="`${day.toISOString()}-${time}`"
                                    class="relative border-b border-r border-gray-100 p-1 last:border-r-0"
                                    :class="{
                                        'bg-electric-50/30': isToday(day),
                                        'bg-gray-50/30': hasEventSpanning(day, time) && !isToday(day),
                                    }"
                                    style="min-height: 2.5rem;"
                                >
                                    <Link
                                        v-for="event in getEventsForSlot(day, time)"
                                        :key="event.id"
                                        :href="route(`/bookings/${event.id}`)"
                                        class="mb-1 block rounded-lg border p-1.5 text-xs transition hover:shadow-md cursor-pointer"
                                        :class="getEventColor(event.appointment_type)"
                                        :style="{ minHeight: getEventHeight(event.duration_minutes) }"
                                    >
                                        <p class="font-semibold truncate">{{ event.reference }}</p>
                                        <p class="truncate">{{ event.vehicle_registration }}</p>
                                        <p class="truncate opacity-75">{{ event.customer_name }}</p>
                                        <p v-if="event.technician_name" class="truncate opacity-60 mt-0.5">
                                            {{ event.technician_name }}
                                        </p>
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
