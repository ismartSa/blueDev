<template>
    <Head title="My Courses" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Courses
                </h2>
                <Link :href="route('courses.explore')" 
                      class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Explore More Courses
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-blue-600">{{ stats.total_enrolled }}</div>
                        <div class="text-gray-600">Total Enrolled</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-green-600">{{ stats.completed }}</div>
                        <div class="text-gray-600">Completed</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-yellow-600">{{ stats.in_progress }}</div>
                        <div class="text-gray-600">In Progress</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-3xl font-bold text-gray-600">{{ stats.not_started }}</div>
                        <div class="text-gray-600">Not Started</div>
                    </div>
                </div>

                <!-- Enrolled Courses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-6">My Enrolled Courses</h3>
                        
                        <div v-if="enrolledCourses.length === 0" class="text-center py-12">
                            <div class="text-gray-500 mb-4">You haven't enrolled in any courses yet.</div>
                            <Link :href="route('courses.explore')" 
                                  class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                                Browse Courses
                            </Link>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="course in enrolledCourses" :key="course.id" 
                                 class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="relative">
                                    <img :src="course.image || '/images/default-course.jpg'" 
                                         :alt="course.title" 
                                         class="w-full h-48 object-cover">
                                    <div class="absolute top-2 right-2">
                                        <span v-if="course.is_completed" 
                                              class="bg-green-500 text-white px-2 py-1 rounded text-xs">
                                            Completed
                                        </span>
                                        <span v-else-if="course.progress > 0" 
                                              class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">
                                            {{ course.progress }}% Complete
                                        </span>
                                        <span v-else 
                                              class="bg-gray-500 text-white px-2 py-1 rounded text-xs">
                                            Not Started
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h4 class="font-semibold text-lg mb-2">{{ course.title }}</h4>
                                    <p class="text-gray-600 text-sm mb-2">{{ course.category }}</p>
                                    <p class="text-gray-500 text-sm mb-3">by {{ course.instructor }}</p>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>Progress</span>
                                            <span>{{ course.completed_lectures }}/{{ course.total_lectures }} lectures</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                                 :style="{ width: course.progress + '%' }"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Quizzes Section -->
                                    <div v-if="course.quizzes && course.quizzes.length > 0" class="mb-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Course Quizzes</h5>
                                        <div class="space-y-2">
                                            <div v-for="quiz in course.quizzes" :key="quiz.id" 
                                                 class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                <div>
                                                    <span class="text-sm font-medium">{{ quiz.title }}</span>
                                                    <span v-if="quiz.is_completed" 
                                                          class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                                        Completed
                                                    </span>
                                                    <span v-else 
                                                          class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                                                        Pending
                                                    </span>
                                                </div>
                                                <Link :href="route('quizzes.show', quiz.id)" 
                                                      class="text-blue-500 hover:text-blue-600 text-xs">
                                                    {{ quiz.is_completed ? 'Review' : 'Take Quiz' }}
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">
                                            Enrolled: {{ course.enrollment_date }}
                                        </span>
                                        <Link :href="route('courses.player', { courseId: course.id, courseSlug: course.slug })" 
                                              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition-colors">
                                            {{ course.progress > 0 ? 'Continue' : 'Start' }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Suggestions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Recommended for You</h3>
                            <button @click="loadMoreSuggestions" 
                                    class="text-blue-500 hover:text-blue-600 text-sm">
                                View More
                            </button>
                        </div>
                        
                        <div v-if="suggestions.length === 0" class="text-center py-8">
                            <div class="text-gray-500">No recommendations available at the moment.</div>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="course in suggestions" :key="course.id" 
                                 class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="relative">
                                    <img :src="course.image || '/images/default-course.jpg'" 
                                         :alt="course.title" 
                                         class="w-full h-48 object-cover">
                                    <div class="absolute top-2 right-2">
                                        <span v-if="course.is_free" 
                                              class="bg-green-500 text-white px-2 py-1 rounded text-xs">
                                            Free
                                        </span>
                                        <span v-else 
                                              class="bg-blue-500 text-white px-2 py-1 rounded text-xs">
                                            ${{ course.price }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h4 class="font-semibold text-lg mb-2">{{ course.title }}</h4>
                                    <p class="text-gray-600 text-sm mb-2">{{ course.description }}</p>
                                    <div class="flex justify-between text-sm text-gray-500 mb-3">
                                        <span>{{ course.category }}</span>
                                        <span>{{ course.duration }}</span>
                                    </div>
                                    <p class="text-gray-500 text-sm mb-4">by {{ course.instructor }}</p>
                                    
                                    <div class="flex justify-between items-center">
                                        <span v-if="course.level" 
                                              class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                            {{ course.level }}
                                        </span>
                                        <Link :href="route('courses.details', { id: course.id, courseSlug: course.slug })" 
                                              class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm transition-colors">
                                            View Details
                                        </Link>
                                    </div>
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
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    enrolledCourses: Array,
    suggestions: Array,
    stats: Object
})

const suggestions = ref(props.suggestions)

const loadMoreSuggestions = async () => {
    try {
        const response = await axios.get(route('my-courses.suggestions'))
        suggestions.value = response.data
    } catch (error) {
        console.error('Failed to load more suggestions:', error)
    }
}
</script>