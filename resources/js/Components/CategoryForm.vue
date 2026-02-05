<script setup>
import { ref } from 'vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { FolderOpen, Upload } from 'lucide-vue-next'

const props = defineProps({
    modelValue: Object,
})

const emit = defineEmits(['update:modelValue', 'imageChange', 'fieldUpdate'])

// Helper to update form fields - emit event for parent to handle
const updateField = (field, value) => {
    emit('fieldUpdate', { field, value })
}

const imagePreview = ref(null)

const iconOptions = [
    { value: 'globe', label: 'Globe (Web)' },
    { value: 'smartphone', label: 'Smartphone (Mobile)' },
    { value: 'bar-chart', label: 'Bar Chart (Data)' },
    { value: 'server', label: 'Server (DevOps)' },
    { value: 'shield', label: 'Shield (Security)' },
    { value: 'palette', label: 'Palette (Design)' },
    { value: 'code', label: 'Code (Programming)' },
    { value: 'database', label: 'Database' },
    { value: 'cpu', label: 'CPU (Hardware)' },
    { value: 'cloud', label: 'Cloud' },
]

const statusOptions = [
    { value: true, label: 'Active' },
    { value: false, label: 'Inactive' },
]

const handleImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        emit('imageChange', file)
        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const triggerImageInput = () => {
    document.getElementById('category-image-upload').click()
}

// Get current image URL for preview
const getCurrentImage = () => {
    if (imagePreview.value) return imagePreview.value
    if (props.modelValue.image_url) {
        // Check if it's already a full URL or a storage path
        if (props.modelValue.image_url.startsWith('http') || props.modelValue.image_url.startsWith('/storage/')) {
            return props.modelValue.image_url
        }
        return `/storage/${props.modelValue.image_url}`
    }
    return null
}
</script>

<template>
    <div class="flex gap-6">
        <!-- Left Column - Form Fields -->
        <div class="flex-1 space-y-6">
            <!-- Basic Information Section -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <FolderOpen class="w-5 h-5 text-gray-600" />
                    <h3 class="font-semibold text-gray-900">Category Information</h3>
                </div>

                <div class="space-y-4">
                    <FormInput
                        :modelValue="modelValue.category_name"
                        @update:modelValue="updateField('category_name', $event)"
                        label="Category Name"
                        placeholder="Enter category name"
                        :error="modelValue.errors?.category_name"
                        required
                    />

                    <FormInput
                        :modelValue="modelValue.description"
                        @update:modelValue="updateField('description', $event)"
                        label="Description"
                        type="textarea"
                        placeholder="Describe this category..."
                        :error="modelValue.errors?.description"
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <FormSelect
                            :modelValue="modelValue.icon"
                            @update:modelValue="updateField('icon', $event)"
                            label="Icon"
                            :options="iconOptions"
                            placeholder="Select icon"
                            :error="modelValue.errors?.icon"
                        />

                        <FormSelect
                            :modelValue="modelValue.is_active"
                            @update:modelValue="updateField('is_active', $event)"
                            label="Status"
                            :options="statusOptions"
                            :error="modelValue.errors?.is_active"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Image Upload -->
        <div class="w-56 shrink-0">
            <div class="sticky top-0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Image</label>
                <div
                    @click="triggerImageInput"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors aspect-square flex flex-col items-center justify-center"
                >
                    <template v-if="getCurrentImage()">
                        <img
                            :src="getCurrentImage()"
                            alt="Preview"
                            class="max-h-32 max-w-full object-contain rounded"
                        />
                        <p class="mt-2 text-xs text-gray-500">Click to change</p>
                    </template>
                    <template v-else>
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <Upload class="w-6 h-6 text-gray-400" />
                        </div>
                        <p class="text-sm font-medium text-gray-700">Click to upload</p>
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                    </template>
                </div>
                <input
                    id="category-image-upload"
                    type="file"
                    accept="image/*"
                    @change="handleImageChange"
                    class="hidden"
                />
                <p v-if="modelValue.errors?.image" class="mt-2 text-sm text-red-500">
                    {{ modelValue.errors.image }}
                </p>
                <p class="mt-2 text-xs text-gray-500">Category photo or icon</p>
            </div>
        </div>
    </div>
</template>
