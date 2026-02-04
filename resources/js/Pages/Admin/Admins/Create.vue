<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save, Upload, X } from 'lucide-vue-next'

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
    image: null,
})

const imagePreview = ref(null)

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
]

const submit = () => {
    form.post('/admin/admins')
}

const handleImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = () => {
    form.image = null
    imagePreview.value = null
    document.getElementById('image-upload').value = ''
}

const triggerImageInput = () => {
    document.getElementById('image-upload').click()
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
                </div>

                <!-- Profile Picture Upload -->
                <div>
                    <label class="label block mb-1.5">Profile Picture</label>
                    <div
                        @click="triggerImageInput"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors"
                    >
                        <template v-if="imagePreview">
                            <div class="relative inline-block">
                                <img
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="max-h-40 max-w-full object-contain rounded"
                                />
                                <button
                                    type="button"
                                    @click.stop="removeImage"
                                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Click to change</p>
                        </template>
                        <template v-else>
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <Upload class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-700">Click to upload image</p>
                            <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 2MB</p>
                        </template>
                    </div>
                    <input
                        id="image-upload"
                        type="file"
                        accept="image/*"
                        @change="handleImageChange"
                        class="hidden"
                    />
                    <p v-if="form.errors.image" class="mt-2 text-sm text-red-500">
                        {{ form.errors.image }}
                    </p>
                </div>

                <div>
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
