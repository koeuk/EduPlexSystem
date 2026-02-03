<script setup>
import { TrendingUp, TrendingDown } from 'lucide-vue-next'

defineProps({
    title: String,
    value: [String, Number],
    icon: Object,
    trend: {
        type: Number,
        default: null
    },
    trendLabel: String,
    color: {
        type: String,
        default: 'primary'
    }
})

const colorClasses = {
    primary: 'bg-primary-100 text-primary-600',
    green: 'bg-green-100 text-green-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    red: 'bg-red-100 text-red-600',
    purple: 'bg-purple-100 text-purple-600',
    blue: 'bg-blue-100 text-blue-600',
}
</script>

<template>
    <div class="card p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ title }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ value }}</p>
                <div v-if="trend !== null" class="flex items-center mt-2">
                    <TrendingUp v-if="trend >= 0" class="w-4 h-4 text-green-500 mr-1" />
                    <TrendingDown v-else class="w-4 h-4 text-red-500 mr-1" />
                    <span :class="trend >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                        {{ Math.abs(trend) }}%
                    </span>
                    <span v-if="trendLabel" class="text-sm text-gray-500 ml-1">{{ trendLabel }}</span>
                </div>
            </div>
            <div :class="['p-3 rounded-lg', colorClasses[color]]">
                <component :is="icon" class="w-6 h-6" />
            </div>
        </div>
    </div>
</template>
