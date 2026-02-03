<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
})

const form = useForm({
    module_title: '',
    description: '',
})

const submit = () => {
    form.post(`/admin/courses/${props.course.id}/modules`)
}
</script>

<template>
    <AdminLayout>
        <Head title="Create Module" />

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link :href="`/admin/courses/${course.id}/modules`" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Module</h1>
                    <p class="text-gray-500">{{ course.course_name }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <FormInput
                    v-model="form.module_title"
                    label="Module Title"
                    :error="form.errors.module_title"
                    required
                />

                <FormInput
                    v-model="form.description"
                    label="Description"
                    type="textarea"
                    :error="form.errors.description"
                />

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <Link :href="`/admin/courses/${course.id}/modules`" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Create Module
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
