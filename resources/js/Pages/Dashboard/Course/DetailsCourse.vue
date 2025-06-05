<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { reactive, computed, ref, onMounted } from "vue";
import { ChevronLeftIcon, ShareIcon, BookmarkIcon, UsersIcon, EyeIcon, ClockIcon } from '@heroicons/vue/24/outline';
import CourseHeader from "@/Components/Course/CourseHeader.vue";
import CourseTabs from "@/Components/Course/CourseTabs.vue";
import CourseOverview from "@/Components/Course/CourseOverview.vue";
import LessonManager from "@/Components/Course/LessonManager.vue";
import QuizManager from "@/Components/Course/QuizManager.vue";
import VideoPlayer from "@/Components/Course/VideoPlayer.vue";
import SuccessNotification from "@/Components/SuccessNotification.vue";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";

// Props
const props = defineProps({
    course: { type: Object, required: true },
    lessons: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: () => [] },
    quizzes: { type: Object, default: () => ({}) },
    quizDomains: { type: Array, default: () => [] },
});

// State management
const data = reactive({
    activeTab: 'overview',
    showSuccess: false,
    successMessage: '',
    isLoading: false,
});

// Computed properties
const courseProgress = computed(() => {
    const totalLessons = props.lessons?.data?.length || 0;
    const completedLessons = props.lessons?.data?.filter(lesson => lesson.completed)?.length || 0;
    return totalLessons > 0 ? Math.round((completedLessons / totalLessons) * 100) : 0;
});

const lessonsCount = computed(() => props.lessons?.data?.length || 0);
const quizzesCount = computed(() => Object.keys(props.quizzes).length);

// Methods
const showSuccessMessage = (message) => {
    data.successMessage = message;
    data.showSuccess = true;
    setTimeout(() => {
        data.showSuccess = false;
    }, 5000);
};

const handleTabChange = (newTab) => {
    data.activeTab = newTab;
};

