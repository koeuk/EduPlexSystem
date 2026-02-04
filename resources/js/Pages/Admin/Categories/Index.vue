<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import FormModal from '@/Components/FormModal.vue'
import CategoryForm from '@/Components/CategoryForm.vue'
import { Plus, Pencil, Trash2, Search, FolderOpen, Eye } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    statusOptions: Array,
    filters: Object,
})

const search = ref(props.filters?.search || '')

// Modal states
const createModal = ref(false)
const editModal = ref(false)
const deleteModal = ref(false)
const categoryToDelete = ref(null)
const categoryToEdit = ref(null)

// Create form
const createForm = useForm({
    category_name: '',
    description: '',
    icon: '',
    image: null,
    is_active: true,
})

// Edit form
const editForm = useForm({
    _method: 'PUT',
    category_name: '',
    description: '',
    icon: '',
    image: null,
    is_active: true,
})

const columns = [
    { key: 'icon', label: 'Icon', class: 'w-16' },
    { key: 'category_name', label: 'Name' },
    { key: 'description', label: 'Description' },
    { key: 'courses_count', label: 'Courses', class: 'w-56' },
    { key: 'is_active', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-32' },
]

const applyFilters = () => {
    router.get('/admin/categories', {
        search: search.value,
    }, { preserveState: true })
}

// Create modal functions
const openCreateModal = () => {
    createForm.reset()
    createForm.clearErrors()
    createModal.value = true
}

const closeCreateModal = () => {
    createModal.value = false
}

const submitCreate = () => {
    createForm.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal()
        },
    })
}

const handleCreateImageChange = (file) => {
    createForm.image = file
}

// Edit modal functions
const openEditModal = (category) => {
    categoryToEdit.value = category
    editForm.category_name = category.category_name
    editForm.description = category.description || ''
    editForm.icon = category.icon || ''
    editForm.image = null
    editForm.image_url = category.full_image_url || ''
    editForm.is_active = category.is_active
    editForm.clearErrors()
    editModal.value = true
}

const closeEditModal = () => {
    editModal.value = false
    categoryToEdit.value = null
}

const submitEdit = () => {
    editForm.post(`/admin/categories/${categoryToEdit.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal()
        },
    })
}

const handleEditImageChange = (file) => {
    editForm.image = file
}

// Delete modal functions
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
                <button @click="openCreateModal" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Category
                </button>
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
                <template #icon="{ row }">
                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center overflow-hidden">
                        <img v-if="row.full_image_url" :src="row.full_image_url" :alt="row.category_name" class="w-full h-full object-cover" />
                        <FolderOpen v-else class="w-5 h-5 text-primary-600" />
                    </div>
                </template>
                <template #description="{ value }">
                    <span class="text-sm text-gray-500 line-clamp-2">{{ value || '-' }}</span>
                </template>
                <template #courses_count="{ row }">
                    <div v-if="row.courses && row.courses.length > 0" class="space-y-1">
                        <div v-for="course in row.courses.slice(0, 3)" :key="course.id" class="text-sm text-gray-600 truncate max-w-48">
                            {{ course.course_name }}
                        </div>
                        <span v-if="row.courses.length > 3" class="text-xs text-gray-400">
                            +{{ row.courses.length - 3 }} more
                        </span>
                    </div>
                    <span v-else class="text-sm text-gray-400">-</span>
                </template>
                <template #is_active="{ value }">
                    <Badge :variant="value ? 'success' : 'gray'">
                        {{ value ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/categories/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600">
                            <Eye class="w-4 h-4" />
                        </Link>
                        <button @click="openEditModal(row)" class="p-1 text-gray-500 hover:text-primary-600">
                            <Pencil class="w-4 h-4" />
                        </button>
                        <button @click="confirmDelete(row)" class="p-1 text-gray-500 hover:text-red-600">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Create Modal -->
        <FormModal
            :show="createModal"
            title="Create Category"
            description="Add a new course category"
            :icon="FolderOpen"
            :loading="createForm.processing"
            submit-text="Create Category"
            @close="closeCreateModal"
            @submit="submitCreate"
        >
            <CategoryForm v-model="createForm" @image-change="handleCreateImageChange" />
        </FormModal>

        <!-- Edit Modal -->
        <FormModal
            :show="editModal"
            title="Edit Category"
            :description="`Update ${categoryToEdit?.category_name || 'category'} details`"
            :icon="FolderOpen"
            :loading="editForm.processing"
            submit-text="Update Category"
            @close="closeEditModal"
            @submit="submitEdit"
        >
            <CategoryForm v-model="editForm" @image-change="handleEditImageChange" />
        </FormModal>

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
