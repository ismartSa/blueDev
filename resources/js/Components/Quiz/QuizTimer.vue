<template>
  <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
      {{ title }}
    </h2>
    
    <div class="flex items-center gap-4">
      <div class="text-lg font-medium text-gray-900 dark:text-white">
        Time Remaining: {{ formatTime(timeRemaining) }}
      </div>
      
      <div class="text-sm text-gray-600 dark:text-gray-400">
        {{ currentQuestion + 1 }} of {{ totalQuestions }}
      </div>
    </div>
  </div>
  
  <!-- Progress Bar -->
  <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-2.5 mb-6">
    <div 
      class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" 
      :style="{ width: `${progress}%` }"
    ></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  timeRemaining: { type: Number, required: true },
  currentQuestion: { type: Number, required: true },
  totalQuestions: { type: Number, required: true }
})

const progress = computed(() => 
  Math.round((props.currentQuestion / props.totalQuestions) * 100)
)

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}
</script>