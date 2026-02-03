<script setup>
import Modal from './Modal.vue'
import { AlertTriangle } from 'lucide-vue-next'

defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Confirm Action'
    },
    message: {
        type: String,
        default: 'Are you sure you want to perform this action?'
    },
    confirmText: {
        type: String,
        default: 'Confirm'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    danger: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'confirm'])
</script>

<template>
    <Modal :show="show" max-width="sm" @close="emit('close')">
        <div class="text-center">
            <div :class="['mx-auto w-12 h-12 rounded-full flex items-center justify-center', danger ? 'bg-red-100' : 'bg-yellow-100']">
                <AlertTriangle :class="['w-6 h-6', danger ? 'text-red-600' : 'text-yellow-600']" />
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ title }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ message }}</p>
        </div>

        <div class="mt-6 flex justify-center gap-3">
            <button
                @click="emit('close')"
                class="btn btn-secondary btn-md"
            >
                {{ cancelText }}
            </button>
            <button
                @click="emit('confirm')"
                :class="['btn btn-md', danger ? 'btn-danger' : 'btn-primary']"
            >
                {{ confirmText }}
            </button>
        </div>
    </Modal>
</template>
