<script setup lang="ts">
import { ref, computed, inject, watch, onMounted } from 'vue'
import { Head, useForm, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const route = inject<(path: string) => string>('route', (p) => p)

// Garage contact details from shared props
const garagePhone   = computed(() => ((usePage().props as any).garageSettings?.phone)  || '+44 141 482 0726')
const garageName    = computed(() => ((usePage().props as any).garageSettings?.garage_name) || 'Doyen Auto Services')
const garageTelHref = computed(() => 'tel:+44' + garagePhone.value.replace(/\s/g, '').replace(/^0/, ''))

const currentStep = ref(1)
const totalSteps = 3

// DVLA Lookup states
const isLookingUp = ref(false)
const lookupMessage = ref('')
const lookupSuccess = ref(false)
const lookupError = ref(false)
let lookupTimeout: number | null = null

// Vehicle identity card data (populated from DVLA, display-only)
const dvlaCard = ref<{
    year: number | null
    co2: number | null
    motStatus: string | null
    motDate: string | null
    taxStatus: string | null
    taxDate: string | null
    wheelplan: string | null
    monthFirstReg: string | null
} | null>(null)

function formatDateDisplay(dateStr: string): string {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

const form = useForm({
    customer_type: 'new',

    // Customer details
    customer_first_name: '',
    customer_last_name: '',
    customer_email: '',
    customer_phone: '',
    customer_address: '',
    customer_postcode: '',
    
    // Vehicle details
    vehicle_registration: '',
    vehicle_make: '',
    vehicle_model: '',
    vehicle_year: null as number | null,
    vehicle_colour: '',
    vehicle_mileage: null as number | null,
    vehicle_fuel_type: '',
    vehicle_engine_size: null as number | null,
    vehicle_transmission: '',
    vehicle_mot_due_date: '',
    vehicle_tax_due_date: '',
    
    // Appointment details
    requested_service: '',
    appointment_type: '',
    scheduled_date: '',
    scheduled_time: '',
    quote_request: '',
    description: '',
    customer_notes: '',
    attachments: [] as File[],
    create_account: false,
    password: '',
    password_confirmation: '',
})

const selectedFiles = ref<File[]>([])

function handleFiles(event: Event) {
    const input = event.target as HTMLInputElement
    if (!input.files) return
    const files = Array.from(input.files)
    // Limit to 5 files
    selectedFiles.value = files.slice(0, 5)
    form.attachments = selectedFiles.value
}

function removeFile(index: number) {
    selectedFiles.value.splice(index, 1)
    form.attachments = [...selectedFiles.value]
}

// Watch vehicle registration for auto-lookup
watch(() => form.vehicle_registration, (newValue) => {
    // Clear previous timeout
    if (lookupTimeout) {
        clearTimeout(lookupTimeout)
    }

    // Reset states
    lookupMessage.value = ''
    lookupSuccess.value = false
    lookupError.value = false

    // Only lookup if we have a valid-looking registration (at least 4 characters)
    const cleanReg = newValue.replace(/\s/g, '').toUpperCase()
    if (cleanReg.length >= 4) {
        // Debounce the lookup by 800ms
        lookupTimeout = window.setTimeout(() => {
            lookupVehicle(cleanReg)
        }, 800)
    }
})

async function lookupVehicle(registration: string) {
    isLookingUp.value = true
    lookupMessage.value = '🔍 Looking up vehicle details...'

    try {
        const response = await axios.post(route('/api/vehicle-lookup'), {
            registration: registration
        })

        if (response.data.success) {
            const d = response.data.data
            // Auto-fill the form with DVLA data
            form.vehicle_make         = d.make || ''
            form.vehicle_model        = d.model || ''
            form.vehicle_year         = d.year || null
            form.vehicle_colour       = d.color || ''
            form.vehicle_fuel_type    = d.fuel_type || ''
            form.vehicle_engine_size  = d.engine_size || null
            form.vehicle_transmission = d.transmission || ''
            form.vehicle_mot_due_date = d.mot_due_date || ''
            form.vehicle_tax_due_date = d.tax_due_date || ''

            // Populate display card
            dvlaCard.value = {
                year:         d.year || null,
                co2:          d.co2_emissions || null,
                motStatus:    d.mot_status || null,
                motDate:      d.mot_due_date ? formatDateDisplay(d.mot_due_date) : null,
                taxStatus:    d.tax_status || null,
                taxDate:      d.tax_due_date ? formatDateDisplay(d.tax_due_date) : null,
                wheelplan:    d.wheelplan || null,
                monthFirstReg: d.month_first_registered || null,
            }

            lookupSuccess.value = true
            lookupError.value = false
            lookupMessage.value = '\u2705 Vehicle found! Details auto-filled.'
        }
    } catch (error: any) {
        lookupError.value = true
        lookupSuccess.value = false
        dvlaCard.value = null

        if (error.response?.status === 404) {
            lookupMessage.value = '⚠️ Vehicle not found. Please enter details manually.'
        } else {
            lookupMessage.value = '⚠️ Unable to lookup vehicle. Please enter details manually.'
        }
    } finally {
        isLookingUp.value = false
    }
}

const bookingCategories = [
    { group: 'Maintenance & MOT', items: [
        { value: 'full-service',                     label: 'Full Service',                          category: 'service',   duration: '120 mins' },
        { value: 'interim-service',                  label: 'Interim Service',                       category: 'service',   duration: '90 mins' },
        { value: 'oil-filter-change',                label: 'Oil & Oil Filter Change',               category: 'service',   duration: '45 mins' },
        { value: 'full-service-mot',                 label: 'Full Service & MOT',                    category: 'mot',       duration: '150 mins' },
        { value: 'interim-service-mot',              label: 'Interim Service & MOT',                 category: 'mot',       duration: '120 mins' },
        { value: 'oil-filter-change-mot',            label: 'Oil & Filter Change with MOT',          category: 'mot',       duration: '90 mins' },
        { value: 'mot-only',                         label: 'MOT Test',                              category: 'mot',       duration: '60 mins' },
    ]},
    { group: 'General Vehicle Repairs', items: [
        { value: 'general-repairs-maintenance',      label: 'General Repairs & Maintenance',         category: 'service',   duration: '60–180 mins' },
        { value: 'full-vehicle-diagnostics',          label: 'Full Vehicle Diagnostics',              category: 'diagnosis', duration: '60 mins' },
        { value: 'engine-repairs-servicing',          label: 'Engine Repairs & Servicing',            category: 'repair',    duration: '120 mins' },
        { value: 'brake-repair-replacement',          label: 'Brake Repair & Replacement',            category: 'repair',    duration: '90 mins' },
        { value: 'suspension-steering-repairs',       label: 'Suspension & Steering Repairs',         category: 'repair',    duration: '120 mins' },
        { value: 'clutch-gearbox-repairs',            label: 'Clutch & Gearbox Repairs',              category: 'repair',    duration: '180 mins' },
        { value: 'timing-belt-chain-replacement',     label: 'Timing Belt & Chain Replacement',       category: 'repair',    duration: '150 mins' },
        { value: 'mot-preparation',                   label: 'MOT Preparation',                       category: 'mot',       duration: '60 mins' },
    ]},
    { group: 'Advanced Diagnostics & ECU Services', items: [
        { value: 'ecu-testing-fault-code-analysis',  label: 'ECU Diagnostics & Fault Finding',       category: 'diagnosis', duration: '60 mins' },
        { value: 'ecu-repair-cloning',               label: 'ECU Testing & Replacement',             category: 'repair',    duration: '120 mins' },
        { value: 'module-coding-programming',        label: 'ECU Coding & Programming',              category: 'diagnosis', duration: '90 mins' },
        { value: 'immobiliser-programming',          label: 'Immobiliser Fault Diagnosis',           category: 'diagnosis', duration: '60 mins' },
        { value: 'key-cutting-programming',          label: 'Key Cutting & Programming',             category: 'service',   duration: '60 mins' },
    ]},
    { group: 'Airbag & Safety System Services', items: [
        { value: 'airbag-crash-data-reset',          label: 'Airbag Crash Data Removal (SRS Reset)', category: 'repair',    duration: '90 mins' },
        { value: 'airbag-module-repair',             label: 'Airbag Module Repair',                  category: 'repair',    duration: '120 mins' },
        { value: 'seatbelt-pretensioner-reset',      label: 'Seatbelt Pretensioner Reset',           category: 'repair',    duration: '60 mins' },
        { value: 'airbag-light-diagnostics',         label: 'Airbag Light Diagnostics',              category: 'diagnosis', duration: '60 mins' },
    ]},
    { group: 'Emission Services', items: [
        { value: 'dpf-repair-off',                   label: 'DPF Repair / DPF Off',                  category: 'repair',    duration: '90 mins' },
        { value: 'egr-repair-off',                   label: 'EGR Repair / EGR Off',                  category: 'repair',    duration: '90 mins' },
        { value: 'adblue-scr-repair',                label: 'AdBlue / SCR Repair or Off',            category: 'repair',    duration: '90 mins' },
        { value: 'lambda-oxygen-repair',             label: 'Oxygen (Lambda) Sensor Repair',         category: 'repair',    duration: '60 mins' },
        { value: 'dtc-delete',                       label: 'DTCs Delete',                           category: 'service',   duration: '60 mins' },
        { value: 'dpf-egr-adblue-solutions',         label: 'DPF, EGR & AdBlue Solutions',           category: 'repair',    duration: '90 mins' },
        { value: 'adblue-system-diagnostics',        label: 'AdBlue System Diagnostics',             category: 'diagnosis', duration: '90 mins' },
        { value: 'nox-sensor-replacement',           label: 'NOx Sensor Replacement',                category: 'repair',    duration: '90 mins' },
        { value: 'egr-system-diagnostics',           label: 'EGR System Diagnostics',                category: 'diagnosis', duration: '90 mins' },
    ]},
    { group: 'ECU Remapping & Performance Tuning', items: [
        { value: 'ecu-remapping',                    label: 'ECU Remapping',                         category: 'service',   duration: '90 mins' },
        { value: 'stage-1-tuning',                   label: 'Stage 1 Performance Remap',             category: 'service',   duration: '90 mins' },
        { value: 'stage-2-tuning',                   label: 'Stage 2 Performance Remap',             category: 'service',   duration: '120 mins' },
        { value: 'eco-fuel-remap',                   label: 'Eco & Fuel Economy Remap',              category: 'service',   duration: '90 mins' },
        { value: 'gearbox-tcu-tuning',               label: 'Gearbox (TCU) Tuning',                  category: 'service',   duration: '90 mins' },
        { value: 'custom-tuning',                    label: 'Custom Tuning Solution',                category: 'service',   duration: '120 mins' },
        { value: 'software-updates',                 label: 'Software Updates',                      category: 'service',   duration: '60 mins' },
    ]},
    { group: 'Mileage Correction Services', items: [
        { value: 'instrument-cluster-replacement',   label: 'Instrument Cluster Replacement',        category: 'repair',    duration: '90 mins' },
        { value: 'mileage-correction',               label: 'Mileage Correction',                    category: 'service',   duration: '60 mins' },
        { value: 'dashboard-display-repair',         label: 'Dashboard Display Repair',              category: 'repair',    duration: '60 mins' },
    ]},
    { group: 'Electrical & Electronic Repairs', items: [
        { value: 'electrical-fault-tracing',         label: 'Electrical Fault Tracing & CAN Diagnostics', category: 'diagnosis', duration: '120 mins' },
        { value: 'battery-drain-diagnosis',          label: 'Battery Drain Diagnosis',               category: 'diagnosis', duration: '90 mins' },
        { value: 'starter-alternator-testing',       label: 'Starter & Alternator Testing',          category: 'diagnosis', duration: '90 mins' },
    ]},
    { group: 'Commercial & Fleet Services', items: [
        { value: 'commercial-fleet',                 label: 'Commercial Van Diagnostics',            category: 'service',   duration: '90 mins' },
        { value: 'fleet-maintenance',                label: 'Fleet Maintenance Support',             category: 'service',   duration: '120 mins' },
    ]},
]
const serviceOptions = computed(() => bookingCategories.flatMap(cat => cat.items))

function selectService(serviceValue: string) {
    form.requested_service = serviceValue
    const selectedService = serviceOptions.value.find((service) => service.value === serviceValue)
    form.appointment_type = selectedService ? selectedService.category : ''
}

onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const preselectedService = params.get('service')

    if (preselectedService && serviceOptions.value.some((service) => service.value === preselectedService)) {
        selectService(preselectedService)
    }
})

