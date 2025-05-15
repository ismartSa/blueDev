<template>
  <Head :title="title" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Back Button -->
            <div class="mb-6">
              <Link
                :href="route('quizzes.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 focus:bg-gray-700 dark:focus:bg-gray-600 active:bg-gray-900 dark:active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Quizzes List
              </Link>
            </div>

            <!-- Quiz Info -->
            <div class="mb-8">
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                {{ quiz.title }}
              </h1>
              <p class="text-gray-600 dark:text-gray-400">
                {{ quiz.description }}
              </p>
            </div>

            <!-- Controls -->
            <div class="mb-6 flex justify-between items-center">
              <PrimaryButton
                @click="toggleShowAnswers"
                :class="showAnswers ? 'bg-gray-600 hover:bg-gray-700' : ''"
              >
                {{ showAnswers ? 'Hide Correct Answers' : 'Show Correct Answers' }}
              </PrimaryButton>
              <div class="text-gray-600 dark:text-gray-400">
                Total Questions: {{ quiz.questions.length }}
              </div>
            </div>

            <!-- Questions List -->
            <div v-if="quiz.questions.length > 0" class="space-y-6">
              <div v-for="(question, index) in quiz.questions"
                   :key="question.id"
                   class="p-6 bg-gray-50 dark:bg-slate-700 rounded-lg">
                <div class="flex items-start justify-between">
                  <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ index + 1 }}. {{ question.question_title }}
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                      Type: {{ getQuestionTypeName(question.question_type_id) }}
                    </p>
                  </div>
                </div>

                <ul class="mt-4 space-y-2">
                  <li v-for="answer in question.answers"
                      :key="answer.id"
                      class="flex items-center p-3 rounded-md"
                      :class="{
                        'bg-green-50 dark:bg-green-900/20': showAnswers && answer.is_correct,
                        'bg-white dark:bg-slate-600': !showAnswers || !answer.is_correct
                      }">
                    <span class="text-gray-900 dark:text-white">
                      {{ answer.answer }}
                    </span>
                    <span v-if="showAnswers && answer.is_correct"
                          class="ml-2 text-xs font-medium text-green-600 dark:text-green-400">
                      (Correct)
                    </span>
                  </li>
                </ul>
              </div>
            </div>
            <p v-else class="text-gray-600 dark:text-gray-400 text-center py-4">
              No questions added to this quiz yet.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    quiz: {
        type: Object,
        required: true
    },
    breadcrumbs: {
        type: Array,
        required: true
    }
})

const showAnswers = ref(false)

const title = computed(() => `Questions for ${props.quiz.title}`)

function toggleShowAnswers() {
    showAnswers.value = !showAnswers.value
}

function getQuestionTypeName(typeId) {
    const types = {
        1: 'Multiple Choice',
        2: 'True/False',
        3: 'Short Answer',
        4: 'Multiple Select',
        5: 'Matching',
        6: 'Fill in the Blank'
    }
    return types[typeId] || 'Unknown'
}
</script>
