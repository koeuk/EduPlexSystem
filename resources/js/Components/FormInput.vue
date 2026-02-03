<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: 'text'
    },
    error: String,
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    rows: {
        type: Number,
        default: 3
    }
})

const emit = defineEmits(['update:modelValue'])

const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})
</script>

<template>
    <div>
        <label v-if="label" class="label block mb-1.5">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <textarea
            v-if="type === 'textarea'"
            v-model="inputValue"
            :rows="rows"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="['input', error ? 'border-red-500 focus:ring-red-500' : '']"
        />
        <input
            v-else
            v-model="inputValue"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="['input', error ? 'border-red-500 focus:ring-red-500' : '']"
        />
        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>
