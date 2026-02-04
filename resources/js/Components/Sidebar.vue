<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard, Users, GraduationCap, FolderOpen, BookOpen,
    FileText, ClipboardList, CreditCard, Award, Star, Bell,
    BarChart3, ChevronLeft, ChevronRight, HelpCircle
} from 'lucide-vue-next'

const props = defineProps({
    open: Boolean
})

const emit = defineEmits(['toggle'])

const page = usePage()

const menuItems = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    { name: 'Admins', href: '/admin/admins', icon: Users },
    { name: 'Students', href: '/admin/students', icon: GraduationCap },
    { name: 'Categories', href: '/admin/categories', icon: FolderOpen },
    { name: 'Courses', href: '/admin/courses', icon: BookOpen },
    { name: 'Quizzes', href: '/admin/quizzes', icon: HelpCircle },
    { name: 'Enrollments', href: '/admin/enrollments', icon: ClipboardList },
    { name: 'Payments', href: '/admin/payments', icon: CreditCard },
    { name: 'Certificates', href: '/admin/certificates', icon: Award },
    { name: 'Reviews', href: '/admin/reviews', icon: Star },
    { name: 'Notifications', href: '/admin/notifications', icon: Bell },
    { name: 'Reports', href: '/admin/reports', icon: BarChart3 },
    { name: 'Activity Log', href: '/admin/activity-log', icon: FileText },
]

const isActive = (href) => {
    const currentPath = page.url
    if (href === '/admin/dashboard') {
        return currentPath === '/admin/dashboard'
    }
    return currentPath.startsWith(href)
}
</script>

<template>
    <aside
        :class="[
            'fixed left-0 top-0 z-40 h-screen bg-white border-r border-gray-200 transition-all duration-300',
            open ? 'w-64' : 'w-20'
        ]"
    >
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
            <Link href="/admin/dashboard" class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                    <GraduationCap class="w-6 h-6 text-white" />
                </div>
                <span v-if="open" class="text-xl font-bold text-gray-900">EduPlex</span>
            </Link>
            <button
                @click="emit('toggle')"
                class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"
            >
                <ChevronLeft v-if="open" class="w-5 h-5" />
                <ChevronRight v-else class="w-5 h-5" />
            </button>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
            <Link
                v-for="item in menuItems"
                :key="item.name"
                :href="item.href"
                :class="[
                    'flex items-center px-3 py-2.5 rounded-lg transition-colors group',
                    isActive(item.href)
                        ? 'bg-primary-50 text-primary-700'
                        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                ]"
            >
                <component
                    :is="item.icon"
                    :class="[
                        'w-5 h-5 flex-shrink-0',
                        isActive(item.href) ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600'
                    ]"
                />
                <span v-if="open" class="ml-3 font-medium">{{ item.name }}</span>
            </Link>
        </nav>
    </aside>
</template>
