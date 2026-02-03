<script setup>
import { computed } from 'vue'
import { Checkbox } from '@/Components/ui/checkbox'
import { Label } from '@/Components/ui/label'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    permissions: {
        type: Array,
        required: true
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])

// Group permissions by category
const groupedPermissions = computed(() => {
    const groups = {}
    props.permissions.forEach(permission => {
        const [category] = permission.name.split('.')
        if (!groups[category]) {
            groups[category] = []
        }
        groups[category].push(permission)
    })
    return groups
})

const categoryLabels = {
    students: 'Students',
    courses: 'Courses',
    modules: 'Modules',
    lessons: 'Lessons',
    quizzes: 'Quizzes',
    enrollments: 'Enrollments',
    payments: 'Payments',
    certificates: 'Certificates',
    categories: 'Categories',
    reviews: 'Reviews',
    reports: 'Reports',
    admins: 'Admins',
    'activity-log': 'Activity Log'
}

function isChecked(permissionId) {
    return props.modelValue.includes(permissionId)
}

function togglePermission(permissionId) {
    if (props.disabled) return

    const newValue = [...props.modelValue]
    const index = newValue.indexOf(permissionId)

    if (index === -1) {
        newValue.push(permissionId)
    } else {
        newValue.splice(index, 1)
    }

    emit('update:modelValue', newValue)
}

function toggleCategory(category) {
    if (props.disabled) return

    const categoryPermissions = groupedPermissions.value[category].map(p => p.id)
    const allSelected = categoryPermissions.every(id => props.modelValue.includes(id))

    let newValue = [...props.modelValue]

    if (allSelected) {
        // Remove all category permissions
        newValue = newValue.filter(id => !categoryPermissions.includes(id))
    } else {
        // Add all category permissions
        categoryPermissions.forEach(id => {
            if (!newValue.includes(id)) {
                newValue.push(id)
            }
        })
    }

    emit('update:modelValue', newValue)
}

function isCategoryAllSelected(category) {
    const categoryPermissions = groupedPermissions.value[category].map(p => p.id)
    return categoryPermissions.every(id => props.modelValue.includes(id))
}

function isCategorySomeSelected(category) {
    const categoryPermissions = groupedPermissions.value[category].map(p => p.id)
    return categoryPermissions.some(id => props.modelValue.includes(id)) && !isCategoryAllSelected(category)
}

function formatPermissionName(name) {
    const [, action] = name.split('.')
    return action.charAt(0).toUpperCase() + action.slice(1).replace(/-/g, ' ')
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="(permissions, category) in groupedPermissions"
            :key="category"
            class="border rounded-lg p-4"
        >
            <div class="flex items-center gap-2 mb-3">
                <Checkbox
                    :id="`category-${category}`"
                    :checked="isCategoryAllSelected(category)"
                    :indeterminate="isCategorySomeSelected(category)"
                    :disabled="disabled"
                    @update:checked="toggleCategory(category)"
                />
                <Label
                    :for="`category-${category}`"
                    class="text-sm font-semibold cursor-pointer"
                >
                    {{ categoryLabels[category] || category }}
                </Label>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 pl-6">
                <div
                    v-for="permission in permissions"
                    :key="permission.id"
                    class="flex items-center gap-2"
                >
                    <Checkbox
                        :id="`permission-${permission.id}`"
                        :checked="isChecked(permission.id)"
                        :disabled="disabled"
                        @update:checked="togglePermission(permission.id)"
                    />
                    <Label
                        :for="`permission-${permission.id}`"
                        class="text-sm cursor-pointer"
                    >
                        {{ formatPermissionName(permission.name) }}
                    </Label>
                </div>
            </div>
        </div>
    </div>
</template>
