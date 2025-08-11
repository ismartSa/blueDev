<template>
  <Head :title="`Take Quiz - ${quiz.title}`" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="`Take Quiz - ${quiz.title}`" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Quiz Header -->
            <div class="mb-8">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ quiz.title }}
                </h2>
                <div class="text-lg font-medium text-gray-900 dark:text-white">
                  Time Remaining: {{ formatTime(timeRemaining) }}
                </div>
              </div>
              <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-2.5">
                <div class="bg-indigo-600 h-2.5 rounded-full"
                     :style="{ width: `${progress}%` }">
                </div>
              </div>
            </div>

            <!-- Question -->
            <div v-if="currentQuestion" class="mb-8">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions.length }}
              </h3>
              <p class="text-gray-700 dark:text-gray-300 mb-6">
                {{ currentQuestion.text }}
              </p>
              <div class="space-y-4">
                <div v-for="answer in currentQuestion.answers"
                     :key="answer.id"
                     class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                     :class="{
                         'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/50': form.answers.find(a => a.question_id === currentQuestion.id && a.answer_id === answer.id),
                         'border-gray-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-500': !form.answers.find(a => a.question_id === currentQuestion.id && a.answer_id === answer.id)
                     }"
                     @click="selectAnswer(answer.id)">
                  <div class="flex-1">
                    <p class="text-gray-900 dark:text-white">
                      {{ answer.text }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between">
              <PrimaryButton
                v-if="currentQuestionIndex > 0"
                @click="previousQuestion"
                class="bg-gray-600 hover:bg-gray-700"
              >
                Previous Question
              </PrimaryButton>
              <div v-else class="w-32"></div>

              <PrimaryButton
                v-if="currentQuestionIndex < quiz.questions.length - 1"
                @click="nextQuestion"
              >
                Next Question
              </PrimaryButton>
              <PrimaryButton
                v-else
                @click="submitQuiz"
                :disabled="isSubmitting"
                :class="{ 'opacity-50': isSubmitting }"
              >
                Submit Quiz
              </PrimaryButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { calculateScore, formatTime, calculateRemainingTime } from '@/Pages/Quizzes/utils/quizHelpers'

const props = defineProps({
    quiz: {
        type: Object,
        required: true
    },
    course: {
        type: Object,
        required: true
    },
    breadcrumbs: {
        type: Array,
        required: true
    }
})

const form = useForm({
    answers: []
})

const currentQuestionIndex = ref(0)
const timeRemaining = ref(calculateRemainingTime(props.quiz.time_limit, 0))
const timer = ref(null)
const isSubmitting = ref(false)

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value])

const progress = computed(() => {
    return Math.round((currentQuestionIndex.value / props.quiz.questions.length) * 100)
})

const startTimer = () => {
    timer.value = setInterval(() => {
        timeRemaining.value = Math.max(0, timeRemaining.value - 1)
        if (timeRemaining.value === 0) {
            submitQuiz()
        }
    }, 1000)
}

const stopTimer = () => {
    if (timer.value) {
        clearInterval(timer.value)
    }
}

const selectAnswer = (answerId) => {
    const questionId = currentQuestion.value.id
    const existingAnswerIndex = form.answers.findIndex(a => a.question_id === questionId)

    if (existingAnswerIndex !== -1) {
        form.answers[existingAnswerIndex].answer_id = answerId
    } else {
        form.answers.push({
            question_id: questionId,
            answer_id: answerId
        })
    }
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

const submitQuiz = () => {
    if (isSubmitting.value) return

    isSubmitting.value = true
    stopTimer()

    form.post(route('quizzes.submit', {
        quizId: props.quiz.id,
        courseId: props.course.id
    }))
}

onMounted(() => {
    startTimer()
})

onUnmounted(() => {
    stopTimer()
})
</script>
