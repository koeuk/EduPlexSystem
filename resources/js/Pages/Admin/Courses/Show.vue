<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import QrcodeVue from 'qrcode.vue'
import { ArrowLeft, BookOpen, Clock, Users, Star, DollarSign, Layers, QrCode, Copy, Check, Download } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    stats: Object,
})

const course = props.item
const modules = course.modules || []

const getStatusVariant = (status) => {
    const variants = {
        draft: 'gray',
        published: 'success',
        archived: 'danger',
    }
    return variants[status] || 'gray'
}

const formatPrice = (price) => {
    if (!price || price == 0) return 'Free'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}

const getImageUrl = () => {
    const url = course.thumbnail_url || course.image_url
    if (!url) return null
    if (url.startsWith('http')) return url
    return `/storage/${url}`
}

// QR Code functionality
const copied = ref(false)
const qrCodeRef = ref(null)

const copyEnrollmentCode = async () => {
    try {
        await navigator.clipboard.writeText(course.enrollment_code)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy:', err)
    }
}

const downloadQrCode = () => {
    const canvas = document.querySelector('#qr-code canvas')
    if (canvas) {
        const link = document.createElement('a')
        link.download = `${course.course_code}-qr-code.png`
        link.href = canvas.toDataURL('image/png')
        link.click()
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="course.course_name" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/courses" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ course.course_name }}</h1>
                        <p class="text-gray-500">{{ course.course_code }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <Link :href="`/admin/courses/${course.id}/modules`" class="btn btn-secondary btn-md">
                        <Layers class="w-4 h-4 mr-2" />
                        Manage Modules
                    </Link>
                    <Link :href="`/admin/courses/${course.id}/edit`" class="btn btn-primary btn-md">
                        Edit Course
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Course Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Thumbnail & Basic Info -->
                    <div class="card p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="w-full md:w-64 h-40 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <img v-if="getImageUrl()" :src="getImageUrl()" :alt="course.course_name"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <BookOpen class="w-12 h-12 text-gray-400" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <Badge :variant="getStatusVariant(course.status)">
                                        {{ course.status }}
                                    </Badge>
                                    <Badge v-if="course.is_featured" variant="info">Featured</Badge>
                                </div>
                                <p class="text-gray-600 mb-4">{{ course.short_description || course.description }}</p>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <Users class="w-4 h-4" />
                                        {{ enrollmentsCount || 0 }} students
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Clock class="w-4 h-4" />
                                        {{ course.duration_hours || 0 }} hours
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Star class="w-4 h-4" />
                                        {{ averageRating?.toFixed(1) || 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">Description</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ course.description || 'No description provided.' }}</p>
                    </div>

                    <!-- What You'll Learn -->
                    <div v-if="course.what_you_learn" class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">What You'll Learn</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ course.what_you_learn }}</p>
                    </div>

                    <!-- Requirements -->
                    <div v-if="course.requirements" class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">Requirements</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ course.requirements }}</p>
                    </div>

                    <!-- Modules -->
                    <div class="card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold">Course Modules</h2>
                            <span class="text-sm text-gray-500">{{ modules?.length || 0 }} modules</span>
                        </div>

                        <div v-if="modules && modules.length > 0" class="space-y-3">
                            <div v-for="(mod, index) in modules" :key="mod.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
                                        {{ index + 1 }}
                                    </span>
                                    <div>
                                        <p class="font-medium">{{ mod.module_title }}</p>
                                        <p class="text-sm text-gray-500">{{ mod.lessons_count || 0 }} lessons</p>
                                    </div>
                                </div>
                                <Badge :variant="mod.is_published ? 'success' : 'gray'">
                                    {{ mod.is_published ? 'Published' : 'Draft' }}
                                </Badge>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 text-center py-8">No modules yet</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Price Card -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">Pricing</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Price</span>
                                <span class="text-xl font-bold text-primary-600">{{ formatPrice(course.price) }}</span>
                            </div>
                            <div v-if="course.discount_price" class="flex items-center justify-between">
                                <span class="text-gray-500">Discount Price</span>
                                <span class="font-medium text-green-600">{{ formatPrice(course.discount_price) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Course Details -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold mb-4">Course Details</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Category</span>
                                <span>{{ course.category?.category_name || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Instructor</span>
                                <span>{{ course.instructor?.user?.full_name || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Difficulty</span>
                                <Badge variant="info">{{ course.difficulty_level }}</Badge>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Language</span>
                                <span>{{ course.language || 'English' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Duration</span>
                                <span>{{ course.duration_hours || 0 }} hours</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Created</span>
                                <span>{{ formatDate(course.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Last Updated</span>
                                <span>{{ formatDate(course.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Enrollment -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                            <QrCode class="w-5 h-5" />
                            Quick Enrollment
                        </h2>
                        <div class="space-y-4">
                            <!-- Enrollment Code -->
                            <div>
                                <label class="text-sm text-gray-500 block mb-1">Enrollment Code</label>
                                <div class="flex items-center gap-2">
                                    <code class="flex-1 px-3 py-2 bg-gray-100 rounded-lg font-mono text-lg text-center">
                                        {{ course.enrollment_code }}
                                    </code>
                                    <button
                                        @click="copyEnrollmentCode"
                                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                        :title="copied ? 'Copied!' : 'Copy code'"
                                    >
                                        <Check v-if="copied" class="w-5 h-5 text-green-500" />
                                        <Copy v-else class="w-5 h-5 text-gray-500" />
                                    </button>
                                </div>
                            </div>

                            <!-- QR Code -->
                            <div class="flex flex-col items-center">
                                <div id="qr-code" class="bg-white p-4 rounded-lg border">
                                    <QrcodeVue
                                        :value="course.enrollment_code"
                                        :size="160"
                                        level="H"
                                        render-as="canvas"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-2 text-center">
                                    Students can scan this QR code to enroll
                                </p>
                            </div>

                            <!-- Download Button -->
                            <button
                                @click="downloadQrCode"
                                class="btn btn-secondary btn-md w-full"
                            >
                                <Download class="w-4 h-4 mr-2" />
                                Download QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
