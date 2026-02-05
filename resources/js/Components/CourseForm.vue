<script setup>
import { ref, computed, watch } from 'vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { BookOpen, Settings, Upload, DollarSign } from 'lucide-vue-next'

const props = defineProps({
    modelValue: Object,
    categories: Array,
    levelOptions: Array,
    statusOptions: Array,
    pricingTypeOptions: Array,
    isEdit: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue', 'imageChange', 'fieldUpdate'])

// Helper to update form fields - emit event for parent to handle
const updateField = (field, value) => {
    emit('fieldUpdate', { field, value })
}

const imagePreview = ref(null)

const featuredOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]

// Computed property to check if course is free
const isFree = computed(() => props.modelValue.pricing_type === 'free')

// Watch pricing type changes
watch(() => props.modelValue.pricing_type, (newValue) => {
    if (newValue === 'free') {
        emit('fieldUpdate', { field: 'price', value: 0 })
    }
})

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
    document.getElementById('course-image-upload').click()
}

// Get current image URL for preview
const getCurrentImage = () => {
    if (imagePreview.value) return imagePreview.value
    if (props.modelValue.image_url) {
        if (props.modelValue.image_url.startsWith('http') || props.modelValue.image_url.startsWith('/storage/')) {
            return props.modelValue.image_url
        }
        return `/storage/${props.modelValue.image_url}`
    }
    if (props.modelValue.thumbnail_url) {
        return props.modelValue.thumbnail_url
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
                    <BookOpen class="w-5 h-5 text-gray-600" />
                    <h3 class="font-semibold text-gray-900">Basic Information</h3>
                </div>

                <div class="space-y-4">
                    <FormInput
                        :modelValue="modelValue.course_name"
                        @update:modelValue="updateField('course_name', $event)"
                        label="Course Name"
                        placeholder="Enter course name"
                        :error="modelValue.errors?.course_name"
                        required
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <FormInput
                            :modelValue="modelValue.course_code"
                            @update:modelValue="updateField('course_code', $event)"
                            label="Course Code"
                            placeholder="e.g., CS101"
                            :error="modelValue.errors?.course_code"
                            required
                        />
                        <FormSelect
                            :modelValue="modelValue.category_id"
                            @update:modelValue="updateField('category_id', $event)"
                            label="Category"
                            :options="categories?.map(c => ({ value: c.id, label: c.category_name }))"
                            placeholder="Select category"
                            :error="modelValue.errors?.category_id"
                        />
                    </div>

                    <FormInput
                        :modelValue="modelValue.description"
                        @update:modelValue="updateField('description', $event)"
                        label="Description"
                        type="textarea"
                        placeholder="Describe what students will learn..."
                        :error="modelValue.errors?.description"
                    />

                    <FormSelect
                        v-if="isEdit && statusOptions"
                        :modelValue="modelValue.status"
                        @update:modelValue="updateField('status', $event)"
                        label="Status"
                        :options="statusOptions"
                        :error="modelValue.errors?.status"
                    />
                </div>
            </div>

            <!-- Course Details Section -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <Settings class="w-5 h-5 text-gray-600" />
                    <h3 class="font-semibold text-gray-900">Course Details</h3>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <FormSelect
                            :modelValue="modelValue.level"
                            @update:modelValue="updateField('level', $event)"
                            label="Level"
                            :options="levelOptions"
                            :error="modelValue.errors?.level"
                            required
                        />
                        <FormInput
                            :modelValue="modelValue.instructor_name"
                            @update:modelValue="updateField('instructor_name', $event)"
                            label="Instructor Name"
                            placeholder="Instructor name"
                            :error="modelValue.errors?.instructor_name"
                        />
                    </div>

                    <!-- Pricing Section -->
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-2 mb-3">
                            <DollarSign class="w-4 h-4 text-gray-600" />
                            <span class="text-sm font-medium text-gray-700">Pricing</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <FormSelect
                                :modelValue="modelValue.pricing_type"
                                @update:modelValue="updateField('pricing_type', $event)"
                                label="Pricing Type"
                                :options="pricingTypeOptions"
                                placeholder="Select pricing type"
                                :error="modelValue.errors?.pricing_type"
                                required
                            />
                            <FormInput
                                :modelValue="modelValue.price"
                                @update:modelValue="updateField('price', $event)"
                                label="Price ($)"
                                type="number"
                                placeholder="0.00"
                                :error="modelValue.errors?.price"
                                :disabled="isFree"
                                :class="{ 'opacity-50': isFree }"
                                required
                            />
                        </div>
                        <p v-if="isFree" class="mt-2 text-xs text-green-600">
                            This course is free for all students
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <FormInput
                            :modelValue="modelValue.duration_hours"
                            @update:modelValue="updateField('duration_hours', $event)"
                            label="Duration (hours)"
                            type="number"
                            placeholder="Hours"
                            :error="modelValue.errors?.duration_hours"
                        />
                        <FormInput
                            :modelValue="modelValue.enrollment_limit"
                            @update:modelValue="updateField('enrollment_limit', $event)"
                            label="Enrollment Limit"
                            type="number"
                            placeholder="Unlimited"
                            :error="modelValue.errors?.enrollment_limit"
                        />
                    </div>

                    <FormSelect
                        :modelValue="modelValue.is_featured"
                        @update:modelValue="updateField('is_featured', $event)"
                        label="Featured Course"
                        :options="featuredOptions"
                        :error="modelValue.errors?.is_featured"
                    />
                </div>
            </div>
        </div>

        <!-- Right Column - Image Upload -->
        <div class="w-72 shrink-0">
            <div class="sticky top-0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                <div
                    @click="triggerImageInput"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors aspect-video flex flex-col items-center justify-center"
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
                        <p class="text-sm font-medium text-gray-700">Click to upload image</p>
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 2MB</p>
                    </template>
                </div>
                <input
                    id="course-image-upload"
                    type="file"
                    accept="image/*"
                    @change="handleImageChange"
                    class="hidden"
                />
                <p v-if="modelValue.errors?.image" class="mt-2 text-sm text-red-500">
                    {{ modelValue.errors.image }}
                </p>
                <p class="mt-2 text-xs text-gray-500">Course thumbnail image</p>
            </div>
        </div>
    </div>
</template>
