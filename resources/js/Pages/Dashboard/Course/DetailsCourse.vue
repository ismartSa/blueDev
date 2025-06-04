<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { reactive, computed } from "vue";
import CourseHeader from "@/Components/Course/CourseHeader.vue";
import CourseTabs from "@/Components/Course/CourseTabs.vue";
import CourseOverview from "@/Components/Course/CourseOverview.vue";
import LessonManager from "@/Components/Course/LessonManager.vue";
import QuizManager from "@/Components/Course/QuizManager.vue";
import VideoPlayer from "@/Components/Course/VideoPlayer.vue";
import SuccessNotification from "@/Components/SuccessNotification.vue";

// Props
const props = defineProps({
    course: { type: Object, required: true },
    lessons: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: () => [] },
    quizzes: { type: Object, default: () => ({}) },
    quizDomains: { type: Array, default: () => [] },
});

// Main state
const data = reactive({
    activeTab: 'overview',
    showSuccess: false,
    successMessage: '',
});

const showSuccessMessage = (message) => {
    data.successMessage = message;
    data.showSuccess = true;
    setTimeout(() => {
        data.showSuccess = false;
    }, 5000);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Course: ${course.title}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Course Header -->
                <CourseHeader
                    :course="course"
                    :stats="stats"
                    @success="showSuccessMessage"
                />

                <!-- Course Tabs -->
                <CourseTabs
                    v-model:activeTab="data.activeTab"
                    :lessons-count="lessons?.data?.length || 0"
                    :quizzes-count="Object.keys(quizzes).length"
                />

                <!-- Tab Content -->
                <div class="mt-6">
                    <CourseOverview
                        v-if="data.activeTab === 'overview'"
                        :course="course"
                        :stats="stats"
                    />

                    <LessonManager
                        v-if="data.activeTab === 'lessons'"
                        :course="course"
                        :lessons="lessons"
                        @success="showSuccessMessage"
                    />

                    <QuizManager
                        v-if="data.activeTab === 'quizzes'"
                        :course="course"
                        :quizzes="quizzes"
                        :quiz-domains="quizDomains"
                        @success="showSuccessMessage"
                    />
                </div>
            </div>
        </div>

        <!-- Success Notification -->
        <SuccessNotification
            :show="data.showSuccess"
            :message="data.successMessage"
        />
    </AuthenticatedLayout>
</template>
