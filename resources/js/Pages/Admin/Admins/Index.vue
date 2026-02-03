<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    filters: Object,
})

const admins = props.items

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const deleteModal = ref(false)
const adminToDelete = ref(null)

const columns = [
    { key: 'user.full_name', label: 'Name' },
    { key: 'user.email', label: 'Email' },
    { key: 'department', label: 'Department' },
    { key: 'user.status', label: 'Status' },
    { key: 'actions', label: 'Actions', class: 'w-24' },
]

const applyFilters = () => {
    router.get('/admin/admins', {
        search: search.value,
        status: status.value,
    }, { preserveState: true })
}

const confirmDelete = (admin) => {
    adminToDelete.value = admin
    deleteModal.value = true
}

const deleteAdmin = () => {
    router.delete(`/admin/admins/${adminToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            adminToDelete.value = null
        },
    })
}

const getStatusVariant = (status) => {
    const variants = {
        active: 'success',
        inactive: 'gray',
        suspended: 'danger',
    }
    return variants[status] || 'gray'
}
</script>

<template>
    <AdminLayout>
        <Head title="Admins" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Admins</h1>
                    <p class="text-gray-500">Manage administrator accounts</p>
                </div>
                <Link href="/admin/admins/create" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Admin
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
                            placeholder="Search admins..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="status" class="input w-full sm:w-40" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="admins?.data || []" :pagination="admins">
                <template #user.status="{ value }">
                    <Badge :variant="getStatusVariant(value)">{{ value }}</Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/admins/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
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
            title="Delete Admin"
            :message="`Are you sure you want to delete ${adminToDelete?.user?.full_name}?`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteAdmin"
        />
    </AdminLayout>
</template>
