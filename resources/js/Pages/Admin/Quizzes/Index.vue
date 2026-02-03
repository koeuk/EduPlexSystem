<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, Eye, HelpCircle } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    filters: Object,
})

const quizzes = props.items
const search = ref(props.filters?.filter?.search || '')
const deleteModal = ref(false)
const quizToDelete = ref(null)

const columns = [
    { key: 'quiz_title', label: 'Quiz Title' },
    { key: 'questions_count', label: 'Questions', class: 'w-24' },
    { key: 'attempts_count', label: 'Attempts', class: 'w-24' },
    { key: 'passing_score', label: 'Passing', class: 'w-24' },
    { key: 'time_limit', label: 'Time', class: 'w-24' },
    { key: 'is_published', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-32' },
]

const applyFilters = () => {
    router.get('/admin/quizzes', {
        'filter[search]': search.value || undefined,
    }, { preserveState: true })
}

const confirmDelete = (quiz) => {
    quizToDelete.value = quiz
    deleteModal.value = true
}

const deleteQuiz = () => {
    router.delete(`/admin/quizzes/${quizToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            quizToDelete.value = null
        },
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="Quizzes" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quizzes</h1>
                    <p class="text-gray-500">Manage course quizzes and assessments</p>
                </div>
                <Link href="/admin/quizzes/create" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Quiz
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
                            placeholder="Search quizzes..."
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
            <DataTable :columns="columns" :data="quizzes?.data || []" :pagination="quizzes">
                <template #quiz_title="{ row }">
                    <div>
                        <p class="font-medium">{{ row.quiz_title }}</p>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ row.description || 'No description' }}</p>
                    </div>
                </template>
                <template #questions_count="{ row }">
                    <div class="flex items-center gap-1">
                        <HelpCircle class="w-4 h-4 text-gray-400" />
                        <span>{{ row.questions_count || 0 }}</span>
                    </div>
                </template>
                <template #attempts_count="{ row }">
                    <span class="text-sm">{{ row.attempts_count || 0 }}</span>
                </template>
                <template #passing_score="{ row }">
                    <span class="text-sm">{{ row.passing_score }}%</span>
                </template>
                <template #time_limit="{ row }">
                    <span class="text-sm">{{ row.time_limit_minutes || 'No limit' }} min</span>
                </template>
                <template #is_published="{ row }">
                    <Badge :variant="row.is_published ? 'success' : 'gray'">
                        {{ row.is_published ? 'Published' : 'Draft' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/quizzes/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600">
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link :href="`/admin/quizzes/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
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
            title="Delete Quiz"
            :message="`Are you sure you want to delete ${quizToDelete?.quiz_title}? This will also delete all questions and attempts.`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteQuiz"
        />
    </AdminLayout>
</template>
