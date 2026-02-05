<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import CourseEditLayout from '@/Layouts/CourseEditLayout.vue'
import ModuleForm from '@/Components/ModuleForm.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
})

const form = useForm({
    module_title: '',
    description: '',
})

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/modules`, {
        preserveScroll: true,
    })
}

const handleFieldUpdate = ({ field, value }) => {
    form[field] = value
}
</script>

<template>
    <CourseEditLayout :course="course">
        <Head title="Create Module" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/courses/${course.id}/modules`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create Module</h1>
                        <p class="text-gray-500">Add a new module to organize course content</p>
                    </div>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-md"
                >
                    <Save class="w-4 h-4 mr-2" />
                    Create Module
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="card p-6">
                <ModuleForm v-model="form" @field-update="handleFieldUpdate" />
            </form>
        </div>
    </CourseEditLayout>
</template>
