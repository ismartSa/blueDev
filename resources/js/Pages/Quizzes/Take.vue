<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ quizCompleted ? 'Quiz Result' : 'Taking: ' + quiz.title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div v-if="!quizCompleted">
              <div v-if="currentQuestion" class="mb-6">
                <h3 class="text-lg font-semibold mb-2">
                  Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions.length }}:
                  {{ currentQuestion.question_title }}
                </h3>
                <div v-for="answer in currentQuestion.answers" :key="answer.id" class="mb-2">
                  <label class="inline-flex items-center">
                    <input
                      type="checkbox"
                      :value="answer.id"
                      v-model="userAnswers[currentQuestion.id]"
                      class="form-checkbox"
                      :disabled="timeRemaining <= 0"
                    >
                    <span class="ml-2">{{ answer.answer }}</span>
                  </label>
                </div>
              </div>
              <div class="mt-6 flex justify-between items-center">
                <button
                  @click="previousQuestion"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                  :disabled="currentQuestionIndex === 0"
                >
                  Previous
                </button>
                <div>
                  <p>Time remaining: {{ formatTime(timeRemaining) }}</p>
                  <p>Progress: {{ currentQuestionIndex + 1 }} / {{ quiz.questions.length }}</p>
                </div>
                <button
                  v-if="currentQuestionIndex < quiz.questions.length - 1"
                  @click="nextQuestion"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                  :disabled="timeRemaining <= 0"
                >
                  Next
                </button>
                <button
                  v-else
                  @click="submitQuiz"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                  :disabled="timeRemaining <= 0"
                >
                  Submit Quiz
                </button>
              </div>
            </div>
            <div v-else class="text-center">
              <h3 class="text-2xl font-semibold mb-4">Quiz Completed</h3>
              <p v-if="quizScore !== null" class="text-xl mb-2">
                Your score: {{ quizScore.toFixed(2) }}%
              </p>
              <p v-else class="text-xl mb-2">Score is being calculated...</p>
              <p class="text-lg mb-4">Passing score: {{ quiz.passing_score }}%</p>
              <p v-if="quizScore !== null && quizScore >= quiz.passing_score" class="text-green-600 text-xl mb-6">
                Congratulations! You passed the quiz.
              </p>
              <p v-else-if="quizScore !== null" class="text-red-600 text-xl mb-6">
                Unfortunately, you did not pass the quiz. You can try again later.
              </p>
              <button 
                @click="returnToQuizPage" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Return to Quiz Page
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  components: {
    AuthenticatedLayout,
  },
  props: {
    quiz: Object,
    attempt: Object,
    score: {
      type: Number,
      default: null
    },
  },
  setup(props) {
    const userAnswers = ref({})
    
    // تهيئة userAnswers لكل سؤال كمصفوفة فارغة
    props.quiz.questions.forEach(question => {
        userAnswers.value[question.id] = []
    })

    const quizCompleted = ref(false)
    const quizScore = ref(props.score !== null ? props.score : null)
    const timeRemaining = ref(props.quiz.time_limit * 60) // Convert minutes to seconds
    const currentQuestionIndex = ref(0)

    const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value])

    const form = useForm({
      answers: {},
    })

    const formatTime = (seconds) => {
      const minutes = Math.floor(seconds / 60)
      const remainingSeconds = seconds % 60
      return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
    }

    const submitQuiz = () => {
      form.answers = userAnswers.value
      form.post(route('quizzes.submit', [props.quiz.id, props.attempt.id]), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (response) => {
          quizCompleted.value = true
          if (response.props.score !== undefined) {
            quizScore.value = response.props.score
          }
        },
      })
    }

    const nextQuestion = () => {
      if (currentQuestionIndex.value < props.quiz.questions.length - 1) {
        currentQuestionIndex.value++
      }
    }

    const previousQuestion = () => {
      if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--
      }
    }

    const returnToQuizPage = () => {
      window.location.href = route('quizzes.show', props.quiz.id)
    }

    let timer
    onMounted(() => {
      timer = setInterval(() => {
        if (timeRemaining.value > 0) {
          timeRemaining.value--
        } else {
          submitQuiz()
        }
      }, 1000)
    })

    onUnmounted(() => {
      clearInterval(timer)
    })

    return {
      userAnswers,
      quizCompleted,
      quizScore,
      timeRemaining,
      currentQuestionIndex,
      currentQuestion,
      formatTime,
      submitQuiz,
      nextQuestion,
      previousQuestion,
      returnToQuizPage,
    }
  },
}
</script>
