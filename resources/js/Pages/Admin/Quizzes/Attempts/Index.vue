<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import QuizEditLayout from '@/Layouts/QuizEditLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import { Search, Eye, User, Clock, CheckCircle, XCircle } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    items: Object,
    filters: Object,
})

const attempts = props.items
const search = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')

const columns = [
    { key: 'student', label: 'Student' },
    { key: 'attempt_number', label: 'Attempt #', class: 'w-24' },
    { key: 'score', label: 'Score', class: 'w-28' },
    { key: 'status', label: 'Status', class: 'w-28' },
    { key: 'time_taken', label: 'Time', class: 'w-24' },
    { key: 'submitted_at', label: 'Submitted', class: 'w-40' },
    { key: 'actions', label: 'Actions', class: 'w-20' },
]

const applyFilters = () => {
    router.get(`/admin/quizzes/${props.quiz.id}/attempts`, {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true })
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString()
}
</script>

<template>
    <QuizEditLayout :quiz="quiz">
        <Head :title="`Attempts - ${quiz.quiz_title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quiz Attempts</h1>
                    <p class="text-gray-500">View student attempts for this quiz</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-500">
                        <span class="font-medium text-gray-900">{{ items?.total || 0 }}</span> total attempts
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by student name or email..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="statusFilter" class="input w-full sm:w-40">
                        <option value="">All Status</option>
                        <option value="passed">Passed</option>
                        <option value="failed">Failed</option>
                        <option value="in_progress">In Progress</option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Total Attempts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ items?.total || 0 }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Passed</p>
                    <p class="text-2xl font-bold text-green-600">{{ quiz.passed_count || 0 }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Failed</p>
                    <p class="text-2xl font-bold text-red-600">{{ quiz.failed_count || 0 }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Average Score</p>
                    <p class="text-2xl font-bold text-primary-600">{{ quiz.average_score != null ? Number(quiz.average_score).toFixed(1) + '%' : '-' }}</p>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="attempts?.data || []" :pagination="attempts">
                <template #student="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <User class="w-4 h-4 text-gray-500" />
                        </div>
                        <div>
                            <p class="font-medium">{{ row.student?.first_name }} {{ row.student?.last_name }}</p>
                            <p class="text-sm text-gray-500">{{ row.student?.email }}</p>
                        </div>
                    </div>
                </template>
                <template #attempt_number="{ row }">
                    <span class="font-medium">#{{ row.attempt_number }}</span>
                </template>
                <template #score="{ row }">
                    <div v-if="row.submitted_at">
                        <span class="font-medium" :class="row.passed ? 'text-green-600' : 'text-red-600'">
                            {{ row.score_percentage }}%
                        </span>
                        <p class="text-xs text-gray-500">{{ row.total_points }}/{{ row.max_points }} pts</p>
                    </div>
                    <span v-else class="text-gray-400">-</span>
                </template>
                <template #status="{ row }">
                    <Badge v-if="!row.submitted_at" variant="warning">
                        <Clock class="w-3 h-3 mr-1" />
                        In Progress
                    </Badge>
                    <Badge v-else-if="row.passed" variant="success">
                        <CheckCircle class="w-3 h-3 mr-1" />
                        Passed
                    </Badge>
                    <Badge v-else variant="danger">
                        <XCircle class="w-3 h-3 mr-1" />
                        Failed
                    </Badge>
                </template>
                <template #time_taken="{ row }">
                    <span v-if="row.time_taken_minutes" class="text-sm">
                        {{ row.time_taken_minutes }} min
                    </span>
                    <span v-else class="text-gray-400">-</span>
                </template>
                <template #submitted_at="{ row }">
                    <span class="text-sm">{{ formatDate(row.submitted_at) }}</span>
                </template>
                <template #actions="{ row }">
                    <Link
                        :href="`/admin/quizzes/${quiz.id}/attempts/${row.id}`"
                        class="p-1 text-gray-500 hover:text-primary-600"
                        title="View Details"
                    >
                        <Eye class="w-4 h-4" />
                    </Link>
                </template>
            </DataTable>

            <!-- Empty state -->
            <div v-if="!attempts?.data?.length && !search && !statusFilter" class="card p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Clock class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No attempts yet</h3>
                <p class="text-gray-500">Students haven't attempted this quiz yet.</p>
            </div>
        </div>
    </QuizEditLayout>
</template>
