<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import CourseEditLayout from '@/Layouts/CourseEditLayout.vue'
import CourseForm from '@/Components/CourseForm.vue'
import { Save, BookOpen } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    categories: Array,
    levelOptions: Array,
    statusOptions: Array,
})

const course = props.item
const imagePreview = ref(null)

const form = useForm({
    _method: 'PUT',
    course_name: course.course_name,
    course_code: course.course_code,
    description: course.description || '',
    category_id: course.category_id || '',
    level: course.level || 'beginner',
    duration_hours: course.duration_hours || '',
    price: course.price || 0,
    instructor_name: course.instructor_name || '',
    enrollment_limit: course.enrollment_limit || '',
    is_featured: course.is_featured || false,
    status: course.status || 'draft',
    image: null,
    image_url: course.image_url || '',
})

const submit = () => {
    form.post(`/admin/courses/${course.id}`, {
        preserveScroll: true,
    })
}

const handleImageChange = (file) => {
    form.image = file
}
</script>

<template>
    <CourseEditLayout :course="course">
        <Head title="Edit Course" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Course Information</h1>
                    <p class="text-gray-500">Update course details and settings</p>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-md"
                >
                    <Save class="w-4 h-4 mr-2" />
                    Save Changes
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="card p-6">
                <CourseForm
                    v-model="form"
                    :categories="categories"
                    :level-options="levelOptions"
                    :status-options="statusOptions"
                    :is-edit="true"
                    @image-change="handleImageChange"
                />
            </form>
        </div>
    </CourseEditLayout>
</template>
