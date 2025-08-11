<template>
    <button 
        :disabled="disabled || loading"
        :class="buttonClasses"
        v-bind="$attrs"
    >
        <component 
            :is="icon" 
            :class="iconClasses" 
        />
        {{ text }}
    </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger'].includes(value)
    },
    icon: {
        type: [Object, Function],
        required: true
    },
    text: {
        type: String,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

// Base button styles
const baseClasses = 'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed'

// Variant-specific styles
const variantClasses = {
    primary: 'border border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
    secondary: 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-indigo-500',
    danger: 'border border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500'
}

const buttonClasses = computed(() => {
    return `${baseClasses} ${variantClasses[props.variant]}`
})

const iconClasses = computed(() => {
    return `w-4 h-4 mr-2 ${props.loading ? 'animate-spin' : ''}`
})
</script>