<script setup>
import { watch } from 'vue'
import { X, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    show: Boolean,
    title: String,
    description: String,
    icon: Object,
    loading: {
        type: Boolean,
        default: false
    },
    closeOnClickOutside: {
        type: Boolean,
        default: true
    },
    submitText: {
        type: String,
        default: 'Submit'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    showFooter: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['close', 'submit'])

const close = () => {
    if (!props.loading) {
        emit('close')
    }
}

const handleBackdropClick = () => {
    if (props.closeOnClickOutside && !props.loading) {
        emit('close')
    }
}

const handleSubmit = () => {
    emit('submit')
}

watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = ''
    }
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-hidden">
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50"
                    @click="handleBackdropClick"
                />

                <!-- Slide Panel -->
                <div class="fixed inset-y-0 right-0 flex max-w-full">
                    <Transition
                        enter-active-class="transform transition ease-out duration-300"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transform transition ease-in duration-200"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div
                            v-if="show"
                            class="w-screen max-w-2xl bg-white shadow-xl flex flex-col h-full"
                        >
                            <!-- Header -->
                            <div class="flex items-start justify-between px-6 py-4 border-b bg-gray-50">
                                <div class="flex items-center gap-3">
                                    <div v-if="icon" class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <component :is="icon" class="w-5 h-5 text-primary-600" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">
                                            <slot name="title">{{ title }}</slot>
                                        </h2>
                                        <p v-if="description || $slots.description" class="text-sm text-gray-500">
                                            <slot name="description">{{ description }}</slot>
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="close"
                                    :disabled="loading"
                                    class="p-2 rounded-lg hover:bg-gray-200 text-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 overflow-y-auto p-6">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div v-if="showFooter" class="flex items-center justify-between gap-3 px-6 py-4 border-t bg-gray-50">
                                <button
                                    type="button"
                                    @click="close"
                                    :disabled="loading"
                                    class="btn btn-secondary btn-md"
                                >
                                    {{ cancelText }}
                                </button>
                                <button
                                    type="button"
                                    @click="handleSubmit"
                                    :disabled="loading"
                                    class="btn btn-primary btn-md flex-1 max-w-md"
                                >
                                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                    {{ submitText }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
