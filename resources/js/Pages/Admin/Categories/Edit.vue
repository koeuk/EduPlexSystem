<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
})

const category = props.item

const form = useForm({
    category_name: category.category_name,
    description: category.description || '',
    icon: category.icon || '',
    image_url: category.image_url || '',
    is_active: category.is_active,
})

const iconOptions = [
    { value: 'globe', label: 'Globe (Web)' },
    { value: 'smartphone', label: 'Smartphone (Mobile)' },
    { value: 'bar-chart', label: 'Bar Chart (Data)' },
    { value: 'server', label: 'Server (DevOps)' },
    { value: 'shield', label: 'Shield (Security)' },
    { value: 'palette', label: 'Palette (Design)' },
    { value: 'code', label: 'Code (Programming)' },
    { value: 'database', label: 'Database' },
    { value: 'cpu', label: 'CPU (Hardware)' },
    { value: 'cloud', label: 'Cloud' },
]

const statusOptions = [
    { value: true, label: 'Active' },
    { value: false, label: 'Inactive' },
]

const submit = () => {
    form.put(`/admin/categories/${category.id}`)
}
</script>

<template>
    <AdminLayout>
        <Head title="Edit Category" />

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/categories" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Category</h1>
                    <p class="text-gray-500">Update category information</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <FormInput
                    v-model="form.category_name"
                    label="Category Name"
                    :error="form.errors.category_name"
                    required
                />

                <FormInput
                    v-model="form.description"
                    label="Description"
                    type="textarea"
                    :error="form.errors.description"
                />

                <FormInput
                    v-model="form.image_url"
                    label="Image URL"
                    placeholder="https://example.com/image.jpg"
                    :error="form.errors.image_url"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormSelect
                        v-model="form.icon"
                        label="Icon"
                        :options="iconOptions"
                        placeholder="Select icon"
                        :error="form.errors.icon"
                    />

                    <FormSelect
                        v-model="form.is_active"
                        label="Status"
                        :options="statusOptions"
                        :error="form.errors.is_active"
                    />
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <Link href="/admin/categories" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
