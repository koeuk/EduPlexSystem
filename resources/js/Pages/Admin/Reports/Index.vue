<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
    BarChart3, Users, BookOpen, DollarSign, Award, TrendingUp,
    Download, Calendar, ArrowRight
} from 'lucide-vue-next'

const props = defineProps({
    stats: Object,
})

const reports = [
    {
        title: 'Student Report',
        description: 'View enrollment trends, student demographics, and progress metrics',
        icon: Users,
        color: 'blue',
        link: '/admin/reports/students',
    },
    {
        title: 'Course Report',
        description: 'Analyze course performance, completion rates, and engagement',
        icon: BookOpen,
        color: 'green',
        link: '/admin/reports/courses',
    },
    {
        title: 'Revenue Report',
        description: 'Track sales, revenue trends, and payment analytics',
        icon: DollarSign,
        color: 'yellow',
        link: '/admin/reports/revenue',
    },
    {
        title: 'Certificate Report',
        description: 'View certificate issuance and completion statistics',
        icon: Award,
        color: 'purple',
        link: '/admin/reports/certificates',
    },
]

const colorClasses = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    purple: 'bg-purple-100 text-purple-600',
}

const formatNumber = (num) => {
    return new Intl.NumberFormat().format(num || 0)
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price || 0)
}
</script>

<template>
    <AdminLayout>
        <Head title="Reports" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                    <p class="text-gray-500">View insights and analytics for your platform</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <Calendar class="w-4 h-4" />
                    <span>Last updated: {{ new Date().toLocaleDateString() }}</span>
                </div>
            </div>

            <!-- Overview Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Students</p>
                            <p class="text-2xl font-bold">{{ formatNumber(stats?.total_students) }}</p>
                            <p class="text-xs text-green-600 flex items-center gap-1 mt-1">
                                <TrendingUp class="w-3 h-3" />
                                +{{ stats?.new_students_this_month || 0 }} this month
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <Users class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Courses</p>
                            <p class="text-2xl font-bold">{{ formatNumber(stats?.total_courses) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ stats?.published_courses || 0 }} published
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <BookOpen class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold">{{ formatPrice(stats?.total_revenue) }}</p>
                            <p class="text-xs text-green-600 flex items-center gap-1 mt-1">
                                <TrendingUp class="w-3 h-3" />
                                {{ formatPrice(stats?.revenue_this_month) }} this month
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <DollarSign class="w-6 h-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Certificates Issued</p>
                            <p class="text-2xl font-bold">{{ formatNumber(stats?.total_certificates) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ stats?.certificates_this_month || 0 }} this month
                            </p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <Award class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link v-for="report in reports" :key="report.title"
                    :href="report.link"
                    class="card p-6 hover:shadow-md transition-shadow group">
                    <div class="flex items-start gap-4">
                        <div :class="['p-3 rounded-lg', colorClasses[report.color]]">
                            <component :is="report.icon" class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                                {{ report.title }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">{{ report.description }}</p>
                        </div>
                        <ArrowRight class="w-5 h-5 text-gray-400 group-hover:text-primary-600 transition-colors" />
                    </div>
                </Link>
            </div>

            <!-- Quick Actions -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold mb-4">Export Data</h2>
                <div class="flex flex-wrap gap-3">
                    <button class="btn btn-secondary btn-sm">
                        <Download class="w-4 h-4 mr-2" />
                        Export Students CSV
                    </button>
                    <button class="btn btn-secondary btn-sm">
                        <Download class="w-4 h-4 mr-2" />
                        Export Enrollments CSV
                    </button>
                    <button class="btn btn-secondary btn-sm">
                        <Download class="w-4 h-4 mr-2" />
                        Export Payments CSV
                    </button>
                    <button class="btn btn-secondary btn-sm">
                        <Download class="w-4 h-4 mr-2" />
                        Export Certificates CSV
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
