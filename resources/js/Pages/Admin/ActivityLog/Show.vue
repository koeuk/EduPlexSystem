<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeft, Activity, User, Clock, FileText } from 'lucide-vue-next'

const props = defineProps({
    activity: Object,
})

const getTypeVariant = (type) => {
    const variants = {
        auth: 'info',
        course: 'success',
        enrollment: 'warning',
        payment: 'purple',
        user: 'gray',
    }
    return variants[type] || 'gray'
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString()
}

const getSubjectType = () => {
    if (!props.activity.subject_type) return '-'
    return props.activity.subject_type.split('\\').pop()
}

const formatProperties = (properties) => {
    if (!properties) return null
    try {
        if (typeof properties === 'string') {
            return JSON.parse(properties)
        }
        return properties
    } catch {
        return null
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Activity Details" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/activity-log" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-100 rounded-lg">
                        <Activity class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Activity Details</h1>
                        <p class="text-gray-500">{{ activity.description }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Activity Info -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-lg font-semibold">Activity Information</h2>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Type</p>
                            <Badge :variant="getTypeVariant(activity.log_name)">
                                {{ activity.log_name || 'default' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Event</p>
                            <p class="font-medium capitalize">{{ activity.event || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Description</p>
                            <p class="font-medium">{{ activity.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Subject & Causer -->
                <div class="card p-6 space-y-4">
                    <h2 class="text-lg font-semibold">Related Information</h2>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Performed By</p>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                    <User class="w-4 h-4 text-gray-500" />
                                </div>
                                <div>
                                    <p class="font-medium text-sm">{{ activity.causer?.full_name || 'System' }}</p>
                                    <p class="text-xs text-gray-500">{{ activity.causer?.email || '' }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Subject</p>
                            <div class="flex items-center gap-2">
                                <FileText class="w-4 h-4 text-gray-400" />
                                <span class="font-medium">{{ getSubjectType() }}</span>
                                <span v-if="activity.subject_id" class="text-gray-500">#{{ activity.subject_id }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Date & Time</p>
                            <div class="flex items-center gap-2">
                                <Clock class="w-4 h-4 text-gray-400" />
                                <span class="font-medium">{{ formatDateTime(activity.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold mb-4">Properties</h2>

                    <div v-if="formatProperties(activity.properties)" class="space-y-4">
                        <!-- Old Values -->
                        <div v-if="formatProperties(activity.properties)?.old">
                            <p class="text-sm text-gray-500 mb-2">Old Values</p>
                            <div class="bg-red-50 p-3 rounded-lg text-sm">
                                <pre class="whitespace-pre-wrap text-red-800">{{ JSON.stringify(formatProperties(activity.properties).old, null, 2) }}</pre>
                            </div>
                        </div>

                        <!-- New Values -->
                        <div v-if="formatProperties(activity.properties)?.attributes">
                            <p class="text-sm text-gray-500 mb-2">New Values</p>
                            <div class="bg-green-50 p-3 rounded-lg text-sm">
                                <pre class="whitespace-pre-wrap text-green-800">{{ JSON.stringify(formatProperties(activity.properties).attributes, null, 2) }}</pre>
                            </div>
                        </div>

                        <!-- Other Properties -->
                        <div v-if="!formatProperties(activity.properties)?.old && !formatProperties(activity.properties)?.attributes">
                            <div class="bg-gray-50 p-3 rounded-lg text-sm">
                                <pre class="whitespace-pre-wrap text-gray-800">{{ JSON.stringify(formatProperties(activity.properties), null, 2) }}</pre>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-4">No additional properties</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
