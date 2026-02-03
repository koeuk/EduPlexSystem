<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, Layers, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    items: Object,
    filters: Object,
})

const modules = props.items

const search = ref(props.filters?.search || '')
const deleteModal = ref(false)
const moduleToDelete = ref(null)

const columns = [
    { key: 'order', label: '#', class: 'w-16' },
    { key: 'module_title', label: 'Module Title' },
    { key: 'lessons_count', label: 'Lessons', class: 'w-24' },
    { key: 'duration', label: 'Duration', class: 'w-24' },
    { key: 'is_published', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-32' },
]

const applyFilters = () => {
    router.get(`/admin/courses/${props.course.id}/modules`, {
        search: search.value,
    }, { preserveState: true })
}

const confirmDelete = (mod) => {
    moduleToDelete.value = mod
    deleteModal.value = true
}

const deleteModule = () => {
    router.delete(`/admin/courses/${props.course.id}/modules/${moduleToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            moduleToDelete.value = null
        },
    })
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Modules - ${course.course_name}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/courses/${course.id}`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Course Modules</h1>
                        <p class="text-gray-500">{{ course.course_name }}</p>
                    </div>
                </div>
                <Link :href="`/admin/courses/${course.id}/modules/create`" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Module
                </Link>
            </div>

            <!-- Filters -->
            <div class="card p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search modules..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="modules?.data || []" :pagination="modules">
                <template #order="{ row }">
                    <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
                        {{ row.module_order }}
                    </span>
                </template>
                <template #module_title="{ row }">
                    <div>
                        <p class="font-medium">{{ row.module_title }}</p>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ row.description || 'No description' }}</p>
                    </div>
                </template>
                <template #lessons_count="{ row }">
                    <span class="text-sm">{{ row.lessons_count || 0 }} lessons</span>
                </template>
                <template #duration="{ row }">
                    <span class="text-sm">{{ row.duration_minutes || 0 }} min</span>
                </template>
                <template #is_published="{ row }">
                    <Badge :variant="row.is_published ? 'success' : 'gray'">
                        {{ row.is_published ? 'Published' : 'Draft' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/courses/${course.id}/lessons`" class="p-1 text-gray-500 hover:text-primary-600" title="Manage Lessons">
                            <Layers class="w-4 h-4" />
                        </Link>
                        <Link :href="`/admin/courses/${course.id}/modules/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button @click="confirmDelete(row)" class="p-1 text-gray-500 hover:text-red-600">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :show="deleteModal"
            title="Delete Module"
            :message="`Are you sure you want to delete ${moduleToDelete?.module_title}? This will also delete all lessons in this module.`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteModule"
        />
    </AdminLayout>
</template>
