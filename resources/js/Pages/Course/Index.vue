<template>
  <Head :title="title" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg">
          <div class="p-6 sm:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
              <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage and organize your courses</p>
              </div>
              <div class="mt-4 sm:mt-0">
                <Link
                  :href="route('courses.create')"
                  class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-150 ease-in-out w-full sm:w-auto justify-center"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add New Course
                </Link>
              </div>
            </div>

            <!-- Search and Filters -->
            <div class="mb-8">
              <form @submit.prevent="performSearch" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                  <input
                    v-model="data.params.search"
                    type="text"
                    placeholder="Search for courses..."
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                  />
                </div>
                <button
                  type="submit"
                  class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition duration-150 ease-in-out"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  Search
                </button>
              </form>
            </div>

            <!-- Courses List -->
            <div v-if="courses.data.length === 0" class="text-center py-12">
              <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No courses found</h3>
              <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first course</p>
              <Link
                :href="route('courses.create')"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-150 ease-in-out"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Course
              </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
              <div
                v-for="course in courses"
                :key="course.id"
                class="bg-white dark:bg-slate-700 rounded-xl shadow-sm border border-gray-200 dark:border-slate-600 overflow-hidden hover:shadow-lg transition-all duration-300 ease-in-out transform hover:-translate-y-1"
              >
                <div class="relative">
                  <img :src="course.image" :alt="course.title" class="w-full h-48 object-cover">
                  <div class="absolute top-4 right-4">
                    <span v-if="course.price" class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                      {{ course.price }} SAR
                    </span>
                    <span v-else class="bg-green-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                      Free
                    </span>
                  </div>
                </div>

                <div class="p-6">
                  <h4 class="text-xl font-bold mb-3 text-gray-900">{{ course.title }}</h4>
                  <p class="text-gray-600 mb-4 line-clamp-2">{{ course.description }}</p>

                  <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-center">
                      <Link
                        v-if="course.user_enrolled"
                        :href="route('courses.show', { id: course.id, slug: course.slug })"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex-1 text-center"
                      >
                        Continue Learning
                      </Link>
                      <button
                        v-else
                        @click="enrollCourse(course.id)"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex-1"
                      >
                        Enroll in Course
                      </button>
                    </div>

                    <div class="flex justify-between gap-2">
                      <Link
                        :href="route('courses.details', { id: course.id, slug: course.slug })"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex-1 text-center"
                      >
                        Course Details
                      </Link>
                      <button
                        @click="editCourse(course.id)"
                        class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 font-medium p-2 rounded-lg transition duration-150 ease-in-out"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <DangerButton
                        @click="deleteCourse(course.id)"
                        class="p-2"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </DangerButton>
                    </div>
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
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { debounce } from 'lodash'

const props = defineProps({
  title: String,
  courses: Object,
  filters: Object,
  breadcrumbs: Array,
})

const data = reactive({
  params: {
    search: props.filters.search || '',
    field: props.filters.field || 'created_at',
    order: props.filters.order || 'desc',
    perPage: props.filters.perPage || 10,
  },
  selectedId: [],
  multipleSelect: false,
})

const performSearch = debounce(() => {
  router.get(route('courses.index'), pickBy(data.params), {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

const order = (field) => {
  data.params.field = field
  data.params.order = data.params.order === 'asc' ? 'desc' : 'asc'
}

const selectAll = (event) => {
  data.selectedId = event.target.checked ? props.courses.data.map(course => course.id) : []
  data.multipleSelect = event.target.checked
}

const select = () => {
  data.multipleSelect = props.courses.data.length === data.selectedId.length
}

const editCourse = (id) => router.get(route('courses.edit', id))
const deleteCourse = (id) => router.delete(route('courses.destroy', id))

const enrollCourse = async (id) => {
  try {
    await router.post(route('courses.enroll', id))
  } catch (error) {
    console.error('Error enrolling in course:', error)
  }
}
</script>

