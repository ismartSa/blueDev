<template>
  <Head :title="pageTitle" />
  <component :is="layoutComponent">
    <template #header>
      <Breadcrumb :title="pageTitle" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-8">
      <div :class="containerClasses">
        <!-- Hero Section -->
        <div :class="heroClasses">
          <div class="max-w-3xl">
            <h1 :class="heroTitleClasses">{{ content.hero.title }}</h1>
            <p :class="heroSubtitleClasses">{{ content.hero.subtitle }}</p>
            <div :class="statsContainerClasses">
              <div v-for="stat in heroStats" :key="stat.key" :class="statItemClasses">
                <component :is="stat.icon" class="h-5 w-5" />
                <span>{{ stat.value }}+ {{ stat.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filters -->
        <div :class="searchContainerClasses">
          <form @submit.prevent="performSearch" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
              <!-- Search Input -->
              <div class="flex-1">
                <div class="relative">
                  <MagnifyingGlassIcon :class="searchIconClasses" />
                  <input
                    v-model="filters.search"
                    type="text"
                    :placeholder="content.search.placeholder"
                    :class="inputClasses"
                  />
                </div>
              </div>
              
              <!-- Filter Selects -->
              <select v-for="filter in filterOptions" :key="filter.key"
                v-model="filters[filter.key]"
                :class="selectClasses(filter.minWidth)"
              >
                <option value="">{{ filter.allOption }}</option>
                <option v-for="option in filter.options" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              
              <!-- Search Button -->
              <button type="submit" :class="searchButtonClasses">
                <MagnifyingGlassIcon class="h-5 w-5" />
                {{ content.search.button }}
              </button>
            </div>
          </form>
        </div>

        <!-- Results Header -->
        <div :class="resultsHeaderClasses">
          <div>
            <h2 :class="resultsTitleClasses">{{ content.results.title }}</h2>
            <p :class="resultsSubtitleClasses">{{ resultsText }}</p>
          </div>
          
          <!-- Sort Options -->
          <select v-model="filters.sort" @change="performSearch" :class="sortSelectClasses">
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Courses Grid -->
        <div v-if="!hasCourses" :class="emptyStateClasses">
          <BookOpenIcon :class="emptyIconClasses" />
          <h3 :class="emptyTitleClasses">{{ content.empty.title }}</h3>
          <p :class="emptyMessageClasses">{{ content.empty.message }}</p>
        </div>

        <div v-else :class="coursesGridClasses">
          <CourseCard
            v-for="course in courses.data"
            :key="course.id"
            :course="course"
            @enroll="handleEnrollment"
          />
        </div>

        <!-- Pagination -->
        <div v-if="showPagination" class="flex justify-center">
          <Pagination :links="courses" :filters="filters" />
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import CourseCard from '@/Components/Course/CourseCard.vue'
import Pagination from '@/Components/Pagination.vue'
import {
  BookOpenIcon,
  UsersIcon,
  AcademicCapIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  courses: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) }
})

// Page data
const page = usePage()

// Reactive data
const filters = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  price: props.filters.price || '',
  sort: props.filters.sort || 'latest'
})

// Loading state for enrollment
const enrolling = ref(false)

// Authentication and layout
const isAuthenticated = computed(() => page.props.auth?.user)
const layoutComponent = computed(() => isAuthenticated.value ? AuthenticatedLayout : GuestLayout)

// Page metadata
const pageTitle = computed(() => content.value.pageTitle)
const breadcrumbs = computed(() => [
  { name: content.value.breadcrumbs.home, href: route('dashboard') },
  { name: content.value.breadcrumbs.courses }
])

// Data state
const hasCourses = computed(() => props.courses.data.length > 0)
const showPagination = computed(() => props.courses.last_page > 1)

// Dynamic class bindings for performance
const baseClasses = {
  container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
  card: 'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700',
  input: 'border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors',
  button: 'rounded-lg transition-colors flex items-center gap-2 font-medium',
  text: 'transition-colors'
}

const containerClasses = computed(() => baseClasses.container)
const heroClasses = computed(() => 'bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 mb-8 text-white')
const heroTitleClasses = computed(() => 'text-4xl font-bold mb-4')
const heroSubtitleClasses = computed(() => 'text-xl opacity-90 mb-6')
const statsContainerClasses = computed(() => 'flex items-center gap-6 text-sm')
const statItemClasses = computed(() => 'flex items-center gap-2')

