<template>
  <Head :title="`Quizzes - ${course?.title || 'Course'}`" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="`Quizzes - ${course?.title || 'Course'}`" :breadcrumbs="[
        { label: 'Dashboard', href: route('dashboard') },
        { label: 'Courses', href: route('courses.index') },
        { label: course?.title || 'Course', href: route('courses.details', { id: course?.id, courseSlug: course?.slug }) },
        { label: 'Quizzes', href: '#' }
      ]" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row lg:space-x-8">
          <!-- Left Column: Quiz Content and History -->
          <div class="lg:w-2/3">
            <!-- Quiz Content -->
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg mb-8">
              <div class="px-6 py-5 sm:p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Current Quiz</h2>
                <div v-if="selectedQuiz" class="mb-6">
                  <h3 class="text-2xl font-bold mb-3 text-gray-800 dark:text-white">
                    {{ selectedQuiz.title }}
                  </h3>
                  <p class="text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                    {{ selectedQuiz.description }}
                  </p>
                  <div class="flex flex-wrap gap-4 text-sm mb-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200">
                      <ClockIcon class="h-4 w-4 mr-1.5" />
                      Time: {{ selectedQuiz.time_limit }} minutes
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                      <CheckCircleIcon class="h-4 w-4 mr-1.5" />
                      Passing Score: {{ selectedQuiz.passing_score }}%
                    </span>
                  </div>
                  <button
                    @click="startQuiz"
                    class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                  >
                    <PlayIcon class="h-5 w-5 mr-2" />
                    Start Quiz
                  </button>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400 italic">
                  Select a quiz from the list to view details and start.
                </p>
              </div>
            </div>

            <!-- Quiz History -->
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg">
              <div class="px-6 py-5 sm:p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quiz History</h2>
                <div v-if="quizHistory && quizHistory.length > 0" class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Quiz
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Score
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Status
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                      <tr v-for="attempt in quizHistory"
                        :key="attempt.id"
                        class="hover:bg-gray-50 dark:hover:bg-slate-700 transition duration-150 ease-in-out">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                          {{ attempt.quiz_title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                          {{ formatDate(attempt.date) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                          {{ attempt.score }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                          <span :class="attempt.passed ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                            {{ attempt.passed ? 'Passed' : 'Failed' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-400 italic">
                  No quiz attempts yet.
                </p>
              </div>
            </div>
          </div>

          <!-- Right Column: Quiz List -->
          <div class="lg:w-1/3 mt-8 lg:mt-0">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg sticky top-6">
              <div class="px-6 py-5 sm:p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Available Quizzes</h2>
                <ul v-if="quizzes && quizzes.length > 0" class="divide-y divide-gray-200 dark:divide-slate-700">
                  <li v-for="quiz in quizzes"
                    :key="quiz.id"
                    @click="selectQuiz(quiz)"
                    class="py-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 transition duration-150 ease-in-out"
                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/50': selectedQuiz && selectedQuiz.id === quiz.id }">
                    <div class="flex items-center space-x-4">
                      <div class="flex-shrink-0">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 dark:text-indigo-300">
                          <DocumentTextIcon class="h-6 w-6" />
                        </span>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                          {{ quiz.title }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                          {{ quiz.questions_count }} questions
                        </p>
                      </div>
                    </div>
                  </li>
                </ul>
                <p v-else class="text-gray-600 dark:text-gray-400 italic">
                  No quizzes available for this course yet.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, usePage, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import {
  ClockIcon,
  CheckCircleIcon,
  DocumentTextIcon,
  PlayIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
const course = computed(() => page.props.course)
const quizzes = ref(page.props.quizzes || [])
const selectedQuiz = ref(null)
const quizHistory = ref(page.props.quizHistory || [])

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const selectQuiz = (quiz) => {
  selectedQuiz.value = quiz
}

const startQuiz = () => {
  if (!selectedQuiz.value) return

  router.visit(route('quizzes.take', {
    quizId: selectedQuiz.value.id,
    courseId: course.value.id
  }))
}
</script>
