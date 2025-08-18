<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Playlist Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-xl font-bold mb-2">{{ course.title }} - Quiz Playlist</h3>
          <p class="text-blue-100">{{ completedQuizzes }}/{{ totalQuizzes }} quizzes completed</p>
        </div>
        <div class="text-right">
          <div class="text-2xl font-bold">{{ overallProgress }}%</div>
          <div class="text-sm text-blue-100">Overall Progress</div>
        </div>
      </div>
      
      <!-- Progress Bar -->
      <div class="mt-4">
        <div class="w-full bg-blue-500 bg-opacity-30 rounded-full h-2">
          <div 
            class="bg-white h-2 rounded-full transition-all duration-500 ease-out"
            :style="{ width: overallProgress + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Quiz List -->
    <div class="max-h-96 overflow-y-auto">
      <div 
        v-for="(quiz, index) in quizzes" 
        :key="quiz.id"
        class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors"
        :class="{ 'bg-blue-50': currentQuizIndex === index }"
      >
        <div class="p-4 flex items-center justify-between">
          <!-- Quiz Info -->
          <div class="flex items-center space-x-4 flex-1">
            <!-- Play/Status Icon -->
            <div class="flex-shrink-0">
              <div 
                v-if="quiz.is_completed"
                class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <div 
                v-else-if="currentQuizIndex === index"
                class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white animate-pulse"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                </svg>
              </div>
              <div 
                v-else
                class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600"
              >
                <span class="text-sm font-medium">{{ index + 1 }}</span>
              </div>
            </div>

            <!-- Quiz Details -->
            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-medium text-gray-900 truncate">{{ quiz.title }}</h4>
              <div class="flex items-center space-x-4 mt-1">
                <span class="text-xs text-gray-500">
                  {{ quiz.questions_count || 0 }} questions
                </span>
                <span class="text-xs text-gray-500">
                  {{ quiz.time_limit || 30 }} min
                </span>
                <span v-if="quiz.is_completed" class="text-xs text-green-600 font-medium">
                  Score: {{ quiz.last_score || 0 }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center space-x-2">
            <button
              v-if="quiz.is_completed"
              @click="reviewQuiz(quiz)"
              class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
            >
              Review
            </button>
            <button
              @click="startQuiz(quiz, index)"
              class="px-4 py-2 text-sm rounded-lg transition-colors"
              :class="quiz.is_completed 
                ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' 
                : 'bg-blue-500 text-white hover:bg-blue-600'"
            >
              {{ quiz.is_completed ? 'Retake' : 'Start' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Playlist Controls -->
    <div class="bg-gray-50 p-4 border-t">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <button
            @click="previousQuiz"
            :disabled="currentQuizIndex <= 0"
            class="p-2 rounded-full transition-colors"
            :class="currentQuizIndex <= 0 
              ? 'text-gray-400 cursor-not-allowed' 
              : 'text-gray-600 hover:bg-gray-200'"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
          
          <button
            @click="nextQuiz"
            :disabled="currentQuizIndex >= totalQuizzes - 1"
            class="p-2 rounded-full transition-colors"
            :class="currentQuizIndex >= totalQuizzes - 1 
              ? 'text-gray-400 cursor-not-allowed' 
              : 'text-gray-600 hover:bg-gray-200'"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <div class="text-center flex-1">
          <div class="text-sm text-gray-600">
            Quiz {{ currentQuizIndex + 1 }} of {{ totalQuizzes }}
          </div>
          <div v-if="currentQuiz" class="text-xs text-gray-500 mt-1">
            {{ currentQuiz.title }}
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button
            @click="shuffleQuizzes"
            class="p-2 text-gray-600 hover:bg-gray-200 rounded-full transition-colors"
            title="Shuffle Quizzes"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
            </svg>
          </button>
          
          <button
            v-if="hasIncompleteQuizzes"
            @click="startNextIncomplete"
            class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition-colors"
          >
            Continue Learning
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  quizzes: {
    type: Array,
    required: true
  },
  initialQuizIndex: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['quiz-selected', 'quiz-started'])

const currentQuizIndex = ref(props.initialQuizIndex)

// Computed properties
const totalQuizzes = computed(() => props.quizzes.length)
const completedQuizzes = computed(() => props.quizzes.filter(quiz => quiz.is_completed).length)
const overallProgress = computed(() => {
  if (totalQuizzes.value === 0) return 0
  return Math.round((completedQuizzes.value / totalQuizzes.value) * 100)
})
const currentQuiz = computed(() => props.quizzes[currentQuizIndex.value])
const hasIncompleteQuizzes = computed(() => props.quizzes.some(quiz => !quiz.is_completed))

// Methods
const startQuiz = (quiz, index) => {
  currentQuizIndex.value = index
  emit('quiz-started', quiz)
  
  // Navigate to quiz show page
  router.visit(route('quizzes.show', quiz.id))
}

const reviewQuiz = (quiz) => {
  // Navigate to quiz show page for review
  router.visit(route('quizzes.show', quiz.id))
}

const previousQuiz = () => {
  if (currentQuizIndex.value > 0) {
    currentQuizIndex.value--
    emit('quiz-selected', currentQuiz.value)
  }
}

const nextQuiz = () => {
  if (currentQuizIndex.value < totalQuizzes.value - 1) {
    currentQuizIndex.value++
    emit('quiz-selected', currentQuiz.value)
  }
}

const shuffleQuizzes = () => {
  // Find next incomplete quiz for better UX
  const incompleteIndex = props.quizzes.findIndex(quiz => !quiz.is_completed)
  if (incompleteIndex !== -1) {
    currentQuizIndex.value = incompleteIndex
    emit('quiz-selected', currentQuiz.value)
  }
}

const startNextIncomplete = () => {
  const nextIncomplete = props.quizzes.find(quiz => !quiz.is_completed)
  if (nextIncomplete) {
    const index = props.quizzes.indexOf(nextIncomplete)
    startQuiz(nextIncomplete, index)
  }
}
</script>

<style scoped>
/* Custom scrollbar for quiz list */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>