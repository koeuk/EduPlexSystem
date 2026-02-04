<script setup>
import { Head, Link } from '@inertiajs/vue3'
import QuizEditLayout from '@/Layouts/QuizEditLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, User, Clock, CheckCircle, XCircle, HelpCircle } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    item: Object,
})

const attempt = props.item

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString()
}

const getAnswerStatus = (answer) => {
    if (!answer) return 'unanswered'
    if (answer.is_correct) return 'correct'
    return 'incorrect'
}
</script>

<template>
    <QuizEditLayout :quiz="quiz">
        <Head :title="`Attempt Details - ${quiz.quiz_title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/quizzes/${quiz.id}/attempts`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Attempt Details</h1>
                        <p class="text-gray-500">Attempt #{{ attempt.attempt_number }} by {{ attempt.student?.first_name }} {{ attempt.student?.last_name }}</p>
                    </div>
                </div>
                <Badge v-if="!attempt.submitted_at" variant="warning" class="text-base px-4 py-2">
                    <Clock class="w-4 h-4 mr-2" />
                    In Progress
                </Badge>
                <Badge v-else-if="attempt.passed" variant="success" class="text-base px-4 py-2">
                    <CheckCircle class="w-4 h-4 mr-2" />
                    Passed
                </Badge>
                <Badge v-else variant="danger" class="text-base px-4 py-2">
                    <XCircle class="w-4 h-4 mr-2" />
                    Failed
                </Badge>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                            <User class="w-5 h-5 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Student</p>
                            <p class="font-medium">{{ attempt.student?.first_name }} {{ attempt.student?.last_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Score</p>
                    <p class="text-2xl font-bold" :class="attempt.passed ? 'text-green-600' : 'text-red-600'">
                        {{ attempt.score_percentage || 0 }}%
                    </p>
                    <p class="text-xs text-gray-500">{{ attempt.total_points || 0 }}/{{ attempt.max_points || 0 }} points</p>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Time Taken</p>
                    <p class="text-2xl font-bold text-gray-900">{{ attempt.time_taken_minutes || '-' }}</p>
                    <p class="text-xs text-gray-500">minutes</p>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Submitted</p>
                    <p class="font-medium text-gray-900">{{ formatDate(attempt.submitted_at) }}</p>
                    <p class="text-xs text-gray-500">Started: {{ formatDate(attempt.started_at) }}</p>
                </div>
            </div>

            <!-- Answers Review -->
            <div class="card">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <HelpCircle class="w-5 h-5 text-gray-600" />
                        Answers Review
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <div v-for="(answer, index) in attempt.answers" :key="answer.id" class="p-4">
                        <div class="flex items-start gap-4">
                            <span
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium flex-shrink-0"
                                :class="answer.is_correct ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                            >
                                {{ index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 mb-2">{{ answer.question?.question_text }}</p>

                                <!-- Student's Answer -->
                                <div class="mb-2">
                                    <p class="text-sm text-gray-500 mb-1">Student's Answer:</p>
                                    <p
                                        class="text-sm p-2 rounded"
                                        :class="answer.is_correct ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                                    >
                                        {{ answer.selected_option?.option_text || answer.text_answer || 'No answer' }}
                                    </p>
                                </div>

                                <!-- Correct Answer (if wrong) -->
                                <div v-if="!answer.is_correct && answer.question?.options" class="mb-2">
                                    <p class="text-sm text-gray-500 mb-1">Correct Answer:</p>
                                    <p class="text-sm p-2 rounded bg-green-50 text-green-700">
                                        {{ answer.question.options.find(o => o.is_correct)?.option_text || '-' }}
                                    </p>
                                </div>

                                <!-- Points -->
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-gray-500">
                                        Points: <span class="font-medium">{{ answer.points_earned || 0 }}/{{ answer.question?.points || 0 }}</span>
                                    </span>
                                    <Badge v-if="answer.is_correct" variant="success">Correct</Badge>
                                    <Badge v-else variant="danger">Incorrect</Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </QuizEditLayout>
</template>
