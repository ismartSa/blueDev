<template>
    <div 
        class="group p-3 sm:p-4 cursor-pointer transition-all duration-200 hover:bg-slate-50 hover:shadow-sm"
        :class="itemClasses"
        @click="$emit('select', lecture)"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <div v-if="isCompleted" class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center shadow-sm">
                            <CheckIcon class="w-3 h-3 text-white" />
                        </div>
                        <div v-else-if="isCurrent" class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center shadow-sm">
                            <PlayIcon class="w-3 h-3 text-white" />
                        </div>
                        <div v-else class="w-5 h-5 border-2 border-slate-300 rounded-full group-hover:border-blue-400 transition-colors"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium line-clamp-2 leading-relaxed" :class="titleClasses">
                            {{ lecture.title }}
                        </h4>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                <ClockIcon class="w-3 h-3 text-indigo-600" />
                                {{ formatDuration(lecture.duration) }}
                            </p>
                            <span class="text-xs text-slate-400">•</span>
                            <p class="text-xs text-slate-500">Lesson {{ lecture.order }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-1 flex-shrink-0">
                <span v-if="lecture.type === 'video'" class="text-slate-400 group-hover:text-blue-500 transition-colors">
                    <PlayIcon class="w-4 h-4" />
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

// Icons as inline SVG components
const CheckIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`
}

const PlayIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" /></svg>`
}

const ClockIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>`
}

const props = defineProps({
    lecture: {
        type: Object,
        required: true
    },
    isCurrent: {
        type: Boolean,
        default: false
    },
    isCompleted: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['select'])

const itemClasses = computed(() => {
    if (props.isCurrent) {
        return 'bg-gradient-to-r from-blue-50 to-indigo-50/60 border-r-4 border-r-blue-500 shadow-sm'
    }
    if (props.isCompleted) {
        return 'bg-green-50/70'
    }
    return ''
})

const titleClasses = computed(() => {
    if (props.isCurrent) {
        return 'text-blue-900'
    }
    if (props.isCompleted) {
        return 'text-green-800'
    }
    return 'text-slate-900'
})

const formatDuration = (minutes) => {
    if (!minutes) return '0m'
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>