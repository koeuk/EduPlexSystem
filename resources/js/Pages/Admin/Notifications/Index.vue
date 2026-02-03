<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { Bell, Check, Trash2, Mail, AlertCircle, Info, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
    items: Object,
    typeOptions: Array,
})

const notifications = props.items

const getTypeIcon = (type) => {
    const icons = {
        info: Info,
        success: CheckCircle,
        warning: AlertCircle,
        error: AlertCircle,
    }
    return icons[type] || Bell
}

const getTypeClass = (type) => {
    const classes = {
        info: 'bg-blue-100 text-blue-600',
        success: 'bg-green-100 text-green-600',
        warning: 'bg-yellow-100 text-yellow-600',
        error: 'bg-red-100 text-red-600',
    }
    return classes[type] || 'bg-gray-100 text-gray-600'
}

const markAsRead = (id) => {
    router.post(`/admin/notifications/${id}/mark-as-read`)
}

const deleteNotification = (id) => {
    router.delete(`/admin/notifications/${id}`)
}

const formatDateTime = (date) => {
    if (!date) return '-'
    const d = new Date(date)
    const now = new Date()
    const diff = now - d

    // Less than 1 hour
    if (diff < 3600000) {
        const mins = Math.floor(diff / 60000)
        return `${mins} min ago`
    }
    // Less than 24 hours
    if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000)
        return `${hours} hour${hours > 1 ? 's' : ''} ago`
    }
    // Less than 7 days
    if (diff < 604800000) {
        const days = Math.floor(diff / 86400000)
        return `${days} day${days > 1 ? 's' : ''} ago`
    }
    return d.toLocaleDateString()
}
</script>

<template>
    <AdminLayout>
        <Head title="Notifications" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-100 rounded-lg">
                        <Bell class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                        <p class="text-gray-500">
                            Manage notifications
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card divide-y">
                <div v-if="notifications?.data?.length > 0">
                    <div v-for="notification in notifications?.data" :key="notification.id"
                        class="p-4 hover:bg-gray-50 transition-colors"
                        :class="{ 'bg-blue-50/50': !notification.read_at }">
                        <div class="flex items-start gap-4">
                            <div :class="['p-2 rounded-lg', getTypeClass(notification.data?.type)]">
                                <component :is="getTypeIcon(notification.data?.type)" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium" :class="{ 'text-gray-900': !notification.read_at, 'text-gray-600': notification.read_at }">
                                            {{ notification.data?.title || 'Notification' }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ notification.data?.message || notification.data?.body }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-2">
                                            {{ formatDateTime(notification.created_at) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button v-if="!notification.read_at" @click="markAsRead(notification.id)"
                                            class="p-1 text-gray-400 hover:text-primary-600" title="Mark as read">
                                            <Check class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteNotification(notification.id)"
                                            class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center">
                    <Mail class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500">No notifications yet</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications?.links?.length > 3" class="flex justify-center gap-2">
                <template v-for="link in notifications?.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.get(link.url)"
                        class="px-3 py-1 text-sm rounded"
                        :class="link.active ? 'bg-primary-500 text-white' : 'bg-gray-100 hover:bg-gray-200'"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
