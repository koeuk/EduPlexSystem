<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeft, Info, HelpCircle, ClipboardList } from 'lucide-vue-next'

const props = defineProps({
    quiz: {
        type: Object,
        required: true
    }
})

const page = usePage()

const menuItems = computed(() => [
    {
        name: 'Information',
        href: `/admin/quizzes/${props.quiz.id}/edit`,
        icon: Info,
        description: 'Quiz settings'
    },
    {
        name: 'Questions',
        href: `/admin/quizzes/${props.quiz.id}/questions`,
        icon: HelpCircle,
        description: 'Manage questions'
    },
    {
        name: 'Attempts',
        href: `/admin/quizzes/${props.quiz.id}/attempts`,
        icon: ClipboardList,
        description: 'Student attempts'
    },
])

const isActive = (href) => {
    const currentPath = page.url.split('?')[0]
    // Check if current path starts with the menu href (for nested routes)
    if (href.includes('/questions')) {
        return currentPath.includes('/questions')
    }
    if (href.includes('/attempts')) {
        return currentPath.includes('/attempts')
    }
    return currentPath === href
}
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar -->
            <div class="lg:w-64 flex-shrink-0">
                <div class="card p-4 sticky top-4">
                    <!-- Back to Quizzes -->
                    <Link
                        href="/admin/quizzes"
                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4 pb-4 border-b border-gray-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Back to Quizzes
                    </Link>

                    <!-- Quiz Name -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Editing Quiz</p>
                        <h2 class="font-semibold text-gray-900 line-clamp-2">{{ quiz.quiz_title }}</h2>
                        <p v-if="quiz.questions_count !== undefined" class="text-xs text-gray-500 mt-1">
                            {{ quiz.questions_count }} question{{ quiz.questions_count !== 1 ? 's' : '' }}
                        </p>
                    </div>

                    <!-- Menu Items -->
                    <nav class="space-y-1">
                        <Link
                            v-for="item in menuItems"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group',
                                isActive(item.href)
                                    ? 'bg-primary-50 text-primary-700'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                            ]"
                        >
                            <component
                                :is="item.icon"
                                :class="[
                                    'w-5 h-5 flex-shrink-0',
                                    isActive(item.href)
                                        ? 'text-primary-600'
                                        : 'text-gray-400 group-hover:text-gray-600'
                                ]"
                            />
                            <div>
                                <span class="font-medium block">{{ item.name }}</span>
                                <span class="text-xs text-gray-500">{{ item.description }}</span>
                            </div>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <slot />
            </div>
        </div>
    </AdminLayout>
</template>
