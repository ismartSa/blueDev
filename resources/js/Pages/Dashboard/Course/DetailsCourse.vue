<script setup>
import { reactive, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import CourseOverview from '@/Components/Course/CourseOverview.vue'
import CourseTabs from '@/Components/Course/CourseTabs.vue'
import LessonManager from '@/Components/Course/LessonManager.vue'
import QuizManager from '@/Components/Course/QuizManager.vue'
import CourseStatistics from '@/Components/Course/CourseStatistics.vue'
import SuccessNotification from '@/Components/SuccessNotification.vue'
import LoadingSpinner from '@/Components/LoadingSpinner.vue'

// Props definition with defaults
const props = defineProps({
    course: { type: Object, required: true },
    lessons: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: () => [] },
    quizzes: { type: Object, default: () => ({}) },
    quizDomains: { type: Array, default: () => [] }
})

// Reactive state
const data = reactive({
    activeTab: 'overview',
    showSuccess: false,
    successMessage: '',
    isLoading: false
})

// Optimized computed properties
const lessonsData = computed(() => props.lessons?.data || [])
const lessonsCount = computed(() => lessonsData.value.length)
const quizzesCount = computed(() => Object.keys(props.quizzes).length)
const courseProgress = computed(() => {
    if (!lessonsCount.value) return 0
    const completed = lessonsData.value.filter(lesson => lesson.completed).length
    return Math.round((completed / lessonsCount.value) * 100)
})

// Consolidated methods
const handleTabChange = (newTab) => data.activeTab = newTab

const showSuccessMessage = (message) => {
    Object.assign(data, { successMessage: message, showSuccess: true })
    setTimeout(() => data.showSuccess = false, 3000)
}

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Course: ${props.course.data.title}`" />

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <Breadcrumb :breadcrumbs="props.breadcrumbs" />
        </div>

        <!-- Notifications & Overlays -->
        <SuccessNotification
            :show="data.showSuccess"
            :message="data.successMessage"
            @close="data.showSuccess = false"
            class="fixed top-20 right-5 z-50"
        />
        
        <LoadingSpinner v-if="data.isLoading" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" />
        
        <!-- Debug section (development only) -->
        <div v-if="!props.course.data.slug && process.env.NODE_ENV === 'development'" 
             class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            Course title not available
        </div>

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <CourseOverview
                :course="props.course.data"
                :stats="props.stats"
                :course-progress="courseProgress"
                :lessons-count="lessonsCount"
            />

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Main Content -->
                    <div class="flex-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-4 overflow-hidden">
                            <CourseTabs
                                :active-tab="data.activeTab"
                                :lessons-count="lessonsCount"
                                :quizzes-count="quizzesCount"
                                @update:active-tab="handleTabChange"
                            />

                            <!-- Tab Content -->
                            <div class="p-4">
                                <Transition name="fade" mode="out-in">
                                    <LessonManager
                                        v-if="data.activeTab === 'lessons'"
                                        :course="props.course.data"
                                        :lessons="props.lessons"
                                        @success="showSuccessMessage"
                                        key="lessons"
                                    />
                                    <QuizManager
                                        v-else-if="data.activeTab === 'quizzes'"
                                        :course="props.course"
                                        :quizzes="props.quizzes"
                                        :quiz-domains="props.quizDomains"
                                        @success="showSuccessMessage"
                                        key="quizzes"
                                    />
                                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        Course overview is displayed above
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:w-72">
                        <div class="sticky top-4">
                            <CourseStatistics :stats="props.stats" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Optimized transitions and responsive design */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
    .course-details { padding: 1rem; }
    .course-tabs { margin-bottom: 1rem; }
}
</style>
