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
            <QuizTimer 
              :title="quiz.title"
              :time-remaining="timeRemaining"
              :current-question="currentQuestionIndex"
              :total-questions="quiz.questions.length"
            />

            <!-- Question -->
            <QuizQuestion 
              v-if="currentQuestion"
              :question="currentQuestion"
              :question-number="currentQuestionIndex + 1"
              :total-questions="quiz.questions.length"
              :selected-answers="form.answers"
              @select-answer="handleAnswerSelect"
            />

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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import QuizTimer from '@/Components/Quiz/QuizTimer.vue'
import QuizQuestion from '@/Components/Quiz/QuizQuestion.vue'

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
const timeRemaining = ref(props.quiz.time_limit * 60) // Convert minutes to seconds
const timer = ref(null)
const isSubmitting = ref(false)

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value])

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

const handleAnswerSelect = (answerData) => {
    const existingAnswerIndex = form.answers.findIndex(a => a.question_id === answerData.question_id)

    if (existingAnswerIndex !== -1) {
        form.answers[existingAnswerIndex] = answerData
    } else {
        form.answers.push(answerData)
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

<script>
export default {
    components: {
        QuizTimer,
        QuizQuestion
    }
}
</script>
