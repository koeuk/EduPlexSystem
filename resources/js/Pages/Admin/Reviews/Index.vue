<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Search, Trash2, Star, Eye } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    courses: Array,
    stats: Object,
    ratingOptions: Array,
    filters: Object,
})

const reviews = props.items

const search = ref(props.filters?.filter?.search || '')
const courseFilter = ref(props.filters?.filter?.course_id || '')
const ratingFilter = ref(props.filters?.filter?.rating || '')
const deleteModal = ref(false)
const reviewToDelete = ref(null)

const columns = [
    { key: 'student', label: 'Student' },
    { key: 'course', label: 'Course' },
    { key: 'rating', label: 'Rating', class: 'w-32' },
    { key: 'review', label: 'Review' },
    { key: 'created_at', label: 'Date', class: 'w-32' },
    { key: 'actions', label: '', class: 'w-20' },
]

const ratingOptions = [
    { value: '', label: 'All Ratings' },
    { value: '5', label: '5 Stars' },
    { value: '4', label: '4 Stars' },
    { value: '3', label: '3 Stars' },
    { value: '2', label: '2 Stars' },
    { value: '1', label: '1 Star' },
]

const applyFilters = () => {
    router.get('/admin/reviews', {
        'filter[search]': search.value || undefined,
        'filter[course_id]': courseFilter.value || undefined,
        'filter[rating]': ratingFilter.value || undefined,
    }, { preserveState: true })
}

const confirmDelete = (review) => {
    reviewToDelete.value = review
    deleteModal.value = true
}

const deleteReview = () => {
    router.delete(`/admin/reviews/${reviewToDelete.value.id}`, {
        onSuccess: () => {
            deleteModal.value = false
            reviewToDelete.value = null
        },
    })
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Reviews" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Course Reviews</h1>
                <p class="text-gray-500">Manage student reviews and ratings</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="card p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <Star class="w-5 h-5 text-yellow-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Average Rating</p>
                            <p class="text-xl font-bold">{{ stats?.avgRating || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="card p-4">
                    <p class="text-sm text-gray-500">Total Reviews</p>
                    <p class="text-xl font-bold">{{ stats?.totalReviews || 0 }}</p>
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
                            placeholder="Search reviews..."
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
                    <select v-model="ratingFilter" class="input w-full sm:w-32">
                        <option v-for="opt in ratingOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyFilters" class="btn btn-secondary btn-md">
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="reviews?.data || []" :pagination="reviews">
                <template #student="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                            <img
                                v-if="row.student?.user?.image_url"
                                :src="row.student.user.image_url.startsWith('http') ? row.student.user.image_url : `/storage/${row.student.user.image_url}`"
                                :alt="row.student?.user?.full_name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="text-sm font-medium text-gray-500">
                                {{ row.student?.user?.full_name?.charAt(0) || '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-sm">{{ row.student?.user?.full_name || 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ row.student?.user?.email }}</p>
                        </div>
                    </div>
                </template>
                <template #course="{ row }">
                    <span class="text-sm">{{ row.course?.course_name || '-' }}</span>
                </template>
                <template #rating="{ row }">
                    <div class="flex items-center gap-1">
                        <Star v-for="i in 5" :key="i"
                            class="w-4 h-4"
                            :class="i <= row.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" />
                    </div>
                </template>
                <template #review="{ row }">
                    <p class="text-sm text-gray-600 line-clamp-2">{{ row.review_text || 'No comment' }}</p>
                </template>
                <template #created_at="{ row }">
                    <span class="text-sm">{{ formatDate(row.created_at) }}</span>
                </template>
                <template #actions="{ row }">
                    <button @click="confirmDelete(row)" class="p-1 text-gray-500 hover:text-red-600">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </template>
            </DataTable>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :show="deleteModal"
            title="Delete Review"
            message="Are you sure you want to delete this review? This action cannot be undone."
            confirm-text="Delete"
            :danger="true"
            @close="deleteModal = false"
            @confirm="deleteReview"
        />
    </AdminLayout>
</template>
