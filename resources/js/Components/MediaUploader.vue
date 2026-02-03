<script setup>
import { ref, computed } from 'vue'
import { Upload, X, File, Image, Video, FileText } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'

const props = defineProps({
    modelValue: {
        type: [File, Object, null],
        default: null
    },
    accept: {
        type: String,
        default: 'image/*'
    },
    maxSize: {
        type: Number,
        default: 2 // MB
    },
    preview: {
        type: String,
        default: null
    },
    label: {
        type: String,
        default: 'Upload File'
    },
    hint: {
        type: String,
        default: null
    }
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const previewUrl = ref(props.preview)
const error = ref(null)
const isDragging = ref(false)

const fileIcon = computed(() => {
    if (!props.modelValue && !previewUrl.value) return Upload

    const type = props.accept
    if (type.includes('image')) return Image
    if (type.includes('video')) return Video
    if (type.includes('pdf') || type.includes('document')) return FileText
    return File
})

const isImage = computed(() => {
    return props.accept.includes('image')
})

const formattedMaxSize = computed(() => {
    return props.maxSize >= 1024 ? `${(props.maxSize / 1024).toFixed(1)} GB` : `${props.maxSize} MB`
})

function handleFileSelect(event) {
    const file = event.target.files[0]
    if (file) {
        validateAndSetFile(file)
    }
}

function handleDrop(event) {
    isDragging.value = false
    const file = event.dataTransfer.files[0]
    if (file) {
        validateAndSetFile(file)
    }
}

function validateAndSetFile(file) {
    error.value = null

    // Check file size
    const maxBytes = props.maxSize * 1024 * 1024
    if (file.size > maxBytes) {
        error.value = `File size must be less than ${formattedMaxSize.value}`
        return
    }

    // Check file type
    const acceptedTypes = props.accept.split(',').map(t => t.trim())
    const isAccepted = acceptedTypes.some(type => {
        if (type.endsWith('/*')) {
            return file.type.startsWith(type.replace('/*', '/'))
        }
        return file.type === type || file.name.endsWith(type.replace('.', ''))
    })

    if (!isAccepted) {
        error.value = `File type not accepted. Allowed: ${props.accept}`
        return
    }

    // Set preview for images
    if (file.type.startsWith('image/')) {
        previewUrl.value = URL.createObjectURL(file)
    } else {
        previewUrl.value = null
    }

    emit('update:modelValue', file)
}

function removeFile() {
    emit('update:modelValue', null)
    previewUrl.value = null
    error.value = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

function openFileDialog() {
    fileInput.value?.click()
}
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-sm font-medium text-foreground">
            {{ label }}
        </label>

        <div
            class="relative border-2 border-dashed rounded-lg p-6 transition-colors"
            :class="{
                'border-primary bg-primary/5': isDragging,
                'border-border hover:border-primary/50': !isDragging,
                'border-destructive': error
            }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
        >
            <!-- Preview or Upload Area -->
            <div v-if="previewUrl && isImage" class="relative">
                <img
                    :src="previewUrl"
                    alt="Preview"
                    class="mx-auto max-h-48 rounded-lg object-contain"
                />
                <Button
                    variant="destructive"
                    size="icon"
                    class="absolute -top-2 -right-2 h-6 w-6"
                    @click="removeFile"
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <div v-else-if="modelValue" class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <component :is="fileIcon" class="h-10 w-10 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium">{{ modelValue.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ (modelValue.size / 1024 / 1024).toFixed(2) }} MB
                        </p>
                    </div>
                </div>
                <Button variant="ghost" size="icon" @click="removeFile">
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <div v-else class="text-center cursor-pointer" @click="openFileDialog">
                <component :is="fileIcon" class="mx-auto h-12 w-12 text-muted-foreground" />
                <p class="mt-2 text-sm text-muted-foreground">
                    <span class="font-medium text-primary">Click to upload</span> or drag and drop
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ hint || `Max size: ${formattedMaxSize}` }}
                </p>
            </div>

            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                class="hidden"
                @change="handleFileSelect"
            />
        </div>

        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>
