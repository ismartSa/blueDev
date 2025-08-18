<template>
    <Head title="Course Suggestions" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Recommended Courses
                </h2>
                <Link :href="route('my-courses')" 
                      class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Back to My Courses
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter Options -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Filter Recommendations</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <select v-model="filters.category" @change="applyFilters" 
                                    class="border rounded-lg px-3 py-2">
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                            
                            <select v-model="filters.level" @change="applyFilters" 
                                    class="border rounded-lg px-3 py-2">
                                <option value="">All Levels</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                            
                            <select v-model="filters.price" @change="applyFilters" 
                                    class="border rounded-lg px-3 py-2">
                                <option value="">All Prices</option>
                                <option value="free">Free Only</option>
                                <option value="paid">Paid Only</option>
                            </select>
                            
                            <select v-model="filters.sort" @change="applyFilters" 
                                    class="border rounded-lg px-3 py-2">
                                <option value="relevance">Most Relevant</option>
                                <option value="rating">Highest Rated</option>
                                <option value="newest">Newest</option>
                                <option value="popular">Most Popular</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Course Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="loading" class="text-center py-12">
                            <div class="text-gray-500">Loading recommendations...</div>
                        </div>
                        
                        <div v-else-if="filteredCourses.length === 0" class="text-center py-12">
                            <div class="text-gray-500 mb-4">No courses match your current filters.</div>
                            <button @click="clearFilters" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                Clear Filters
                            </button>
                        </div>

                        <div v-else>
                            <div class="mb-4 text-sm text-gray-600">
                                Showing {{ filteredCourses.length }} of {{ totalCourses }} courses
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div v-for="course in filteredCourses" :key="course.id" 
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
                                        <div v-if="course.rating" class="absolute bottom-2 left-2">
                                            <div class="bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs flex items-center">
                                                <span class="text-yellow-400 mr-1">★</span>
                                                {{ course.rating }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4">
                                        <h4 class="font-semibold text-lg mb-2">{{ course.title }}</h4>
                                        <p class="text-gray-600 text-sm mb-2 line-clamp-2">{{ course.description }}</p>
                                        
                                        <div class="flex justify-between text-sm text-gray-500 mb-2">
                                            <span>{{ course.category }}</span>
                                            <span>{{ course.duration }}</span>
                                        </div>
                                        
                                        <p class="text-gray-500 text-sm mb-3">by {{ course.instructor }}</p>
                                        
                                        <div class="flex justify-between items-center mb-3">
                                            <span v-if="course.level" 
                                                  class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                                {{ course.level }}
                                            </span>
                                            <span v-if="course.students_count" 
                                                  class="text-xs text-gray-500">
                                                {{ course.students_count }} students
                                            </span>
                                        </div>
                                        
                                        <div class="flex gap-2">
                                            <Link :href="route('courses.details', { id: course.id, courseSlug: course.slug })" 
                                                  class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm text-center transition-colors">
                                                View Details
                                            </Link>
                                            <button v-if="!course.is_enrolled" 
                                                    @click="enrollInCourse(course.id)"
                                                    :disabled="enrolling === course.id"
                                                    class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white px-4 py-2 rounded text-sm transition-colors">
                                                {{ enrolling === course.id ? 'Enrolling...' : 'Enroll' }}
                                            </button>
                                            <span v-else class="bg-gray-100 text-gray-600 px-4 py-2 rounded text-sm">
                                                Enrolled
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Load More Button -->
                            <div v-if="hasMore" class="text-center mt-8">
                                <button @click="loadMore" 
                                        :disabled="loadingMore"
                                        class="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg transition-colors">
                                    {{ loadingMore ? 'Loading...' : 'Load More Courses' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    courses: Array,
    categories: Array,
    totalCourses: Number,
    hasMore: Boolean
})

const courses = ref(props.courses || [])
const loading = ref(false)
const loadingMore = ref(false)
const enrolling = ref(null)
const hasMore = ref(props.hasMore || false)

const filters = ref({
    category: '',
    level: '',
    price: '',
    sort: 'relevance'
})

const filteredCourses = computed(() => {
    let filtered = courses.value
    
    if (filters.value.category) {
        filtered = filtered.filter(course => course.category === filters.value.category)
    }
    
    if (filters.value.level) {
        filtered = filtered.filter(course => course.level === filters.value.level)
    }
    
    if (filters.value.price === 'free') {
        filtered = filtered.filter(course => course.is_free)
    } else if (filters.value.price === 'paid') {
        filtered = filtered.filter(course => !course.is_free)
    }
    
    // Sort courses
    if (filters.value.sort === 'rating') {
        filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0))
    } else if (filters.value.sort === 'newest') {
        filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    } else if (filters.value.sort === 'popular') {
        filtered.sort((a, b) => (b.students_count || 0) - (a.students_count || 0))
    }
    
    return filtered
})

const applyFilters = async () => {
    loading.value = true
    try {
        const response = await axios.get(route('my-courses.suggestions'), {
            params: filters.value
        })
        courses.value = response.data.courses
        hasMore.value = response.data.hasMore
    } catch (error) {
        console.error('Failed to apply filters:', error)
    } finally {
        loading.value = false
    }
}

const clearFilters = () => {
    filters.value = {
        category: '',
        level: '',
        price: '',
        sort: 'relevance'
    }
    applyFilters()
}

const loadMore = async () => {
    loadingMore.value = true
    try {
        const response = await axios.get(route('my-courses.suggestions'), {
            params: {
                ...filters.value,
                offset: courses.value.length
            }
        })
        courses.value.push(...response.data.courses)
        hasMore.value = response.data.hasMore
    } catch (error) {
        console.error('Failed to load more courses:', error)
    } finally {
        loadingMore.value = false
    }
}

const enrollInCourse = async (courseId) => {
    enrolling.value = courseId
    try {
        await axios.post(route('courses.enroll-api', courseId))
        // Update course enrollment status
        const courseIndex = courses.value.findIndex(c => c.id === courseId)
        if (courseIndex !== -1) {
            courses.value[courseIndex].is_enrolled = true
        }
        // Optionally show success message
    } catch (error) {
        console.error('Failed to enroll in course:', error)
        // Optionally show error message
    } finally {
        enrolling.value = null
    }
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>