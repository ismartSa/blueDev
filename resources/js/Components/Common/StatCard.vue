<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div :class="valueClasses" class="text-3xl font-bold mb-1">
            {{ value }}
        </div>
        <div class="text-gray-600 text-sm">
            {{ label }}
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    value: {
        type: [Number, String],
        required: true
    },
    label: {
        type: String,
        required: true
    },
    color: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'yellow', 'red', 'gray', 'purple'].includes(value)
    }
})

const valueClasses = computed(() => {
    const colorMap = {
        blue: 'text-blue-600',
        green: 'text-green-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        gray: 'text-gray-600',
        purple: 'text-purple-600'
    }
    return colorMap[props.color] || colorMap.blue
})
</script>