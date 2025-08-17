<template>
    <div v-if="currentLecture && currentLecture.video_url" class="bg-white/95 backdrop-blur-sm p-4 border-b border-slate-200/60 shadow-sm" :class="{ 'hidden': isFullscreen }">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="$emit('previous')" :disabled="!hasPrevious" class="btn-control">
                    <PreviousIcon class="w-5 h-5" />
                </button>
                <button @click="$emit('next')" :disabled="!hasNext" class="btn-control">
                    <NextIcon class="w-5 h-5" />
                </button>
                <button @click="$emit('toggle-autoplay')" :class="autoPlayEnabled ? 'text-indigo-600 bg-indigo-50' : 'text-slate-400'" class="btn-control">
                    <AutoPlayIcon class="w-5 h-5" />
                </button>
            </div>
            <div class="text-sm text-slate-700 font-medium bg-slate-100 px-3 py-1 rounded-full">
                {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
            </div>
        </div>
    </div>
</template>

<script setup>
// Icons as inline SVG components
const PreviousIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"></path></svg>`
}

const NextIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798L4.555 5.168z"></path></svg>`
}

const AutoPlayIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path></svg>`
}

const props = defineProps({
    currentLecture: {
        type: Object,
        default: null
    },
    isFullscreen: {
        type: Boolean,
        default: false
    },
    hasPrevious: {
        type: Boolean,
        default: false
    },
    hasNext: {
        type: Boolean,
        default: false
    },
    autoPlayEnabled: {
        type: Boolean,
        default: true
    },
    currentTime: {
        type: Number,
        default: 0
    },
    duration: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['previous', 'next', 'toggle-autoplay'])

const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return '0:00'
    const mins = Math.floor(seconds / 60)
    const secs = Math.floor(seconds % 60)
    return `${mins}:${secs.toString().padStart(2, '0')}`
}
</script>

<style scoped>
.btn-control {
    @apply p-2 rounded-lg transition-colors text-gray-600 hover:text-gray-900 hover:bg-gray-100 disabled:text-gray-300 disabled:cursor-not-allowed disabled:hover:bg-transparent;
}
</style>