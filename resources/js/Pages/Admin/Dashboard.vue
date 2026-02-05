<script setup>
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatsCard from '@/Components/StatsCard.vue'
import { Link } from '@inertiajs/vue3'
import {
    Users, BookOpen, DollarSign, ClipboardList,
    TrendingUp, Award, Eye
} from 'lucide-vue-next'
import { Line, Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

const props = defineProps({
    stats: Object,
    monthlyEnrollments: Array,
    monthlyRevenue: Array,
    recentEnrollments: Array,
    recentPayments: Array,
    topCourses: Array,
    courseStatusCounts: Object,
})

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

const enrollmentChartData = computed(() => ({
    labels: props.monthlyEnrollments?.map(e => months[e.month - 1]) || [],
    datasets: [{
        label: 'Enrollments',
        data: props.monthlyEnrollments?.map(e => e.count) || [],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
    }]
}))

const revenueChartData = computed(() => ({
    labels: props.monthlyRevenue?.map(r => months[r.month - 1]) || [],
    datasets: [{
        label: 'Revenue',
        data: props.monthlyRevenue?.map(r => r.total) || [],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.4,
    }]
}))

const courseStatusChartData = computed(() => ({
    labels: ['Draft', 'Published', 'Archived'],
    datasets: [{
        data: [
            props.courseStatusCounts?.draft || 0,
            props.courseStatusCounts?.published || 0,
            props.courseStatusCounts?.archived || 0,
        ],
        backgroundColor: ['#f59e0b', '#10b981', '#6b7280'],
    }]
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
}

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-500">Welcome back! Here's an overview of your LMS.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatsCard
                    title="Total Students"
                    :value="stats.totalStudents"
                    :icon="Users"
                    color="primary"
                />
                <StatsCard
                    title="Total Courses"
                    :value="stats.totalCourses"
                    :icon="BookOpen"
                    color="green"
                />
                <StatsCard
                    title="Active Enrollments"
                    :value="stats.activeEnrollments"
                    :icon="ClipboardList"
                    color="yellow"
                />
                <StatsCard
                    title="Total Revenue"
                    :value="formatCurrency(stats.totalRevenue)"
                    :icon="DollarSign"
                    color="purple"
                />
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Enrollments Chart -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Enrollments</h3>
                    <div class="h-64">
                        <Line :data="enrollmentChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue</h3>
                    <div class="h-64">
                        <Line :data="revenueChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Enrollments -->
                <div class="card lg:col-span-2">
                    <div class="p-6 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Enrollments</h3>
                            <Link href="/admin/enrollments" class="text-sm text-primary-600 hover:text-primary-700">
                                View all
                            </Link>
                        </div>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="enrollment in recentEnrollments"
                            :key="enrollment.id"
                            class="p-4 flex items-center justify-between"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                                    <img
                                        v-if="enrollment.student?.user?.image_url"
                                        :src="enrollment.student.user.image_url.startsWith('http') ? enrollment.student.user.image_url : `/storage/${enrollment.student.user.image_url}`"
                                        :alt="enrollment.student?.user?.full_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <Users v-else class="w-5 h-5 text-primary-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ enrollment.student?.user?.full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ enrollment.course?.course_name }}</p>
                                </div>
                            </div>
                            <span class="text-sm text-gray-500">{{ formatDate(enrollment.enrollment_date) }}</span>
                        </div>
                        <div v-if="!recentEnrollments?.length" class="p-4 text-center text-gray-500">
                            No recent enrollments
                        </div>
                    </div>
                </div>

                <!-- Course Status -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Course Status</h3>
                    <div class="h-48">
                        <Doughnut :data="courseStatusChartData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>

            <!-- Top Courses -->
            <div class="card">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Top Courses by Enrollments</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Enrollments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="course in topCourses" :key="course.id">
                                <td>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ course.course_name }}</p>
                                        <p class="text-sm text-gray-500">{{ course.course_code }}</p>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ course.enrollments_count }}</span>
                                </td>
                                <td>
                                    <Link
                                        :href="`/admin/courses/${course.id}`"
                                        class="text-primary-600 hover:text-primary-700"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!topCourses?.length">
                                <td colspan="3" class="text-center text-gray-500">No courses yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