const searchContainerClasses = computed(() => `${baseClasses.card} p-6 mb-8`)
const searchIconClasses = computed(() => 'absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400')
const inputClasses = computed(() => `w-full pl-10 pr-4 py-3 ${baseClasses.input}`)
const selectClasses = computed(() => (minWidth) => `px-4 py-3 ${baseClasses.input} ${minWidth}`)
const searchButtonClasses = computed(() => `bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 ${baseClasses.button}`)

const resultsHeaderClasses = computed(() => 'flex items-center justify-between mb-6')
const resultsTitleClasses = computed(() => `text-2xl font-bold text-gray-900 dark:text-white ${baseClasses.text}`)
const resultsSubtitleClasses = computed(() => `text-gray-600 dark:text-gray-400 mt-1 ${baseClasses.text}`)
const sortSelectClasses = computed(() => `px-4 py-2 ${baseClasses.input}`)

const emptyStateClasses = computed(() => 'text-center py-16')
const emptyIconClasses = computed(() => 'h-16 w-16 mx-auto text-gray-400 mb-4')
const emptyTitleClasses = computed(() => `text-xl font-semibold text-gray-900 dark:text-white mb-2 ${baseClasses.text}`)
const emptyMessageClasses = computed(() => `text-gray-600 dark:text-gray-400 ${baseClasses.text}`)
const coursesGridClasses = computed(() => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8')

// Dynamic data for hero stats
const heroStats = computed(() => [
  { key: 'courses', icon: BookOpenIcon, value: props.stats.totalCourses, label: content.value.stats.courses },
  { key: 'students', icon: UsersIcon, value: props.stats.totalStudents, label: content.value.stats.students },
  { key: 'instructors', icon: AcademicCapIcon, value: props.stats.totalInstructors, label: content.value.stats.instructors }
])

// Dynamic filter options
const filterOptions = computed(() => [
  {
    key: 'category',
    allOption: content.value.filters.allCategories,
    options: props.categories.map(cat => ({ value: cat.id, label: cat.name })),
    minWidth: 'min-w-[200px]'
  },
  {
    key: 'price',
    allOption: content.value.filters.allPrices,
    options: [
      { value: 'free', label: content.value.filters.free },
      { value: 'paid', label: content.value.filters.paid }
    ],
    minWidth: 'min-w-[150px]'
  }
])

// Dynamic sort options
const sortOptions = computed(() => [
  { value: 'latest', label: content.value.sort.latest },
  { value: 'popular', label: content.value.sort.popular },
  { value: 'title', label: content.value.sort.title },
  { value: 'price_low', label: content.value.sort.priceLow },
  { value: 'price_high', label: content.value.sort.priceHigh }
])

// Dynamic results text
const resultsText = computed(() => 
  `${content.value.results.showing} ${props.courses.data.length} ${content.value.results.of} ${props.courses.total} ${content.value.results.courses}`
)

// Bilingual content
const content = computed(() => ({
  pageTitle: 'Explore Courses',
  hero: {
    title: 'Discover Amazing Courses',
    subtitle: 'Expand your knowledge with our comprehensive course library'
  },
  stats: {
    courses: 'Courses',
    students: 'Students',
    instructors: 'Instructors'
  },
  search: {
    placeholder: 'Search courses...',
    button: 'Search'
  },
  filters: {
    allCategories: 'All Categories',
    allPrices: 'All Prices',
    free: 'Free',
    paid: 'Paid'
  },
  results: {
    title: 'Available Courses',
    showing: 'Showing',
    of: 'of',
    courses: 'courses'
  },
  sort: {
    latest: 'Latest',
    popular: 'Most Popular',
    title: 'Title A-Z',
    priceLow: 'Price: Low to High',
    priceHigh: 'Price: High to Low'
  },
  empty: {
    title: 'No courses found',
    message: 'Try adjusting your search criteria or browse all courses'
  },
  breadcrumbs: {
    home: 'Dashboard',
    courses: 'Explore Courses'
  }
}))

// Optimized methods with better error handling
const performSearch = debounce(() => {
  router.get(route('courses.explore'), filters, {
    preserveState: true,
    preserveScroll: true,
    onError: (errors) => {
      console.error('Search failed:', errors)
    }
  })
}, 300)

const handleEnrollment = async (courseId) => {
  if (enrolling.value) return
  
  enrolling.value = true
  try {
    await router.post(route('courses.enroll', courseId), {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Enrollment success handled by CourseCard component
      },
      onError: (errors) => {
        console.error('Enrollment failed:', errors)
      }
    })
  } finally {
    enrolling.value = false
  }
}

// Watch for filter changes
watch(() => [filters.category, filters.price, filters.sort], () => {
  performSearch()
})
</script>