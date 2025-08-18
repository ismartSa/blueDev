<template>
    <AuthenticatedLayout>
        <div :class="playerStyles.containers.main">
            <!-- Top Progress Bar -->
            <div class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm border-b border-slate-200/60">
                <div :class="playerStyles.progress.container">
                    <div :class="getProgressBarClasses(overallProgress)" :style="{ width: overallProgress + '%' }"></div>
                </div>
            </div>
            
            <!-- Header -->
            <div class="bg-white/98 backdrop-blur-lg shadow-lg border-b border-slate-200/80 mt-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-20 py-4">
                        <div class="flex items-center space-x-4 min-w-0 flex-1">
                            <!-- Mobile menu button moved to floating position -->
                            <Link :href="route('courses.player', { courseId: course.id, courseSlug: course.slug })" :class="playerStyles.buttons.ghost + ' p-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div class="min-w-0 flex-1 space-y-1">
                                <h1 :class="playerStyles.text.heading + ' text-xl sm:text-2xl font-bold truncate leading-tight'">{{ course.title }}</h1>
                                <p :class="playerStyles.text.muted + ' hidden sm:block font-medium text-sm truncate'">by {{ course.instructor.name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 flex-shrink-0">
                            <div class="text-sm text-slate-700 hidden md:block font-medium whitespace-nowrap">
                                Progress: {{ overallProgress }}%
                            </div>
                            <div :class="playerStyles.progress.container + ' w-24 sm:w-32'">
                                <div :class="getProgressBarClasses(overallProgress)" :style="{ width: overallProgress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex h-[calc(100vh-8rem)] relative overflow-hidden">
                <!-- Mobile Sidebar Overlay -->
                <div v-if="sidebarOpen" @click="closeSidebar" class="fixed inset-0 bg-black/60 backdrop-blur-md z-40 lg:hidden transition-all duration-300"></div>
                
                <!-- Mobile Menu Button -->
                <button @click="toggleSidebar" class="fixed top-24 right-4 z-50 lg:hidden p-3 bg-white rounded-xl shadow-lg border border-slate-200 text-slate-700 hover:text-indigo-700 hover:bg-indigo-50 transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out" :class="{ 'lg:mr-12': sidebarCollapsed, 'lg:mr-80': !sidebarCollapsed }">
                    <!-- Video Player -->
                    <div class="flex-shrink-0">
                        <VideoPlayer
                            :current-lecture="currentLecture"
                            :video-loading="videoLoading"
                            :is-fullscreen="isFullscreen"
                            :show-auto-play-notification="showAutoPlayNotification"
                            :auto-play-countdown="autoPlayCountdown"
                            @video-load-start="onVideoLoadStart"
                            @video-loaded="onVideoLoaded"
                            @video-error="onVideoError"
                            @time-update="onTimeUpdate"
                            @video-ended="onVideoEnded"
                            @toggle-fullscreen="toggleFullscreen"
                            @cancel-auto-play="cancelAutoPlay"
                            @select-first-lecture="selectFirstLecture"
                        />
                    </div>
                    
                    <!-- Video Controls -->
                    <VideoControls
                        :current-lecture="currentLecture"
                        :is-fullscreen="isFullscreen"
                        :has-previous="hasPreviousLecture"
                        :has-next="hasNextLecture"
                        :auto-play-enabled="autoPlayEnabled"
                        :current-time="currentTime"
                        :duration="duration"
                        @previous="previousLecture"
                        @next="nextLecture"
                        @toggle-autoplay="toggleAutoPlay"
                    />

                    <!-- Dashboard Layout -->
                    <div v-if="currentLecture" :class="[playerStyles.containers.content, 'bg-gradient-to-br from-slate-50/50 to-blue-50/30 border-t border-slate-200/60 flex-1 overflow-y-auto', { 'hidden': isFullscreen }]">
                        <div class="max-w-7xl mx-auto space-y-8 p-4 sm:p-6 lg:p-8">
                            <!-- Lecture Header -->
                            <div :class="playerStyles.cards.elevated + ' p-8'">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                                    <div class="flex-1 space-y-6">
                                        <h2 :class="playerStyles.text.heading + ' text-2xl sm:text-3xl lg:text-4xl leading-tight font-bold text-slate-900'">{{ currentLecture.title }}</h2>
                                        <p v-if="currentLecture.description" :class="playerStyles.text.body + ' text-base sm:text-lg leading-relaxed max-w-4xl text-slate-600 font-medium'">{{ currentLecture.description }}</p>
                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            <span class="flex items-center gap-2 bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-700 px-5 py-3 rounded-xl font-semibold border border-indigo-200/50 shadow-sm">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ formatDuration(currentLecture.duration) }}
                                            </span>
                                            <span class="bg-gradient-to-r from-blue-50 to-purple-50 text-blue-700 px-5 py-3 rounded-xl font-semibold border border-blue-200/50 shadow-sm">Lesson {{ currentLecture.order }}</span>
                                            <span class="flex items-center gap-2 bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 px-5 py-3 rounded-xl font-semibold border border-green-200/50 shadow-sm">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ completedLectures.size }} / {{ allLectures.length }} completed
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center lg:flex-shrink-0">
                                        <button @click="markAsCompleted" :class="getVariantClasses(isLectureCompleted ? 'success' : 'primary', 'button') + ' flex items-center space-x-3 px-8 py-4 rounded-2xl font-semibold text-lg shadow-xl hover:shadow-2xl transition-all duration-200'" :disabled="isMarkingCompleted || isLectureCompleted">
                                            <svg v-if="isLectureCompleted" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else-if="isMarkingCompleted" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span v-if="isLectureCompleted">Completed</span>
                                            <span v-else-if="isMarkingCompleted">Marking...</span>
                                            <span v-else>Mark as Completed</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                         </div>

                         <!-- Course Sections and Lectures -->
                         <div :class="playerStyles.cards.elevated + ' p-8'">
                             <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
                                 <h3 :class="playerStyles.text.heading + ' text-xl sm:text-2xl font-bold text-slate-900'">Course Content</h3>
                                 <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                                     <input
                                         v-model="searchQuery"
                                         type="text"
                                         placeholder="Search lectures..."
                                         class="px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm w-full sm:w-64 shadow-sm"
                                     />
                                     <button
                                         @click="toggleShowCompleted"
                                         :class="getVariantClasses(showCompleted ? 'success' : 'secondary', 'button') + ' px-6 py-3 rounded-xl text-sm font-medium shadow-sm hover:shadow-md transition-all duration-200'"
                                     >
                                         {{ showCompleted ? 'Hide' : 'Show' }} Completed
                                     </button>
                                 </div>
                             </div>

                             <div v-if="filteredSections.length === 0" class="text-center py-16">
                                 <div class="text-slate-400 text-lg font-medium">No lectures found matching your search.</div>
                             </div>

                             <div v-else class="space-y-8">
                                 <div v-for="section in filteredSections" :key="section.id" class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow duration-200">
                                     <div class="bg-gradient-to-r from-slate-50 to-blue-50/30 p-8 border-b border-slate-200">
                                         <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                             <div class="flex items-start space-x-6">
                                                 <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg flex-shrink-0">
                                                     {{ section.order }}
                                                 </div>
                                                 <div class="min-w-0 flex-1">
                                                     <h4 class="text-lg sm:text-xl font-bold text-slate-900 mb-2 leading-tight">{{ section.title }}</h4>
                                                     <p v-if="section.description" class="text-sm sm:text-base text-slate-600 leading-relaxed font-medium">{{ section.description }}</p>
                                                 </div>
                                             </div>
                                             <div class="flex items-center gap-4 text-sm text-slate-500 flex-shrink-0">
                                                 <span class="bg-white px-4 py-2 rounded-xl font-medium shadow-sm border border-slate-200">
                                                     {{ section.lectures.length }} lectures
                                                 </span>
                                                 <span class="bg-white px-4 py-2 rounded-xl font-medium shadow-sm border border-slate-200">
                                                     {{ formatDuration(section.lectures.reduce((total, lecture) => total + lecture.duration, 0)) }}
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="p-4">
                                         <div class="space-y-2">
                                             <LectureItem
                                                 v-for="lecture in section.lectures"
                                                 :key="lecture.id"
                                                 :lecture="lecture"
                                                 :is-current="currentLecture && currentLecture.id === lecture.id"
                                                 :is-completed="completedLectures.has(lecture.id)"
                                                 @click="selectLecture(lecture)"
                                             />
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <!-- Dashboard Stats Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
                            <div class="transform hover:scale-105 transition-all duration-200">
                                <StatCard
                                    title="Video Progress"
                                    :value="Math.round(watchProgress) + '%'"
                                    :subtitle="formatTime(currentTime) + ' / ' + formatTime(duration)"
                                    variant="blue"
                                    :progress="watchProgress"
                                    :icon="{ name: 'play', color: 'blue' }"
                                />
                            </div>
                            <div class="transform hover:scale-105 transition-all duration-200">
                                <StatCard
                                    title="Course Progress"
                                    :value="Math.round(overallProgress) + '%'"
                                    :subtitle="completedLectures.size + ' of ' + allLectures.length + ' lectures'"
                                    variant="green"
                                    :progress="overallProgress"
                                    :icon="{ name: 'check', color: 'green' }"
                                />
                            </div>
                            <div class="transform hover:scale-105 transition-all duration-200">
                                <StatCard
                                    title="Learning Time"
                                    :value="formatTime(currentTime)"
                                    subtitle="Current session"
                                    variant="purple"
                                    :icon="{ name: 'clock', color: 'purple' }"
                                />
                            </div>
                            <div class="transform hover:scale-105 transition-all duration-200">
                                <StatCard
                                    title="Course Rating"
                                    :value="course.rating || '4.8'"
                                    :subtitle="(course.reviews_count || '1,234') + ' reviews'"
                                    variant="yellow"
                                    :icon="{ name: 'star', color: 'yellow' }"
                                />
                            </div>
                        </div>

                        <!-- Course Sections Overview -->
                        <div :class="playerStyles.cards.base + ' p-6 sm:p-8'">
                            <h3 :class="playerStyles.text.subheading + ' mb-6 flex items-center gap-3'">
                                <div class="p-2 bg-indigo-500/15 rounded-xl">
                                    <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                                    </svg>
                                </div>
                                <span>Sections Overview</span>
                            </h3>
                            <div class="space-y-6">
                                <SectionCard
                                    v-for="section in sections"
                                    :key="section.id"
                                    :section="section"
                                    :current-lecture="currentLecture"
                                    :is-lecture-completed="isLectureCompletedById"
                                    @lecture-select="selectLecture"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Course Content -->
                <div :class="[
                    'bg-white/95 backdrop-blur-sm border-l border-slate-200/60 overflow-y-auto transition-all duration-300 ease-in-out z-30 shadow-lg',
                    // Desktop sizing
                    sidebarCollapsed ? 'lg:w-16' : 'lg:w-80',
                    // Mobile sizing and positioning
                    'w-80 sm:w-96',
                    'lg:relative lg:flex-shrink-0',
                    'fixed inset-y-0 right-0 lg:inset-y-auto lg:right-auto',
                    // Mobile visibility
                    sidebarOpen ? 'translate-x-0' : 'translate-x-full',
                    // Desktop visibility
                    'lg:translate-x-0',
                    sidebarCollapsed ? 'lg:w-16' : 'lg:w-80'
                ]" style="top: 8rem;">
                    <!-- Collapse/Expand Toggle -->
                    <div class="absolute -left-12 top-6 z-10 hidden lg:block">
                        <button @click="toggleSidebarCollapse" :class="playerStyles.buttons.secondary + ' p-3 shadow-lg hover:shadow-xl transform hover:scale-105 rounded-xl'" :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                            <svg class="w-5 h-5 text-slate-600 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <div :class="sidebarCollapsed ? 'p-4' : 'p-6'">
                        <div v-if="!sidebarCollapsed" class="flex items-center justify-between mb-8 bg-gradient-to-r from-slate-50 to-blue-50/30 p-5 rounded-xl border border-slate-200/60 shadow-sm">
                            <h2 :class="playerStyles.text.subheading + ' flex items-center gap-3 font-semibold'">
                                <div class="p-2 bg-indigo-100 rounded-lg">
                                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                                    </svg>
                                </div>
                                <span class="hidden sm:inline">Course Content</span>
                                <span class="sm:hidden">Content</span>
                            </h2>
                            <button @click="closeSidebar" :class="playerStyles.buttons.ghost + ' lg:hidden'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Collapsed State - Mini Icons -->
                        <div v-if="sidebarCollapsed" class="space-y-6">
                            <div v-for="(section, sectionIndex) in getEnhancedSections(completedLectures)" :key="section.id" class="relative group flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center cursor-pointer transition-all duration-200 shadow-md hover:shadow-lg" 
                                     :class="{
                                         'bg-green-100 text-green-700 hover:bg-green-200': section.status === 'completed',
                                         'bg-blue-100 text-blue-700 hover:bg-blue-200': section.status === 'in_progress',
                                         'bg-gray-100 text-gray-600 hover:bg-gray-200': section.status === 'not_started',
                                         'ring-2 ring-indigo-500 ring-offset-2': section.isCurrent
                                     }"
                                     :title="`${section.title} (${section.progress}%)`">
                                    <span class="text-sm font-bold">{{ sectionIndex + 1 }}</span>
                                </div>
                                <div class="space-y-2 mt-3 flex flex-col items-center">
                                    <LectureItem
                                        v-for="lecture in section.lectures"
                                        :key="lecture.id"
                                        :lecture="lecture"
                                        :is-current="currentLecture && currentLecture.id === lecture.id"
                                        :is-completed="isLectureCompletedById(lecture.id)"
                                        class="w-6 h-6 rounded-full cursor-pointer transition-all duration-200 hover:scale-110 shadow-sm"
                                        @click="selectLecture(lecture)"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Expanded State - Full Content -->
                        <div v-if="!sidebarCollapsed" class="space-y-6">
                            <!-- Sidebar Filters -->
                            <SidebarFilter
                                :show-completed-sections="showCompletedSections"
                                :show-in-progress-sections="showInProgressSections"
                                :show-not-started-sections="showNotStartedSections"
                                :hide-empty-sections="hideEmptySections"
                                :show-only-current-section="showOnlyCurrentSection"
                                :min-progress-threshold="minProgressThreshold"
                                :filter-summary="filterSummary"
                                @toggle-completed="toggleCompletedSections"
                                @toggle-in-progress="toggleInProgressSections"
                                @toggle-not-started="toggleNotStartedSections"
                                @toggle-empty-sections="toggleEmptySections"
                                @toggle-current-only="toggleCurrentSectionOnly"
                                @set-progress-threshold="setProgressThreshold"
                                @reset-filters="resetFilters"
                            />
                            
                            <!-- Filtered Sections -->
                            <div v-if="getEnhancedSections(completedLectures).length > 0" class="space-y-6">
                                <SectionCard
                                    v-for="section in getEnhancedSections(completedLectures)"
                                    :key="section.id"
                                    :section="section"
                                    :current-lecture="currentLecture"
                                    :is-lecture-completed="isLectureCompletedById"
                                    @lecture-select="selectLecture"
                                />
                            </div>
                            
                            <!-- No Sections Message -->
                            <div v-else class="text-center py-8">
                                <div class="text-slate-400 mb-2">
                                    <svg class="w-12 h-12 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500 font-medium">No sections match your current filters</p>
                                <button 
                                    @click="resetFilters" 
                                    class="mt-2 text-xs text-indigo-600 hover:text-indigo-700 font-medium"
                                >
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VideoPlayer from '@/Components/Player/VideoPlayer.vue'
import VideoControls from '@/Components/Player/VideoControls.vue'
import StatCard from '@/Components/Player/StatCard.vue'
import LectureItem from '@/Components/Player/LectureItem.vue'
import SectionCard from '@/Components/Player/SectionCard.vue'
import SidebarFilter from '@/Components/Player/SidebarFilter.vue'
import { useVideoPlayer } from '@/Composables/useVideoPlayer.js'
import { useProgressTracking } from '@/Composables/useProgressTracking.js'
import { useSidebarFiltering } from '@/Composables/useSidebarFiltering.js'
import { playerStyles, getVariantClasses, getProgressBarClasses } from '@/utils/playerStyles.js'

