<script setup>
import { Head, Link } from '@inertiajs/vue3'
import CourseEditLayout from '@/Layouts/CourseEditLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, Pencil, Video, BookOpen, HelpCircle, Clock, CheckCircle, XCircle, Image } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    module: Object,
    item: Object,
})

const lesson = props.item

const getLessonTypeIcon = (type) => {
    const icons = {
        video: Video,
        text: BookOpen,
        quiz: HelpCircle,
    }
    return icons[type] || BookOpen
}

const getLessonTypeVariant = (type) => {
    const variants = {
        video: 'info',
        text: 'success',
        quiz: 'warning',
    }
    return variants[type] || 'gray'
}

const getImageUrl = () => {
    if (!lesson.image_url) return null
    if (lesson.image_url.startsWith('http')) return lesson.image_url
    return `/storage/${lesson.image_url}`
}

const getVideoUrl = () => {
    if (!lesson.video_url) return null
    if (lesson.video_url.startsWith('http')) return lesson.video_url
    return `/storage/${lesson.video_url}`
}
</script>

<template>
    <CourseEditLayout :course="course">
        <Head :title="`Lesson - ${lesson.lesson_title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <p class="text-sm text-gray-500">{{ module.module_title }}</p>
                        <h1 class="text-2xl font-bold text-gray-900">{{ lesson.lesson_title }}</h1>
                    </div>
                </div>
                <Link :href="`/admin/courses/${course.id}/modules/${module.id}/lessons/${lesson.id}/edit`" class="btn btn-primary btn-md">
                    <Pencil class="w-4 h-4 mr-2" />
                    Edit Lesson
                </Link>
            </div>

            <!-- Lesson Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Video Player -->
                    <div v-if="lesson.lesson_type === 'video' && getVideoUrl()" class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <Video class="w-5 h-5 text-gray-600" />
                            Lesson Video
                        </h3>
                        <video
                            :src="getVideoUrl()"
                            controls
                            class="w-full rounded-lg bg-black"
                            preload="metadata"
                        >
                            Your browser does not support the video tag.
                        </video>
                    </div>

                    <!-- Basic Info Card -->
                    <div class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Lesson Information</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-gray-500">Title</label>
                                <p class="font-medium text-gray-900">{{ lesson.lesson_title }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-500">Description</label>
                                <p class="text-gray-700">{{ lesson.description || 'No description provided' }}</p>
                            </div>

                            <!-- Content for text lessons -->
                            <div v-if="lesson.lesson_type === 'text' && lesson.content">
                                <label class="text-sm text-gray-500">Content</label>
                                <div class="mt-2 p-4 bg-gray-50 rounded-lg prose prose-sm max-w-none">
                                    <p class="whitespace-pre-wrap text-gray-700">{{ lesson.content }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Preview -->
                    <div v-if="getImageUrl()" class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <Image class="w-5 h-5 text-gray-600" />
                            Lesson Image
                        </h3>
                        <img
                            :src="getImageUrl()"
                            :alt="lesson.lesson_title"
                            class="rounded-lg max-h-64 object-cover"
                        />
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Type & Status Card -->
                    <div class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Details</h3>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Type</span>
                                <Badge :variant="getLessonTypeVariant(lesson.lesson_type)">
                                    <component :is="getLessonTypeIcon(lesson.lesson_type)" class="w-3 h-3 mr-1" />
                                    {{ lesson.lesson_type }}
                                </Badge>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Required</span>
                                <Badge :variant="lesson.is_mandatory ? 'warning' : 'gray'">
                                    <component :is="lesson.is_mandatory ? CheckCircle : XCircle" class="w-3 h-3 mr-1" />
                                    {{ lesson.is_mandatory ? 'Required' : 'Optional' }}
                                </Badge>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Order</span>
                                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
                                    {{ lesson.lesson_order }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Duration Card -->
                    <div class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <Clock class="w-5 h-5 text-gray-600" />
                            Duration
                        </h3>

                        <div class="space-y-3">
                            <div v-if="lesson.duration_minutes" class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Estimated Time</span>
                                <span class="font-medium">{{ lesson.duration_minutes }} min</span>
                            </div>

                            <div v-if="lesson.lesson_type === 'video' && lesson.video_duration" class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Video Duration</span>
                                <span class="font-medium">{{ Math.floor(lesson.video_duration / 60) }}:{{ String(lesson.video_duration % 60).padStart(2, '0') }}</span>
                            </div>

                            <p v-if="!lesson.duration_minutes && !lesson.video_duration" class="text-sm text-gray-400">
                                No duration set
                            </p>
                        </div>
                    </div>

                    <!-- Quiz Info (if quiz type) -->
                    <div v-if="lesson.lesson_type === 'quiz' && lesson.quiz" class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <HelpCircle class="w-5 h-5 text-gray-600" />
                            Linked Quiz
                        </h3>

                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="font-medium text-gray-900">{{ lesson.quiz.quiz_title }}</p>
                            <Link :href="`/admin/quizzes/${lesson.quiz.id}`" class="text-sm text-primary-600 hover:underline">
                                View Quiz Details
                            </Link>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="card p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Metadata</h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Lesson ID</span>
                                <span class="font-mono text-gray-700">{{ lesson.id }}</span>
                            </div>
                            <div v-if="lesson.created_at" class="flex items-center justify-between">
                                <span class="text-gray-500">Created</span>
                                <span class="text-gray-700">{{ new Date(lesson.created_at).toLocaleDateString() }}</span>
                            </div>
                            <div v-if="lesson.updated_at" class="flex items-center justify-between">
                                <span class="text-gray-500">Updated</span>
                                <span class="text-gray-700">{{ new Date(lesson.updated_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CourseEditLayout>
</template>
