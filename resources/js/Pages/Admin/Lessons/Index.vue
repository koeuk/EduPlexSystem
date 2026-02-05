<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, ArrowLeft, Play, FileText, HelpCircle, File, Link as LinkIcon } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    items: Object,
    modules: Array,
    lessonTypeOptions: Array,
})

const lessons = props.items
const search = ref('')
const moduleFilter = ref('')
const typeFilter = ref('')
const deleteModal = ref(false)
const lessonToDelete = ref(null)

const columns = [
    { key: 'order', label: '#', class: 'w-16' },
    { key: 'lesson_title', label: 'Lesson' },
    { key: 'module', label: 'Module' },
    { key: 'lesson_type', label: 'Type', class: 'w-28' },
    { key: 'duration', label: 'Duration', class: 'w-24' },
    { key: 'is_mandatory', label: 'Required', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-24' },
]

const getTypeIcon = (type) => {
    const icons = {
        video: Play,
        text: FileText,
        quiz: HelpCircle,
    }
    return icons[type] || FileText
}

const getTypeVariant = (type) => {
    const variants = {
        video: 'info',
        text: 'gray',
        quiz: 'warning',
    }
    return variants[type] || 'gray'
}

const applyFilters = () => {
    router.get(`/admin/courses/${props.course.id}/lessons`, {
        'filter[search]': search.value || undefined,
        'filter[module_id]': moduleFilter.value || undefined,
        'filter[lesson_type]': typeFilter.value || undefined,
    }, { preserveState: true })
}

const confirmDelete = (lesson) => {
    lessonToDelete.value = lesson
    deleteModal.value = true
}

const deleteLesson = () => {
    router.delete(`/admin/courses/${props.course.id}/lessons/${lessonToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            lessonToDelete.value = null
        },
    })
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Lessons - ${course.course_name}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/courses/${course.id}`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Course Lessons</h1>
                        <p class="text-gray-500">{{ course.course_name }}</p>
                    </div>
                </div>
                <Link :href="`/admin/courses/${course.id}/lessons/create`" class="btn btn-primary btn-md">
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
                    <select v-model="moduleFilter" class="input w-full sm:w-48">
                        <option value="">All Modules</option>
                        <option v-for="mod in modules" :key="mod.id" :value="mod.id">
                            {{ mod.module_title }}
                        </option>
                    </select>
                    <select v-model="typeFilter" class="input w-full sm:w-36">
                        <option value="">All Types</option>
                        <option v-for="opt in lessonTypeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="lessons?.data || []" :pagination="lessons">
                <template #order="{ row }">
                    <span class="w-8 h-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                        {{ row.lesson_order }}
                    </span>
                </template>
                <template #lesson_title="{ row }">
                    <div>
                        <p class="font-medium">{{ row.lesson_title }}</p>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ row.description || 'No description' }}</p>
                    </div>
                </template>
                <template #module="{ row }">
                    <span class="text-sm">{{ row.module?.module_title || 'No module' }}</span>
                </template>
                <template #lesson_type="{ row }">
                    <Badge :variant="getTypeVariant(row.lesson_type)">
                        <component :is="getTypeIcon(row.lesson_type)" class="w-3 h-3 mr-1" />
                        {{ row.lesson_type }}
                    </Badge>
                </template>
                <template #duration="{ row }">
                    <span class="text-sm">{{ row.duration_minutes || 0 }} min</span>
                </template>
                <template #is_mandatory="{ row }">
                    <Badge :variant="row.is_mandatory ? 'success' : 'gray'">
                        {{ row.is_mandatory ? 'Yes' : 'No' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/courses/${course.id}/lessons/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
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
            title="Delete Lesson"
            :message="`Are you sure you want to delete ${lessonToDelete?.lesson_title}?`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteLesson"
        />
    </AdminLayout>
</template>