const props = defineProps({
    course: Object,
    sections: Array,
    currentLecture: Object,
    enrollment: Object,
    stats: Object
})

// Sidebar state
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const isDesktop = ref(false)

// Use composables
const {
    videoPlayer,
    videoLoading,
    isFullscreen,
    autoPlayEnabled,
    showAutoPlayNotification,
    autoPlayCountdown,
    currentTime,
    duration,
    watchProgress,
    allLectures,
    hasPreviousLecture,
    hasNextLecture,
    onVideoLoadStart,
    onVideoLoaded,
    onVideoError,
    onTimeUpdate,
    onVideoEnded,
    selectLecture,
    selectFirstLecture,
    previousLecture,
    nextLecture,
    toggleFullscreen,
    toggleAutoPlay,
    cancelAutoPlay,
    formatTime
} = useVideoPlayer(props)

const {
    isMarkingCompleted,
    completedLectures,
    isLectureCompleted,
    overallProgress,
    isLectureCompletedById,
    markAsCompleted,
    shareProgress,
    initializeProgress
} = useProgressTracking(props)

const {
    showCompletedSections,
    showInProgressSections,
    showNotStartedSections,
    hideEmptySections,
    showOnlyCurrentSection,
    minProgressThreshold,
    filterSummary,
    getEnhancedSections,
    toggleCompletedSections,
    toggleInProgressSections,
    toggleNotStartedSections,
    toggleEmptySections,
    toggleCurrentSectionOnly,
    resetFilters,
    setProgressThreshold
} = useSidebarFiltering(props)

