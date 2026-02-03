<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    full_name: '',
    phone: '',
    date_of_birth: '',
    gender: '',
    address: '',
    department: '',
    profile_picture: null,
})

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
]

const submit = () => {
    form.post('/admin/admins')
}

const handleFileChange = (e) => {
    form.profile_picture = e.target.files[0]
}
</script>

<template>
    <AdminLayout>
        <Head title="Create Admin" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/admins" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Admin</h1>
                    <p class="text-gray-500">Add a new administrator account</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormInput
                        v-model="form.full_name"
                        label="Full Name"
                        :error="form.errors.full_name"
                        required
                    />
                    <FormInput
                        v-model="form.username"
                        label="Username"
                        :error="form.errors.username"
                        required
                    />
                    <FormInput
                        v-model="form.email"
                        label="Email"
                        type="email"
                        :error="form.errors.email"
                        required
                    />
                    <FormInput
                        v-model="form.phone"
                        label="Phone"
                        :error="form.errors.phone"
                    />
                    <FormInput
                        v-model="form.password"
                        label="Password"
                        type="password"
                        :error="form.errors.password"
                        required
                    />
                    <FormInput
                        v-model="form.password_confirmation"
                        label="Confirm Password"
                        type="password"
                        required
                    />
                    <FormInput
                        v-model="form.date_of_birth"
                        label="Date of Birth"
                        type="date"
                        :error="form.errors.date_of_birth"
                    />
                    <FormSelect
                        v-model="form.gender"
                        label="Gender"
                        :options="genderOptions"
                        placeholder="Select gender"
                        :error="form.errors.gender"
                    />
                    <FormInput
                        v-model="form.department"
                        label="Department"
                        :error="form.errors.department"
                    />
                    <div>
                        <label class="label block mb-1.5">Profile Picture</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleFileChange"
                            class="input"
                        />
                        <p v-if="form.errors.profile_picture" class="mt-1 text-sm text-red-500">
                            {{ form.errors.profile_picture }}
                        </p>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <FormInput
                        v-model="form.address"
                        label="Address"
                        type="textarea"
                        :error="form.errors.address"
                    />
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <Link href="/admin/admins" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Create Admin
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
