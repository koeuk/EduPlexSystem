<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save, Upload, X } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    modules: Array,
    quizzes: Array,
    lessonTypeOptions: Array,
})

const form = useForm({
    lesson_title: '',
    module_id: '',
    lesson_type: 'text',
    description: '',
    content: '',
    video_duration: '',
    quiz_id: '',
    is_mandatory: false,
    duration_minutes: '',
    video: null,
    video_thumbnail: null,
    image: null,
})

const imagePreview = ref(null)

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

const boolOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]

const handleVideoChange = (e) => {
    form.video = e.target.files[0] || null
}

const handleThumbnailChange = (e) => {
    form.video_thumbnail = e.target.files[0] || null
}

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/lessons`)
}
</script>

<template>
    <AdminLayout>
        <Head title="Create Lesson" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link :href="`/admin/courses/${course.id}/lessons`" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Lesson</h1>
                    <p class="text-gray-500">{{ course.course_name }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Basic Information</h2>

                    <FormInput
                        v-model="form.lesson_title"
                        label="Lesson Title"
                        :error="form.errors.lesson_title"
                        required
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormSelect
                            v-model="form.module_id"
                            label="Module"
                            :options="modules?.map(m => ({ value: m.id, label: m.module_title }))"
                            placeholder="Select module (optional)"
                            :error="form.errors.module_id"
                        />

                        <FormSelect
                            v-model="form.lesson_type"
                            label="Lesson Type"
                            :options="lessonTypeOptions"
                            :error="form.errors.lesson_type"
                            required
                        />
                    </div>

                    <FormInput
                        v-model="form.description"
                        label="Description"
                        type="textarea"
                        :error="form.errors.description"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            v-model="form.duration_minutes"
                            label="Duration (minutes)"
                            type="number"
                            :error="form.errors.duration_minutes"
                        />

                        <FormSelect
                            v-model="form.is_mandatory"
                            label="Required"
                            :options="boolOptions"
                            :error="form.errors.is_mandatory"
                        />
                    </div>

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

                <!-- Video Content -->
                <div v-if="form.lesson_type === 'video'" class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Video Content</h2>

                    <div>
                        <label class="label block mb-1.5">Video File</label>
                        <input
                            type="file"
                            accept="video/mp4,video/webm,video/ogg"
                            @change="handleVideoChange"
                            class="input"
                        />
                        <p class="mt-1 text-sm text-gray-500">MP4, WebM or OGG. Max 500MB.</p>
                        <p v-if="form.errors.video" class="mt-1 text-sm text-red-500">
                            {{ form.errors.video }}
                        </p>
                    </div>

                    <FormInput
                        v-model="form.video_duration"
                        label="Video Duration (seconds)"
                        type="number"
                        min="0"
                        :error="form.errors.video_duration"
                    />

                    <div>
                        <label class="label block mb-1.5">Video Thumbnail</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleThumbnailChange"
                            class="input"
                        />
                        <p v-if="form.errors.video_thumbnail" class="mt-1 text-sm text-red-500">
                            {{ form.errors.video_thumbnail }}
                        </p>
                    </div>
                </div>

                <!-- Text Content -->
                <div v-if="form.lesson_type === 'text'" class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Text Content</h2>

                    <FormInput
                        v-model="form.content"
                        label="Content"
                        type="textarea"
                        :error="form.errors.content"
                        class="min-h-[200px]"
                    />
                </div>

                <!-- Quiz Content -->
                <div v-if="form.lesson_type === 'quiz'" class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Quiz</h2>

                    <FormSelect
                        v-model="form.quiz_id"
                        label="Select Quiz"
                        :options="quizzes?.map(q => ({ value: q.id, label: q.quiz_title }))"
                        placeholder="Select a quiz"
                        :error="form.errors.quiz_id"
                    />
                </div>

                <div class="flex justify-end space-x-3">
                    <Link :href="`/admin/courses/${course.id}/lessons`" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Create Lesson
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
