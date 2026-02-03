<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    item: Object,
    questionTypeOptions: Array,
})

const question = props.item

const form = useForm({
    question_text: question.question_text,
    question_type: question.question_type,
    points: question.points,
    explanation: question.explanation || '',
    image_url: question.image_url || '',
    options: question.options?.length > 0
        ? question.options.map(o => ({ id: o.id, option_text: o.option_text, is_correct: o.is_correct }))
        : [
            { option_text: '', is_correct: true },
            { option_text: '', is_correct: false },
        ],
})

const addOption = () => {
    form.options.push({ option_text: '', is_correct: false })
}

const removeOption = (index) => {
    if (form.options.length > 2) {
        form.options.splice(index, 1)
    }
}

const setCorrectOption = (index) => {
    if (form.question_type === 'multiple_choice') {
        form.options.forEach((opt, i) => {
            opt.is_correct = i === index
        })
    }
}

const submit = () => {
    form.put(`/admin/quizzes/${props.quiz.id}/questions/${question.id}`)
}
</script>

<template>
    <AdminLayout>
        <Head title="Edit Question" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Link :href="`/admin/quizzes/${quiz.id}/questions`" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Question</h1>
                    <p class="text-gray-500">{{ quiz.quiz_title }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="card p-6 space-y-6">
                    <h2 class="text-lg font-semibold">Question Details</h2>

                    <FormInput
                        v-model="form.question_text"
                        label="Question"
                        type="textarea"
                        :error="form.errors.question_text"
                        required
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormSelect
                            v-model="form.question_type"
                            label="Question Type"
                            :options="questionTypeOptions"
                            :error="form.errors.question_type"
                            required
                        />

                        <FormInput
                            v-model="form.points"
                            label="Points"
                            type="number"
                            :error="form.errors.points"
                            required
                        />
                    </div>

                    <FormInput
                        v-model="form.explanation"
                        label="Explanation (shown after answering)"
                        type="textarea"
                        :error="form.errors.explanation"
                    />

                    <FormInput
                        v-model="form.image_url"
                        label="Image URL (optional)"
                        placeholder="https://..."
                        :error="form.errors.image_url"
                    />
                </div>

                <!-- Options for Multiple Choice / True-False -->
                <div v-if="form.question_type === 'multiple_choice' || form.question_type === 'true_false'" class="card p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Answer Options</h2>
                        <button v-if="form.question_type === 'multiple_choice'" type="button" @click="addOption" class="btn btn-secondary btn-sm">
                            <Plus class="w-4 h-4 mr-1" />
                            Add Option
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(option, index) in form.options" :key="index" class="flex items-start gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        :name="'correct_option'"
                                        :checked="option.is_correct"
                                        @change="setCorrectOption(index)"
                                        class="w-4 h-4 text-primary-600"
                                    />
                                    <input
                                        v-model="option.option_text"
                                        type="text"
                                        class="input flex-1"
                                        :placeholder="`Option ${index + 1}`"
                                    />
                                    <button
                                        v-if="form.options.length > 2"
                                        type="button"
                                        @click="removeOption(index)"
                                        class="p-2 text-gray-400 hover:text-red-600"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="option.is_correct" class="text-sm text-green-600 ml-7 mt-1">Correct answer</p>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.options" class="text-sm text-red-500">{{ form.errors.options }}</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <Link :href="`/admin/quizzes/${quiz.id}/questions`" class="btn btn-secondary btn-md">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-md">
                        <Save class="w-4 h-4 mr-2" />
                        Update Question
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
