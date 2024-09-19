
<template>
    <div class="section">
      <h3 class="text-lg font-bold mt-4">{{ section.title }}</h3>
      <p class="text-gray-600">{{ section.description }}</p>

      <ul class="list-disc ml-4">
        <li v-for="(lecture, index) in lectures" :key="index">
          <Lecture :lecture="lecture" />
        </li>
      </ul>
    </div>
  </template>

  <script setup>
  import { defineProps } from 'vue';
  import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
  import Lecture from '@/Pages/Course/Lecture.vue';

  const props = defineProps({
    section: Object,
    lectures: Array,
  });
  </script>
