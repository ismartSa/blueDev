<template>
  <Head :title="pageTitle" />
  <component :is="isAuthenticated ? AuthenticatedLayout : GuestLayout">
    <template #header>
      <Breadcrumb :title="pageTitle" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 mb-8 text-white">
          <div class="max-w-3xl">
            <h1 class="text-4xl font-bold mb-4">{{ content.hero.title }}</h1>
            <p class="text-xl opacity-90 mb-6">{{ content.hero.subtitle }}</p>
            <div class="flex items-center gap-6 text-sm">
              <div class="flex items-center gap-2">
                <BookOpenIcon class="h-5 w-5" />
                <span>{{ stats.totalCourses }}+ {{ content.stats.courses }}</span>
              </div>
              <div class="flex items-center gap-2">
                <UsersIcon class="h-5 w-5" />
                <span>{{ stats.totalStudents }}+ {{ content.stats.students }}</span>
              </div>
              <div class="flex items-center gap-2">
                <AcademicCapIcon class="h-5 w-5" />
                <span>{{ stats.totalInstructors }}+ {{ content.stats.instructors }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
          <form @submit.prevent="performSearch" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
              <!-- Search Input -->
              <div class="flex-1">
                <div class="relative">
                  <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                  <input
                    v-model="filters.search"
                    type="text"
                    :placeholder="content.search.placeholder"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                  />
                </div>
              </div>
              
              <!-- Category Filter -->
              <select
                v-model="filters.category"
                class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white min-w-[200px]"
              >
                <option value="">{{ content.filters.allCategories }}</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              
              <!-- Price Filter -->
              <select
                v-model="filters.price"
                class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white min-w-[150px]"
              >
                <option value="">{{ content.filters.allPrices }}</option>
                <option value="free">{{ content.filters.free }}</option>
                <option value="paid">{{ content.filters.paid }}</option>
              </select>
              
              <!-- Search Button -->
              <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center gap-2 font-medium"
              >
                <MagnifyingGlassIcon class="h-5 w-5" />
                {{ content.search.button }}
              </button>
            </div>
          </form>
        </div>

        <!-- Results Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ content.results.title }}</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              {{ content.results.showing }} {{ courses.data.length }} {{ content.results.of }} {{ courses.total }} {{ content.results.courses }}
            </p>
          </div>
          
          <!-- Sort Options -->
          <select
            v-model="filters.sort"
            @change="performSearch"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="latest">{{ content.sort.latest }}</option>
            <option value="popular">{{ content.sort.popular }}</option>
            <option value="title">{{ content.sort.title }}</option>
            <option value="price_low">{{ content.sort.priceLow }}</option>
            <option value="price_high">{{ content.sort.priceHigh }}</option>
          </select>
        </div>

        <!-- Courses Grid -->
        <div v-if="courses.data.length === 0" class="text-center py-16">
          <BookOpenIcon class="h-16 w-16 mx-auto text-gray-400 mb-4" />
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ content.empty.title }}</h3>
          <p class="text-gray-600 dark:text-gray-400">{{ content.empty.message }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
          <CourseCard
            v-for="course in courses.data"
            :key="course.id"
            :course="course"
            @enroll="enrollCourse"
          />
        </div>

        <!-- Pagination -->
        <div v-if="courses.last_page > 1" class="flex justify-center">
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

// Check if user is authenticated
const isAuthenticated = computed(() => {
  return page.props.auth && page.props.auth.user
})

// Reactive data
const filters = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  price: props.filters.price || '',
  sort: props.filters.sort || 'latest'
})

// Computed properties
const pageTitle = computed(() => content.value.pageTitle)
const breadcrumbs = computed(() => [
  { name: content.value.breadcrumbs.home, href: route('dashboard') },
  { name: content.value.breadcrumbs.courses }
])

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

// Methods
const performSearch = debounce(() => {
  router.get(route('courses.explore'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}, 300)

const enrollCourse = (courseId) => {
  router.post(route('courses.enroll', courseId), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by CourseCard component
    }
  })
}

// Watch for filter changes
watch(() => [filters.category, filters.price, filters.sort], () => {
  performSearch()
})
</script>