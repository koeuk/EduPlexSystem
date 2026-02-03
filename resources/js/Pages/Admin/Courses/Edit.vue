<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    categories: Array,
    levelOptions: Array,
    statusOptions: Array,
})

const course = props.item

const form = useForm({
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
    thumbnail: null,
})

const featuredOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]

const submit = () => {
    form.post(`/admin/courses/${course.id}`, {
        _method: 'PUT',
    })
}

const handleFileChange = (e) => {
    form.thumbnail = e.target.files[0]
}
</script>

<template>
    <AdminLayout>
        <Head title="Edit Course" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/courses" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Course</h1>
                    <p class="text-gray-500">Update course information</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Basic Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput
                            v-model="form.course_name"
                            label="Course Name"
                            :error="form.errors.course_name"
                            required
                        />
                        <FormInput
                            v-model="form.course_code"
                            label="Course Code"
                            :error="form.errors.course_code"
                            required
                        />
                        <FormSelect
                            v-model="form.category_id"
                            label="Category"
                            :options="categories?.map(c => ({ value: c.id, label: c.category_name }))"
                            placeholder="Select category"
                            :error="form.errors.category_id"
                        />
                        <FormSelect
                            v-model="form.level"
                            label="Level"
                            :options="levelOptions"
                            :error="form.errors.level"
                            required
                        />
                    </div>

                    <FormInput
                        v-model="form.description"
                        label="Description"
                        type="textarea"
                        :error="form.errors.description"
                    />
                </div>

                <!-- Details -->
                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Course Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <FormInput
                            v-model="form.price"
                            label="Price ($)"
                            type="number"
                            :error="form.errors.price"
                            required
                        />
                        <FormInput
                            v-model="form.duration_hours"
                            label="Duration (hours)"
                            type="number"
                            :error="form.errors.duration_hours"
                        />
                        <FormInput
                            v-model="form.enrollment_limit"
                            label="Enrollment Limit"
                            type="number"
                            placeholder="Unlimited"
                            :error="form.errors.enrollment_limit"
                        />
                        <FormInput
                            v-model="form.instructor_name"
                            label="Instructor Name"
                            :error="form.errors.instructor_name"
                        />
                        <FormSelect
                            v-model="form.status"
                            label="Status"
                            :options="statusOptions"
                            :error="form.errors.status"
                        />
                        <FormSelect
                            v-model="form.is_featured"
                            label="Featured Course"
                            :options="featuredOptions"
                            :error="form.errors.is_featured"
                        />
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Course Thumbnail</h2>

                    <div v-if="course.thumbnail" class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Current thumbnail:</p>
                        <img :src="`/storage/${course.thumbnail}`" :alt="course.course_name"
                            class="w-48 h-32 object-cover rounded" />
                    </div>

                    <div>
                        <label class="label block mb-1.5">New Thumbnail Image</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleFileChange"
                            class="input"
                        />
                        <p v-if="form.errors.thumbnail" class="mt-1 text-sm text-red-500">
                            {{ form.errors.thumbnail }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <Link href="/admin/courses" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Update Course
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
