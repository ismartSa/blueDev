<template>
  <Head :title="title" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
              <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h2>
              </div>
              <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <Link
                  :href="route('courses.create')"
                  class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex items-center gap-2"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  إضافة دورة جديدة
                </Link>
              </div>
            </div>

            <!-- Search and Filters -->
            <div class="mb-8">
              <form @submit.prevent="performSearch" class="flex gap-4">
                <div class="flex-1">
                  <input
                    v-model="data.params.search"
                    type="text"
                    placeholder="ابحث عن الدورات..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                  />
                </div>
                <button
                  type="submit"
                  class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition duration-150 ease-in-out flex items-center gap-2"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  بحث
                </button>
              </form>
            </div>

            <!-- Courses List -->
            <div v-if="courses.data.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 14h.01M12 16h.01M12 18h.01M12 20h.01M12 22h.01" />
              </svg>
              <p class="text-gray-600 text-lg">لم يتم العثور على دورات</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="course in courses"
                :key="course.id"
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 ease-in-out transform hover:-translate-y-1"
              >
                <div class="relative">
                  <img :src="course.image" :alt="course.title" class="w-full h-48 object-cover">
                  <div class="absolute top-4 right-4">
                    <span v-if="course.price" class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                      {{ course.price }} ريال
                    </span>
                    <span v-else class="bg-green-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                      مجاني
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
                        متابعة التعلم
                      </Link>
                      <button
                        v-else
                        @click="enrollCourse(course.id)"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex-1"
                      >
                        التسجيل في الدورة
                      </button>
                    </div>

                    <div class="flex justify-between gap-2">
                      <Link
                        :href="route('courses.details', { id: course.id, slug: course.slug })"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex-1 text-center"
                      >
                        تفاصيل الدورة
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

