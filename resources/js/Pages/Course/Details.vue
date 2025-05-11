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

const props = defineProps({
  course: Object,
  isEnrolled: Boolean
})

const enrollCourse = () => {
  // Implement enrollment logic here
};
</script>
