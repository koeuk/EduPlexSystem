<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    modules: Array,
    quizzes: Array,
    lessonTypeOptions: Array,
})

const form = useForm({
    lesson_title: '',
    module_id: '',
    lesson_type: 'video',
    description: '',
    content: '',
    video_url: '',
    video_duration: '',
    quiz_id: '',
    is_mandatory: true,
    duration_minutes: '',
    video_thumbnail: null,
})

const boolOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]

const handleFileChange = (e) => {
    form.video_thumbnail = e.target.files[0]
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
                </div>

                <!-- Video Content -->
                <div v-if="form.lesson_type === 'video'" class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Video Content</h2>

                    <FormInput
                        v-model="form.video_url"
                        label="Video URL"
                        placeholder="https://..."
                        :error="form.errors.video_url"
                    />

                    <FormInput
                        v-model="form.video_duration"
                        label="Video Duration (seconds)"
                        type="number"
                        :error="form.errors.video_duration"
                    />

                    <div>
                        <label class="label block mb-1.5">Video Thumbnail</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleFileChange"
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
