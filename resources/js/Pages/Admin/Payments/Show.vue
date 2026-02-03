<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, CreditCard, User, BookOpen, Calendar, Receipt } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
})

const payment = props.item

const getStatusVariant = (status) => {
    const variants = {
        pending: 'warning',
        completed: 'success',
        failed: 'danger',
        refunded: 'info',
    }
    return variants[status] || 'gray'
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price || 0)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Payment Details" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/payments" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payment Details</h1>
                    <p class="text-gray-500 font-mono">{{ payment.transaction_id || `#${payment.id}` }}</p>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary-100 rounded-full">
                            <Receipt class="w-8 h-8 text-primary-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold">{{ formatPrice(payment.amount) }}</p>
                            <p class="text-gray-500">Payment Amount</p>
                        </div>
                    </div>
                    <Badge :variant="getStatusVariant(payment.status)" class="text-lg px-4 py-2">
                        {{ payment.status }}
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
                            <span class="font-medium">{{ payment.student?.user?.full_name || 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Email</span>
                            <span>{{ payment.student?.user?.email || '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Student ID</span>
                            <span>{{ payment.student?.student_id_number || '-' }}</span>
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
                            <span class="font-medium">{{ payment.course?.course_name || '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Course Code</span>
                            <span>{{ payment.course?.course_code || '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Original Price</span>
                            <span>{{ formatPrice(payment.course?.price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <CreditCard class="w-5 h-5 text-gray-400" />
                        <h2 class="text-lg font-semibold">Payment Details</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Method</span>
                            <span class="capitalize">{{ payment.payment_method || '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Gateway</span>
                            <span class="capitalize">{{ payment.payment_gateway || '-' }}</span>
                        </div>
                        <div v-if="payment.discount_amount" class="flex justify-between text-sm">
                            <span class="text-gray-500">Discount</span>
                            <span class="text-green-600">-{{ formatPrice(payment.discount_amount) }}</span>
                        </div>
                        <div v-if="payment.coupon_code" class="flex justify-between text-sm">
                            <span class="text-gray-500">Coupon</span>
                            <span class="font-mono">{{ payment.coupon_code }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Calendar class="w-5 h-5 text-gray-400" />
                        <h2 class="text-lg font-semibold">Timeline</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Created</span>
                            <span>{{ formatDateTime(payment.created_at) }}</span>
                        </div>
                        <div v-if="payment.paid_at" class="flex justify-between text-sm">
                            <span class="text-gray-500">Paid</span>
                            <span>{{ formatDateTime(payment.paid_at) }}</span>
                        </div>
                        <div v-if="payment.refunded_at" class="flex justify-between text-sm">
                            <span class="text-gray-500">Refunded</span>
                            <span>{{ formatDateTime(payment.refunded_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="payment.notes" class="card p-6">
                <h2 class="text-lg font-semibold mb-2">Notes</h2>
                <p class="text-gray-600">{{ payment.notes }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