const timeSlots = [
    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
    '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
]

const availableDates = computed(() => {
    const dates: string[] = []
    const today = new Date()
    
    for (let i = 1; i <= 14; i++) {
        const date = new Date(today)
        date.setDate(today.getDate() + i)
        
        // Skip Sundays
        if (date.getDay() !== 0) {
            dates.push(date.toISOString().split('T')[0])
        }
    }
    
    return dates
})

function formatDate(dateStr: string): string {
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' })
}

function nextStep() {
    if (currentStep.value === 1) {
        // Validate vehicle details
        if (!form.vehicle_registration || !form.vehicle_make || !form.vehicle_model) {
            return
        }
    } else if (currentStep.value === 2) {
        // Validate service and booking preferences
        if (!form.requested_service || !form.scheduled_date || !form.scheduled_time) {
            return
        }
    }
    
    if (currentStep.value < totalSteps) {
        currentStep.value++
    }
}

function previousStep() {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

function submit() {
    if (!form.customer_first_name || !form.customer_last_name || !form.customer_email || !form.customer_phone) {
        alert('Please complete your contact details')
        return
    }
    if (!form.requested_service) {
        alert('Please select a required service')
        return
    }
    
    form.post(route('/book-online'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            console.error('Booking submission errors:', errors)
            // Scroll to top to show errors
            window.scrollTo({ top: 0, behavior: 'smooth' })
        },
        onSuccess: () => {
            console.log('Booking submitted successfully')
        }
    })
}
</script>

