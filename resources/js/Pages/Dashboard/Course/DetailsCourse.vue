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
import CourseStatistics from "@/Components/Course/CourseStatistics.vue";

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
// Add debugging logs
onMounted(() => {
    console.log('Course data:', props.course);
    console.log('Course title:', props.course?.data.title);
    console.log('Course name:', props.course?.data.name);
    console.log('All props:', props);
});

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Course: ${props.course.data.title}`" />

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <Breadcrumb :breadcrumbs="props.breadcrumbs" />
        </div>

        <!-- Success Notification -->
        <div class="fixed top-20 right-5 z-50">
            <SuccessNotification
                :show="data.showSuccess"
                :message="data.successMessage"
                @close="data.showSuccess = false"
            />
        </div>

        <!-- Loading Spinner Overlay -->
        <div v-if="data.isLoading" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <LoadingSpinner />
        </div>

        <!-- Single debug section -->
        <div v-if="!props.course.data.slug" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
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

                                    <LessonManager
                                        v-if="data.activeTab === 'lessons'"
                                        :course="course.data"
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

                                    <!-- Default content for overview tab -->
                                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        Course overview is displayed above
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:w-80">
                        <div class="sticky top-8 space-y-6">
                            <CourseStatistics :stats="stats" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remove the duplicate Quick Actions Card section -->

        </div>
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
