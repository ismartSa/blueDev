<script setup>
import { UsersIcon, EyeIcon, ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: { type: Object, default: () => ({}) }
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                Course Statistics
            </h3>
        </div>

        <div class="p-6 space-y-4">
            <!-- Enrolled Students -->
            <div class="group hover:bg-blue-50 dark:hover:bg-blue-900/20 p-4 rounded-lg transition-all border border-transparent hover:border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <UsersIcon class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium">Enrolled Students</p>
                            <p class="text-xs text-gray-500">Active learners</p>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-blue-600">{{ stats.enrolled_students || 0 }}</div>
                </div>
            </div>

            <!-- Total Views -->
            <div class="group hover:bg-green-50 p-4 rounded-lg transition-all border border-transparent hover:border-green-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <EyeIcon class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium">Total Views</p>
                            <p class="text-xs text-gray-500">Course visibility</p>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-green-600">{{ stats.total_views || 0 }}</div>
                </div>
            </div>

            <!-- Completion Rate -->
            <div class="group hover:bg-purple-50 p-4 rounded-lg transition-all border border-transparent hover:border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium">Completion Rate</p>
                            <p class="text-xs text-gray-500">Student progress</p>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-purple-600">{{ stats.completion_rate || 0 }}%</div>
                </div>
            </div>
        </div>
    </div>
</template>
