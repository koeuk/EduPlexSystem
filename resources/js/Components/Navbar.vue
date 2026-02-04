<script setup>
import { ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { Menu, Bell, User, LogOut, ChevronDown, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    sidebarOpen: Boolean
})

const emit = defineEmits(['toggleSidebar'])

const page = usePage()
const user = page.props.auth.user
const dropdownOpen = ref(false)

const getImageUrl = () => {
    const url = user?.image_url
    if (!url) return null
    if (url.startsWith('http')) return url
    return `/storage/${url}`
}

const logout = () => {
    router.post('/logout')
}

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
    dropdownOpen.value = false
}
</script>

<template>
    <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
        <div class="flex items-center justify-between h-16 px-4 lg:px-6">
            <!-- Left side -->
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button
                    @click="emit('toggleSidebar')"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 lg:hidden"
                >
                    <Menu class="w-6 h-6" />
                </button>
                <!-- Desktop sidebar toggle -->
                <button
                    @click="emit('toggleSidebar')"
                    class="hidden lg:flex p-2 rounded-lg hover:bg-gray-100 text-gray-500"
                >
                    <ChevronLeft v-if="sidebarOpen" class="w-5 h-5" />
                    <ChevronRight v-else class="w-5 h-5" />
                </button>
                <h1 class="ml-2 text-lg font-semibold text-gray-900 hidden sm:block">
                    Admin Dashboard
                </h1>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <Link
                    href="/admin/notifications"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 relative"
                >
                    <Bell class="w-5 h-5" />
                </Link>

                <!-- User dropdown -->
                <div class="relative">
                    <button
                        @click="toggleDropdown"
                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100"
                    >
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                            <img
                                v-if="getImageUrl()"
                                :src="getImageUrl()"
                                :alt="user?.full_name"
                                class="w-full h-full object-cover"
                            />
                            <User v-else class="w-4 h-4 text-primary-600" />
                        </div>
                        <span class="hidden md:block font-medium text-gray-700">{{ user?.full_name }}</span>
                        <ChevronDown class="w-4 h-4 text-gray-400 hidden md:block" />
                    </button>

                    <!-- Dropdown menu -->
                    <div
                        v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
                        @click="closeDropdown"
                    >
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center space-x-3">
                            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden flex-shrink-0">
                                <img
                                    v-if="getImageUrl()"
                                    :src="getImageUrl()"
                                    :alt="user?.full_name"
                                    class="w-full h-full object-cover"
                                />
                                <User v-else class="w-5 h-5 text-primary-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ user?.full_name }}</p>
                                <p class="text-xs text-gray-500">{{ user?.email }}</p>
                            </div>
                        </div>
                        <button
                            @click="logout"
                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                        >
                            <LogOut class="w-4 h-4 mr-2" />
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
