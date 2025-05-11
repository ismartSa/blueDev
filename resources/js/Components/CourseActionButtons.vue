<template>
  <div>
    <button
      v-if="!isEnrolled"
      @click="$emit('enroll')"
      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105 mb-2"
    >
      {{ lang().button.enroll_now }}
    </button>

    <Link
      v-else
      :href="route('course.player', [course.id, course.slug])"
      class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105 mb-2"
    >
      {{ lang().button.go_to_course }}
    </Link>

    <Link
      v-if="isEnrolled"
      :href="route('course.quizzes', course.id)"
      class="w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105"
    >
      Course Quizzes
    </Link>
  </div>
</template>

<script setup>
defineProps({
  isEnrolled: Boolean,
  course: Object
})
</script>
