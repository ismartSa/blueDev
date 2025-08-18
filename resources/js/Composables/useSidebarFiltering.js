import { ref, computed } from 'vue'

/**
 * Composable for dynamic sidebar filtering and rendering
 * Provides functionality to show/hide sections based on user progress or preferences
 */
export function useSidebarFiltering(props) {
    // User preferences for sidebar filtering
    const showCompletedSections = ref(true)
    const showInProgressSections = ref(true)
    const showNotStartedSections = ref(true)
    const hideEmptySections = ref(false)
    const showOnlyCurrentSection = ref(false)
    
    // Minimum progress threshold to show a section (0-100)
    const minProgressThreshold = ref(0)
    
    /**
     * Calculate section progress based on completed lectures
     */
    const getSectionProgress = (section, completedLectures) => {
        if (!section.lectures || section.lectures.length === 0) return 0
        
        const completedCount = section.lectures.filter(lecture => 
            completedLectures.has(lecture.id)
        ).length
        
        return Math.round((completedCount / section.lectures.length) * 100)
    }
    
    /**
     * Determine section status based on progress
     */
    const getSectionStatus = (section, completedLectures) => {
        const progress = getSectionProgress(section, completedLectures)
        
        if (progress === 100) return 'completed'
        if (progress > 0) return 'in_progress'
        return 'not_started'
    }
    
    /**
     * Check if section contains the current lecture
     */
    const isCurrentSection = (section, currentLecture) => {
        if (!currentLecture || !section.lectures) return false
        return section.lectures.some(lecture => lecture.id === currentLecture.id)
    }
    
    /**
     * Filter sections based on user preferences and progress
     */
    const getFilteredSections = (completedLectures) => {
        if (!props.sections) return []
        
        return props.sections.filter(section => {
            // Always show current section if showOnlyCurrentSection is enabled
            if (showOnlyCurrentSection.value && props.currentLecture) {
                return isCurrentSection(section, props.currentLecture)
            }
            
            // Get section status and progress
            const status = getSectionStatus(section, completedLectures)
            const progress = getSectionProgress(section, completedLectures)
            
            // Filter by progress threshold
            if (progress < minProgressThreshold.value) {
                return false
            }
            
            // Filter by completion status
            switch (status) {
                case 'completed':
                    return showCompletedSections.value
                case 'in_progress':
                    return showInProgressSections.value
                case 'not_started':
                    return showNotStartedSections.value
                default:
                    return true
            }
        }).filter(section => {
            // Hide empty sections if preference is set
            if (hideEmptySections.value) {
                return section.lectures && section.lectures.length > 0
            }
            return true
        })
    }
    
    /**
     * Get sections with enhanced metadata for rendering
     */
    const getEnhancedSections = (completedLectures) => {
        const filteredSections = getFilteredSections(completedLectures)
        return filteredSections.map(section => ({
            ...section,
            progress: getSectionProgress(section, completedLectures),
            status: getSectionStatus(section, completedLectures),
            isCurrent: isCurrentSection(section, props.currentLecture),
            lectureCount: section.lectures ? section.lectures.length : 0,
            completedCount: section.lectures ? 
                section.lectures.filter(lecture => completedLectures.has(lecture.id)).length : 0
        }))
    }
    
    /**
     * Toggle filter preferences
     */
    const toggleCompletedSections = () => {
        showCompletedSections.value = !showCompletedSections.value
    }
    
    const toggleInProgressSections = () => {
        showInProgressSections.value = !showInProgressSections.value
    }
    
    const toggleNotStartedSections = () => {
        showNotStartedSections.value = !showNotStartedSections.value
    }
    
    const toggleEmptySections = () => {
        hideEmptySections.value = !hideEmptySections.value
    }
    
    const toggleCurrentSectionOnly = () => {
        showOnlyCurrentSection.value = !showOnlyCurrentSection.value
    }
    
    /**
     * Reset all filters to default state
     */
    const resetFilters = () => {
        showCompletedSections.value = true
        showInProgressSections.value = true
        showNotStartedSections.value = true
        hideEmptySections.value = false
        showOnlyCurrentSection.value = false
        minProgressThreshold.value = 0
    }
    
    /**
     * Set progress threshold filter
     */
    const setProgressThreshold = (threshold) => {
        minProgressThreshold.value = Math.max(0, Math.min(100, threshold))
    }
    
    /**
     * Get filter summary for UI display
     */
    const filterSummary = computed(() => {
        const activeFilters = []
        
        if (!showCompletedSections.value) activeFilters.push('Hide Completed')
        if (!showInProgressSections.value) activeFilters.push('Hide In Progress')
        if (!showNotStartedSections.value) activeFilters.push('Hide Not Started')
        if (hideEmptySections.value) activeFilters.push('Hide Empty')
        if (showOnlyCurrentSection.value) activeFilters.push('Current Only')
        if (minProgressThreshold.value > 0) activeFilters.push(`Min ${minProgressThreshold.value}%`)
        
        return activeFilters
    })
    
    return {
        // State
        showCompletedSections,
        showInProgressSections,
        showNotStartedSections,
        hideEmptySections,
        showOnlyCurrentSection,
        minProgressThreshold,
        
        // Computed
        filterSummary,
        
        // Methods
        getSectionProgress,
        getSectionStatus,
        isCurrentSection,
        getEnhancedSections,
        toggleCompletedSections,
        toggleInProgressSections,
        toggleNotStartedSections,
        toggleEmptySections,
        toggleCurrentSectionOnly,
        resetFilters,
        setProgressThreshold
    }
}