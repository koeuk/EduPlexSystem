<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'
import FlashMessage from '@/Components/FlashMessage.vue'

const sidebarOpen = ref(true)
const page = usePage()

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Sidebar :open="sidebarOpen" @toggle="toggleSidebar" />

        <div :class="['transition-all duration-300', sidebarOpen ? 'lg:ml-64' : 'lg:ml-20']">
            <Navbar :sidebarOpen="sidebarOpen" @toggleSidebar="toggleSidebar" />

            <main class="p-4 lg:p-6">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>
