<template>
    <div class="text-center py-12">
        <div class="text-6xl mb-4">
            {{ icon }}
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ title }}
        </h3>
        <p class="text-sm text-gray-500 mb-6">
            {{ subtitle }}
        </p>
        <slot name="action">
            <Link 
                v-if="actionLink"
                :href="actionLink"
                :class="buttonClasses"
            >
                {{ actionText }}
            </Link>
        </slot>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    icon: {
        type: String,
        default: '📋'
    },
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    actionText: {
        type: String,
        default: 'Get Started'
    },
    actionLink: {
        type: String,
        default: null
    }
})

const buttonClasses = 'bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors inline-flex items-center'
</script>