<template>
    <Head title="Book Online - Doyen Auto Services" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('/')" class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-electric-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">D</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ garageName }}</span>
                    </Link>
                    <a :href="garageTelHref" class="text-sm text-electric-600 hover:text-electric-700 font-medium">
                        Call us: {{ garagePhone }}
                    </a>
                </div>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Error Messages -->
            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-red-800 mb-2">Please correct the following errors:</h3>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <div
                        v-for="step in totalSteps"
                        :key="step"
                        class="flex-1"
                        :class="{ 'mr-2': step < totalSteps }"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full font-semibold transition"
                                :class="step <= currentStep ? 'bg-electric-600 text-white' : 'bg-gray-200 text-gray-500'"
                            >
                                {{ step }}
                            </div>
                            <div
                                v-if="step < totalSteps"
                                class="flex-1 h-1 mx-2 transition"
                                :class="step < currentStep ? 'bg-electric-600' : 'bg-gray-200'"
                            ></div>
                        </div>
                        <div class="mt-2 text-xs font-medium text-gray-600 text-center">
                            {{ step === 1 ? 'Vehicle Info' : step === 2 ? 'Service & Schedule' : 'Your Details' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                <!-- Step 1: Customer Details -->
                <div v-if="currentStep === 3">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Your Contact Details</h2>
                    <p class="text-gray-600 mb-6">Final step: tell us who is requesting this service</p>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Type <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input v-model="form.customer_type" type="radio" value="existing" class="text-electric-600 focus:ring-electric-600 border-gray-300" />
                                Existing Customer
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input v-model="form.customer_type" type="radio" value="new" class="text-electric-600 focus:ring-electric-600 border-gray-300" />
                                New Customer
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_first_name"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-500': form.errors.customer_first_name }"
                                required
                            />
                            <p v-if="form.errors.customer_first_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.customer_first_name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_last_name"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-500': form.errors.customer_last_name }"
                                required
                            />
                            <p v-if="form.errors.customer_last_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.customer_last_name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_email"
                                type="email"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-500': form.errors.customer_email }"
                                required
                            />
                            <p v-if="form.errors.customer_email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.customer_email }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_phone"
                                type="tel"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-500': form.errors.customer_phone }"
                                placeholder="e.g. 07XXX XXXXXX"
                                required
                            />
                            <p v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-600">
                                {{ form.errors.customer_phone }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_address"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-300': form.errors.customer_address }"
                                placeholder="Street address"
                            />
                            <p v-if="form.errors.customer_address" class="mt-1 text-xs text-red-600">{{ form.errors.customer_address }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Postcode <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.customer_postcode"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-300': form.errors.customer_postcode }"
                                placeholder="e.g. G73 1SP"
                            />
                            <p v-if="form.errors.customer_postcode" class="mt-1 text-xs text-red-600">{{ form.errors.customer_postcode }}</p>
                        </div>

                        <!-- Optional Portal Account Creation -->
                        <div class="md:col-span-2">
                            <div class="border border-electric-200 rounded-xl p-4 bg-electric-50">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        v-model="form.create_account"
                                        class="w-4 h-4 rounded border-gray-300 text-electric-600 focus:ring-electric-600 cursor-pointer"
                                    />
                                    <span class="text-sm font-semibold text-gray-900">Create a Customer Portal account</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1 ml-7">Track your bookings, view invoices and quotes online — free &amp; instant access.</p>

                                <div v-if="form.create_account" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Password <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.password"
                                            type="password"
                                            autocomplete="new-password"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                            :class="{ 'border-red-300': form.errors.password }"
                                            placeholder="At least 8 characters"
                                        />
                                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Confirm Password <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.password_confirmation"
                                            type="password"
                                            autocomplete="new-password"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                            placeholder="Repeat password"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Vehicle Details -->
                <div v-if="currentStep === 1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Vehicle Information</h2>
                    <p class="text-gray-600 mb-6">Start with the registration number, then complete vehicle details</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Registration Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.vehicle_registration"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 shadow-sm uppercase transition-colors"
                                    :class="[
                                        form.errors.vehicle_registration ? 'border-red-500' : '',
                                        isLookingUp ? 'pr-10' : '',
                                        lookupSuccess ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'focus:border-electric-600 focus:ring-electric-600'
                                    ]"
                                    :readonly="lookupSuccess"
                                    placeholder="e.g. AB12 CDE"
                                    required
                                />
                                <!-- Loading spinner -->
                                <div v-if="isLookingUp" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-5 w-5 text-electric-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <!-- Lookup status message -->
                            <p v-if="lookupMessage" class="mt-2 text-sm" :class="{
                                'text-green-600': lookupSuccess,
                                'text-yellow-600': lookupError,
                                'text-electric-600': isLookingUp
                            }">
                                {{ lookupMessage }}
                            </p>
                            <p v-if="form.errors.vehicle_registration" class="mt-1 text-sm text-red-600">
                                {{ form.errors.vehicle_registration }}
                            </p>
                            <p v-if="!lookupMessage && !form.errors.vehicle_registration" class="mt-1 text-xs text-gray-500">
                                Enter registration number to auto-fill vehicle details from DVLA
                            </p>
                        </div>

                        <!-- Vehicle Identity Card — shown after successful DVLA lookup -->
                        <div v-if="lookupSuccess && dvlaCard" class="md:col-span-2">
                            <div class="bg-gradient-to-br from-slate-800 via-navy-900 to-navy-950 rounded-2xl p-5 text-white shadow-xl border border-navy-700">
                                <div class="flex items-start gap-5">
                                    <!-- Car silhouette SVG -->
                                    <div class="hidden sm:flex flex-col items-center justify-center flex-shrink-0 w-28 h-20 bg-navy-800/50 rounded-xl p-2">
                                        <svg viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full opacity-80">
                                            <!-- Body -->
                                            <path d="M10 38 L18 22 Q22 16 30 15 L75 13 Q88 13 96 20 L110 38 Z" fill="#94a3b8"/>
                                            <!-- Roof -->
                                            <path d="M32 15 L40 6 Q44 3 52 3 L70 3 Q78 3 82 8 L90 15 Z" fill="#cbd5e1"/>
                                            <!-- Windows -->
                                            <path d="M34 14 L41 7 Q44 4.5 51 4.5 L65 4.5 L78 14 Z" fill="#1e293b" opacity="0.7"/>
                                            <!-- Wheels -->
                                            <circle cx="32" cy="40" r="9" fill="#1e293b" stroke="#64748b" stroke-width="2"/>
                                            <circle cx="32" cy="40" r="4" fill="#475569"/>
                                            <circle cx="88" cy="40" r="9" fill="#1e293b" stroke="#64748b" stroke-width="2"/>
                                            <circle cx="88" cy="40" r="4" fill="#475569"/>
                                            <!-- Ground line -->
                                            <line x1="5" y1="49" x2="115" y2="49" stroke="#475569" stroke-width="1" stroke-dasharray="4 3"/>
                                        </svg>
                                        <span class="text-slate-400 text-xs mt-1 font-medium uppercase tracking-wider">VDG</span>
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <!-- UK-style number plate -->
                                        <div class="inline-flex items-center bg-yellow-400 text-black font-black text-lg px-4 py-1 rounded-md tracking-[0.2em] uppercase shadow font-mono border-2 border-yellow-500 mb-3">
                                            {{ form.vehicle_registration.toUpperCase() }}
                                        </div>

                                        <!-- Make / Model -->
                                        <div class="flex items-baseline gap-2 flex-wrap">
                                            <h3 class="text-2xl font-black text-white tracking-wide uppercase leading-none">
                                                {{ form.vehicle_make || 'Unknown Make' }}
                                            </h3>
                                            <span v-if="form.vehicle_model" class="text-xl font-bold text-slate-200 uppercase">{{ form.vehicle_model }}</span>
                                            <span v-if="dvlaCard.year" class="text-slate-300 text-base font-medium">· {{ dvlaCard.year }}</span>
                                        </div>

                                        <!-- Detail chips -->
                                        <div class="flex flex-wrap gap-1.5 mt-3">
                                            <span v-if="form.vehicle_colour" class="bg-navy-800 px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-200">🎨 {{ form.vehicle_colour }}</span>
                                            <span v-if="form.vehicle_fuel_type" class="bg-navy-800 px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-200">⛽ {{ form.vehicle_fuel_type }}</span>
                                            <span v-if="form.vehicle_engine_size" class="bg-navy-800 px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-200">⚙ {{ form.vehicle_engine_size }}cc</span>
                                            <span v-if="form.vehicle_transmission" class="bg-navy-800 px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-200">🔧 {{ form.vehicle_transmission }}</span>
                                            <span v-if="dvlaCard.co2" class="bg-navy-800 px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-200">🌿 {{ dvlaCard.co2 }}g CO₂/km</span>
                                            <span v-if="dvlaCard.wheelplan" class="bg-navy-800 px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-200 capitalize">{{ dvlaCard.wheelplan.toLowerCase() }}</span>
                                        </div>

                                        <!-- MOT + Tax status badges -->
                                        <div class="flex flex-wrap gap-4 mt-3">
                                            <div v-if="dvlaCard.motStatus" class="flex items-center gap-1.5"
                                                :class="dvlaCard.motStatus.toUpperCase() === 'VALID' ? 'text-green-400' : 'text-red-400'">
                                                <svg v-if="dvlaCard.motStatus.toUpperCase() === 'VALID'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                <span class="text-xs font-semibold">MOT {{ dvlaCard.motStatus }}<span v-if="dvlaCard.motDate"> · {{ dvlaCard.motDate }}</span></span>
                                            </div>
                                            <div v-if="dvlaCard.taxStatus" class="flex items-center gap-1.5"
                                                :class="dvlaCard.taxStatus.toUpperCase() === 'TAXED' ? 'text-green-400' : 'text-amber-400'">
                                                <svg v-if="dvlaCard.taxStatus.toUpperCase() === 'TAXED'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                <span class="text-xs font-semibold">Tax {{ dvlaCard.taxStatus }}<span v-if="dvlaCard.taxDate"> · {{ dvlaCard.taxDate }}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Make <span class="text-red-500">*</span>
                                <span v-if="lookupSuccess && form.vehicle_make" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                            </label>
                            <input
                                v-model="form.vehicle_make"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="[
                                    form.errors.vehicle_make ? 'border-red-500' : '',
                                    lookupSuccess && form.vehicle_make ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'focus:border-electric-600 focus:ring-electric-600'
                                ]"
                                :readonly="lookupSuccess && !!form.vehicle_make"
                                placeholder="e.g. Ford, BMW, etc."
                                required
                            />
                            <p v-if="form.errors.vehicle_make" class="mt-1 text-sm text-red-600">
                                {{ form.errors.vehicle_make }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Model <span class="text-red-500">*</span>
                                <span v-if="lookupSuccess && form.vehicle_model" class="text-electric-400" title="Auto-filled from Vehicle Data">🔒</span>
                            </label>
                            <input
                                v-model="form.vehicle_model"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="[
                                    form.errors.vehicle_model ? 'border-red-500' : '',
                                    lookupSuccess && form.vehicle_model ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'focus:border-electric-600 focus:ring-electric-600'
                                ]"
                                :readonly="lookupSuccess && !!form.vehicle_model"
                                placeholder="e.g. Focus, 320d, A-Class…"
                                required
                            />
                            <p v-if="form.errors.vehicle_model" class="mt-1 text-sm text-red-600">
                                {{ form.errors.vehicle_model }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Year <span class="text-gray-400 text-xs">(Optional)</span>
                                <span v-if="lookupSuccess && form.vehicle_year" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                            </label>
                            <input
                                v-model="form.vehicle_year"
                                type="number"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="lookupSuccess && form.vehicle_year ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'focus:border-electric-600 focus:ring-electric-600'"
                                :readonly="lookupSuccess && !!form.vehicle_year"
                                placeholder="e.g. 2020"
                                min="1900"
                                :max="new Date().getFullYear() + 1"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Colour <span class="text-gray-400 text-xs">(Optional)</span>
                                <span v-if="lookupSuccess && form.vehicle_colour" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                            </label>
                            <input
                                v-model="form.vehicle_colour"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="lookupSuccess && form.vehicle_colour ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'focus:border-electric-600 focus:ring-electric-600'"
                                :readonly="lookupSuccess && !!form.vehicle_colour"
                                placeholder="e.g. Silver"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Current Mileage <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <input
                                v-model="form.vehicle_mileage"
                                type="number"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                placeholder="e.g. 45000"
                                min="0"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Fuel Type
                                <span v-if="lookupSuccess && form.vehicle_fuel_type" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                                <span v-else class="text-gray-400 text-xs">(Auto-filled)</span>
                            </label>
                            <input
                                v-model="form.vehicle_fuel_type"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="lookupSuccess && form.vehicle_fuel_type ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'bg-gray-50 focus:border-electric-600 focus:ring-electric-600'"
                                :readonly="lookupSuccess && !!form.vehicle_fuel_type"
                                placeholder="e.g. PETROL, DIESEL"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Engine Size (cc)
                                <span v-if="lookupSuccess && form.vehicle_engine_size" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                                <span v-else class="text-gray-400 text-xs">(Auto-filled)</span>
                            </label>
                            <input
                                v-model="form.vehicle_engine_size"
                                type="number"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="lookupSuccess && form.vehicle_engine_size ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'bg-gray-50 focus:border-electric-600 focus:ring-electric-600'"
                                :readonly="lookupSuccess && !!form.vehicle_engine_size"
                                placeholder="e.g. 1998"
                                min="0"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                MOT Expiry Date
                                <span v-if="lookupSuccess && form.vehicle_mot_due_date" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                                <span v-else class="text-gray-400 text-xs">(Auto-filled)</span>
                            </label>
                            <input
                                v-model="form.vehicle_mot_due_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="lookupSuccess && form.vehicle_mot_due_date ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'bg-gray-50 focus:border-electric-600 focus:ring-electric-600'"
                                :readonly="lookupSuccess && !!form.vehicle_mot_due_date"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
                                Tax Due Date
                                <span v-if="lookupSuccess && form.vehicle_tax_due_date" class="text-electric-400" title="Auto-filled from DVLA">🔒</span>
                                <span v-else class="text-gray-400 text-xs">(Auto-filled)</span>
                            </label>
                            <input
                                v-model="form.vehicle_tax_due_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm transition-colors"
                                :class="lookupSuccess && form.vehicle_tax_due_date ? 'bg-electric-50 cursor-not-allowed text-gray-600 focus:border-electric-200 focus:ring-electric-200' : 'bg-gray-50 focus:border-electric-600 focus:ring-electric-600'"
                                :readonly="lookupSuccess && !!form.vehicle_tax_due_date"
                            />
                        </div>
                    </div>
                </div>

                <!-- Step 3: Appointment Details -->
                <div v-if="currentStep === 2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Choose Your Appointment</h2>
                    <p class="text-gray-600 mb-6">Select required service, request a quote, and choose date & availability time</p>

                    <div class="space-y-6">
                        <!-- Required Service -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Service Required <span class="text-red-500">*</span>
                            </label>
                            <select
                                :value="form.requested_service"
                                @change="selectService(($event.target as HTMLSelectElement).value)"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                :class="{ 'border-red-500': form.errors.requested_service }"
                                required
                            >
                                <option value="">Select a service</option>
                                <template v-for="cat in bookingCategories" :key="cat.group">
                                    <optgroup :label="cat.group">
                                        <option v-for="service in cat.items" :key="service.value" :value="service.value">
                                            {{ service.label }}
                                        </option>
                                    </optgroup>
                                </template>
                            </select>
                            <p v-if="form.requested_service" class="mt-2 text-xs text-gray-600">
                                Estimated duration: {{ serviceOptions.find((service: any) => service.value === form.requested_service)?.duration }}
                            </p>
                            <p v-if="form.errors.requested_service" class="mt-1 text-sm text-red-600">
                                {{ form.errors.requested_service }}
                            </p>
                        </div>

                        <!-- Quote Request -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Quote Request <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <textarea
                                v-model="form.quote_request"
                                rows="2"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                placeholder="Tell us what quote you need (parts, labour, programming, etc.)"
                            ></textarea>
                        </div>

                        <!-- Date Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Preferred Date <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 md:grid-cols-7 gap-2">
                                <button
                                    v-for="date in availableDates"
                                    :key="date"
                                    type="button"
                                    @click="form.scheduled_date = date"
                                    class="p-3 rounded-lg border-2 text-center transition hover:border-electric-600"
                                    :class="form.scheduled_date === date ? 'border-electric-600 bg-electric-50' : 'border-gray-200'"
                                >
                                    <div class="text-xs font-medium text-gray-900">{{ formatDate(date) }}</div>
                                </button>
                            </div>
                            <p v-if="form.errors.scheduled_date" class="mt-1 text-sm text-red-600">
                                {{ form.errors.scheduled_date }}
                            </p>
                        </div>

                        <!-- Time Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Preferred Time <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                                <button
                                    v-for="time in timeSlots"
                                    :key="time"
                                    type="button"
                                    @click="form.scheduled_time = time"
                                    class="p-2 rounded-lg border-2 text-sm text-center transition hover:border-electric-600"
                                    :class="form.scheduled_time === time ? 'border-electric-600 bg-electric-50 font-semibold' : 'border-gray-200'"
                                >
                                    {{ time }}
                                </button>
                            </div>
                            <p v-if="form.errors.scheduled_time" class="mt-1 text-sm text-red-600">
                                {{ form.errors.scheduled_time }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                What needs doing? <span class="text-gray-400 text-xs">(Optional but helpful)</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                placeholder="e.g. Squeaking noise from brakes, dashboard warning light, etc."
                            ></textarea>
                        </div>

                        <!-- Customer Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Additional Notes <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <textarea
                                v-model="form.customer_notes"
                                rows="2"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-electric-600 focus:ring-electric-600"
                                placeholder="Any special requirements or requests?"
                            ></textarea>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Attach Photos / Documents <span class="text-gray-400 text-xs">(Optional — max 5 files, 10MB each)</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Upload photos of warning lights, fault descriptions, or relevant documents (JPG, PNG, PDF, DOC).</p>
                            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <div class="flex flex-col items-center justify-center pt-3 pb-3">
                                    <svg class="w-7 h-7 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-400">Images &amp; PDFs accepted</p>
                                </div>
                                <input type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx" @change="handleFiles" />
                            </label>
                            <!-- File list -->
                            <ul v-if="selectedFiles.length" class="mt-2 space-y-1">
                                <li v-for="(file, i) in selectedFiles" :key="i" class="flex items-center justify-between text-sm text-gray-700 bg-gray-50 rounded px-3 py-1">
                                    <span class="truncate max-w-xs">{{ file.name }}</span>
                                    <button type="button" @click="removeFile(i)" class="ml-2 text-red-400 hover:text-red-600 text-xs">Remove</button>
                                </li>
                            </ul>
                            <p v-if="form.errors.attachments" class="mt-1 text-sm text-red-600">{{ form.errors.attachments }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        @click="previousStep"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back
                    </button>
                    <div v-else></div>

                    <button
                        v-if="currentStep < totalSteps"
                        type="button"
                        @click="nextStep"
                        class="inline-flex items-center px-6 py-3 bg-electric-600 rounded-lg text-sm font-medium text-white hover:bg-electric-700 transition"
                    >
                        Continue
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button
                        v-else
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-8 py-3 bg-green-600 rounded-lg text-sm font-medium text-white hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Submitting...' : 'Confirm Booking' }}
                    </button>
                </div>
            </div>

            <!-- Trust Indicators -->
            <div class="mt-8 text-center">
                <div class="flex flex-wrap justify-center gap-6 text-xs text-gray-500">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>DVSA Approved</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Instant Confirmation</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>No Payment Required</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
