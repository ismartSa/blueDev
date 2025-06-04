<script setup>
import { computed } from 'vue';
import { UsersIcon, EyeIcon, ClockIcon, BookOpenIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    course: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getStatusColor = (status) => {
    const colors = {
        active: 'text-green-600 bg-green-100',
        inactive: 'text-red-600 bg-red-100',
        draft: 'text-yellow-600 bg-yellow-100'
    };
    return colors[status] || 'text-gray-600 bg-gray-100';
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
    return `https://picsum.photos/seed/${courseId}/800/450`;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Course Overview</h2>

            <!-- Course Image Section -->
            <div class="mb-8">
                <div class="relative">
                    <img
                        :src="getCourseImage()"
                        :alt="course.title"
                        class="w-full h-64 object-cover rounded-lg shadow-lg"
                    />
                    <div class="absolute inset-0 bg-black bg-opacity-40 rounded-lg flex items-center justify-center">
                        <div class="text-center text-white">
                            <h3 class="text-2xl font-bold mb-2">{{ course.title }}</h3>
                            <p class="text-lg opacity-90">{{ course.category?.name || 'Course' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white">
                    <div class="flex items-center">
                        <UsersIcon class="h-8 w-8 mr-3" />
                        <div>
                            <p class="text-blue-100 text-sm">Enrolled Students</p>
                            <p class="text-2xl font-bold">{{ stats.enrolled_students || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg text-white">
                    <div class="flex items-center">
                        <EyeIcon class="h-8 w-8 mr-3" />
                        <div>
                            <p class="text-green-100 text-sm">Total Views</p>
                            <p class="text-2xl font-bold">{{ stats.total_views || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
                    <div class="flex items-center">
                        <BookOpenIcon class="h-8 w-8 mr-3" />
                        <div>
                            <p class="text-purple-100 text-sm">Course Price</p>
                            <p class="text-2xl font-bold">${{ course.price || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-lg text-white">
                    <div class="flex items-center">
                        <ClockIcon class="h-8 w-8 mr-3" />
                        <div>
                            <p class="text-orange-100 text-sm">Status</p>
                            <p class="text-lg font-semibold capitalize">{{ course.status }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Course Information -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Course Information</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Course ID:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ course.id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Category:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ course.category?.name || 'Uncategorized' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(course.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(course.updated_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span :class="getStatusColor(course.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ course.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Description -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                {{ course.description || 'No description available for this course.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                View Analytics
                            </button>
                            <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Export Data
                            </button>
                            <button class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Manage Enrollments
                            </button>
                            <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Course Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
