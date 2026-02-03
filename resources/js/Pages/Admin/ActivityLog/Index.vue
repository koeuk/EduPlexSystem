<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import { Search, Activity, User, Clock } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    filters: Object,
    events: Array,
    subjectTypes: Array,
})

const activities = props.items

const search = ref(props.filters?.filter?.search || '')
const typeFilter = ref(props.filters?.filter?.log_name || '')

const columns = [
    { key: 'user', label: 'User' },
    { key: 'description', label: 'Activity' },
    { key: 'subject', label: 'Subject' },
    { key: 'log_name', label: 'Type', class: 'w-28' },
    { key: 'created_at', label: 'Date', class: 'w-40' },
]

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'default', label: 'Default' },
    { value: 'auth', label: 'Authentication' },
    { value: 'course', label: 'Course' },
    { value: 'enrollment', label: 'Enrollment' },
    { value: 'payment', label: 'Payment' },
    { value: 'user', label: 'User' },
]

const getTypeVariant = (type) => {
    const variants = {
        auth: 'info',
        course: 'success',
        enrollment: 'warning',
        payment: 'purple',
        user: 'gray',
    }
    return variants[type] || 'gray'
}

const applyFilters = () => {
    router.get('/admin/activity-log', {
        'filter[search]': search.value || undefined,
        'filter[log_name]': typeFilter.value || undefined,
    }, { preserveState: true })
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Activity Log" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary-100 rounded-lg">
                    <Activity class="w-6 h-6 text-primary-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
                    <p class="text-gray-500">View all system activity and user actions</p>
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
                            placeholder="Search activities..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="typeFilter" class="input w-full sm:w-40">
                        <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="activities?.data || []" :pagination="activities">
                <template #user="{ row }">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <User class="w-4 h-4 text-gray-500" />
                        </div>
                        <div>
                            <p class="font-medium text-sm">{{ row.causer?.full_name || 'System' }}</p>
                            <p class="text-xs text-gray-500">{{ row.causer?.email || '' }}</p>
                        </div>
                    </div>
                </template>
                <template #description="{ row }">
                    <span class="text-sm">{{ row.description }}</span>
                </template>
                <template #subject="{ row }">
                    <span class="text-sm text-gray-500">
                        {{ row.subject_type?.split('\\').pop() || '-' }}
                        {{ row.subject_id ? `#${row.subject_id}` : '' }}
                    </span>
                </template>
                <template #log_name="{ row }">
                    <Badge :variant="getTypeVariant(row.log_name)">
                        {{ row.log_name || 'default' }}
                    </Badge>
                </template>
                <template #created_at="{ row }">
                    <div class="flex items-center gap-1 text-sm text-gray-500">
                        <Clock class="w-3 h-3" />
                        {{ formatDateTime(row.created_at) }}
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
