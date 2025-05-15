<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  lecture: {
    type: Object,
    required: true,
    validator: (lecture) => {
      return lecture.id && lecture.title
    }
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
            :href="route('courses.watch', { courseId: lecture.course_id, courseSlug: lecture.course_slug, lectureID: lecture.id })"
            class="mt-3 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors duration-200"
        >
            Watch Video
        </Link>
    </div>
  </div>
</template>
