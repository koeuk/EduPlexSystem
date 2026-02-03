<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, ArrowLeft, HelpCircle, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    items: Object,
    questionTypeOptions: Array,
})

const questions = props.items
const search = ref('')
const typeFilter = ref('')
const deleteModal = ref(false)
const questionToDelete = ref(null)

const columns = [
    { key: 'order', label: '#', class: 'w-16' },
    { key: 'question_text', label: 'Question' },
    { key: 'question_type', label: 'Type', class: 'w-32' },
    { key: 'points', label: 'Points', class: 'w-20' },
    { key: 'options', label: 'Options', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-24' },
]

const getTypeVariant = (type) => {
    const variants = {
        multiple_choice: 'info',
        true_false: 'warning',
        short_answer: 'success',
        essay: 'purple',
    }
    return variants[type] || 'gray'
}

const applyFilters = () => {
    router.get(`/admin/quizzes/${props.quiz.id}/questions`, {
        'filter[search]': search.value || undefined,
        'filter[question_type]': typeFilter.value || undefined,
    }, { preserveState: true })
}

const confirmDelete = (question) => {
    questionToDelete.value = question
    deleteModal.value = true
}

const deleteQuestion = () => {
    router.delete(`/admin/quizzes/${props.quiz.id}/questions/${questionToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            questionToDelete.value = null
        },
    })
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Questions - ${quiz.quiz_title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/quizzes" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Quiz Questions</h1>
                        <p class="text-gray-500">{{ quiz.quiz_title }}</p>
                    </div>
                </div>
                <Link :href="`/admin/quizzes/${quiz.id}/questions/create`" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Question
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
                            placeholder="Search questions..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="typeFilter" class="input w-full sm:w-40">
                        <option value="">All Types</option>
                        <option v-for="opt in questionTypeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="questions?.data || []" :pagination="questions">
                <template #order="{ row }">
                    <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
                        {{ row.question_order }}
                    </span>
                </template>
                <template #question_text="{ row }">
                    <div>
                        <p class="font-medium line-clamp-2">{{ row.question_text }}</p>
                        <p v-if="row.explanation" class="text-sm text-gray-500 line-clamp-1">{{ row.explanation }}</p>
                    </div>
                </template>
                <template #question_type="{ row }">
                    <Badge :variant="getTypeVariant(row.question_type)">
                        {{ row.question_type?.replace('_', ' ') }}
                    </Badge>
                </template>
                <template #points="{ row }">
                    <span class="font-medium">{{ row.points }}</span>
                </template>
                <template #options="{ row }">
                    <div class="flex items-center gap-1">
                        <HelpCircle class="w-4 h-4 text-gray-400" />
                        <span>{{ row.options?.length || 0 }}</span>
                    </div>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/quizzes/${quiz.id}/questions/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
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
            title="Delete Question"
            message="Are you sure you want to delete this question? This action cannot be undone."
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteQuestion"
        />
    </AdminLayout>
</template>
