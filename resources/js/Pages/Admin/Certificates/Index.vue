<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import { Search, Eye, Award, Download } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    courses: Array,
    filters: Object,
})

const certificates = props.items

const search = ref(props.filters?.filter?.search || '')
const courseFilter = ref(props.filters?.filter?.course_id || '')

const columns = [
    { key: 'certificate_code', label: 'Certificate Code' },
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course' },
    { key: 'issue_date', label: 'Issue Date', class: 'w-32' },
    { key: 'status', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-24' },
]

const applyFilters = () => {
    router.get('/admin/certificates', {
        'filter[search]': search.value || undefined,
        'filter[course_id]': courseFilter.value || undefined,
    }, { preserveState: true })
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Certificates" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Certificates</h1>
                    <p class="text-gray-500">View all issued certificates</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="card p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <Award class="w-5 h-5 text-yellow-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Certificates</p>
                            <p class="text-xl font-bold">{{ certificates?.total || 0 }}</p>
                        </div>
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
                            placeholder="Search by certificate code or student..."
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
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="certificates?.data || []" :pagination="certificates">
                <template #certificate_code="{ row }">
                    <span class="font-mono text-sm font-medium">{{ row.certificate_code }}</span>
                </template>
                <template #student="{ row }">
                    <div>
                        <p class="font-medium text-sm">{{ row.student?.user?.full_name || 'Unknown' }}</p>
                        <p class="text-xs text-gray-500">{{ row.student?.user?.email }}</p>
                    </div>
                </template>
                <template #course="{ row }">
                    <span class="text-sm">{{ row.course?.course_name || '-' }}</span>
                </template>
                <template #issue_date="{ row }">
                    <span class="text-sm">{{ formatDate(row.issue_date) }}</span>
                </template>
                <template #status="{ row }">
                    <Badge :variant="row.is_valid ? 'success' : 'danger'">
                        {{ row.is_valid ? 'Valid' : 'Revoked' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/certificates/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600">
                            <Eye class="w-4 h-4" />
                        </Link>
                        <a v-if="row.certificate_url" :href="row.certificate_url" target="_blank"
                            class="p-1 text-gray-500 hover:text-primary-600">
                            <Download class="w-4 h-4" />
                        </a>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
