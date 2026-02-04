<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import QuizEditLayout from '@/Layouts/QuizEditLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { Save, Settings, FileText } from 'lucide-vue-next'

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
    <QuizEditLayout :quiz="quiz">
        <Head title="Edit Quiz" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Quiz</h1>
                    <p class="text-gray-500">Update quiz settings</p>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn btn-primary btn-md"
                >
                    <Save class="w-4 h-4 mr-2" />
                    Save Changes
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <FileText class="w-5 h-5 text-gray-600" />
                        <h3 class="font-semibold text-gray-900">Basic Information</h3>
                    </div>

                    <div class="space-y-4">
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
                            :rows="4"
                            :error="form.errors.instructions"
                        />
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Settings class="w-5 h-5 text-gray-600" />
                        <h3 class="font-semibold text-gray-900">Quiz Settings</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>
            </form>
        </div>
    </QuizEditLayout>
</template>
