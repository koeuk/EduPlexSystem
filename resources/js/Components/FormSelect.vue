<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    options: Array,
    error: String,
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    valueKey: {
        type: String,
        default: 'value'
    },
    labelKey: {
        type: String,
        default: 'label'
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
        <select
            v-model="inputValue"
            :disabled="disabled"
            :class="['input', error ? 'border-red-500 focus:ring-red-500' : '']"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="option[valueKey] ?? option"
                :value="option[valueKey] ?? option"
            >
                {{ option[labelKey] ?? option }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>
