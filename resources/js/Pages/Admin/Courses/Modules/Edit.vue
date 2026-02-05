<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import CourseEditLayout from '@/Layouts/CourseEditLayout.vue'
import ModuleForm from '@/Components/ModuleForm.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    item: Object,
})

const module = props.item

const form = useForm({
    _method: 'PUT',
    module_title: module.module_title,
    description: module.description || '',
})

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/modules/${module.id}`, {
        preserveScroll: true,
    })
}

const handleFieldUpdate = ({ field, value }) => {
    form[field] = value
}
</script>

<template>
    <CourseEditLayout :course="course">
        <Head :title="`Edit Module - ${module.module_title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/courses/${course.id}/modules`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Module</h1>
                        <p class="text-gray-500">Update module details</p>
                    </div>
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
                <ModuleForm v-model="form" @field-update="handleFieldUpdate" />
            </form>
        </div>
    </CourseEditLayout>
</template>
