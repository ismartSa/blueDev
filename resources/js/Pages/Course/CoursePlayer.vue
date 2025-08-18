<template>
    <AuthenticatedLayout>
        <Head :title="`${course.title} - ${currentLecture.title}`" />
        
        <!-- Top Progress Bar -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 shadow-sm">
            <div class="h-1 bg-gray-200 dark:bg-gray-700">
                <div 
                    class="h-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-300 ease-out"
                    :style="{ width: overallProgress + '%' }"
                ></div>
            </div>
            <div class="px-4 py-2 flex items-center justify-between text-sm">
                <div class="flex items-center space-x-4">
                    <span class="font-medium text-gray-900 dark:text-white">{{ currentLecture.title }}</span>
                    <span class="text-gray-500 dark:text-gray-400">{{ currentLectureIndex + 1 }} / {{ allLectures.length }}</span>
                </div>
                <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-300">
                    <span>{{ formatTime(currentTime) }} / {{ formatTime(duration) }}</span>
                    <div class="w-2 h-2 rounded-full" :class="videoPlayer?.paused === false ? 'bg-green-500' : 'bg-gray-400'"></div>
                </div>
            </div>
        </div>
        
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-16">
            <div class="flex h-[calc(100vh-4rem)]">
                <!-- Video Player Section -->
                <div class="flex-1 flex flex-col">
                    <!-- Video Container -->
                    <div class="bg-black relative" :class="isFullscreen ? 'h-full' : 'aspect-video'">
                        <!-- Fullscreen Toggle -->
                        <button 
                            @click="toggleFullscreen"
                            class="absolute top-4 left-4 z-10 bg-black/50 hover:bg-black/70 text-white p-2 rounded-lg transition-all duration-200"
                        >
                            <svg v-if="!isFullscreen" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9.414l-2.293 2.293a1 1 0 01-1.414-1.414L7.414 3H8zM3 8a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 11H8a1 1 0 010 2H4a1 1 0 01-1-1V8zm8 8a1 1 0 01-1-1v-2a1 1 0 112 0v1.586l2.293-2.293a1 1 0 111.414 1.414L12.586 16H11zm5-8a1 1 0 01-2 0V8.414l-2.293 2.293a1 1 0 01-1.414-1.414L14.586 8H13a1 1 0 010-2h4a1 1 0 011 1v3z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <video 
                            ref="videoPlayer"
                            :src="currentLecture.video_url"
                            class="w-full h-full"
                            controls
                            @ended="handleVideoEnd"
                            @timeupdate="updateProgress"
                            @loadedmetadata="onVideoLoaded"
                        >
                            Your browser does not support the video tag.
                        </video>
                        
                        <!-- Auto-play notification -->
                        <div v-if="showAutoPlayNotification" class="absolute top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg">
                            <div class="flex items-center space-x-2">
                                <span>Next video in {{ autoPlayCountdown }}s</span>
                                <button @click="cancelAutoPlay" class="text-white hover:text-gray-200">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video Controls -->
                    <div class="bg-white dark:bg-gray-800 p-4 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <button @click="playPrevious" :disabled="!hasPrevious" class="btn-control">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"></path>
                                    </svg>
                                </button>
                                <button @click="playNext" :disabled="!hasNext" class="btn-control">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798L4.555 5.168z"></path>
                                    </svg>
                                </button>
                                <button @click="toggleAutoPlay" :class="autoPlayEnabled ? 'text-blue-600' : 'text-gray-400'" class="btn-control">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lecture Info -->
                    <div class="bg-white dark:bg-gray-800 p-6 flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ currentLecture.title }}</h1>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">{{ currentLecture.description }}</p>
                        
                        <!-- Video Progress Bar -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <span>Video Progress</span>
                                <span>{{ Math.round(watchProgress) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: watchProgress + '%' }"></div>
                            </div>
                        </div>
                        
                        <!-- Course Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300 mb-1">
                                <span>Course Progress</span>
                                <span>{{ Math.round(overallProgress) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-green-500 to-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: overallProgress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Playlist Sidebar -->
                <div class="w-80 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 overflow-y-auto" :class="{ 'hidden': isFullscreen }">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Course Playlist</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ course.title }}</p>
                    </div>
                    
                    <div class="p-4">
                        <div v-for="section in sortedSections" :key="section.id" class="mb-6">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">{{ section.title }}</h3>
                            <div class="space-y-2">
                                <div 
                                    v-for="lecture in getLecturesBySection(section.id)" 
                                    :key="lecture.id"
                                    @click="selectLecture(lecture)"
                                    :class="playlistItemClass(lecture)"
                                    class="playlist-item"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div v-if="lecture.id === currentLecture.id" class="w-4 h-4 bg-blue-600 rounded-full flex items-center justify-center">
                                                <div class="w-2 h-2 bg-white rounded-full"></div>
                                            </div>
                                            <div v-else class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded-full"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate" :class="lecture.id === currentLecture.id ? 'text-blue-600' : 'text-gray-900 dark:text-white'">{{ lecture.title }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ lecture.duration }} min</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    course: Object,
    sections: Array,
    lectures: Array,
    currentLectureId: Number
})