// Computed properties for filtering
const filteredSections = computed(() => {
    return getEnhancedSections(completedLectures.value)
})

// Sidebar controls
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
    sidebarOpen.value = false
}

const toggleSidebarCollapse = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
    if (sidebarCollapsed.value) {
        sidebarOpen.value = true
    }
}

// Utility functions
const formatDuration = (minutes) => {
    if (!minutes) return '0m'
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`
}

// Keyboard navigation
const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        if (isFullscreen.value) {
            toggleFullscreen()
        } else {
            closeSidebar()
        }
        return
    }
    
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        return
    }
    
    switch (e.key) {
        case 'ArrowLeft':
            e.preventDefault()
            previousLecture()
            break
        case 'ArrowRight':
            e.preventDefault()
            nextLecture()
            break
        case ' ':
            e.preventDefault()
            if (videoPlayer.value) {
                if (videoPlayer.value.paused) {
                    videoPlayer.value.play()
                } else {
                    videoPlayer.value.pause()
                }
            }
            break
        case 'c':
        case 'C':
            e.preventDefault()
            if (!isLectureCompleted.value) {
                markAsCompleted()
            }
            break
        case 'm':
        case 'M':
            e.preventDefault()
            toggleSidebar()
            break
        case 'f':
        case 'F':
            e.preventDefault()
            toggleFullscreen()
            break
    }
}

const handleResize = () => {
    if (window.innerWidth >= 1024) {
        sidebarOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
    window.addEventListener('resize', handleResize)
    initializeProgress()
    
    // Check screen size for responsive behavior
    checkScreenSize()
    window.addEventListener('resize', checkScreenSize)
})

const checkScreenSize = () => {
    isDesktop.value = window.innerWidth >= 1024
    if (isDesktop.value) {
        sidebarOpen.value = false
    }
}

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
    window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>