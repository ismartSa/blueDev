import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useProgressTracking(props) {
    const isMarkingCompleted = ref(false)
    const completedLectures = ref(new Set(props.enrollment?.completed_lectures || []))

    // Computed properties
    const isLectureCompleted = computed(() => {
        return props.currentLecture ? completedLectures.value.has(props.currentLecture.id) : false
    })

    const allLectures = computed(() => {
        return props.sections?.flatMap(section => section.lectures) || []
    })

    const overallProgress = computed(() => {
        if (!allLectures.value.length) return 0
        return (completedLectures.value.size / allLectures.value.length) * 100
    })

    // Methods
    const isLectureCompletedById = (lectureId) => {
        return completedLectures.value.has(lectureId)
    }

    const getSectionProgress = (section) => {
        if (!section.lectures || section.lectures.length === 0) return 0
        const completedInSection = section.lectures.filter(lecture => 
            completedLectures.value.has(lecture.id)
        ).length
        return (completedInSection / section.lectures.length) * 100
    }

    const markAsCompleted = async () => {
        if (!props.currentLecture || isMarkingCompleted.value || isLectureCompleted.value) return
        
        isMarkingCompleted.value = true
        
        try {
            await router.post(route('lecture.complete'), {
                course_id: props.course.id,
                lecture_id: props.currentLecture.id
            }, {
                preserveState: true,
                onSuccess: () => {
                    completedLectures.value.add(props.currentLecture.id)
                    showNotification('Lecture marked as completed!', 'success')
                },
                onError: (errors) => {
                    showNotification('Failed to mark lecture as completed', 'error')
                }
            })
        } finally {
            isMarkingCompleted.value = false
        }
    }

    const resetProgress = () => {
        if (confirm('Are you sure you want to reset your progress? This action cannot be undone.')) {
            completedLectures.value.clear()
            showNotification('Progress reset successfully!')
        }
    }

    const shareProgress = () => {
        const progressText = `I'm ${Math.round(overallProgress.value)}% through "${props.course.title}"!`
        if (navigator.share) {
            navigator.share({
                title: 'My Learning Progress',
                text: progressText,
                url: window.location.href
            })
        } else {
            navigator.clipboard.writeText(progressText)
            showNotification('Progress copied to clipboard!')
        }
    }

    const showNotification = (message, type = 'info') => {
        // Simple notification system - could be enhanced with a toast library
        console.log(`${type.toUpperCase()}: ${message}`)
    }

    // Initialize completed lectures from enrollment data
    const initializeProgress = () => {
        if (props.enrollment?.completed_lectures) {
            completedLectures.value = new Set(props.enrollment.completed_lectures)
        }
    }

    return {
        // Refs
        isMarkingCompleted,
        completedLectures,
        
        // Computed
        isLectureCompleted,
        allLectures,
        overallProgress,
        
        // Methods
        isLectureCompletedById,
        getSectionProgress,
        markAsCompleted,
        resetProgress,
        shareProgress,
        initializeProgress,
        showNotification
    }
}