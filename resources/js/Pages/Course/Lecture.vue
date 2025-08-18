<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  lecture: {
    type: Object,
    required: true,
    validator: (lecture) => {
      return lecture.id && lecture.title
    }
  },
  courseSlug: {
    type: String,
    required: true
  }
})
</script>

<template>
  <div class="lecture-card">
    <div class="lecture bg-gray-50 dark:bg-slate-600 p-4 rounded-lg">
        <div class="flex justify-between items-center">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ lecture.title }}</h4>
            <span class="text-sm text-gray-500 dark:text-gray-300">{{ lecture.duration }} minutes</span>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-2">{{ lecture.description }}</p>
        <Link
            :href="route('courses.watch', { courseId: lecture.course_id, courseSlug: courseSlug, lectureID: lecture.id })"
            class="mt-3 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors duration-200 flex items-center space-x-2"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
            </svg>
            <span>Play Video</span>
        </Link>
    </div>
  </div>
</template>
