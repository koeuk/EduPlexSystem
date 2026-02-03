<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, Award, User, BookOpen, Calendar, Download, XCircle } from 'lucide-vue-next'

const props = defineProps({
    certificate: Object,
})

const certificate = props.certificate

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}

const revokeCertificate = () => {
    if (confirm('Are you sure you want to revoke this certificate?')) {
        router.delete(`/admin/certificates/${certificate.id}`)
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Certificate Details" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/certificates" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Certificate Details</h1>
                        <p class="text-gray-500 font-mono">{{ certificate.certificate_code }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a v-if="certificate.certificate_url" :href="certificate.certificate_url"
                        target="_blank" class="btn btn-secondary btn-md">
                        <Download class="w-4 h-4 mr-2" />
                        Download
                    </a>
                    <button v-if="certificate.is_valid" @click="revokeCertificate"
                        class="btn btn-danger btn-md">
                        <XCircle class="w-4 h-4 mr-2" />
                        Revoke
                    </button>
                </div>
            </div>

            <!-- Certificate Card -->
            <div class="card p-8 text-center border-2 border-yellow-200 bg-gradient-to-b from-yellow-50 to-white">
                <Award class="w-16 h-16 text-yellow-500 mx-auto mb-4" />
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Certificate of Completion</h2>
                <p class="text-gray-500 mb-6">This certifies that</p>
                <p class="text-3xl font-bold text-primary-600 mb-2">
                    {{ certificate.student?.user?.full_name }}
                </p>
                <p class="text-gray-500 mb-6">has successfully completed</p>
                <p class="text-xl font-semibold text-gray-900 mb-6">
                    {{ certificate.course?.course_name }}
                </p>
                <div class="flex items-center justify-center gap-8 text-sm text-gray-500">
                    <div>
                        <p class="font-medium text-gray-900">Issue Date</p>
                        <p>{{ formatDate(certificate.issue_date) }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Certificate ID</p>
                        <p class="font-mono">{{ certificate.certificate_code }}</p>
                    </div>
                </div>
                <div class="mt-6">
                    <Badge :variant="certificate.is_valid ? 'success' : 'danger'" class="text-sm">
                        {{ certificate.is_valid ? 'Valid Certificate' : 'Revoked' }}
                    </Badge>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student Info -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <User class="w-5 h-5 text-gray-400" />
                        <h2 class="text-lg font-semibold">Student Information</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Name</span>
                            <span class="font-medium">{{ certificate.student?.user?.full_name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Email</span>
                            <span>{{ certificate.student?.user?.email }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Student ID</span>
                            <span>{{ certificate.student?.student_id_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <BookOpen class="w-5 h-5 text-gray-400" />
                        <h2 class="text-lg font-semibold">Course Information</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Course</span>
                            <span class="font-medium">{{ certificate.course?.course_name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Code</span>
                            <span>{{ certificate.course?.course_code }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Duration</span>
                            <span>{{ certificate.course?.duration_hours }} hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
