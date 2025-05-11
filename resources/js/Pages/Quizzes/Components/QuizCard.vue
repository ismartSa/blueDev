<template>
  <div
    class="quiz-card p-4 rounded-lg border transition-all cursor-pointer"
    :class="{
      'border-indigo-500 bg-indigo-50': isSelected,
      'border-gray-200 hover:border-indigo-300': !isSelected
    }"
    @click="$emit('selected')"
  >
    <div class="flex items-center space-x-3">
      <div class="flex-shrink-0 bg-indigo-100 p-2 rounded-full">
        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <div class="min-w-0">
        <h3 class="text-sm font-medium text-gray-900 truncate">{{ quiz.title }}</h3>
        <p class="text-sm text-gray-500">{{ quiz.questions_count }} أسئلة</p>
      </div>
    </div>
    <div class="mt-2 flex justify-between text-xs text-gray-500">
      <span>الوقت: {{ quiz.time_limit }} دقيقة</span>
      <span>درجة النجاح: {{ quiz.passing_score }}%</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  quiz: {
    type: Object,
    required: true
  },
  isSelected: Boolean
})

defineEmits(['selected'])
</script>
