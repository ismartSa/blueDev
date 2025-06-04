<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { reactive, computed, ref, onMounted } from "vue";
import { ChevronLeftIcon, ShareIcon, BookmarkIcon } from '@heroicons/vue/24/outline';
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

// Enhanced state management
const data = reactive({
    activeTab: 'overview',
    showSuccess: false,
    successMessage: '',
    isLoading: false,
    sidebarCollapsed: false,
    showQuickActions: false,
});

// Computed properties for better performance
const courseProgress = computed(() => {
    // Calculate course completion percentage
    const totalLessons = props.lessons?.data?.length || 0;
    const completedLessons = props.lessons?.data?.filter(lesson => lesson.completed)?.length || 0;
    return totalLessons > 0 ? Math.round((completedLessons / totalLessons) * 100) : 0;
});

const tabsWithCounts = computed(() => [
    {
        id: 'overview',
        name: 'نظرة عامة',
        icon: 'BookOpenIcon',
        count: null
    },
    {
        id: 'lessons',
        name: 'الدروس',
        icon: 'VideoCameraIcon',
        count: props.lessons?.data?.length || 0
    },
    {
        id: 'quizzes',
        name: 'الاختبارات',
        icon: 'AcademicCapIcon',
        count: Object.keys(props.quizzes).length
    },
    {
        id: 'analytics',
        name: 'التحليلات',
        icon: 'ChartBarIcon',
        count: null
    }
]);

// Enhanced methods
const showSuccessMessage = (message) => {
    data.successMessage = message;
    data.showSuccess = true;
    setTimeout(() => {
        data.showSuccess = false;
    }, 5000);
};

const toggleSidebar = () => {
    data.sidebarCollapsed = !data.sidebarCollapsed;
};

const shareCourse = async () => {
    try {
        await navigator.share({
            title: props.course.title,
            text: props.course.description,
            url: window.location.href
        });
    } catch (err) {
        // Fallback to clipboard
        navigator.clipboard.writeText(window.location.href);
        showSuccessMessage('تم نسخ رابط الكورس');
    }
};

const bookmarkCourse = () => {
    // Implement bookmark functionality
    showSuccessMessage('تم إضافة الكورس للمفضلة');
};

onMounted(() => {
    // Initialize any required data
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`كورس: ${course.title}`" />

        <!-- Enhanced Breadcrumb -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Breadcrumb :items="breadcrumbs" class="py-4" />
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Enhanced Course Header with Hero Section -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <!-- Course Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <button
                                    @click="$inertia.visit(route('courses.index'))"
                                    class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                                >
                                    <ChevronLeftIcon class="h-5 w-5" />
                                </button>
                                <span class="text-sm opacity-80">العودة للكورسات</span>
                            </div>

                            <h1 class="text-3xl lg:text-4xl font-bold mb-2">{{ course.title }}</h1>
                            <p class="text-lg opacity-90 mb-4">{{ course.description }}</p>

                            <!-- Course Meta -->
                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                <span class="bg-white/20 px-3 py-1 rounded-full">
                                    {{ course.category?.name || 'غير محدد' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <ClockIcon class="h-4 w-4" />
                                    {{ course.duration }} دقيقة
                                </span>
                                <span class="flex items-center gap-1">
                                    <UsersIcon class="h-4 w-4" />
                                    {{ stats.enrolled_students || 0 }} طالب
                                </span>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex items-center gap-3">
                            <button
                                @click="shareCourse"
                                class="p-3 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                                title="مشاركة الكورس"
                            >
                                <ShareIcon class="h-5 w-5" />
                            </button>
                            <button
                                @click="bookmarkCourse"
                                class="p-3 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                                title="إضافة للمفضلة"
                            >
                                <BookmarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span>تقدم الكورس</span>
                            <span>{{ courseProgress }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2">
                            <div
                                class="bg-white h-2 rounded-full transition-all duration-300"
                                :style="{ width: `${courseProgress}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Main Content -->
                    <div class="flex-1">
                        <!-- Enhanced Tabs -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <nav class="flex space-x-8 px-6" dir="rtl">
                                    <button
                                        v-for="tab in tabsWithCounts"
                                        :key="tab.id"
                                        @click="data.activeTab = tab.id"
                                        :class="[
                                            data.activeTab === tab.id
                                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2'
                                        ]"
                                    >
                                        {{ tab.name }}
                                        <span
                                            v-if="tab.count !== null"
                                            class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded-full text-xs"
                                        >
                                            {{ tab.count }}
                                        </span>
                                    </button>
                                </nav>
                            </div>

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

                                    <div v-else-if="data.activeTab === 'analytics'" key="analytics">
                                        <!-- Analytics Component -->
                                        <h3 class="text-lg font-semibold mb-4">تحليلات الكورس</h3>
                                        <p class="text-gray-600 dark:text-gray-400">قريباً...</p>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Sidebar -->
                    <div class="lg:w-80">
                        <div class="sticky top-8 space-y-6">
                            <!-- Course Stats Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">إحصائيات الكورس</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">الطلاب المسجلين</span>
                                        <span class="font-semibold">{{ stats.enrolled_students || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">إجمالي المشاهدات</span>
                                        <span class="font-semibold">{{ stats.total_views || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">معدل الإكمال</span>
                                        <span class="font-semibold">{{ courseProgress }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">إجراءات سريعة</h3>
                                <div class="space-y-3">
                                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition-colors">
                                        تحرير الكورس
                                    </button>
                                    <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors">
                                        إضافة درس جديد
                                    </button>
                                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg transition-colors">
                                        إنشاء اختبار
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Success Notification -->
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
