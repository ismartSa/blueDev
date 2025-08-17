<template>
    <div class="bg-black flex items-center justify-center relative overflow-hidden" :class="containerClasses">
        <!-- Fullscreen Toggle -->
        <button 
            v-if="currentLecture && currentLecture.video_url"
            @click="toggleFullscreen"
            class="absolute top-4 left-4 z-10 bg-black/50 hover:bg-black/70 text-white p-2 rounded-lg transition-all duration-200"
        >
            <FullscreenIcon v-if="!isFullscreen" class="w-5 h-5" />
            <ExitFullscreenIcon v-else class="w-5 h-5" />
        </button>
        
        <!-- Auto-play notification -->
        <div v-if="showAutoPlayNotification" class="absolute top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg">
            <div class="flex items-center space-x-2">
                <span>Next video in {{ autoPlayCountdown }}s</span>
                <button @click="$emit('cancel-autoplay')" class="text-white hover:text-gray-200">
                    <CloseIcon class="w-4 h-4" />
                </button>
            </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="videoLoading" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-75 z-10">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                <p class="text-white text-sm">Loading video...</p>
            </div>
        </div>
        
        <div v-if="currentLecture && currentLecture.video_url" class="w-full h-full max-w-full">
            <video 
                ref="videoPlayer"
                class="w-full h-full max-w-full object-contain"
                controls
                :src="currentLecture.video_url"
                @loadstart="$emit('video-load-start')"
                @loadedmetadata="$emit('video-loaded')"
                @timeupdate="$emit('time-update', $event)"
                @ended="$emit('video-ended')"
                @error="$emit('video-error')">
                Your browser does not support the video tag.
            </video>
        </div>
        <div v-else class="text-white text-center p-8">
            <div class="max-w-md mx-auto">
                <PlayCircleIcon class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-6 text-gray-400" />
                <h3 class="text-xl font-semibold mb-2">Ready to Learn?</h3>
                <p class="text-gray-400 mb-4">Select a lecture from the course content to start your learning journey.</p>
                <button @click="$emit('start-first-lecture')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Start First Lecture
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

// Icons as inline SVG components
const FullscreenIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path></svg>`
}

const ExitFullscreenIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9.414l-2.293 2.293a1 1 0 01-1.414-1.414L7.414 3H8zM3 8a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 11H8a1 1 0 010 2H4a1 1 0 01-1-1V8zm8 8a1 1 0 01-1-1v-2a1 1 0 112 0v1.586l2.293-2.293a1 1 0 111.414 1.414L12.586 16H11zm5-8a1 1 0 01-2 0V8.414l-2.293 2.293a1 1 0 01-1.414-1.414L14.586 8H13a1 1 0 010-2h4a1 1 0 011 1v3z" clip-rule="evenodd"></path></svg>`
}

const CloseIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>`
}

const PlayCircleIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" /></svg>`
}

const props = defineProps({
    currentLecture: {
        type: Object,
        default: null
    },
    videoLoading: {
        type: Boolean,
        default: false
    },
    isFullscreen: {
        type: Boolean,
        default: false
    },
    showAutoPlayNotification: {
        type: Boolean,
        default: false
    },
    autoPlayCountdown: {
        type: Number,
        default: 5
    }
})

const emit = defineEmits([
    'toggle-fullscreen',
    'cancel-autoplay',
    'video-load-start',
    'video-loaded',
    'time-update',
    'video-ended',
    'video-error',
    'start-first-lecture'
])

const videoPlayer = ref(null)

const containerClasses = computed(() => {
    return props.isFullscreen ? 'fixed inset-0 z-50' : 'h-[65vh] lg:h-[70vh]'
})

const toggleFullscreen = () => {
    emit('toggle-fullscreen')
}

// Expose video player ref for parent component access
defineExpose({
    videoPlayer
})
</script>