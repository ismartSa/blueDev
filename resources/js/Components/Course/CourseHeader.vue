<script setup>
import { reactive } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { ArrowLeftIcon, PencilIcon } from "@heroicons/vue/24/solid";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import EditCourseModal from "@/Components/Course/EditCourseModal.vue";

const props = defineProps({
    course: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['success']);

const data = reactive({
    editModalOpen: false,
});

const goBack = () => {
    router.get(route('courses.index'));
};

const getStatusBadgeClass = (status) => {
    const statusClasses = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        inactive: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        draft: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    };
    return statusClasses[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
};

const getCourseImage = () => {
    if (props.course.thumbnail) {
        return props.course.thumbnail;
    }
    if (props.course.image) {
        return props.course.image;
    }
    // Fallback to a placeholder image
    const courseId = props.course.id || 1;
    return `https://picsum.photos/seed/${courseId}/400/300`;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

        <div class="p-6">
            <!-- Back Button -->
            <button @click="goBack" class="mb-4 flex items-center text-gray-600 hover:text-gray-900">
                <ArrowLeftIcon class="h-5 w-5 mr-2" />
                Back to Courses
            </button>

            <!-- Course Info with Image -->
            <div class="flex justify-between items-start">
                <div class="flex-1 flex space-x-6">
                    <!-- Course Image -->
                    <div class="flex-shrink-0">
                        <img
                            :src="getCourseImage()"
                            :alt="course.title"
                            class="w-32 h-32 object-cover rounded-lg shadow-md"
                        />
                    </div>

                    <!-- Course Details -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ course.title }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ course.description }}
                        </p>

                    <!-- Course Stats -->
                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                        <span class="flex items-center">
                            <UsersIcon class="h-4 w-4 mr-1" />
                            {{ stats.enrolled_students || 0 }} Students
                        </span>
                        <span class="flex items-center">
                            <EyeIcon class="h-4 w-4 mr-1" />
                            {{ stats.total_views || 0 }} Views
                        </span>
                        <span class="flex items-center">
                            <span class="text-lg font-semibold text-green-600">
                                ${{ course.price || 0 }}
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-3">
                    <span :class="getStatusBadgeClass(course.status)"
                          class="px-2 py-1 text-xs font-medium rounded-full">
                        {{ course.status }}
                    </span>
                    <PrimaryButton @click="data.editModalOpen = true">
                        <PencilIcon class="h-4 w-4 mr-2" />
                        Edit Course
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <EditCourseModal
        :show="data.editModalOpen"
        :course="course"
        @close="data.editModalOpen = false"
        @success="(msg) => { data.editModalOpen = false; emit('success', msg); }"
    />
    </div>
</template>
