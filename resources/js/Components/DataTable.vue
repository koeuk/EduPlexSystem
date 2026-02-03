<script setup>
import { Link } from '@inertiajs/vue3'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

defineProps({
    columns: Array,
    data: Array,
    pagination: Object
})

const getNestedValue = (obj, path) => {
    return path.split('.').reduce((acc, part) => acc && acc[part], obj)
}
</script>

<template>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th v-for="column in columns" :key="column.key" :class="column.class">
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in data" :key="row.id || index">
                        <td v-for="column in columns" :key="column.key" :class="column.cellClass">
                            <slot :name="column.key" :row="row" :value="getNestedValue(row, column.key)">
                                {{ getNestedValue(row, column.key) }}
                            </slot>
                        </td>
                    </tr>
                    <tr v-if="!data || data.length === 0">
                        <td :colspan="columns.length" class="text-center py-8 text-gray-500">
                            No data available
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
            </div>
            <div class="flex items-center space-x-2">
                <Link
                    v-if="pagination.prev_page_url"
                    :href="pagination.prev_page_url"
                    class="btn btn-secondary btn-sm"
                    preserve-scroll
                >
                    <ChevronLeft class="w-4 h-4" />
                </Link>
                <span class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <Link
                    v-if="pagination.next_page_url"
                    :href="pagination.next_page_url"
                    class="btn btn-secondary btn-sm"
                    preserve-scroll
                >
                    <ChevronRight class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </div>
</template>
