<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import FormModal from '@/Components/FormModal.vue'
import CourseForm from '@/Components/CourseForm.vue'
import { Plus, Pencil, Trash2, Search, Eye, BookOpen } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    categories: Array,
    statusOptions: Array,
    levelOptions: Array,
    filters: Object,
})

const search = ref(props.filters?.search || '')
const categoryFilter = ref(props.filters?.category_id || '')
const statusFilter = ref(props.filters?.status || '')

// Modal states
const createModal = ref(false)
const editModal = ref(false)
const deleteModal = ref(false)
const courseToDelete = ref(null)
const courseToEdit = ref(null)

// Create form
const createForm = useForm({
    course_name: '',
    course_code: '',
    description: '',
    category_id: '',
    level: 'beginner',
    duration_hours: '',
    price: 0,
    instructor_name: '',
    enrollment_limit: '',
    is_featured: false,
    image: null,
    image_url: '',
})

// Edit form
const editForm = useForm({
    _method: 'PUT',
    course_name: '',
    course_code: '',
    description: '',
    category_id: '',
    level: 'beginner',
    duration_hours: '',
    price: 0,
    instructor_name: '',
    enrollment_limit: '',
    is_featured: false,
    status: 'draft',
    image: null,
    image_url: '',
})

const columns = [
    { key: 'thumbnail', label: 'Image', class: 'w-20' },
    { key: 'course_name', label: 'Course Name' },
    { key: 'category', label: 'Category' },
    { key: 'instructor', label: 'Instructor' },
    { key: 'price', label: 'Price', class: 'w-24' },
    { key: 'status', label: 'Status', class: 'w-24' },
    { key: 'actions', label: 'Actions', class: 'w-32' },
]

const filterStatusOptions = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
]

const levelOptions = [
    { value: 'beginner', label: 'Beginner' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'advanced', label: 'Advanced' },
]

const editStatusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
]

const getStatusVariant = (status) => {
    const variants = {
        draft: 'gray',
        published: 'success',
        archived: 'danger',
    }
    return variants[status] || 'gray'
}

const applyFilters = () => {
    router.get('/admin/courses', {
        search: search.value,
        category_id: categoryFilter.value,
        status: statusFilter.value,
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
    createForm.post('/admin/courses', {
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
const openEditModal = (course) => {
    courseToEdit.value = course
    editForm.course_name = course.course_name
    editForm.course_code = course.course_code
    editForm.description = course.description || ''
    editForm.category_id = course.category_id || ''
    editForm.level = course.level || 'beginner'
    editForm.duration_hours = course.duration_hours || ''
    editForm.price = course.price || 0
    editForm.instructor_name = course.instructor_name || ''
    editForm.enrollment_limit = course.enrollment_limit || ''
    editForm.is_featured = course.is_featured || false
    editForm.status = course.status || 'draft'
    editForm.image = null
    editForm.image_url = course.thumbnail_url || ''
    editForm.clearErrors()
    editModal.value = true
}

const closeEditModal = () => {
    editModal.value = false
    courseToEdit.value = null
}

const submitEdit = () => {
    editForm.post(`/admin/courses/${courseToEdit.value.id}`, {
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
const confirmDelete = (course) => {
    courseToDelete.value = course
    deleteModal.value = true
}

const deleteCourse = () => {
    router.delete(`/admin/courses/${courseToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            courseToDelete.value = null
        },
    })
}

const formatPrice = (price) => {
    if (!price || price == 0) return 'Free'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price)
}
</script>

<template>
    <AdminLayout>
        <Head title="Courses" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Courses</h1>
                    <p class="text-gray-500">Manage all courses</p>
                </div>
                <button @click="openCreateModal" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Course
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
                            placeholder="Search courses..."
                            class="input pl-10"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="categoryFilter" class="input w-full sm:w-48">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.category_name }}
                        </option>
                    </select>
                    <select v-model="statusFilter" class="input w-full sm:w-40">
                        <option v-for="opt in filterStatusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="items?.data || []" :pagination="items">
                <template #thumbnail="{ row }">
                    <div class="w-16 h-12 bg-gray-100 rounded overflow-hidden">
                        <img v-if="row.thumbnail_url || row.image_url" :src="row.thumbnail_url || row.image_url" :alt="row.course_name"
                            class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <BookOpen class="w-6 h-6 text-gray-400" />
                        </div>
                    </div>
                </template>
                <template #course_name="{ row }">
                    <div>
                        <p class="font-medium">{{ row.course_name }}</p>
                        <p class="text-sm text-gray-500">{{ row.course_code }}</p>
                    </div>
                </template>
                <template #category="{ row }">
                    <span class="text-sm">{{ row.category?.category_name || '-' }}</span>
                </template>
                <template #instructor="{ row }">
                    <span class="text-sm">{{ row.admin?.user?.full_name || row.instructor_name || '-' }}</span>
                </template>
                <template #price="{ row }">
                    <span class="text-sm font-medium">{{ formatPrice(row.price) }}</span>
                </template>
                <template #status="{ row }">
                    <Badge :variant="getStatusVariant(row.status)">
                        {{ row.status }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/courses/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600">
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
            title="Create Course"
            description="Add a new course to the system"
            :icon="BookOpen"
            :loading="createForm.processing"
            submit-text="Create Course"
            @close="closeCreateModal"
            @submit="submitCreate"
        >
            <CourseForm
                v-model="createForm"
                :categories="categories"
                :level-options="levelOptions"
                @image-change="handleCreateImageChange"
            />
        </FormModal>

        <!-- Edit Modal -->
        <FormModal
            :show="editModal"
            title="Edit Course"
            :description="`Update ${courseToEdit?.course_name || 'course'} details`"
            :icon="BookOpen"
            :loading="editForm.processing"
            submit-text="Update Course"
            @close="closeEditModal"
            @submit="submitEdit"
        >
            <CourseForm
                v-model="editForm"
                :categories="categories"
                :level-options="levelOptions"
                :status-options="editStatusOptions"
                :is-edit="true"
                @image-change="handleEditImageChange"
            />
        </FormModal>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :show="deleteModal"
            title="Delete Course"
            :message="`Are you sure you want to delete ${courseToDelete?.course_name}? This will also delete all modules and lessons.`"
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteCourse"
        />
    </AdminLayout>
</template>