const handleCourseUpdate = () => {
    showSuccessMessage('تم تحديث الكورس بنجاح');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`كورس: ${course.title}`" />

        <!-- Breadcrumb Section -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Breadcrumb :items="breadcrumbs" class="py-4" />
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Course Header Component -->
            <div class="bg-white dark:bg-gray-800 shadow-sm">
                <div class="max-w-7xl mx-auto">
                    <CourseHeader
                        :course="course"
                        :stats="stats"
                        @success="handleCourseUpdate"
                    />
                </div>
            </div>

            <!-- Progress Bar Section -->
            <!-- Enhanced Course Progress Section -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Progress Header -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
                        <div class="mb-4 sm:mb-0">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Course Progress</h2>
                            <p class="text-gray-600 dark:text-gray-400">Track your learning journey</p>
                        </div>

                        <!-- Progress Stats -->
                        <div class="flex items-center space-x-8">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ courseProgress }}%</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Completed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ lessonsCount }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Lessons</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ Math.ceil((lessonsCount * courseProgress) / 100) }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Completed</div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Progress Bar -->
                    <div class="space-y-6">
                        <!-- Progress Bar Container -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Overall Progress</span>
                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium">
                                    {{ courseProgress }}% Complete
                                </span>
                            </div>

                            <!-- Main Progress Bar -->
                            <div class="relative w-full bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden">
                                <!-- Progress Fill -->
                                <div
                                    class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-1000 ease-out relative"
                                    :style="{ width: `${courseProgress}%` }"
                                >
                                    <!-- Subtle shine effect -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
                                </div>
                            </div>

                            <!-- Progress Milestones -->
                            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mt-3">
                                <span class="font-medium">0%</span>
                                <span class="font-medium">25%</span>
                                <span class="font-medium">50%</span>
                                <span class="font-medium">75%</span>
                                <span class="font-medium">100%</span>
                            </div>
                        </div>

                        <!-- Achievement Badges -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Achievements</h3>
                            <div class="flex flex-wrap gap-3">
                                <div v-if="courseProgress >= 25"
                                     class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-4 py-2 rounded-lg text-sm font-medium border border-blue-200 dark:border-blue-700">
                                    🌟 Getting Started
                                </div>
                                <div v-if="courseProgress >= 50"
                                     class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-4 py-2 rounded-lg text-sm font-medium border border-green-200 dark:border-green-700">
                                    🔥 Halfway Hero
                                </div>
                                <div v-if="courseProgress >= 75"
                                     class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-4 py-2 rounded-lg text-sm font-medium border border-purple-200 dark:border-purple-700">
                                    💪 Almost There
                                </div>
                                <div v-if="courseProgress >= 100"
                                     class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-4 py-2 rounded-lg text-sm font-medium border border-yellow-200 dark:border-yellow-700">
                                    🏆 Course Master
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Main Content -->
                    <div class="flex-1">
                        <!-- Course Tabs Component -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
                            <CourseTabs
                                :active-tab="data.activeTab"
                                :lessons-count="lessonsCount"
                                :quizzes-count="quizzesCount"
                                @update:active-tab="handleTabChange"
                            />

                            <!-- Tab Content -->
                            <div class="p-6">
                                <Transition name="fade" mode="out-in">
                                    <CourseOverview
                                        v-if="data.activeTab === 'overview'"
                                        :course="course"
                                        :stats="stats"
                                        key="overview"
                                    />

                                    <LessonManager
                                        v-else-if="data.activeTab === 'lessons'"
                                        :course="course"
                                        :lessons="lessons"
                                        @success="showSuccessMessage"
                                        key="lessons"
                                    />

                                    <QuizManager
                                        v-else-if="data.activeTab === 'quizzes'"
                                        :course="course"
                                        :quizzes="quizzes"
                                        :quiz-domains="quizDomains"
                                        @success="showSuccessMessage"
                                        key="quizzes"
                                    />
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:w-80">
                        <div class="sticky top-8 space-y-6">
                            <!-- Enhanced Course Stats Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <!-- Card Header -->
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                        Course Statistics
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Real-time course metrics</p>
                                </div>

                                <!-- Stats Grid -->
                                <div class="p-6 space-y-4">
                                    <!-- Enrolled Students -->
                                    <div class="group hover:bg-blue-50 dark:hover:bg-blue-900/20 p-4 rounded-lg transition-all duration-200 border border-transparent hover:border-blue-200 dark:hover:border-blue-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50 transition-colors">
                                                    <UsersIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Enrolled Students</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Active learners</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.enrolled_students || 0 }}</div>
                                                <div class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    +12% this week
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Views -->
                                        <div class="group hover:bg-green-50 dark:hover:bg-green-900/20 p-4 rounded-lg transition-all duration-200 border border-transparent hover:border-green-200 dark:hover:border-green-700">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                                                        <EyeIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Total Views</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Course visibility</p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.total_views || 0 }}</div>
                                                    <div class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        +8% this week
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Completion Rate -->
                                        <div class="group hover:bg-purple-50 dark:hover:bg-purple-900/20 p-4 rounded-lg transition-all duration-200 border border-transparent hover:border-purple-200 dark:hover:border-purple-700">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg group-hover:bg-purple-200 dark:group-hover:bg-purple-800/50 transition-colors">
                                                        <ClockIcon class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Completion Rate</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Average progress</p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ courseProgress }}%</div>
                                                    <div class="text-xs text-purple-600 dark:text-purple-400">
                                                        {{ Math.ceil((lessonsCount * courseProgress) / 100) }}/{{ lessonsCount }} lessons
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress Ring -->
                                        <div class="mt-6 flex justify-center">
                                            <div class="relative w-24 h-24">
                                                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                                                    <!-- Background Circle -->
                                                    <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" fill="none" class="text-gray-200 dark:text-gray-700"/>
                                                    <!-- Progress Circle -->
                                                    <circle
                                                        cx="50"
                                                        cy="50"
                                                        r="40"
                                                        stroke="currentColor"
                                                        stroke-width="8"
                                                        fill="none"
                                                        :stroke-dasharray="`${2 * Math.PI * 40}`"
                                                        :stroke-dashoffset="`${2 * Math.PI * 40 * (1 - courseProgress / 100)}`"
                                                        class="text-blue-500 transition-all duration-700 ease-out"
                                                        stroke-linecap="round"
                                                    />
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ courseProgress }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
            </div>

            <!-- Remove the duplicate Quick Actions Card section -->
            <!-- The Quick Actions are now handled in CourseOverview.vue component -->
        </div>
    </div>
</div>
        <!-- Success Notification -->
        <Transition name="notification">
            <SuccessNotification
                v-if="data.showSuccess"
                :message="data.successMessage"
                @close="data.showSuccess = false"
            />
        </Transition>

        <!-- Loading Overlay -->
        <Transition name="fade">
            <div
                v-if="data.isLoading"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <LoadingSpinner />
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.notification-enter-active {
    transition: all 0.3s ease;
}
.notification-leave-active {
    transition: all 0.3s ease;
}
.notification-enter-from {
    transform: translateY(-100%);
    opacity: 0;
}
.notification-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}
</style>
