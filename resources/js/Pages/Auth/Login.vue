<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import { GraduationCap, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const showPassword = ref(false)

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 rounded-2xl mb-4">
                    <GraduationCap class="w-8 h-8 text-primary-600" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">EduPlex LMS</h1>
                <p class="text-gray-500 mt-1">Admin Dashboard</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email -->
                <div>
                    <label class="label block mb-1.5">Email Address</label>
                    <div class="relative">
                        <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="form.email"
                            type="email"
                            class="input pl-10"
                            placeholder="admin@example.com"
                            required
                        />
                    </div>
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                    <label class="label block mb-1.5">Password</label>
                    <div class="relative">
                        <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="input pl-10 pr-10"
                            placeholder="Enter your password"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <EyeOff v-if="showPassword" class="w-5 h-5" />
                            <Eye v-else class="w-5 h-5" />
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                </div>

                <!-- Remember me -->
                <div class="flex items-center">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        id="remember"
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                    />
                    <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-lg w-full"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign in</span>
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
