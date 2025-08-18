<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Analytics Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Courses</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ analytics.total_courses || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average Price</p>
                                <p class="text-2xl font-semibold text-gray-900">${{ analytics.average_price || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Avg Duration (hrs)</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ analytics.average_duration || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Avg Enrollments</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ analytics.average_enrollments || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Course Statistics Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Course Statistics</h3>
                        <div class="h-64 flex items-center justify-center bg-gray-50 rounded">
                            <p class="text-gray-500">Chart visualization would go here</p>
                        </div>
                    </div>

                    <!-- Enrollment Trends -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Enrollment Trends</h3>
                        <div class="h-64 flex items-center justify-center bg-gray-50 rounded">
                            <p class="text-gray-500">Trend chart would go here</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analytics Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Detailed Analytics</h3>
                            <button 
                                @click="refreshData" 
                                :disabled="loading"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                            >
                                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Refresh Data
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <h4 class="font-medium text-gray-900">Course Metrics</h4>
                                <div class="text-sm text-gray-600">
                                    <p>Total Courses: {{ analytics.total_courses || 0 }}</p>
                                    <p>Average Price: ${{ analytics.average_price || 0 }}</p>
                                    <p>Average Duration: {{ analytics.average_duration || 0 }} hours</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h4 class="font-medium text-gray-900">Enrollment Metrics</h4>
                                <div class="text-sm text-gray-600">
                                    <p>Average Enrollments: {{ analytics.average_enrollments || 0 }}</p>
                                    <p>Completion Rate: {{ analytics.completion_rate || 0 }}%</p>
                                    <p>User Engagement: {{ analytics.user_engagement || 0 }}%</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h4 class="font-medium text-gray-900">System Health</h4>
                                <div class="text-sm text-gray-600">
                                    <p>Cache Status: <span :class="health.cache ? 'text-green-600' : 'text-red-600'">{{ health.cache ? 'Active' : 'Inactive' }}</span></p>
                                    <p>Database: <span :class="health.database ? 'text-green-600' : 'text-red-600'">{{ health.database ? 'Connected' : 'Disconnected' }}</span></p>
                                    <p>Last Updated: {{ lastUpdated }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    analytics: {
        type: Object,
        default: () => ({})
    },
    health: {
        type: Object,
        default: () => ({ cache: false, database: false })
    }
})

const loading = ref(false)
const lastUpdated = ref(new Date().toLocaleString())

const refreshData = async () => {
    loading.value = true
    try {
        await router.post(route('analytics.refresh'))
        router.reload({ only: ['analytics', 'health'] })
        lastUpdated.value = new Date().toLocaleString()
    } catch (error) {
        console.error('Failed to refresh analytics data:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    // Auto-refresh every 5 minutes
    setInterval(() => {
        if (!loading.value) {
            refreshData()
        }
    }, 300000)
})
</script>