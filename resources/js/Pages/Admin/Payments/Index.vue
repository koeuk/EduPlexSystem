<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import { Search, Eye, DollarSign } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    stats: Object,
    statusOptions: Array,
    filters: Object,
})

const payments = props.items

const search = ref(props.filters?.filter?.search || '')
const statusFilter = ref(props.filters?.filter?.status || '')

const columns = [
    { key: 'transaction_id', label: 'Transaction ID' },
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course' },
    { key: 'amount', label: 'Amount', class: 'w-28' },
    { key: 'payment_method', label: 'Method', class: 'w-28' },
    { key: 'status', label: 'Status', class: 'w-28' },
    { key: 'created_at', label: 'Date', class: 'w-32' },
    { key: 'actions', label: '', class: 'w-16' },
]

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'pending', label: 'Pending' },
    { value: 'completed', label: 'Completed' },
    { value: 'failed', label: 'Failed' },
    { value: 'refunded', label: 'Refunded' },
]

const getStatusVariant = (status) => {
    const variants = {
        pending: 'warning',
        completed: 'success',
        failed: 'danger',
        refunded: 'info',
    }
    return variants[status] || 'gray'
}

const applyFilters = () => {
    router.get('/admin/payments', {
        'filter[search]': search.value || undefined,
        'filter[status]': statusFilter.value || undefined,
    }, { preserveState: true })
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price || 0)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Payments" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
                <p class="text-gray-500">View all payment transactions</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="card p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <DollarSign class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Revenue</p>
                            <p class="text-xl font-bold">{{ formatPrice(stats?.totalRevenue) }}</p>
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
                            placeholder="Search by transaction ID or student..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
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
            <DataTable :columns="columns" :data="payments?.data || []" :pagination="payments">
                <template #transaction_id="{ row }">
                    <span class="font-mono text-sm">{{ row.transaction_id || row.id }}</span>
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
                <template #amount="{ row }">
                    <span class="font-medium">{{ formatPrice(row.amount) }}</span>
                </template>
                <template #payment_method="{ row }">
                    <span class="text-sm capitalize">{{ row.payment_method || '-' }}</span>
                </template>
                <template #status="{ row }">
                    <Badge :variant="getStatusVariant(row.status)">
                        {{ row.status }}
                    </Badge>
                </template>
                <template #created_at="{ row }">
                    <span class="text-sm">{{ formatDate(row.created_at) }}</span>
                </template>
                <template #actions="{ row }">
                    <Link :href="`/admin/payments/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600">
                        <Eye class="w-4 h-4" />
                    </Link>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
