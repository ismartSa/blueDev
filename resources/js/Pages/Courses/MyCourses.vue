<template>
    <Head title="My Courses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Courses
                </h2>
                <Link :href="route('courses.explore')"
                      :class="buttonClasses.primary">
                    Explore More Courses
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        v-for="stat in statsData"
                        :key="stat.key"
                        :value="stats[stat.key]"
                        :label="stat.label"
                        :color="stat.color"
                    />
                </div>

                <!-- Course Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex flex-wrap gap-4 items-center">
                            <div class="flex-1 min-w-64">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search courses..."
                                    :class="inputClasses"
                                >
                            </div>
                            <select
                                v-for="filter in filterOptions"
                                :key="filter.key"
                                v-model="filters[filter.key]"
                                :class="inputClasses"
                            >
                                <option :value="filter.defaultValue">{{ filter.defaultLabel }}</option>
                                <option
                                    v-for="option in filter.options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Enrolled Courses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">My Enrolled Courses</h3>
                            <button
                                @click="showQuizCreationModal = true"
                                :class="buttonClasses.secondary"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Quiz to Course
                            </button>
                        </div>

                        <EmptyState
                            v-if="filteredCourses.length === 0"
                            icon="📚"
                            title="No courses found matching your criteria"
                            subtitle="Try adjusting your search or filters"
                        />

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <CourseCard
                                v-for="course in filteredCourses"
                                :key="course.id"
                                :course="course"
                                :expanded="expandedCourse === course.id"
                                @toggle-quiz="toggleQuizPlaylist"
                                @continue-learning="handleContinueLearning"
                            />
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

        <!-- Quiz Creation Modal -->
        <QuizCreationModal
            v-if="showQuizCreationModal"
            :courses="enrolledCourses"
            @close="showQuizCreationModal = false"
            @quiz-created="handleQuizCreated"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuizPlaylist from '@/Components/Quiz/QuizPlaylist.vue'
import StatCard from '@/Components/Common/StatCard.vue'
import EmptyState from '@/Components/Common/EmptyState.vue'
import CourseCard from '@/Components/Course/CourseCard.vue'
import QuizCreationModal from '@/Components/Quiz/QuizCreationModal.vue'
import axios from 'axios'

const props = defineProps({
    enrolledCourses: {
        type: Array,
        default: () => []
    },
    suggestions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_enrolled: 0,
            completed: 0,
            in_progress: 0,
            not_started: 0
        })
    },
    categories: {
        type: Array,
        default: () => []
    }
})

const suggestions = ref(props.suggestions)

// Reactive data
const expandedCourse = ref(null)
const searchQuery = ref('')
const showQuizCreationModal = ref(false)
const filters = reactive({
    status: 'all',
    category: 'all'
})

// Computed properties for dynamic rendering
const statsData = computed(() => [
    { key: 'total_enrolled', label: 'Total Enrolled', color: 'blue' },
    { key: 'completed', label: 'Completed', color: 'green' },
    { key: 'in_progress', label: 'In Progress', color: 'yellow' },
    { key: 'not_started', label: 'Not Started', color: 'gray' }
])

const filterOptions = computed(() => [
    {
        key: 'status',
        defaultValue: 'all',
        defaultLabel: 'All Status',
        options: [
            { value: 'not_started', label: 'Not Started' },
            { value: 'in_progress', label: 'In Progress' },
            { value: 'completed', label: 'Completed' }
        ]
    },
    {
        key: 'category',
        defaultValue: 'all',
        defaultLabel: 'All Categories',
        options: props.categories.map(cat => ({ value: cat.id, label: cat.name }))
    }
])

const filteredCourses = computed(() => {
    return props.enrolledCourses.filter(course => {
        const matchesSearch = !searchQuery.value ||
            course.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            course.category?.name.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesStatus = filters.status === 'all' ||
            course.pivot?.status === filters.status

        const matchesCategory = filters.category === 'all' ||
            course.category_id === filters.category

        return matchesSearch && matchesStatus && matchesCategory
    })
})

// Reusable CSS classes
const buttonClasses = {
    primary: 'bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors',
    secondary: 'flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors'
}

const inputClasses = 'px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent'

// Methods
const toggleQuizPlaylist = (courseId) => {
    expandedCourse.value = expandedCourse.value === courseId ? null : courseId
}

const handleContinueLearning = (courseId) => {
    // Navigate to course learning page
    const course = props.enrolledCourses.find(c => c.id === courseId)
    if (course) {
        router.visit(route('courses.player', {
            courseId: course.id,
            courseSlug: course.slug
        }))
    }
}

const handleQuizCreated = (quiz) => {
    // Handle quiz creation success
    showQuizCreationModal.value = false
    // Optionally refresh the course data or show success message
    console.log('Quiz created successfully:', quiz)
}

// Get completed quiz count
const getCompletedQuizCount = (quizzes) => {
    return quizzes.filter(quiz => quiz.is_completed).length
}

// Handle quiz events
const handleQuizStarted = (quiz) => {
    console.log('Quiz started:', quiz.title)
}

const handleQuizSelected = (quiz) => {
    console.log('Quiz selected:', quiz.title)
}

const loadMoreSuggestions = async () => {
    try {
        const response = await axios.get(route('my-courses.suggestions'))
        suggestions.value = response.data
    } catch (error) {
        console.error('Failed to load more suggestions:', error)
    }
}
</script>
