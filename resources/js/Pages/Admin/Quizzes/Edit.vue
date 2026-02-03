<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    item: Object,
})

const quiz = props.item

const form = useForm({
    quiz_title: quiz.quiz_title,
    instructions: quiz.instructions || '',
    time_limit_minutes: quiz.time_limit_minutes || '',
    passing_score: quiz.passing_score || 70,
    max_attempts: quiz.max_attempts || '',
    show_correct_answers: quiz.show_correct_answers ?? true,
    randomize_questions: quiz.randomize_questions || false,
})

const boolOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]

const submit = () => {
    form.put(`/admin/quizzes/${quiz.id}`)
}
</script>

<template>
    <AdminLayout>
        <Head title="Edit Quiz" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link href="/admin/quizzes" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Quiz</h1>
                    <p class="text-gray-500">Update quiz settings</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Basic Information</h2>

                    <FormInput
                        v-model="form.quiz_title"
                        label="Quiz Title"
                        :error="form.errors.quiz_title"
                        required
                    />

                    <FormInput
                        v-model="form.instructions"
                        label="Instructions"
                        type="textarea"
                        :error="form.errors.instructions"
                    />
                </div>

                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Quiz Settings</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <FormInput
                            v-model="form.passing_score"
                            label="Passing Score (%)"
                            type="number"
                            :error="form.errors.passing_score"
                            required
                        />

                        <FormInput
                            v-model="form.time_limit_minutes"
                            label="Time Limit (minutes)"
                            type="number"
                            placeholder="No limit"
                            :error="form.errors.time_limit_minutes"
                        />

                        <FormInput
                            v-model="form.max_attempts"
                            label="Max Attempts"
                            type="number"
                            placeholder="Unlimited"
                            :error="form.errors.max_attempts"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormSelect
                            v-model="form.randomize_questions"
                            label="Randomize Questions"
                            :options="boolOptions"
                            :error="form.errors.randomize_questions"
                        />

                        <FormSelect
                            v-model="form.show_correct_answers"
                            label="Show Correct Answers"
                            :options="boolOptions"
                            :error="form.errors.show_correct_answers"
                        />
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <Link href="/admin/quizzes" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Update Quiz
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
