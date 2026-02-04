<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, Mail, Phone, MapPin, Calendar, BookOpen, Award } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    lessonProgress: Array,
})

// Alias for easier template usage
const student = props.item

const getStatusVariant = (status) => {
    const variants = {
        active: 'success',
        inactive: 'gray',
        suspended: 'danger',
        graduated: 'info',
        completed: 'success',
        dropped: 'danger',
    }
    return variants[status] || 'gray'
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}

const getImageUrl = () => {
    const url = student.user?.image_url
    if (!url) return null
    if (url.startsWith('http')) return url
    return `/storage/${url}`
}
</script>

<template>
    <AdminLayout>
        <Head :title="student.user.full_name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/students" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ student.user.full_name }}</h1>
                        <p class="text-gray-500">{{ student.student_id_number }}</p>
                    </div>
                </div>
                <Link :href="`/admin/students/${student.id}/edit`" class="btn btn-primary btn-md">
                    Edit Student
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Student Info -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-lg font-semibold">Student Information</h2>

                    <div class="flex items-center space-x-3">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                            <img
                                v-if="getImageUrl()"
                                :src="getImageUrl()"
                                :alt="student.user.full_name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="text-2xl font-bold text-gray-500">
                                {{ student.user.full_name.charAt(0) }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium">{{ student.user.full_name }}</p>
                            <Badge :variant="getStatusVariant(student.student_status)">
                                {{ student.student_status }}
                            </Badge>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t">
                        <div class="flex items-center space-x-3 text-sm">
                            <Mail class="w-4 h-4 text-gray-400" />
                            <span>{{ student.user.email }}</span>
                        </div>
                        <div v-if="student.user.phone" class="flex items-center space-x-3 text-sm">
                            <Phone class="w-4 h-4 text-gray-400" />
                            <span>{{ student.user.phone }}</span>
                        </div>
                        <div v-if="student.user.address" class="flex items-center space-x-3 text-sm">
                            <MapPin class="w-4 h-4 text-gray-400" />
                            <span>{{ student.user.address }}</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <Calendar class="w-4 h-4 text-gray-400" />
                            <span>Enrolled: {{ formatDate(student.enrollment_date) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Enrollments -->
                <div class="lg:col-span-2 card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Course Enrollments</h2>
                        <BookOpen class="w-5 h-5 text-gray-400" />
                    </div>

                    <div v-if="student.enrollments && student.enrollments.length > 0" class="space-y-3">
                        <div v-for="enrollment in student.enrollments" :key="enrollment.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">{{ enrollment.course?.course_name }}</p>
                                <p class="text-sm text-gray-500">
                                    Progress: {{ enrollment.progress_percentage }}%
                                </p>
                            </div>
                            <Badge :variant="getStatusVariant(enrollment.status)">
                                {{ enrollment.status }}
                            </Badge>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No enrollments yet</p>
                </div>

                <!-- Certificates -->
                <div class="lg:col-span-3 card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Certificates</h2>
                        <Award class="w-5 h-5 text-gray-400" />
                    </div>

                    <div v-if="student.certificates && student.certificates.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="cert in student.certificates" :key="cert.id"
                            class="p-4 border rounded-lg">
                            <p class="font-medium">{{ cert.course?.course_name }}</p>
                            <p class="text-sm text-gray-500">{{ cert.certificate_code }}</p>
                            <p class="text-sm text-gray-500">Issued: {{ formatDate(cert.issue_date) }}</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No certificates earned yet</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
