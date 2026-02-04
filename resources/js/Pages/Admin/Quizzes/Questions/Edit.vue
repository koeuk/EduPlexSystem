<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import QuizEditLayout from '@/Layouts/QuizEditLayout.vue'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import { ArrowLeft, Save, Plus, Trash2, FileText, Settings, Upload, X } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    item: Object,
    questionTypeOptions: Array,
})

const question = props.item

const form = useForm({
    _method: 'PUT',
    question_text: question.question_text,
    question_type: question.question_type,
    points: question.points,
    explanation: question.explanation || '',
    image: null,
    options: question.options?.length > 0
        ? question.options.map(o => ({ id: o.id, option_text: o.option_text, is_correct: o.is_correct }))
        : [
            { option_text: '', is_correct: true },
            { option_text: '', is_correct: false },
        ],
})

const imagePreview = ref(null)

const getCurrentImage = () => {
    if (question.image_url) {
        if (question.image_url.startsWith('http')) {
            return question.image_url
        }
        return `/storage/${question.image_url}`
    }
    return null
}

const addOption = () => {
    form.options.push({ option_text: '', is_correct: false })
}

const removeOption = (index) => {
    if (form.options.length > 2) {
        form.options.splice(index, 1)
    }
}

const setCorrectOption = (index) => {
    if (form.question_type === 'multiple_choice' || form.question_type === 'true_false') {
        form.options.forEach((opt, i) => {
            opt.is_correct = i === index
        })
    }
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
    document.getElementById('question-image-upload').value = ''
}

const triggerImageInput = () => {
    document.getElementById('question-image-upload').click()
}

const submit = () => {
    // Ensure is_correct is properly set as boolean for each option
    form.options = form.options.map(opt => ({
        ...opt,
        is_correct: opt.is_correct === true
    }))
    form.post(`/admin/quizzes/${props.quiz.id}/questions/${question.id}`)
}
</script>

<template>
    <QuizEditLayout :quiz="quiz">
        <Head title="Edit Question" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/admin/quizzes/${quiz.id}/questions`" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Question</h1>
                        <p class="text-gray-500">Update question details</p>
                    </div>
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
                        <h3 class="font-semibold text-gray-900">Question Details</h3>
                    </div>

                    <div class="space-y-4">
                        <FormInput
                            v-model="form.question_text"
                            label="Question"
                            type="textarea"
                            :error="form.errors.question_text"
                            required
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                        <!-- Image Upload -->
                        <div>
                            <label class="label block mb-1.5">Question Image (optional)</label>
                            <div
                                @click="triggerImageInput"
                                class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors"
                            >
                                <template v-if="imagePreview || getCurrentImage()">
                                    <div class="relative inline-block">
                                        <img
                                            :src="imagePreview || getCurrentImage()"
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
                                id="question-image-upload"
                                type="file"
                                accept="image/*"
                                @change="handleImageChange"
                                class="hidden"
                            />
                            <p v-if="form.errors.image" class="mt-2 text-sm text-red-500">
                                {{ form.errors.image }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Options for Multiple Choice / True-False -->
                <div v-if="form.question_type === 'multiple_choice' || form.question_type === 'true_false'" class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <Settings class="w-5 h-5 text-gray-600" />
                            <h3 class="font-semibold text-gray-900">Answer Options</h3>
                        </div>
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
                    <p v-if="form.errors.options" class="text-sm text-red-500 mt-2">{{ form.errors.options }}</p>
                </div>
            </form>
        </div>
    </QuizEditLayout>
</template>
