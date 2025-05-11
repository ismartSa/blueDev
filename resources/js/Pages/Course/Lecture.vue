<script setup>
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
    <div class="lecture bg-gray-50 p-4 rounded-lg">
        <div class="flex justify-between items-center">
            <h4 class="text-lg font-semibold">{{ lecture.title }}</h4>
            <span class="text-sm text-gray-500">{{ lecture.duration }} دقيقة</span>
        </div>
        <p class="text-gray-600 mt-2">{{ lecture.description }}</p>
        <a
            :href="lecture.video_url"
            target="_blank"
            class="mt-3 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
        >
            شاهد الفيديو
        </a>
    </div>
</template>
