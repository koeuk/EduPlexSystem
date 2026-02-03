<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, BookOpen, Clock, CheckCircle, XCircle } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    lessonProgress: Array,
    quizAttempts: Array,
})

const enrollment = props.item

const getStatusVariant = (status) => {
    const variants = {
        active: 'info',
        completed: 'success',
        dropped: 'danger',
        expired: 'gray',
    }
    return variants[status] || 'gray'
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Enrollment Details" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/enrollments" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Enrollment Details</h1>
                    <p class="text-gray-500">{{ enrollment.student?.user?.full_name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Enrollment Info -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-lg font-semibold">Enrollment Info</h2>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Status</span>
                            <Badge :variant="getStatusVariant(enrollment.status)">
                                {{ enrollment.status }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Course</span>
                            <span class="font-medium">{{ enrollment.course?.course_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Student</span>
                            <span>{{ enrollment.student?.user?.full_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Enrolled</span>
                            <span>{{ formatDate(enrollment.enrollment_date) }}</span>
                        </div>
                        <div v-if="enrollment.completed_at" class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Completed</span>
                            <span>{{ formatDate(enrollment.completed_at) }}</span>
                        </div>
                        <div v-if="enrollment.expires_at" class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Expires</span>
                            <span>{{ formatDate(enrollment.expires_at) }}</span>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="pt-4 border-t">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium">Progress</span>
                            <span class="text-sm font-medium">{{ enrollment.progress_percentage || 0 }}%</span>
                        </div>
                        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full transition-all"
                                :style="{ width: `${enrollment.progress_percentage || 0}%` }"></div>
                        </div>
                    </div>
                </div>

                <!-- Lesson Progress -->
                <div class="lg:col-span-2 card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Lesson Progress</h2>
                        <span class="text-sm text-gray-500">
                            {{ lessonProgress?.filter(l => l.is_completed).length || 0 }} / {{ lessonProgress?.length || 0 }} completed
                        </span>
                    </div>

                    <div v-if="lessonProgress && lessonProgress.length > 0" class="space-y-2">
                        <div v-for="progress in lessonProgress" :key="progress.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div :class="progress.is_completed ? 'text-green-500' : 'text-gray-300'">
                                    <CheckCircle v-if="progress.is_completed" class="w-5 h-5" />
                                    <Clock v-else class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="font-medium text-sm">{{ progress.lesson?.lesson_title }}</p>
                                    <p class="text-xs text-gray-500">{{ progress.lesson?.module?.module_title }}</p>
                                </div>
                            </div>
                            <div class="text-right text-sm">
                                <p v-if="progress.is_completed" class="text-green-600">Completed</p>
                                <p v-else class="text-gray-500">{{ progress.watch_time_seconds || 0 }}s watched</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No lesson progress yet</p>
                </div>

                <!-- Quiz Attempts -->
                <div class="lg:col-span-3 card p-6">
                    <h2 class="text-lg font-semibold mb-4">Quiz Attempts</h2>

                    <div v-if="quizAttempts && quizAttempts.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-500">Quiz</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-500">Score</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-500">Result</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-500">Date</th>
                                    <th class="text-left py-2 px-3 text-sm font-medium text-gray-500">Time Taken</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="attempt in quizAttempts" :key="attempt.id" class="border-b">
                                    <td class="py-2 px-3">{{ attempt.quiz?.quiz_title }}</td>
                                    <td class="py-2 px-3">{{ attempt.score }}%</td>
                                    <td class="py-2 px-3">
                                        <Badge :variant="attempt.is_passed ? 'success' : 'danger'">
                                            {{ attempt.is_passed ? 'Passed' : 'Failed' }}
                                        </Badge>
                                    </td>
                                    <td class="py-2 px-3 text-sm">{{ formatDateTime(attempt.started_at) }}</td>
                                    <td class="py-2 px-3 text-sm">{{ attempt.time_taken_minutes || '-' }} min</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No quiz attempts yet</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
