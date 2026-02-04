<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, Mail, Phone, MapPin, Calendar, Building, User } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
})

const admin = props.item

const getStatusVariant = (status) => {
    const variants = {
        active: 'success',
        inactive: 'gray',
        suspended: 'danger',
    }
    return variants[status] || 'gray'
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString()
}

const getProfileImage = () => {
    if (admin.user?.image_url) {
        if (admin.user.image_url.startsWith('http')) {
            return admin.user.image_url
        }
        return `/storage/${admin.user.image_url}`
    }
    return null
}
</script>

<template>
    <AdminLayout>
        <Head :title="admin.user?.full_name || 'Admin Details'" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/admins" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ admin.user?.full_name }}</h1>
                        <p class="text-gray-500">Administrator</p>
                    </div>
                </div>
                <Link :href="`/admin/admins/${admin.id}/edit`" class="btn btn-primary btn-md">
                    Edit Admin
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Admin Info -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-lg font-semibold">Admin Information</h2>

                    <div class="flex items-center space-x-3">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                            <img
                                v-if="getProfileImage()"
                                :src="getProfileImage()"
                                :alt="admin.user?.full_name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="text-2xl font-bold text-gray-500">
                                {{ admin.user?.full_name?.charAt(0) || 'A' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium">{{ admin.user?.full_name }}</p>
                            <Badge :variant="getStatusVariant(admin.user?.status)">
                                {{ admin.user?.status || 'unknown' }}
                            </Badge>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t">
                        <div class="flex items-center space-x-3 text-sm">
                            <User class="w-4 h-4 text-gray-400" />
                            <span>{{ admin.user?.username }}</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <Mail class="w-4 h-4 text-gray-400" />
                            <span>{{ admin.user?.email }}</span>
                        </div>
                        <div v-if="admin.user?.phone" class="flex items-center space-x-3 text-sm">
                            <Phone class="w-4 h-4 text-gray-400" />
                            <span>{{ admin.user.phone }}</span>
                        </div>
                        <div v-if="admin.user?.address" class="flex items-center space-x-3 text-sm">
                            <MapPin class="w-4 h-4 text-gray-400" />
                            <span>{{ admin.user.address }}</span>
                        </div>
                        <div v-if="admin.department" class="flex items-center space-x-3 text-sm">
                            <Building class="w-4 h-4 text-gray-400" />
                            <span>{{ admin.department }}</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <Calendar class="w-4 h-4 text-gray-400" />
                            <span>Joined: {{ formatDate(admin.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="lg:col-span-2 card p-6">
                    <h2 class="text-lg font-semibold mb-4">Account Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Date of Birth</p>
                                <p class="font-medium">{{ formatDate(admin.user?.date_of_birth) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-medium capitalize">{{ admin.user?.gender || '-' }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Last Updated</p>
                                <p class="font-medium">{{ formatDate(admin.updated_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">User Type</p>
                                <p class="font-medium capitalize">{{ admin.user?.user_type || 'admin' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
