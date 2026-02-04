<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import CourseEditLayout from '@/Layouts/CourseEditLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save, FileText, Settings, Upload, X } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    module: Object,
    quizzes: Array,
    lessonTypeOptions: Array,
})

const form = useForm({
    lesson_title: '',
    lesson_type: 'video',
    description: '',
    content: '',
    duration_minutes: '',
    video_duration: '',
    quiz_id: '',
    is_mandatory: false,
    image: null,
})

const imagePreview = ref(null)

const mandatoryOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]

const handleImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = () => {
    form.image = null
    imagePreview.value = null
    document.getElementById('lesson-image-upload').value = ''
}

const triggerImageInput = () => {
    document.getElementById('lesson-image-upload').click()
}

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/modules/${props.module.id}/lessons`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <CourseEditLayout :course="course">
        <Head title="Create Lesson" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <p class="text-sm text-gray-500">{{ module.module_title }}</p>
                        <h1 class="text-2xl font-bold text-gray-900">Create Lesson</h1>
                    </div>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-md"
                >
                    <Save class="w-4 h-4 mr-2" />
                    Create Lesson
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <FileText class="w-5 h-5 text-gray-600" />
                        <h3 class="font-semibold text-gray-900">Basic Information</h3>
                    </div>

                    <div class="space-y-4">
                        <FormInput
                            v-model="form.lesson_title"
                            label="Lesson Title"
                            placeholder="Enter lesson title"
                            :error="form.errors.lesson_title"
                            required
                        />

                        <FormSelect
                            v-model="form.lesson_type"
                            label="Lesson Type"
                            :options="lessonTypeOptions"
                            :error="form.errors.lesson_type"
                            required
                        />

                        <FormInput
                            v-model="form.description"
                            label="Description"
                            type="textarea"
                            placeholder="Describe what this lesson covers..."
                            :error="form.errors.description"
                            :rows="3"
                        />
                    </div>
                </div>

                <!-- Lesson Details -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Settings class="w-5 h-5 text-gray-600" />
                        <h3 class="font-semibold text-gray-900">Lesson Details</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.duration_minutes"
                                label="Duration (minutes)"
                                type="number"
                                placeholder="e.g., 30"
                                :error="form.errors.duration_minutes"
                            />

                            <FormSelect
                                v-model="form.is_mandatory"
                                label="Required"
                                :options="mandatoryOptions"
                                :error="form.errors.is_mandatory"
                            />
                        </div>

                        <!-- Content for text type -->
                        <FormInput
                            v-if="form.lesson_type === 'text'"
                            v-model="form.content"
                            label="Content"
                            type="textarea"
                            placeholder="Enter lesson content..."
                            :error="form.errors.content"
                            :rows="8"
                        />

                        <!-- Video duration for video type -->
                        <FormInput
                            v-if="form.lesson_type === 'video'"
                            v-model="form.video_duration"
                            label="Video Duration (seconds)"
                            type="number"
                            placeholder="e.g., 600"
                            :error="form.errors.video_duration"
                        />

                        <!-- Quiz selection for quiz type -->
                        <FormSelect
                            v-if="form.lesson_type === 'quiz' && quizzes?.length"
                            v-model="form.quiz_id"
                            label="Quiz"
                            :options="quizzes?.map(q => ({ value: q.id, label: q.quiz_title }))"
                            placeholder="Select a quiz"
                            :error="form.errors.quiz_id"
                        />

                        <!-- Image Upload -->
                        <div>
                            <label class="label block mb-1.5">Lesson Image (optional)</label>
                            <div
                                @click="triggerImageInput"
                                class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors"
                            >
                                <template v-if="imagePreview">
                                    <div class="relative inline-block">
                                        <img
                                            :src="imagePreview"
                                            alt="Preview"
                                            class="max-h-40 max-w-full object-contain rounded"
                                        />
                                        <button
                                            type="button"
                                            @click.stop="removeImage"
                                            class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Click to change</p>
                                </template>
                                <template v-else>
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <Upload class="w-6 h-6 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-700">Click to upload image</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 2MB</p>
                                </template>
                            </div>
                            <input
                                id="lesson-image-upload"
                                type="file"
                                accept="image/*"
                                @change="handleImageChange"
                                class="hidden"
                            />
                            <p v-if="form.errors.image" class="mt-2 text-sm text-red-500">
                                {{ form.errors.image }}
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </CourseEditLayout>
</template>
