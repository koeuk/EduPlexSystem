<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Construction, ArrowLeft } from 'lucide-vue-next'

defineProps({
    title: String,
    backUrl: {
        type: String,
        default: '/admin/dashboard'
    }
})
</script>

<template>
    <AdminLayout>
        <Head :title="title" />

        <div class="flex flex-col items-center justify-center min-h-[60vh]">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-100 rounded-full mb-6">
                    <Construction class="w-10 h-10 text-yellow-600" />
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ title }}</h1>
                <p class="text-lg text-gray-500 mb-8">This page is coming soon. We're working on it!</p>
                <Link :href="backUrl" class="btn btn-primary btn-md inline-flex items-center">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Go Back
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
