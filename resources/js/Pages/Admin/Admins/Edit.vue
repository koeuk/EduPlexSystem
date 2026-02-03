<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
})

const admin = props.item

const form = useForm({
    username: admin.user?.username || '',
    email: admin.user?.email || '',
    password: '',
    password_confirmation: '',
    full_name: admin.user?.full_name || '',
    phone: admin.user?.phone || '',
    date_of_birth: admin.user?.date_of_birth || '',
    gender: admin.user?.gender || '',
    address: admin.user?.address || '',
    status: admin.user?.status || 'active',
    department: admin.department || '',
    profile_picture: null,
})

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
]

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'suspended', label: 'Suspended' },
]

const submit = () => {
    form.post(`/admin/admins/${admin.id}`, {
        _method: 'PUT',
        forceFormData: true,
    })
}

const handleFileChange = (e) => {
    form.profile_picture = e.target.files[0]
}
</script>

<template>
    <AdminLayout>
        <Head title="Edit Admin" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/admins" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Admin</h1>
                    <p class="text-gray-500">Update administrator details</p>
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
                        placeholder="Leave blank to keep current"
                        :error="form.errors.password"
                    />
                    <FormInput
                        v-model="form.password_confirmation"
                        label="Confirm Password"
                        type="password"
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
                    <FormSelect
                        v-model="form.status"
                        label="Status"
                        :options="statusOptions"
                        :error="form.errors.status"
                        required
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
                        Update Admin
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
