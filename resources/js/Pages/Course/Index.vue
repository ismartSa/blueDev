<template>
  <Head :title="title" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold">Courses</h3>
              <Link
                :href="route('courses.create')"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out"
              >
                Create Course
              </Link>
            </div>

            <div class="mb-6">
              <form @submit.prevent="performSearch" class="flex items-center">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search courses..."
                  class="flex-grow px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                />
                <button
                  type="submit"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </button>
              </form>
            </div>

            <div v-if="courses.length === 0" class="text-center py-4 text-gray-500">
              No courses found.
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="course in courses"
                :key="course.id"
                class="bg-white rounded-lg shadow-md overflow-hidden transition duration-300 ease-in-out transform hover:scale-105"
              >
                <img :src="course.image" :alt="course.title" class="w-full h-48 object-cover">
                <div class="p-4">
                  <h4 class="text-xl font-bold mb-2 text-gray-800">{{ course.title }}</h4>
                  <p class="text-gray-600 mb-4 text-sm">{{ course.description }}</p>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-700 font-semibold">{{ course.price ? `$${course.price}` : 'Free' }}</span>
                    <div class="space-x-2">
                      <Link
                        v-if="course.user_enrolled"
                        :href="route('courses.show', [course.id, course.slug])"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-sm transition duration-150 ease-in-out"
                      >
                        View
                      </Link>
                      <button
                        v-else
                        @click="enrollCourse(course.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm transition duration-150 ease-in-out"
                      >
                        Enroll
                      </button>
                      <Link
                        :href="route('course.details', [course.id, course.slug])"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded text-sm transition duration-150 ease-in-out"
                      >
                        Details
                      </Link>
                    </div>
                  </div>
                  <div class="mt-4 flex justify-end space-x-2">
                    <button
                      @click="editCourse(course.id)"
                      class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded text-xs transition duration-150 ease-in-out"
                    >
                      Edit
                    </button>
                    <DangerButton
                      @click="deleteCourse(course.id)"
                      class="text-xs py-1 px-2"
                    >
                      Delete
                    </DangerButton>
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
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
  title: String,
  courses: {
    type: Array,
    default: () => []
  },
  filters: Object,
  breadcrumbs: Array,
});

const searchQuery = ref(props.filters.search || '');
const courses = ref(props.courses);

const performSearch = debounce(() => {
  router.get(
    route('courses.index'),
    { search: searchQuery.value },
    { preserveState: true, preserveScroll: true }
  );
}, 300);

watch(searchQuery, performSearch);

const editCourse = (courseId) => {
  router.get(route('courses.edit', courseId));
};

const deleteCourse = (courseId) => {
  if (confirm('Are you sure you want to delete this course?')) {
    router.delete(route('courses.destroy', courseId), {
      preserveState: true,
      preserveScroll: true,
    });
  }
};

const enrollCourse = (courseId) => {
  router.post(route('courses.enroll', courseId), {}, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

