<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, FolderOpen } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    statusOptions: Array,
    filters: Object,
})

const search = ref(props.filters?.search || '')
const deleteModal = ref(false)
const categoryToDelete = ref(null)

const columns = [
    { key: 'icon', label: 'Icon', class: 'w-16' },
    { key: 'category_name', label: 'Name' },
    { key: 'description', label: 'Description' },
    { key: 'courses_count', label: 'Courses', class: 'w-24' },
    { key: 'is_active', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-24' },
]

const applyFilters = () => {
    router.get('/admin/categories', {
        search: search.value,
    }, { preserveState: true })
}

const confirmDelete = (category) => {
    categoryToDelete.value = category
    deleteModal.value = true
}

const deleteCategory = () => {
    router.delete(`/admin/categories/${categoryToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            categoryToDelete.value = null
        },
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="Categories" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                    <p class="text-gray-500">Manage course categories</p>
                </div>
                <Link href="/admin/categories/create" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Category
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
                            placeholder="Search categories..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="items?.data || []" :pagination="items">
                <template #icon="{ value }">
                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                        <FolderOpen class="w-5 h-5 text-primary-600" />
                    </div>
                </template>
                <template #description="{ value }">
                    <span class="text-sm text-gray-500 line-clamp-2">{{ value || '-' }}</span>
                </template>
                <template #is_active="{ value }">
                    <Badge :variant="value ? 'success' : 'gray'">
                        {{ value ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/categories/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600">
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
            title="Delete Category"
            :message="`Are you sure you want to delete ${categoryToDelete?.category_name}? This may affect courses in this category.`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteCategory"
        />
    </AdminLayout>
</template>
