<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import {
    ArrowLeft, Award, User, BookOpen, Calendar, Download, XCircle,
    Shield, CheckCircle, Clock, GraduationCap, Star, Printer
} from 'lucide-vue-next'

const props = defineProps({
    certificate: Object,
    enrollment: Object,
})

const certificate = props.certificate
const enrollment = props.enrollment

const revokeModal = ref(false)

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatShortDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const revokeCertificate = () => {
    router.delete(`/admin/certificates/${certificate.id}`, {
        onSuccess: () => {
            revokeModal.value = false
        }
    })
}

const printCertificate = () => {
    window.print()
}
</script>

<template>
    <AdminLayout>
        <Head title="Certificate Details" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between print:hidden">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/certificates" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Certificate Details</h1>
                        <p class="text-gray-500 font-mono text-sm">{{ certificate.certificate_code }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="printCertificate" class="btn btn-secondary btn-md">
                        <Printer class="w-4 h-4 mr-2" />
                        Print
                    </button>
                    <a v-if="certificate.certificate_url" :href="certificate.certificate_url"
                        target="_blank" class="btn btn-secondary btn-md">
                        <Download class="w-4 h-4 mr-2" />
                        Download PDF
                    </a>
                    <button v-if="certificate.is_valid" @click="revokeModal = true"
                        class="btn btn-danger btn-md">
                        <XCircle class="w-4 h-4 mr-2" />
                        Revoke
                    </button>
                </div>
            </div>

            <!-- Certificate Card - Professional Design with Brand Colors -->
            <div class="certificate-wrapper bg-white rounded-xl shadow-lg overflow-hidden print:shadow-none print:rounded-none">
                <!-- Decorative Top Border - Blue to Green gradient matching logo -->
                <div class="h-3 print:h-2 bg-gradient-to-r from-primary-500 via-primary-400 to-green-500"></div>

                <div class="certificate-content relative p-10 md:p-14 print:p-6">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-[0.03]">
                        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%233b82f6\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                    </div>

                    <!-- Corner Decorations - Brand Blue -->
                    <div class="absolute top-6 left-6 print:top-3 print:left-3 w-16 h-16 print:w-10 print:h-10 border-t-4 border-l-4 print:border-t-2 print:border-l-2 border-primary-400 rounded-tl-lg"></div>
                    <div class="absolute top-6 right-6 print:top-3 print:right-3 w-16 h-16 print:w-10 print:h-10 border-t-4 border-r-4 print:border-t-2 print:border-r-2 border-green-400 rounded-tr-lg"></div>
                    <div class="absolute bottom-6 left-6 print:bottom-3 print:left-3 w-16 h-16 print:w-10 print:h-10 border-b-4 border-l-4 print:border-b-2 print:border-l-2 border-green-400 rounded-bl-lg"></div>
                    <div class="absolute bottom-6 right-6 print:bottom-3 print:right-3 w-16 h-16 print:w-10 print:h-10 border-b-4 border-r-4 print:border-b-2 print:border-r-2 border-primary-400 rounded-br-lg"></div>

                    <div class="relative text-center space-y-6 print:space-y-3">
                        <!-- Logo/Award Icon -->
                        <div class="flex justify-center print:mb-1">
                            <div class="relative">
                                <div class="absolute inset-0 bg-green-400 rounded-full blur-xl opacity-30 print:hidden"></div>
                                <div class="relative bg-green-500 rounded-full p-5 print:p-3 shadow-lg print:shadow-none print:border-2 print:border-green-500">
                                    <Award class="w-12 h-12 print:w-8 print:h-8 text-white print:text-green-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Header Text -->
                        <div class="space-y-2 print:space-y-1">
                            <p class="text-primary-600 font-semibold tracking-widest uppercase text-sm print:text-xs">EduPlex Learning Platform</p>
                            <h2 class="text-4xl md:text-5xl print:text-3xl font-serif font-bold text-gray-800 tracking-tight">
                                Certificate of Completion
                            </h2>
                        </div>

                        <!-- Divider -->
                        <div class="flex items-center justify-center gap-3 print:gap-2">
                            <div class="h-px w-24 print:w-16 bg-gradient-to-r from-transparent to-primary-400"></div>
                            <Star class="w-5 h-5 print:w-4 print:h-4 text-primary-500" />
                            <div class="h-px w-24 print:w-16 bg-gradient-to-l from-transparent to-green-400"></div>
                        </div>

                        <!-- Presented To -->
                        <div class="space-y-3 print:space-y-1 py-4 print:py-2">
                            <p class="text-gray-500 text-lg print:text-sm">This is to certify that</p>
                            <p class="text-4xl md:text-5xl print:text-2xl font-serif font-bold text-primary-600 py-2 print:py-1">
                                {{ certificate.student?.user?.full_name }}
                            </p>
                            <p class="text-gray-500 text-lg print:text-sm">has successfully completed the course</p>
                        </div>

                        <!-- Course Name -->
                        <div class="bg-gradient-to-r from-primary-50 via-blue-50 to-green-50 rounded-xl py-5 px-8 print:py-2 print:px-4 inline-block border border-primary-100">
                            <p class="text-2xl md:text-3xl print:text-lg font-semibold text-gray-800">
                                {{ certificate.course?.course_name }}
                            </p>
                            <p v-if="certificate.course?.category" class="text-green-600 mt-1 print:text-sm print:mt-0">
                                {{ certificate.course.category.category_name }}
                            </p>
                        </div>

                        <!-- Course Details -->
                        <div class="flex flex-wrap justify-center gap-6 print:gap-4 text-sm print:text-xs text-gray-600 pt-4 print:pt-1">
                            <div class="flex items-center gap-2 print:gap-1">
                                <Clock class="w-4 h-4 print:w-3 print:h-3 text-primary-500" />
                                <span>{{ certificate.course?.duration_hours || 0 }} hours</span>
                            </div>
                            <div v-if="certificate.course?.level" class="flex items-center gap-2 print:gap-1">
                                <GraduationCap class="w-4 h-4 print:w-3 print:h-3 text-green-500" />
                                <span class="capitalize">{{ certificate.course?.level }}</span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="flex items-center justify-center gap-3 py-2 print:py-1">
                            <div class="h-px w-16 print:w-10 bg-gray-300"></div>
                            <CheckCircle class="w-5 h-5 print:w-4 print:h-4 text-green-500" />
                            <div class="h-px w-16 print:w-10 bg-gray-300"></div>
                        </div>

                        <!-- Certificate Details -->
                        <div class="grid grid-cols-3 gap-6 print:gap-4 max-w-2xl mx-auto pt-4 print:pt-2">
                            <div class="text-center">
                                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 print:mb-0 print:text-[10px]">Issue Date</p>
                                <p class="font-semibold text-gray-800 print:text-sm">{{ formatDate(certificate.issue_date) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 print:mb-0 print:text-[10px]">Certificate ID</p>
                                <p class="font-mono font-semibold text-gray-800 text-sm print:text-xs">{{ certificate.certificate_code }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 print:mb-0 print:text-[10px]">Status</p>
                                <Badge :variant="certificate.is_valid ? 'success' : 'danger'" class="text-sm print:text-xs">
                                    {{ certificate.is_valid ? 'Valid' : 'Revoked' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Signature Area -->
                        <div class="flex justify-center gap-16 print:gap-8 pt-8 print:pt-3 pb-4 print:pb-2">
                            <div class="text-center">
                                <div class="w-40 print:w-28 border-b-2 border-primary-300 mb-2 print:mb-1"></div>
                                <p class="text-sm print:text-xs text-gray-500">Platform Director</p>
                            </div>
                            <div class="text-center">
                                <div class="w-40 print:w-28 border-b-2 border-green-300 mb-2 print:mb-1"></div>
                                <p class="text-sm print:text-xs text-gray-500">Course Instructor</p>
                            </div>
                        </div>

                        <!-- Verification Footer -->
                        <div class="pt-4 print:pt-2 border-t border-gray-200">
                            <div class="flex items-center justify-center gap-2 print:gap-1 text-sm print:text-xs text-gray-500">
                                <Shield class="w-4 h-4 print:w-3 print:h-3 text-green-500" />
                                <span>Verify at: </span>
                                <code class="bg-primary-50 text-primary-700 px-2 py-1 print:px-1 print:py-0.5 rounded text-xs print:text-[10px] border border-primary-100">
                                    {{ certificate.verification_url || `${window.location.origin}/verify/${certificate.certificate_code}` }}
                                </code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decorative Bottom Border - Blue to Green gradient -->
                <div class="h-3 print:h-2 bg-gradient-to-r from-primary-500 via-primary-400 to-green-500"></div>
            </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print:hidden">
                <!-- Student Info -->
                <div class="card p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 bg-primary-100 rounded-lg">
                            <User class="w-5 h-5 text-primary-600" />
                        </div>
                        <h2 class="text-lg font-semibold">Student Information</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Full Name</span>
                            <span class="font-medium text-gray-900">{{ certificate.student?.user?.full_name }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Email</span>
                            <span class="text-gray-900">{{ certificate.student?.user?.email }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Student ID</span>
                            <span class="font-mono text-gray-900">{{ certificate.student?.student_id_number || '-' }}</span>
                        </div>
                        <div v-if="enrollment" class="flex justify-between items-center py-2">
                            <span class="text-gray-500">Enrolled On</span>
                            <span class="text-gray-900">{{ formatShortDate(enrollment.enrollment_date) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="card p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <BookOpen class="w-5 h-5 text-green-600" />
                        </div>
                        <h2 class="text-lg font-semibold">Course Information</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Course Name</span>
                            <span class="font-medium text-gray-900">{{ certificate.course?.course_name }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Course Code</span>
                            <span class="font-mono text-gray-900">{{ certificate.course?.course_code }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Duration</span>
                            <span class="text-gray-900">{{ certificate.course?.duration_hours || 0 }} hours</span>
                        </div>
                        <div v-if="enrollment" class="flex justify-between items-center py-2">
                            <span class="text-gray-500">Completed On</span>
                            <span class="text-gray-900">{{ formatShortDate(enrollment.completion_date) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div v-if="enrollment" class="card p-6 print:hidden">
                <div class="flex items-center gap-3 mb-5">
                    <div class="p-2 bg-primary-100 rounded-lg">
                        <Calendar class="w-5 h-5 text-primary-600" />
                    </div>
                    <h2 class="text-lg font-semibold">Completion Timeline</h2>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-primary-500 rounded-full"></div>
                        <div>
                            <p class="font-medium text-gray-900">Enrolled</p>
                            <p class="text-sm text-gray-500">{{ formatShortDate(enrollment.enrollment_date) }}</p>
                        </div>
                    </div>
                    <div class="flex-1 mx-4 h-1 bg-gradient-to-r from-primary-500 to-primary-400 rounded"></div>
                    <div class="flex items-center gap-3">
                        <div>
                            <p class="font-medium text-gray-900 text-right">Completed</p>
                            <p class="text-sm text-gray-500">{{ formatShortDate(enrollment.completion_date) }}</p>
                        </div>
                        <div class="w-3 h-3 bg-primary-400 rounded-full"></div>
                    </div>
                    <div class="flex-1 mx-4 h-1 bg-gradient-to-r from-primary-400 to-green-500 rounded"></div>
                    <div class="flex items-center gap-3">
                        <div>
                            <p class="font-medium text-gray-900 text-right">Certified</p>
                            <p class="text-sm text-gray-500">{{ formatShortDate(certificate.issue_date) }}</p>
                        </div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revoke Confirmation Modal -->
        <ConfirmModal
            :show="revokeModal"
            title="Revoke Certificate"
            :message="`Are you sure you want to revoke this certificate for ${certificate.student?.user?.full_name}? This action cannot be undone.`"
            confirm-text="Revoke Certificate"
            :danger="true"
            @close="revokeModal = false"
            @confirm="revokeCertificate"
        />
    </AdminLayout>
</template>

<style scoped>
.font-serif {
    font-family: Georgia, 'Times New Roman', serif;
}
</style>

<style>
@media print {
    /* Hide everything except certificate */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        height: 100% !important;
        overflow: hidden !important;
    }

    body * {
        visibility: hidden;
    }

    .certificate-wrapper,
    .certificate-wrapper * {
        visibility: visible;
    }

    .certificate-wrapper {
        position: fixed;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        margin: 0;
        padding: 0;
        box-shadow: none !important;
        border-radius: 0 !important;
        overflow: hidden;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    .certificate-wrapper * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Compact certificate content for print */
    .certificate-wrapper .certificate-content {
        padding: 1.5rem 2rem !important;
    }

    .certificate-wrapper .space-y-6 > * + * {
        margin-top: 0.75rem !important;
    }

    .certificate-wrapper .text-5xl,
    .certificate-wrapper .md\:text-5xl {
        font-size: 2rem !important;
        line-height: 1.2 !important;
    }

    .certificate-wrapper .text-4xl,
    .certificate-wrapper .md\:text-4xl {
        font-size: 1.75rem !important;
        line-height: 1.2 !important;
    }

    .certificate-wrapper .text-3xl,
    .certificate-wrapper .md\:text-3xl {
        font-size: 1.25rem !important;
        line-height: 1.3 !important;
    }

    .certificate-wrapper .text-2xl {
        font-size: 1.1rem !important;
    }

    .certificate-wrapper .text-lg {
        font-size: 0.875rem !important;
    }

    .certificate-wrapper .py-4,
    .certificate-wrapper .py-5 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .certificate-wrapper .pt-8 {
        padding-top: 1rem !important;
    }

    .certificate-wrapper .pb-4 {
        padding-bottom: 0.5rem !important;
    }

    .certificate-wrapper .pt-4 {
        padding-top: 0.5rem !important;
    }

    .certificate-wrapper .gap-16 {
        gap: 3rem !important;
    }

    .certificate-wrapper .gap-6 {
        gap: 1rem !important;
    }

    .certificate-wrapper .p-5 {
        padding: 0.75rem !important;
    }

    .certificate-wrapper .w-12 {
        width: 2rem !important;
        height: 2rem !important;
    }

    .certificate-wrapper .w-16 {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }

    /* Corner decorations smaller */
    .certificate-wrapper .absolute.top-6 {
        top: 0.75rem !important;
    }
    .certificate-wrapper .absolute.bottom-6 {
        bottom: 0.75rem !important;
    }
    .certificate-wrapper .absolute.left-6 {
        left: 0.75rem !important;
    }
    .certificate-wrapper .absolute.right-6 {
        right: 0.75rem !important;
    }

    /* Ensure proper sizing for print */
    @page {
        size: A4 landscape;
        margin: 0;
    }
}
</style>
