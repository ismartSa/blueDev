<template>
  <div
    class="quiz-card p-4 rounded-lg border transition-all cursor-pointer"
    :class="{
      'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/50': isSelected,
      'border-gray-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-500': !isSelected
    }"
    @click="$emit('selected')"
  >
    <div class="flex items-center space-x-3">
      <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900/50 p-2 rounded-full">
        <DocumentTextIcon class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
      </div>
      <div class="min-w-0">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ quiz.title }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ quiz.questions_count }} questions</p>
      </div>
    </div>
    <div class="mt-2 flex justify-between text-xs text-gray-500 dark:text-gray-400">
      <span class="flex items-center">
        <ClockIcon class="h-4 w-4 mr-1" />
        Time: {{ quiz.time_limit }} minutes
      </span>
      <span class="flex items-center">
        <CheckCircleIcon class="h-4 w-4 mr-1" />
        Passing Score: {{ quiz.passing_score }}%
      </span>
    </div>
  </div>
</template>

<script setup>
import { DocumentTextIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'

defineProps({
  quiz: {
    type: Object,
    required: true
  },
  isSelected: {
    type: Boolean,
    default: false
  }
})

defineEmits(['selected'])

// Remove this line if not used
// import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
</script>