// Reactive state
const videoPlayer = ref(null)
const currentTime = ref(0)
const duration = ref(0)
const autoPlayEnabled = ref(true)
const showAutoPlayNotification = ref(false)
const autoPlayCountdown = ref(5)
const autoPlayTimer = ref(null)
const isFullscreen = ref(false)

// Current lecture management
const currentLecture = ref(props.lectures.find(l => l.id === props.currentLectureId) || props.lectures[0])

// Computed properties
const sortedSections = computed(() => 
    [...props.sections].sort((a, b) => a.order - b.order)
)

const allLectures = computed(() => {
    const lectures = []
    sortedSections.value.forEach(section => {
        const sectionLectures = getLecturesBySection(section.id)
        lectures.push(...sectionLectures.sort((a, b) => a.order - b.order))
    })
    return lectures
})

const currentLectureIndex = computed(() => 
    allLectures.value.findIndex(l => l.id === currentLecture.value.id)
)

const hasPrevious = computed(() => currentLectureIndex.value > 0)
const hasNext = computed(() => currentLectureIndex.value < allLectures.value.length - 1)

const watchProgress = computed(() => {
    if (duration.value === 0) return 0
    return (currentTime.value / duration.value) * 100
})

const overallProgress = computed(() => {
    const totalLectures = allLectures.value.length
    if (totalLectures === 0) return 0
    
    const completedLectures = currentLectureIndex.value
    const currentVideoProgress = watchProgress.value / 100
    
    return ((completedLectures + currentVideoProgress) / totalLectures) * 100
})

// Helper functions
const getLecturesBySection = (sectionId) => 
    props.lectures.filter(lecture => lecture.section_id === sectionId)

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60)
    const secs = Math.floor(seconds % 60)
    return `${mins}:${secs.toString().padStart(2, '0')}`
}

const playlistItemClass = (lecture) => ({
    'p-3 rounded-lg cursor-pointer transition-colors duration-200': true,
    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800': lecture.id === currentLecture.value.id,
    'hover:bg-gray-50 dark:hover:bg-gray-700': lecture.id !== currentLecture.value.id
})

// Video event handlers
const onVideoLoaded = () => {
    if (videoPlayer.value) {
        duration.value = videoPlayer.value.duration
    }
}

const updateProgress = () => {
    if (videoPlayer.value) {
        currentTime.value = videoPlayer.value.currentTime
    }
}

const handleVideoEnd = () => {
    if (autoPlayEnabled.value && hasNext.value) {
        startAutoPlayCountdown()
    }
}

// Navigation functions
const selectLecture = (lecture) => {
    currentLecture.value = lecture
    updateURL()
}

const playNext = () => {
    if (hasNext.value) {
        currentLecture.value = allLectures.value[currentLectureIndex.value + 1]
        updateURL()
    }
}

const playPrevious = () => {
    if (hasPrevious.value) {
        currentLecture.value = allLectures.value[currentLectureIndex.value - 1]
        updateURL()
    }
}

// Auto-play functionality
const toggleAutoPlay = () => {
    autoPlayEnabled.value = !autoPlayEnabled.value
}

const startAutoPlayCountdown = () => {
    showAutoPlayNotification.value = true
    autoPlayCountdown.value = 5
    
    autoPlayTimer.value = setInterval(() => {
        autoPlayCountdown.value--
        if (autoPlayCountdown.value <= 0) {
            playNext()
            cancelAutoPlay()
        }
    }, 1000)
}

const cancelAutoPlay = () => {
    showAutoPlayNotification.value = false
    if (autoPlayTimer.value) {
        clearInterval(autoPlayTimer.value)
        autoPlayTimer.value = null
    }
}

// URL management
const updateURL = () => {
    const url = route('courses.watch', {
        courseId: props.course.id,
        courseSlug: props.course.slug,
        lectureID: currentLecture.value.id
    })
    
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Fullscreen functionality
const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value
}

// Keyboard shortcuts
const handleKeyPress = (event) => {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return
    
    switch (event.key) {
        case 'ArrowLeft':
            if (hasPrevious.value) playPrevious()
            break
        case 'ArrowRight':
            if (hasNext.value) playNext()
            break
        case ' ':
            event.preventDefault()
            if (videoPlayer.value) {
                videoPlayer.value.paused ? videoPlayer.value.play() : videoPlayer.value.pause()
            }
            break
        case 'f':
        case 'F':
            event.preventDefault()
            toggleFullscreen()
            break
        case 'Escape':
            if (isFullscreen.value) {
                isFullscreen.value = false
            }
            break
    }
}

// Lifecycle
onMounted(() => {
    document.addEventListener('keydown', handleKeyPress)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyPress)
    cancelAutoPlay()
})

// Watch for lecture changes
watch(() => currentLecture.value, () => {
    if (videoPlayer.value) {
        videoPlayer.value.load()
    }
})
</script>

<style scoped>
.btn-control {
    @apply p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200;
}

.playlist-item {
    @apply transition-all duration-200;
}

.playlist-item:hover {
    @apply transform scale-[1.02];
}
</style>