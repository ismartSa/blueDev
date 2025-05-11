<template>
  <div class="md:w-1/3 pr-8">
    <img :src="course.image" :alt="course.title" class="w-full h-auto rounded-lg shadow-md mb-6">
    <div class="bg-gray-100 rounded-lg p-4 mb-6">
      <CourseInfoItem
        icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
        :text="`${lang().label.duration}: ${course.duration}`"
      />
      <CourseInfoItem
        icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
        :text="`${lang().label.enrolled}: ${course.enrollments_count}`"
      />
      <CourseInfoItem
        icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
        :text="`${lang().label.lectures}: ${course.lectures_count}`"
      />
    </div>
    <div class="text-center">
      <span v-if="course.price === 0" class="text-2xl font-bold text-green-600 mb-4 block">FREE</span>
      <span v-else class="text-2xl font-bold text-green-600 mb-4 block">${{ course.price.toFixed(2) }}</span>
      <CourseActionButtons
        :isEnrolled="isEnrolled"
        :course="course"
        @enroll="$emit('enroll')"
      />
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const translations = computed(() => page.props.translations?.course || {})

defineProps({
  course: Object,
  isEnrolled: Boolean
})

const lang = () => translations.value
</script>

<template>
  <div class="md:w-1/3 pr-8">
    <img :src="course.image" :alt="course.title" class="w-full h-auto rounded-lg shadow-md mb-6">
    <div class="bg-gray-100 rounded-lg p-4 mb-6">
      <CourseInfoItem
        icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
        :text="`${lang().label.duration}: ${course.duration}`"
      />
      <CourseInfoItem
        icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
        :text="`${lang().label.enrolled}: ${course.enrollments_count}`"
      />
      <CourseInfoItem
        icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
        :text="`${lang().label.lectures}: ${course.lectures_count}`"
      />
    </div>
    <div class="text-center">
      <span v-if="course.price === 0" class="text-2xl font-bold text-green-600 mb-4 block">FREE</span>
      <span v-else class="text-2xl font-bold text-green-600 mb-4 block">${{ course.price.toFixed(2) }}</span>
      <CourseActionButtons
        :isEnrolled="isEnrolled"
        :course="course"
        @enroll="$emit('enroll')"
      />
    </div>
  </div>
</template>
