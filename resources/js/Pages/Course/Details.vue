<template>
  <div v-memo="[course.id]">
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
                <CourseInfoCard
                  :course="course"
                  :isEnrolled="isEnrolled"
                  @enroll="enrollCourse"
                />

                <CourseDetailsContent :course="course" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </div>
</template>

<script setup>
import CourseInfoCard from '@/Components/Course/CourseInfoCard.vue'
import CourseDetailsContent from '@/Components/Course/CourseDetailsContent.vue'
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
  course: Object,
  isEnrolled: Boolean
})

const enrollCourse = () => {
  // Implement enrollment logic here
};
</script>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    course: Object,
    breadcrumbs: Array,
});
</script>

<template>
    <Head :title="course.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="course.title" :breadcrumbs="breadcrumbs" />

        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-800 shadow sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-4">{{ course.title }}</h1>

                <div class="prose dark:prose-invert max-w-none">
                    <div v-html="course.description"></div>
                </div>

                <!-- Add more course details here as needed -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
