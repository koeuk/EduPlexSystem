<script setup>
import FormInput from '@/Components/FormInput.vue'
import { Layers } from 'lucide-vue-next'

const props = defineProps({
    modelValue: Object,
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="space-y-6">
        <!-- Module Information Section -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <Layers class="w-5 h-5 text-gray-600" />
                <h3 class="font-semibold text-gray-900">Module Information</h3>
            </div>

            <div class="space-y-4">
                <FormInput
                    :modelValue="modelValue.module_title"
                    @update:modelValue="$emit('update:modelValue', { ...modelValue, module_title: $event })"
                    label="Module Title"
                    placeholder="Enter module title"
                    :error="modelValue.errors?.module_title"
                    required
                />

                <FormInput
                    :modelValue="modelValue.description"
                    @update:modelValue="$emit('update:modelValue', { ...modelValue, description: $event })"
                    label="Description"
                    type="textarea"
                    placeholder="Describe what this module covers..."
                    :error="modelValue.errors?.description"
                    :rows="4"
                />
            </div>
        </div>
    </div>
</template>
