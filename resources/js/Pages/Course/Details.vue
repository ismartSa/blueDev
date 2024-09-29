<template>
  <Head :title="course.title" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ course.title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6 sm:p-10">
            <div class="flex flex-col md:flex-row">
              <!-- Course Image and Basic Info -->
              <div class="md:w-1/3 pr-8">
                <img :src="course.image" :alt="course.title" class="w-full h-auto rounded-lg shadow-md mb-6">
                <div class="bg-gray-100 rounded-lg p-4 mb-6">
                  <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm text-gray-600">Duration: {{ course.duration }}</span>
                  </div>
                  <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="text-sm text-gray-600">Enrolled: {{ course.enrollments_count }}</span>
                  </div>
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span class="text-sm text-gray-600">Lectures: {{ course.lectures_count }}</span>
                  </div>
                </div>
                <div class="text-center">
                  <span v-if="course.price === 0" class="text-2xl font-bold text-green-600 mb-4 block">FREE</span>
                  <span v-else class="text-2xl font-bold text-green-600 mb-4 block">${{ course.price.toFixed(2) }}</span>
                  <button
                    v-if="!isEnrolled"
                    @click="enrollCourse"
                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105"
                  >
                    Enroll Now
                  </button>
                  <Link
                    v-else
                    :href="route('course.player', [course.id, course.slug])"
                    class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105 inline-block mb-2"
                  >
                    Go to Course
                  </Link>
                  <!-- New button for Course Quizzes -->
                  <Link
                    v-if="isEnrolled"
                    :href="route('course.quizzes', course.id)"
                    class="w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105 inline-block"
                  >
                    Course Quizzes
                  </Link>
                </div>
              </div>

              <!-- Course Details -->
              <div class="md:w-2/3 mt-6 md:mt-0">
                <h1 class="text-3xl font-bold mb-4">{{ course.title }}</h1>
                <p class="text-gray-600 mb-6">{{ course.description }}</p>

                <!-- Course Overview -->
                <div class="mb-8">
                  <h3 class="text-xl font-semibold mb-4">Course Overview</h3>
                  <p class="text-gray-600">{{ course.overview }}</p>
                </div>

                <!-- What You'll Learn -->
                <div class="mb-8">
                  <h3 class="text-xl font-semibold mb-4">What You'll Learn</h3>
                  <ul class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <li v-for="(item, index) in course.learning_objectives" :key="index" class="flex items-start">
                      <svg class="w-5 h-5 text-green-500 mr-2 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                      <span>{{ item }}</span>
                    </li>
                  </ul>
                </div>

                <!-- Course Content -->
                <div>
                  <h3 class="text-xl font-semibold mb-4">Course Content</h3>
                  <div v-for="(section, index) in course.sections" :key="index" class="mb-4">
                    <div @click="toggleSection(index)" class="flex justify-between items-center bg-gray-100 p-3 rounded-lg cursor-pointer">
                      <h4 class="font-semibold">{{ section.title }}</h4>
                      <svg :class="{'transform rotate-180': openSections[index]}" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div v-show="openSections[index]" class="mt-2 ml-4">
                      <ul>
                        <li v-for="lecture in section.lectures" :key="lecture.id" class="flex items-center py-2">
                          <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                          <span>{{ lecture.title }}</span>
                        </li>
                      </ul>
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
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  course: Object,
  isEnrolled: Boolean,
});

const openSections = ref(props.course.sections.map(() => false));

const toggleSection = (index) => {
  openSections.value[index] = !openSections.value[index];
};

const enrollCourse = () => {
  // Implement enrollment logic here
};
</script>
