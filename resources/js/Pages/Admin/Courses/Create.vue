<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CourseForm from '@/Components/CourseForm.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    categories: Array,
    levelOptions: Array,
})

const form = useForm({
    course_name: '',
    course_code: '',
    description: '',
    category_id: '',
    level: 'beginner',
    duration_hours: '',
    price: 0,
    instructor_name: '',
    enrollment_limit: '',
    is_featured: false,
    image: null,
})

const submit = () => {
    form.post('/admin/courses', {
        preserveScroll: true,
    })
}

const handleImageChange = (file) => {
    form.image = file
}

const handleFieldUpdate = ({ field, value }) => {
    form[field] = value
}
</script>

<template>
    <AdminLayout>
        <Head title="Create Course" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/courses" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create Course</h1>
                        <p class="text-gray-500">Add a new course to the system</p>
                    </div>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-md"
                >
                    <Save class="w-4 h-4 mr-2" />
                    Create Course
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="card p-6">
                <CourseForm
                    v-model="form"
                    :categories="categories"
                    :level-options="levelOptions"
                    @image-change="handleImageChange"
                    @field-update="handleFieldUpdate"
                />
            </form>
        </div>
    </AdminLayout>
</template>
