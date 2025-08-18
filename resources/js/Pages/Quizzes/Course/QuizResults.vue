<template>
  <Head :title="`Quiz Results - ${quiz.title}`" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="`Quiz Results - ${quiz.title}`" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="mb-8">
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Results Summary</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/50 p-4 rounded-lg">
                  <p class="text-sm text-blue-800 dark:text-blue-200">Score</p>
                  <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ result.score }}%</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/50 p-4 rounded-lg">
                  <p class="text-sm text-green-800 dark:text-green-200">Correct Answers</p>
                  <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                    {{ result.correct_answers }}/{{ result.total_questions }}
                  </p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/50 p-4 rounded-lg">
                  <p class="text-sm text-purple-800 dark:text-purple-200">Time Taken</p>
                  <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                    {{ result.time_taken }} minutes
                  </p>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Answer Details</h3>
              <div v-for="(question, index) in result.questions" :key="index" class="mb-6 border-b dark:border-slate-700 pb-4 last:border-0">
                <p class="font-medium text-gray-900 dark:text-white mb-2">
                  {{ index + 1 }}. {{ question.text }}
                </p>
                <div v-for="answer in question.answers" :key="answer.id" class="ml-4">
                  <div
                    class="flex items-center"
                    :class="{
                      'text-green-600 dark:text-green-400': answer.is_correct,
                      'text-red-600 dark:text-red-400': answer.is_selected && !answer.is_correct
                    }"
                  >
                    <input
                      type="checkbox"
                      :checked="answer.is_selected"
                      class="mr-2 rounded border-gray-300 dark:border-slate-600 text-blue-600 dark:text-blue-500 focus:ring-blue-500 dark:focus:ring-blue-600"
                      disabled
                    >
                    <span>{{ answer.text }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'

defineProps({
  quiz: {
    type: Object,
    required: true
  },
  result: {
    type: Object,
    required: true
  },
  breadcrumbs: {
    type: Array,
    required: true
  }
})
</script>
