<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import { Search, Eye } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    courses: Array,
    statusOptions: Array,
    paymentStatusOptions: Array,
    filters: Object,
})

const enrollments = props.items

const search = ref(props.filters?.filter?.search || '')
const courseFilter = ref(props.filters?.filter?.course_id || '')
const statusFilter = ref(props.filters?.filter?.status || '')

const columns = [
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course' },
    { key: 'enrollment_date', label: 'Enrolled', class: 'w-32' },
    { key: 'progress', label: 'Progress', class: 'w-32' },
    { key: 'status', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-20' },
]

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'dropped', label: 'Dropped' },
    { value: 'expired', label: 'Expired' },
]

const getStatusVariant = (status) => {
    const variants = {
        active: 'info',
        completed: 'success',
        dropped: 'danger',
        expired: 'gray',
    }
    return variants[status] || 'gray'
}

const applyFilters = () => {
    router.get('/admin/enrollments', {
        'filter[search]': search.value || undefined,
        'filter[course_id]': courseFilter.value || undefined,
        'filter[status]': statusFilter.value || undefined,
    }, { preserveState: true })
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Enrollments" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Enrollments</h1>
                <p class="text-gray-500">View all course enrollments</p>
            </div>

            <!-- Filters -->
            <div class="card p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search student..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="courseFilter" class="input w-full sm:w-48">
                        <option value="">All Courses</option>
                        <option v-for="course in courses" :key="course.id" :value="course.id">
                            {{ course.course_name }}
                        </option>
                    </select>
                    <select v-model="statusFilter" class="input w-full sm:w-40">
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="enrollments?.data || []" :pagination="enrollments">
                <template #student="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-500">
                                {{ row.student?.user?.full_name?.charAt(0) || '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium">{{ row.student?.user?.full_name || 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">{{ row.student?.user?.email }}</p>
                        </div>
                    </div>
                </template>
                <template #course="{ row }">
                    <span class="text-sm">{{ row.course?.course_name || '-' }}</span>
                </template>
                <template #enrollment_date="{ row }">
                    <span class="text-sm">{{ formatDate(row.enrollment_date) }}</span>
                </template>
                <template #progress="{ row }">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full"
                                :style="{ width: `${row.progress_percentage || 0}%` }"></div>
                        </div>
                        <span class="text-sm text-gray-500">{{ row.progress_percentage || 0 }}%</span>
                    </div>
                </template>
                <template #status="{ row }">
                    <Badge :variant="getStatusVariant(row.status)">
                        {{ row.status }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <Link :href="`/admin/enrollments/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600">
                        <Eye class="w-4 h-4" />
                    </Link>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
