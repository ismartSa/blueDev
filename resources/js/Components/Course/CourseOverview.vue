<script setup>
import { computed } from 'vue';
import { UsersIcon, EyeIcon, ClockIcon, BookOpenIcon, StarIcon, AcademicCapIcon, ChartBarIcon } from '@heroicons/vue/24/solid';

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
        active: 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/30',
        inactive: 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900/30',
        draft: 'text-yellow-600 bg-yellow-100 dark:text-yellow-400 dark:bg-yellow-900/30'
    };
    return colors[status] || 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-700';
};

const getCourseImage = () => {
    if (props.course.thumbnail) {
        return props.course.thumbnail;
    }
    if (props.course.image) {
        return props.course.image;
    }
    const courseId = props.course.id || 1;
    return `https://picsum.photos/seed/${courseId}/800/450`;
};
</script>

<template>
    <div class="space-y-8">
        <!-- Hero Section with Course Image and Title -->
        <div class="relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700 rounded-2xl overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10 dark:from-blue-900/20 dark:to-indigo-900/20"></div>

            <div class="relative p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
                    <!-- Course Image -->
                    <div class="lg:col-span-1">
                        <div class="relative group">
                            <img
                                :src="getCourseImage()"
                                :alt="course.title"
                                class="w-full h-64 lg:h-48 object-cover rounded-xl shadow-lg transition-transform duration-300 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                    </div>

                    <!-- Course Info -->
                    <div class="lg:col-span-2 space-y-4">
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <span :class="getStatusColor(course.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                    {{ course.status }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ course.category?.name || 'Course' }}</span>
                            </div>
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3">{{ course.title }}</h1>
                            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ course.description || 'Comprehensive course designed to enhance your skills and knowledge.' }}
                            </p>
                        </div>

                        <!-- Course Meta Info -->
                        <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <ClockIcon class="h-4 w-4" />
                                <span>Created {{ formatDate(course.created_at) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <BookOpenIcon class="h-4 w-4" />
                                <span>Course ID: {{ course.id }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section - Moved to Top -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-lg">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <ChartBarIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                Quick Actions
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-4 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg hover:scale-105">
                    <ChartBarIcon class="h-5 w-5" />
                    View Analytics
                </button>
                <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg hover:scale-105">
                    <BookOpenIcon class="h-5 w-5" />
                    Export Data
                </button>
                <button class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-4 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg hover:scale-105">
                    <UsersIcon class="h-5 w-5" />
                    Manage Enrollments
                </button>
                <button class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-4 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg hover:scale-105">
                    <AcademicCapIcon class="h-5 w-5" />
                    Course Settings
                </button>
            </div>
        </div>

        <!-- Enhanced Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Enrolled Students -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50 transition-colors">
                        <UsersIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.enrolled_students || 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Students</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Enrolled Students</span>
                    <span class="text-xs text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded-full">Active</span>
                </div>
            </div>

            <!-- Total Views -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                        <EyeIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_views || 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Views</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Views</span>
                    <span class="text-xs text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded-full">Trending</span>
                </div>
            </div>

            <!-- Course Price -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg group-hover:bg-purple-200 dark:group-hover:bg-purple-800/50 transition-colors">
                        <BookOpenIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ course.price || 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Course Price</span>
                    <span class="text-xs text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30 px-2 py-1 rounded-full">Premium</span>
                </div>
            </div>

            <!-- Course Rating -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition-colors">
                        <StarIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">4.8</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rating</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Course Rating</span>
                    <div class="flex items-center gap-1">
                        <StarIcon class="h-3 w-3 text-orange-400" />
                        <StarIcon class="h-3 w-3 text-orange-400" />
                        <StarIcon class="h-3 w-3 text-orange-400" />
                        <StarIcon class="h-3 w-3 text-orange-400" />
                        <StarIcon class="h-3 w-3 text-orange-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Details and Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Course Information -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Course Details Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <AcademicCapIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        تفاصيل الدورة
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Course ID</span>
                                <span class="text-gray-900 dark:text-white font-semibold">{{ course.id }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Category</span>
                                <span class="text-gray-900 dark:text-white font-semibold">{{ course.category?.name || 'Uncategorized' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Status</span>
                                <span :class="getStatusColor(course.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                    {{ course.status }}
                                </span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Created</span>
                                <span class="text-gray-900 dark:text-white font-semibold">{{ formatDate(course.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Last Updated</span>
                                <span class="text-gray-900 dark:text-white font-semibold">{{ formatDate(course.updated_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Price</span>
                                <span class="text-gray-900 dark:text-white font-semibold">${{ course.price || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
