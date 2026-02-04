<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { ArrowLeft, Plus, Pencil, Trash2, HelpCircle, Clock, Target, RotateCcw } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    questions: Array,
})

const quiz = props.item

const deleteModal = ref(false)
const questionToDelete = ref(null)

const getQuestionTypeLabel = (type) => {
    const types = {
        multiple_choice: 'Multiple Choice',
        true_false: 'True/False',
        short_answer: 'Short Answer',
        essay: 'Essay',
    }
    return types[type] || type
}

const confirmDelete = (question) => {
    questionToDelete.value = question
    deleteModal.value = true
}

const deleteQuestion = () => {
    router.delete(`/admin/quizzes/${quiz.id}/questions/${questionToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            questionToDelete.value = null
        },
    })
}
</script>

<template>
    <AdminLayout>
        <Head :title="quiz.quiz_title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/quizzes" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ quiz.quiz_title }}</h1>
                        <p class="text-gray-500">{{ quiz.course?.course_name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <Link :href="`/admin/quizzes/${quiz.id}/questions/create`" class="btn btn-secondary btn-md">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Question
                    </Link>
                    <Link :href="`/admin/quizzes/${quiz.id}/edit`" class="btn btn-primary btn-md">
                        Edit Quiz
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quiz Info -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-lg font-semibold">Quiz Settings</h2>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Status</span>
                            <Badge :variant="quiz.is_published ? 'success' : 'gray'">
                                {{ quiz.is_published ? 'Published' : 'Draft' }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 flex items-center gap-1">
                                <Target class="w-4 h-4" />
                                Passing Score
                            </span>
                            <span class="font-medium">{{ quiz.passing_score }}%</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 flex items-center gap-1">
                                <Clock class="w-4 h-4" />
                                Time Limit
                            </span>
                            <span class="font-medium">{{ quiz.time_limit_minutes || 'No limit' }} min</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 flex items-center gap-1">
                                <RotateCcw class="w-4 h-4" />
                                Max Attempts
                            </span>
                            <span class="font-medium">{{ quiz.max_attempts || 'Unlimited' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 flex items-center gap-1">
                                <HelpCircle class="w-4 h-4" />
                                Questions
                            </span>
                            <span class="font-medium">{{ questions?.length || 0 }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t">
                        <p class="text-sm text-gray-600">{{ quiz.description || 'No description' }}</p>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="lg:col-span-2 card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Questions</h2>
                        <span class="text-sm text-gray-500">{{ questions?.length || 0 }} questions</span>
                    </div>

                    <div v-if="questions && questions.length > 0" class="space-y-4">
                        <div v-for="(question, index) in questions" :key="question.id"
                            class="p-4 border rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-6 h-6 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ index + 1 }}
                                        </span>
                                        <Badge variant="info">{{ getQuestionTypeLabel(question.question_type) }}</Badge>
                                        <span class="text-sm text-gray-500">{{ question.points }} pts</span>
                                    </div>
                                    <p class="font-medium">{{ question.question_text }}</p>

                                    <!-- Options for multiple choice -->
                                    <div v-if="question.options && question.options.length > 0" class="mt-2 space-y-1">
                                        <div v-for="(option, optIndex) in question.options" :key="optIndex"
                                            class="flex items-center gap-2 text-sm"
                                            :class="option.is_correct ? 'text-green-600' : 'text-gray-500'">
                                            <span class="w-5 h-5 border rounded flex items-center justify-center text-xs">
                                                {{ String.fromCharCode(65 + optIndex) }}
                                            </span>
                                            {{ option.option_text }}
                                            <span v-if="option.is_correct" class="text-green-600">(Correct)</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 ml-4">
                                    <Link :href="`/admin/quizzes/${quiz.id}/questions/${question.id}/edit`"
                                        class="p-1 text-gray-500 hover:text-primary-600">
                                        <Pencil class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(question)"
                                        class="p-1 text-gray-500 hover:text-red-600">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <HelpCircle class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                        <p class="text-gray-500 mb-4">No questions yet</p>
                        <Link :href="`/admin/quizzes/${quiz.id}/questions/create`" class="btn btn-primary btn-md">
                            <Plus class="w-4 h-4 mr-2" />
                            Add First Question
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :show="deleteModal"
            title="Delete Question"
            message="Are you sure you want to delete this question?"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteQuestion"
        />
    </AdminLayout>
</template>
