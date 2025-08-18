<template>
  <div class="mb-8">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
      Question {{ questionNumber }} of {{ totalQuestions }}
    </h3>
    
    <p class="text-gray-700 dark:text-gray-300 mb-6 text-lg leading-relaxed">
      {{ question.text }}
    </p>
    
    <div class="space-y-3">
      <div 
        v-for="answer in question.answers"
        :key="answer.id"
        class="flex items-center p-4 border rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md"
        :class="getAnswerClasses(answer.id)"
        @click="selectAnswer(answer.id)"
      >
        <div class="flex-shrink-0 mr-4">
          <div 
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
            :class="getRadioClasses(answer.id)"
          >
            <div 
              v-if="isSelected(answer.id)" 
              class="w-2.5 h-2.5 rounded-full bg-indigo-600"
            ></div>
          </div>
        </div>
        
        <div class="flex-1">
          <p class="text-gray-900 dark:text-white font-medium">
            {{ answer.text }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  questionNumber: { type: Number, required: true },
  totalQuestions: { type: Number, required: true },
  selectedAnswers: { type: Array, required: true }
})

const emit = defineEmits(['select-answer'])

const selectedAnswer = computed(() => 
  props.selectedAnswers.find(a => a.question_id === props.question.id)
)

const isSelected = (answerId) => 
  selectedAnswer.value?.answer_id === answerId

const getAnswerClasses = (answerId) => {
  const baseClasses = 'border-gray-200 dark:border-slate-700'
  const selectedClasses = 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/50 ring-2 ring-indigo-200 dark:ring-indigo-800'
  const hoverClasses = 'hover:border-indigo-300 dark:hover:border-indigo-500'
  
  return isSelected(answerId) 
    ? selectedClasses 
    : `${baseClasses} ${hoverClasses}`
}

const getRadioClasses = (answerId) => {
  return isSelected(answerId)
    ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/50'
    : 'border-gray-300 dark:border-slate-600'
}

const selectAnswer = (answerId) => {
  emit('select-answer', {
    question_id: props.question.id,
    answer_id: answerId
  })
}
</script>