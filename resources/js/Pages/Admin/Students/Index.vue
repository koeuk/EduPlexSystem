<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, Eye } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    statusOptions: Array,
    filters: Object,
})

const search = ref(props.filters?.filter?.search || '')
const status = ref(props.filters?.filter?.student_status || '')
const deleteModal = ref(false)
const studentToDelete = ref(null)

const columns = [
    { key: 'student_id_number', label: 'Student ID' },
    { key: 'user.full_name', label: 'Name' },
    { key: 'user.email', label: 'Email' },
    { key: 'user.phone', label: 'Phone' },
    { key: 'student_status', label: 'Status' },
    { key: 'actions', label: 'Actions', class: 'w-32' },
]

const applyFilters = () => {
    const params = {}
    if (search.value) params['filter[search]'] = search.value
    if (status.value) params['filter[student_status]'] = status.value
    router.get('/admin/students', params, { preserveState: true })
}

const confirmDelete = (student) => {
    studentToDelete.value = student
    deleteModal.value = true
}

const deleteStudent = () => {
    router.delete(`/admin/students/${studentToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            studentToDelete.value = null
        },
    })
}

const getStatusVariant = (status) => {
    const variants = {
        active: 'success',
        inactive: 'gray',
        suspended: 'danger',
        graduated: 'info',
    }
    return variants[status] || 'gray'
}
</script>

<template>
    <AdminLayout>
        <Head title="Students" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Students</h1>
                    <p class="text-gray-500">Manage student accounts</p>
                </div>
                <Link href="/admin/students/create" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Student
                </Link>
            </div>

            <!-- Filters -->
            <div class="card p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search students..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="status" class="input w-full sm:w-40" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="graduated">Graduated</option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="items?.data || []" :pagination="items">
                <template #student_status="{ value }">
                    <Badge :variant="getStatusVariant(value)">{{ value }}</Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/students/${row.id}`" class="p-1 text-gray-500 hover:text-blue-600">
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link :href="`/admin/students/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button @click="confirmDelete(row)" class="p-1 text-gray-500 hover:text-red-600">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :show="deleteModal"
            title="Delete Student"
            :message="`Are you sure you want to delete ${studentToDelete?.user?.full_name}?`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteStudent"
        />
    </AdminLayout>
</template>
