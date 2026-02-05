<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Plus, Pencil, Trash2, Search, Eye, BookOpen } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    categories: Array,
    statusOptions: Array,
    levelOptions: Array,
    pricingTypeOptions: Array,
    filters: Object,
})

const search = ref(props.filters?.search || '')
const categoryFilter = ref(props.filters?.category_id || '')
const statusFilter = ref(props.filters?.status || '')
const pricingFilter = ref(props.filters?.pricing_type || '')
const priceMin = ref(props.filters?.price_min || '')
const priceMax = ref(props.filters?.price_max || '')

// Delete modal state
const deleteModal = ref(false)
const courseToDelete = ref(null)

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

const getStatusVariant = (status) => {
    const variants = {
        draft: 'gray',
        published: 'success',
        archived: 'danger',
    }
    return variants[status] || 'gray'
}

const applyFilters = () => {
    // Build price range filter value
    let priceRange = undefined
    if (priceMin.value || priceMax.value) {
        priceRange = `${priceMin.value || ''},${priceMax.value || ''}`
    }

    router.get('/admin/courses', {
        'filter[search]': search.value || undefined,
        'filter[category_id]': categoryFilter.value || undefined,
        'filter[status]': statusFilter.value || undefined,
        'filter[pricing_type]': pricingFilter.value || undefined,
        'filter[price_range]': priceRange,
    }, { preserveState: true })
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

const formatPrice = (row) => {
    if (row.pricing_type === 'free' || (!row.price || row.price == 0)) {
        return { text: 'Free', isFree: true }
    }
    return {
        text: new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(row.price),
        isFree: false
    }
}

const getImageUrl = (row) => {
    const url = row.thumbnail_url || row.image_url
    if (!url) return null
    if (url.startsWith('http')) return url
    return `/storage/${url}`
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
                <Link href="/admin/courses/create" class="btn btn-primary btn-md">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Course
                </Link>
            </div>

            <!-- Filters -->
            <div class="card p-4">
                <div class="flex flex-col gap-4">
                    <!-- Row 1: Search and Category -->
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
                    </div>
                    <!-- Row 2: Pricing filters -->
                    <div class="flex flex-col sm:flex-row gap-4 items-end">
                        <select v-model="pricingFilter" class="input w-full sm:w-32">
                            <option value="">All Pricing</option>
                            <option v-for="opt in pricingTypeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <div class="flex items-center gap-2">
                            <div class="flex flex-col">
                                <label class="text-xs text-gray-500 mb-1">Min Price</label>
                                <input
                                    v-model="priceMin"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="$0"
                                    class="input w-24"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                            <span class="text-gray-400 mt-5">-</span>
                            <div class="flex flex-col">
                                <label class="text-xs text-gray-500 mb-1">Max Price</label>
                                <input
                                    v-model="priceMax"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="$999"
                                    class="input w-24"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <button @click="applyFilters" class="btn btn-secondary btn-md">
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="items?.data || []" :pagination="items">
                <template #thumbnail="{ row }">
                    <div class="w-16 h-12 bg-gray-100 rounded overflow-hidden">
                        <img v-if="getImageUrl(row)" :src="getImageUrl(row)" :alt="row.course_name"
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
                    <Badge v-if="formatPrice(row).isFree" variant="success">
                        Free
                    </Badge>
                    <span v-else class="text-sm font-medium">{{ formatPrice(row).text }}</span>
                </template>
                <template #status="{ row }">
                    <Badge :variant="getStatusVariant(row.status)">
                        {{ row.status }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center space-x-2">
                        <Link :href="`/admin/courses/${row.id}`" class="p-1 text-gray-500 hover:text-primary-600" title="View">
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link :href="`/admin/courses/${row.id}/edit`" class="p-1 text-gray-500 hover:text-primary-600" title="Edit">
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button @click="confirmDelete(row)" class="p-1 text-gray-500 hover:text-red-600" title="Delete">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

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
