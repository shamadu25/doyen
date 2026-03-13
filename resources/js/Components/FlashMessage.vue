<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'

interface Flash {
    success?: string
    error?: string
    warning?: string
    info?: string
}

const props = defineProps<{ flash: Flash }>()
const visible = ref(false)
const message = ref('')
const type = ref<'success' | 'error' | 'warning' | 'info'>('success')

const show = () => {
    if (props.flash?.success) { message.value = props.flash.success; type.value = 'success'; visible.value = true }
    else if (props.flash?.error) { message.value = props.flash.error; type.value = 'error'; visible.value = true }
    else if (props.flash?.warning) { message.value = props.flash.warning; type.value = 'warning'; visible.value = true }
    else if (props.flash?.info) { message.value = props.flash.info; type.value = 'info'; visible.value = true }

    if (visible.value) {
        setTimeout(() => { visible.value = false }, 5000)
    }
}

watch(() => props.flash, show, { deep: true })
onMounted(show)

const bgClasses: Record<string, string> = {
    success: 'bg-green-50 border-green-400 text-green-800',
    error: 'bg-red-50 border-red-400 text-red-800',
    warning: 'bg-yellow-50 border-yellow-400 text-yellow-800',
    info: 'bg-electric-50 border-electric-400 text-electric-700',
}
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
    >
        <div v-if="visible" :class="['fixed top-4 right-4 z-50 max-w-md border-l-4 p-4 rounded-lg shadow-lg', bgClasses[type]]">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium">{{ message }}</p>
                <button @click="visible = false" class="ml-4 text-current opacity-50 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
