<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head } from "@inertiajs/vue3"
import { reactive, computed } from "vue"
import { router } from "@inertiajs/vue3"
import {
    UserIcon, AcademicCapIcon, ChartBarIcon, ClockIcon,
    ArrowLeftIcon, CheckCircleIcon, XCircleIcon
} from "@heroicons/vue/24/solid"
import Pagination from "@/Components/Pagination.vue"

// Configuration constants
const CONFIG = {
    STATUS_COLORS: {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800'
    },
    PROGRESS_COLORS: {
        high: 'bg-green-500',
        medium: 'bg-yellow-500', 
        low: 'bg-red-500'
    }
}

const props = defineProps({
    course: { type: Object, required: true },
    enrollments: { type: Object, required: true },
    stats: { type: Object, required: true }
})

// Reactive state
const state = reactive({
    selectedEnrollments: [],
    searchTerm: '',
    filterStatus: 'all'
})

// Computed properties
const filteredEnrollments = computed(() => {
    let filtered = props.enrollments.data
    
    if (state.searchTerm) {
        filtered = filtered.filter(enrollment => 
            enrollment.user.name.toLowerCase().includes(state.searchTerm.toLowerCase()) ||
            enrollment.user.email.toLowerCase().includes(state.searchTerm.toLowerCase())
        )
    }
    
    if (state.filterStatus !== 'all') {
        filtered = filtered.filter(enrollment => enrollment.status === state.filterStatus)
    }
    
    return filtered
})

const progressPercentage = (enrollment) => {
    if (!props.stats.total_lessons || props.stats.total_lessons === 0) return 0
    return Math.round((enrollment.completed_lessons / props.stats.total_lessons) * 100)
}

const getProgressColor = (percentage) => {
    if (percentage >= 70) return CONFIG.PROGRESS_COLORS.high
    if (percentage >= 40) return CONFIG.PROGRESS_COLORS.medium
    return CONFIG.PROGRESS_COLORS.low
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

// Methods
const goBack = () => {
    router.visit(route('dashboard.courses.index'))
}

const toggleSelection = (enrollmentId) => {
    const index = state.selectedEnrollments.indexOf(enrollmentId)
    if (index > -1) {
        state.selectedEnrollments.splice(index, 1)
    } else {
        state.selectedEnrollments.push(enrollmentId)
    }
}

const selectAll = () => {
    if (state.selectedEnrollments.length === filteredEnrollments.value.length) {
        state.selectedEnrollments = []
    } else {
        state.selectedEnrollments = filteredEnrollments.value.map(e => e.id)
    }
}
</script>

<template>
    <Head :title="`Enrollments - ${course.title}`" />
    
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <button
                                    @click="goBack"
                                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                                >
                                    <ArrowLeftIcon class="h-5 w-5" />
                                </button>
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                        Course Enrollments
                                    </h1>
                                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                                        {{ course.title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <UserIcon class="h-8 w-8 text-blue-500" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Total Students
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.total_enrollments }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <ClockIcon class="h-8 w-8 text-green-500" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Active Students
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.active_students }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <ChartBarIcon class="h-8 w-8 text-purple-500" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Completion Rate
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.completion_rate }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <AcademicCapIcon class="h-8 w-8 text-orange-500" />
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Total Lessons
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.total_lessons }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <input
                                    v-model="state.searchTerm"
                                    type="text"
                                    placeholder="Search students by name or email..."
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <select
                                v-model="state.filterStatus"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            >
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Enrollments Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <input
                                            type="checkbox"
                                            @change="selectAll"
                                            :checked="state.selectedEnrollments.length === filteredEnrollments.length && filteredEnrollments.length > 0"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Student
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Progress
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Enrolled Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="enrollment in filteredEnrollments"
                                    :key="enrollment.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :checked="state.selectedEnrollments.includes(enrollment.id)"
                                            @change="toggleSelection(enrollment.id)"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                    <UserIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ enrollment.user.name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ enrollment.user.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-2 mr-3">
                                                <div
                                                    class="h-2 rounded-full transition-all duration-300"
                                                    :class="getProgressColor(progressPercentage(enrollment))"
                                                    :style="{ width: progressPercentage(enrollment) + '%' }"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ progressPercentage(enrollment) }}%
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ enrollment.completed_lessons }} / {{ stats.total_lessons }} lessons
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="CONFIG.STATUS_COLORS[enrollment.status] || CONFIG.STATUS_COLORS.pending"
                                        >
                                            {{ enrollment.status || 'active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(enrollment.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <Pagination :links="enrollments.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>