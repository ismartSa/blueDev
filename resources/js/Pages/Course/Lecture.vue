<template>
    <div class="lecture">
      <h3 class="text-lg font-bold mt-4">{{ lecture.title }}</h3>
      <p class="text-gray-600">{{ lecture.description }}</p>

      <a :href="lecture.video_url" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        شاهد الفيديو
      </a>

      <p class="text-gray-600 mt-2">مدة الفيديو: {{ lecture.duration }} دقيقة</p>
    </div>
  </template>

  <script setup>
  import { defineProps } from 'vue';

  const props = defineProps({
    lecture: Object,
  });
  </script>
