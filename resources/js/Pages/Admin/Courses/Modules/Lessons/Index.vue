<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import CourseEditLayout from '@/Layouts/CourseEditLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, FileText, ArrowLeft, Video, BookOpen, HelpCircle, FileCheck, File, Eye } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    module: Object,
    items: Object,
    filters: Object,
})

const search = ref(props.filters?.search || '')

// Delete modal state
const deleteModal = ref(false)
const lessonToDelete = ref(null)

const columns = [
    { key: 'order', label: '#', class: 'w-16' },
    { key: 'lesson_title', label: 'Lesson Title' },
    { key: 'lesson_type', label: 'Type', class: 'w-32' },
    { key: 'duration', label: 'Duration', class: 'w-24' },
    { key: 'is_mandatory', label: 'Required', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-32' },
]

const getLessonTypeIcon = (type) => {
    const icons = {
        video: Video,
        text: BookOpen,
        quiz: HelpCircle,
    }
    return icons[type] || FileText
}

const getLessonTypeVariant = (type) => {
    const variants = {
        video: 'info',
        text: 'success',
        quiz: 'warning',
    }
    return variants[type] || 'gray'
}

const applyFilters = () => {
    router.get(`/admin/courses/${props.course.id}/modules/${props.module.id}/lessons`, {
        search: search.value || undefined,
    }, { preserveState: true })
}

// Delete modal functions
const confirmDelete = (lesson) => {
    lessonToDelete.value = lesson
    deleteModal.value = true
}

const deleteLesson = () => {
    router.delete(`/admin/courses/${props.course.id}/modules/${props.module.id}/lessons/${lessonToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            lessonToDelete.value = null
        },
    })
}
</script>

<template>
    <CourseEditLayout :course="course">
        <Head :title="`Lessons - ${module.module_title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Link :href="`/admin/courses/${course.id}/modules`" class="text-gray-500 hover:text-gray-700">
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                        <span class="text-gray-400">/</span>
                        <span class="text-sm text-gray-500">{{ module.module_title }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Module Lessons</h1>
                    <p class="text-gray-500">Manage lessons for this module</p>
                </div>
                <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons/create`" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Lesson
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
                            placeholder="Search lessons..."
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
            <DataTable :columns="columns" :data="items?.data || []" :pagination="items">
                <template #order="{ row }">
                    <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
                        {{ row.lesson_order }}
                    </span>
                </template>
                <template #lesson_title="{ row }">
                    <div>
                        <p class="font-medium">{{ row.lesson_title }}</p>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ row.description || 'No description' }}</p>
                    </div>
                </template>
                <template #lesson_type="{ row }">
                    <Badge :variant="getLessonTypeVariant(row.lesson_type)">
                        <component :is="getLessonTypeIcon(row.lesson_type)" class="w-3 h-3 mr-1" />
                        {{ row.lesson_type }}
                    </Badge>
                </template>
                <template #duration="{ row }">
                    <span class="text-sm">{{ row.duration_minutes ? `${row.duration_minutes} min` : '-' }}</span>
                </template>
                <template #is_mandatory="{ row }">
                    <Badge :variant="row.is_mandatory ? 'warning' : 'gray'">
                        {{ row.is_mandatory ? 'Required' : 'Optional' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600" title="View Lesson">
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600" title="Edit Lesson">
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button @click="confirmDelete(row)" class="p-1 text-gray-500 hover:text-red-600" title="Delete Lesson">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </template>
            </DataTable>

            <!-- Empty state -->
            <div v-if="!items?.data?.length && !search" class="card p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <FileText class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No lessons yet</h3>
                <p class="text-gray-500 mb-4">Get started by creating your first lesson for this module.</p>
                <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons/create`" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Create First Lesson
                </Link>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :show="deleteModal"
            title="Delete Lesson"
            :message="`Are you sure you want to delete ${lessonToDelete?.lesson_title}? This action cannot be undone.`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteLesson"
        />
    </CourseEditLayout>
</template>
