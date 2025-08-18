import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useVideoPlayer(props) {
    const videoPlayer = ref(null)
    const videoLoading = ref(false)
    const isFullscreen = ref(false)
    const autoPlayEnabled = ref(true)
    const showAutoPlayNotification = ref(false)
    const autoPlayCountdown = ref(5)
    const autoPlayTimer = ref(null)
    const currentTime = ref(0)
    const duration = ref(0)
    const watchProgress = ref(0)

    // Computed properties
    const allLectures = computed(() => {
        return props.sections?.flatMap(section => section.lectures) || []
    })

    const currentLectureIndex = computed(() => {
        if (!props.currentLecture) return -1
        return allLectures.value.findIndex(lecture => lecture.id === props.currentLecture.id)
    })

    const hasPreviousLecture = computed(() => currentLectureIndex.value > 0)
    const hasNextLecture = computed(() => currentLectureIndex.value < allLectures.value.length - 1)

    // Video event handlers
    const onVideoLoadStart = () => {
        videoLoading.value = true
    }

    const onVideoLoaded = () => {
        videoLoading.value = false
    }

    const onVideoError = () => {
        videoLoading.value = false
        showNotification('Error loading video. Please try again.', 'error')
    }

    const onTimeUpdate = (event) => {
        currentTime.value = event.target.currentTime
        duration.value = event.target.duration
        
        if (duration.value > 0) {
            watchProgress.value = (currentTime.value / duration.value) * 100
        }
    }

    const onVideoEnded = () => {
        // Auto-advance to next lecture if enabled
        if (autoPlayEnabled.value && hasNextLecture.value) {
            startAutoPlayCountdown()
        }
    }

    // Navigation methods
    const selectLecture = (lecture) => {
        videoLoading.value = true
        router.visit(route('courses.watch', [props.course.id, props.course.slug, lecture.id]))
    }

    const selectFirstLecture = () => {
        const firstLecture = allLectures.value[0]
        if (firstLecture) {
            selectLecture(firstLecture)
        }
    }

    const previousLecture = () => {
        if (hasPreviousLecture.value) {
            const prevLecture = allLectures.value[currentLectureIndex.value - 1]
            selectLecture(prevLecture)
        }
    }

    const nextLecture = () => {
        if (hasNextLecture.value) {
            const nextLecture = allLectures.value[currentLectureIndex.value + 1]
            selectLecture(nextLecture)
        }
    }

    // Video controls
    const toggleVideoPlayback = () => {
        if (videoPlayer.value) {
            if (videoPlayer.value.paused) {
                videoPlayer.value.play()
            } else {
                videoPlayer.value.pause()
            }
        }
    }

    const toggleFullscreen = () => {
        isFullscreen.value = !isFullscreen.value
    }

    const toggleAutoPlay = () => {
        autoPlayEnabled.value = !autoPlayEnabled.value
        showNotification(`Auto-play ${autoPlayEnabled.value ? 'enabled' : 'disabled'}`)
    }

    // Auto-play functionality
    const startAutoPlayCountdown = () => {
        showAutoPlayNotification.value = true
        autoPlayCountdown.value = 5
        
        autoPlayTimer.value = setInterval(() => {
            autoPlayCountdown.value--
            if (autoPlayCountdown.value <= 0) {
                clearInterval(autoPlayTimer.value)
                showAutoPlayNotification.value = false
                nextLecture()
            }
        }, 1000)
    }

    const cancelAutoPlay = () => {
        if (autoPlayTimer.value) {
            clearInterval(autoPlayTimer.value)
            autoPlayTimer.value = null
        }
        showAutoPlayNotification.value = false
    }

    // Utility functions
    const formatTime = (seconds) => {
        if (!seconds || isNaN(seconds)) return '0:00'
        const mins = Math.floor(seconds / 60)
        const secs = Math.floor(seconds % 60)
        return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    const showNotification = (message, type = 'info') => {
        // Simple notification system - could be enhanced with a toast library
        console.log(`${type.toUpperCase()}: ${message}`)
    }

    return {
        // Refs
        videoPlayer,
        videoLoading,
        isFullscreen,
        autoPlayEnabled,
        showAutoPlayNotification,
        autoPlayCountdown,
        currentTime,
        duration,
        watchProgress,
        
        // Computed
        allLectures,
        currentLectureIndex,
        hasPreviousLecture,
        hasNextLecture,
        
        // Methods
        onVideoLoadStart,
        onVideoLoaded,
        onVideoError,
        onTimeUpdate,
        onVideoEnded,
        selectLecture,
        selectFirstLecture,
        previousLecture,
        nextLecture,
        toggleVideoPlayback,
        toggleFullscreen,
        toggleAutoPlay,
        startAutoPlayCountdown,
        cancelAutoPlay,
        formatTime,
        showNotification
    }
}