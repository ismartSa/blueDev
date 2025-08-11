<script setup>
import { computed } from 'vue'

const props = defineProps({
  videoUrl: {
    type: String,
    required: true,
    validator: value => value.startsWith('http') || value.startsWith('/')
  },
  lectureTitle: {
    type: String,
    required: true,
    default: 'Untitled Lecture'
  }
})

const emit = defineEmits(['videoEnded', 'timeUpdate'])

const videoClasses = computed(() => [
  'w-full',
  'h-auto',
  'rounded-lg',
  'shadow-lg',
  'focus:outline-none'
])
</script>

<template>
  <div class="lg:col-span-2 space-y-4">
    <div class="relative aspect-video">
      <video
        :class="videoClasses"
        controls
        @ended="emit('videoEnded')"
        @timeupdate="emit('timeUpdate', $event)"
      >
        <source :src="videoUrl" type="video/mp4">
        <track kind="captions" src="" label="English" srclang="en" default>
        Your browser does not support HTML5 video.
      </video>
    </div>

    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
      {{ lectureTitle }}
    </h3>
  </div>
</template>
