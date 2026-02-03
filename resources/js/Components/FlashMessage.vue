<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { CheckCircle, XCircle, X } from 'lucide-vue-next'

const page = usePage()
const show = ref(false)
const message = ref('')
const type = ref('success')

const showFlash = () => {
    if (page.props.flash?.success) {
        message.value = page.props.flash.success
        type.value = 'success'
        show.value = true
    } else if (page.props.flash?.error) {
        message.value = page.props.flash.error
        type.value = 'error'
        show.value = true
    }

    if (show.value) {
        setTimeout(() => {
            show.value = false
        }, 5000)
    }
}

onMounted(showFlash)

watch(() => page.props.flash, showFlash, { deep: true })
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-[-10px]"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-[-10px]"
    >
        <div
            v-if="show"
            :class="[
                'fixed top-4 right-4 z-50 flex items-center p-4 rounded-lg shadow-lg max-w-sm',
                type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'
            ]"
        >
            <CheckCircle v-if="type === 'success'" class="w-5 h-5 mr-3 text-green-500" />
            <XCircle v-else class="w-5 h-5 mr-3 text-red-500" />
            <span class="text-sm font-medium">{{ message }}</span>
            <button
                @click="show = false"
                class="ml-4 p-1 rounded hover:bg-gray-200"
            >
                <X class="w-4 h-4" />
            </button>
        </div>
    </Transition>
</template>
