<template>
    <div :class="cardClasses">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider" :class="titleClasses">{{ title }}</h3>
            <div :class="iconContainerClasses">
                <component :is="iconComponent" class="w-6 h-6" :class="iconClasses" />
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-900 mb-3">{{ value }}</div>
        <div v-if="progressBar" class="w-full bg-slate-200 rounded-full h-3 mb-3 shadow-inner">
            <div class="h-3 rounded-full transition-all duration-500 shadow-sm" :class="progressClasses" :style="{ width: progress + '%' }"></div>
        </div>
        <p class="text-xs text-slate-600 font-medium">{{ subtitle }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    subtitle: {
        type: String,
        required: true
    },
    variant: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'purple', 'yellow'].includes(value)
    },
    progress: {
        type: Number,
        default: 0
    },
    progressBar: {
        type: Boolean,
        default: false
    },
    iconComponent: {
        type: [String, Object],
        required: true
    }
})

const variantClasses = {
    blue: {
        card: 'bg-gradient-to-br from-white to-blue-50/30 border-blue-200/40',
        title: 'text-blue-700',
        iconContainer: 'bg-blue-500/15',
        icon: 'text-blue-600',
        progress: 'bg-gradient-to-r from-blue-600 to-indigo-600'
    },
    green: {
        card: 'bg-gradient-to-br from-white to-green-50/30 border-green-200/40',
        title: 'text-green-700',
        iconContainer: 'bg-green-500/15',
        icon: 'text-green-600',
        progress: 'bg-gradient-to-r from-green-500 to-emerald-600'
    },
    purple: {
        card: 'bg-gradient-to-br from-white to-purple-50/30 border-purple-200/40',
        title: 'text-purple-700',
        iconContainer: 'bg-purple-500/15',
        icon: 'text-purple-600',
        progress: 'bg-gradient-to-r from-purple-500 to-purple-600'
    },
    yellow: {
        card: 'bg-gradient-to-br from-white to-yellow-50/30 border-yellow-200/40',
        title: 'text-yellow-700',
        iconContainer: 'bg-yellow-500/15',
        icon: 'text-yellow-600',
        progress: 'bg-gradient-to-r from-yellow-500 to-yellow-600'
    }
}

const cardClasses = computed(() => {
    return `rounded-2xl shadow-lg border p-6 hover:shadow-xl transition-all duration-300 ${variantClasses[props.variant].card}`
})

const titleClasses = computed(() => variantClasses[props.variant].title)
const iconContainerClasses = computed(() => `p-3 rounded-xl ${variantClasses[props.variant].iconContainer}`)
const iconClasses = computed(() => variantClasses[props.variant].icon)
const progressClasses = computed(() => variantClasses[props.variant].progress)
</script>