<script setup>
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { FileText, Settings } from 'lucide-vue-next'

const props = defineProps({
    modelValue: Object,
    modules: {
        type: Array,
        default: () => []
    },
    lessonTypeOptions: {
        type: Array,
        required: true
    },
    quizzes: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:modelValue', 'fieldUpdate'])

// Helper to update form fields - emit event for parent to handle
const updateField = (field, value) => {
    emit('fieldUpdate', { field, value })
}

const mandatoryOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <FileText class="w-5 h-5 text-gray-600" />
                <h3 class="font-semibold text-gray-900">Basic Information</h3>
            </div>

            <div class="space-y-4">
                <FormInput
                    :modelValue="modelValue.lesson_title"
                    @update:modelValue="updateField('lesson_title', $event)"
                    label="Lesson Title"
                    placeholder="Enter lesson title"
                    :error="modelValue.errors?.lesson_title"
                    required
                />

                <div class="grid grid-cols-2 gap-4">
                    <FormSelect
                        :modelValue="modelValue.module_id"
                        @update:modelValue="updateField('module_id', $event)"
                        label="Module"
                        :options="modules?.map(m => ({ value: m.id, label: m.module_title }))"
                        placeholder="Select module (optional)"
                        :error="modelValue.errors?.module_id"
                    />

                    <FormSelect
                        :modelValue="modelValue.lesson_type"
                        @update:modelValue="updateField('lesson_type', $event)"
                        label="Lesson Type"
                        :options="lessonTypeOptions"
                        :error="modelValue.errors?.lesson_type"
                        required
                    />
                </div>

                <FormInput
                    :modelValue="modelValue.description"
                    @update:modelValue="updateField('description', $event)"
                    label="Description"
                    type="textarea"
                    placeholder="Describe what this lesson covers..."
                    :error="modelValue.errors?.description"
                    :rows="3"
                />
            </div>
        </div>

        <!-- Lesson Details Section -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <Settings class="w-5 h-5 text-gray-600" />
                <h3 class="font-semibold text-gray-900">Lesson Details</h3>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <FormInput
                        :modelValue="modelValue.duration_minutes"
                        @update:modelValue="updateField('duration_minutes', $event)"
                        label="Duration (minutes)"
                        type="number"
                        placeholder="e.g., 30"
                        :error="modelValue.errors?.duration_minutes"
                    />

                    <FormSelect
                        :modelValue="modelValue.is_mandatory"
                        @update:modelValue="updateField('is_mandatory', $event)"
                        label="Required"
                        :options="mandatoryOptions"
                        :error="modelValue.errors?.is_mandatory"
                    />
                </div>

                <!-- Show content field for text type -->
                <FormInput
                    v-if="modelValue.lesson_type === 'text'"
                    :modelValue="modelValue.content"
                    @update:modelValue="updateField('content', $event)"
                    label="Content"
                    type="textarea"
                    placeholder="Enter lesson content..."
                    :error="modelValue.errors?.content"
                    :rows="6"
                />

                <!-- Show video duration for video type -->
                <FormInput
                    v-if="modelValue.lesson_type === 'video'"
                    :modelValue="modelValue.video_duration"
                    @update:modelValue="updateField('video_duration', $event)"
                    label="Video Duration (seconds)"
                    type="number"
                    placeholder="e.g., 600"
                    :error="modelValue.errors?.video_duration"
                />

                <!-- Show quiz selection for quiz type -->
                <FormSelect
                    v-if="modelValue.lesson_type === 'quiz' && quizzes?.length"
                    :modelValue="modelValue.quiz_id"
                    @update:modelValue="updateField('quiz_id', $event)"
                    label="Quiz"
                    :options="quizzes?.map(q => ({ value: q.id, label: q.quiz_title }))"
                    placeholder="Select a quiz"
                    :error="modelValue.errors?.quiz_id"
                />

                <FormInput
                    :modelValue="modelValue.image_url"
                    @update:modelValue="updateField('image_url', $event)"
                    label="Image URL (optional)"
                    placeholder="https://example.com/image.jpg"
                    :error="modelValue.errors?.image_url"
                />
            </div>
        </div>
    </div>
</template>
