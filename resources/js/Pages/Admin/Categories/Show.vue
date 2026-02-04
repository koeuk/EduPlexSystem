<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, FolderOpen, BookOpen, Users, Calendar, Pencil } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    stats: Object,
})

const category = props.item
const courses = category.courses || []

const getStatusVariant = (status) => {
    const variants = {
        draft: 'gray',
        published: 'success',
        archived: 'danger',
    }
    return variants[status] || 'gray'
}

const formatPrice = (price) => {
    if (!price || price == 0) return 'Free'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <AdminLayout>
        <Head :title="category.category_name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/categories" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ category.category_name }}</h1>
                        <p class="text-gray-500">Category Details</p>
                    </div>
                </div>
                <Link :href="`/admin/categories/${category.id}/edit`" class="btn btn-primary btn-md">
                    <Pencil class="w-4 h-4 mr-2" />
                    Edit Category
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Category Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Image & Basic Info -->
                    <div class="card p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="w-full md:w-48 h-48 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <img v-if="category.full_image_url" :src="category.full_image_url" :alt="category.category_name"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <FolderOpen class="w-16 h-16 text-gray-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <Badge :variant="category.is_active ? 'success' : 'gray'">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
                                <h2 class="text-xl font-semibold mb-2">{{ category.category_name }}</h2>
                                <p class="text-gray-600 mb-4">{{ category.description || 'No description provided.' }}</p>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <BookOpen class="w-4 h-4" />
                                        {{ stats.totalCourses || 0 }} courses
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Users class="w-4 h-4" />
                                        {{ stats.totalEnrollments || 0 }} enrollments
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses List -->
                    <div class="card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold">Courses in this Category</h2>
                            <span class="text-sm text-gray-500">{{ courses?.length || 0 }} courses</span>
                        </div>

                        <div v-if="courses && courses.length > 0" class="space-y-3">
                            <Link v-for="course in courses" :key="course.id"
                                :href="`/admin/courses/${course.id}`"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-12 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                        <img v-if="course.thumbnail_url" :src="course.thumbnail_url" :alt="course.course_name"
                                            class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <BookOpen class="w-6 h-6 text-gray-400" />
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ course.course_name }}</p>
                                        <p class="text-sm text-gray-500">{{ course.course_code }} &bull; {{ course.enrollments_count || 0 }} students</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium">{{ formatPrice(course.price) }}</span>
                                    <Badge :variant="getStatusVariant(course.status)">
                                        {{ course.status }}
                                    </Badge>
                                </div>
                            </Link>
                        </div>
                        <p v-else class="text-gray-500 text-center py-8">No courses in this category yet</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">Statistics</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Total Courses</span>
                                <span class="text-xl font-bold text-primary-600">{{ stats.totalCourses || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Active Courses</span>
                                <span class="text-lg font-semibold text-green-600">{{ stats.activeCourses || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Total Enrollments</span>
                                <span class="text-lg font-semibold">{{ stats.totalEnrollments || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Category Details -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">Category Details</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Status</span>
                                <Badge :variant="category.is_active ? 'success' : 'gray'">
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div v-if="category.icon" class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Icon</span>
                                <span class="capitalize">{{ category.icon }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Created</span>
                                <span>{{ formatDate(category.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Last Updated</span>
                                <span>{{ formatDate(category.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
