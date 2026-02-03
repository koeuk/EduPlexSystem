<script setup>
import { watch } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
    show: Boolean,
    maxWidth: {
        type: String,
        default: 'md'
    },
    title: String
})

const emit = defineEmits(['close'])

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
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
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50"
                    @click="emit('close')"
                />

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="show"
                            :class="['relative bg-white rounded-lg shadow-xl w-full', maxWidthClass[maxWidth]]"
                        >
                            <!-- Header -->
                            <div v-if="title" class="flex items-center justify-between p-4 border-b">
                                <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                                <button
                                    @click="emit('close')"
                                    class="p-1 rounded-lg hover:bg-gray-100 text-gray-500"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div v-if="$slots.footer" class="flex items-center justify-end gap-3 p-4 border-t">
                                <slot name="footer" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
