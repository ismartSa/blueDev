<template>
  <AuthenticatedLayout>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="py-8 border-b border-gray-200">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
              <h1 class="text-3xl font-bold leading-tight text-gray-900 sm:text-4xl">
                {{ course?.title || 'Course' }}
              </h1>
              <p class="mt-2 text-sm text-gray-500">Manage and take quizzes for this course</p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
              <Link
                :href="route('course.details', { id: course.id, courseSlug: course.slug })"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Course
              </Link>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="py-8">
          <div class="flex flex-col lg:flex-row lg:space-x-8">
            <!-- Left Column: Quiz Content and History -->
            <div class="lg:w-2/3">
              <!-- Quiz Content -->
              <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8 transition duration-300 ease-in-out hover:shadow-md">
                <div class="px-6 py-5 sm:p-6">
                  <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Quiz</h2>
                  <div v-if="selectedQuiz" class="mb-6">
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">{{ selectedQuiz.title }}</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">{{ selectedQuiz.description }}</p>
                    <div class="flex flex-wrap gap-4 text-sm mb-6">
                      <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800">
                        <svg class="-ml-1 mr-1.5 h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        Time: {{ selectedQuiz.time_limit }} minutes
                      </span>
                      <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800">
                        <svg class="-ml-1 mr-1.5 h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Passing Score: {{ selectedQuiz.passing_score }}%
                      </span>
                    </div>
                    <button
                      @click="startQuiz"
                      class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                    >
                      <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                      </svg>
                      Start Quiz
                    </button>
                  </div>
                  <p v-else class="text-gray-600 italic">Select a quiz from the list to view details and start.</p>
                </div>
              </div>

              <!-- Quiz History -->
              <div class="bg-white overflow-hidden shadow-sm rounded-lg transition duration-300 ease-in-out hover:shadow-md">
                <div class="px-6 py-5 sm:p-6">
                  <h2 class="text-xl font-semibold text-gray-900 mb-4">Quiz History</h2>
                  <div v-if="quizHistory && quizHistory.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="attempt in quizHistory" :key="attempt.id" class="hover:bg-gray-50 transition duration-150 ease-in-out">
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ attempt.quiz_title }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(attempt.date) }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ attempt.score }}%</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span :class="attempt.passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                              {{ attempt.passed ? 'Passed' : 'Failed' }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <p v-else class="text-gray-600 italic">No quiz attempts yet.</p>
                </div>
              </div>
            </div>

            <!-- Right Column: Quiz List -->
            <div class="lg:w-1/3 mt-8 lg:mt-0">
              <div class="bg-white overflow-hidden shadow-sm rounded-lg sticky top-6 transition duration-300 ease-in-out hover:shadow-md">
                <div class="px-6 py-5 sm:p-6">
                  <h2 class="text-xl font-semibold text-gray-900 mb-4">Available Quizzes</h2>
                  <ul v-if="quizzes && quizzes.length > 0" class="divide-y divide-gray-200">
                    <li
                      v-for="quiz in quizzes"
                      :key="quiz.id"
                      @click="selectQuiz(quiz)"
                      class="py-4 cursor-pointer hover:bg-gray-50 transition duration-150 ease-in-out"
                      :class="{ 'bg-indigo-50': selectedQuiz && selectedQuiz.id === quiz.id }"
                    >
                      <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                          <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100 text-indigo-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                          </span>
                        </div>
                        <div class="flex-1 min-w-0">
                          <p class="text-sm font-medium text-gray-900 truncate">
                            {{ quiz.title }}
                          </p>
                          <p class="text-sm text-gray-500">
                            {{ quiz.questions_count }} questions
                          </p>
                        </div>
                      </div>
                    </li>
                  </ul>
                  <p v-else class="text-gray-600 italic">No quizzes available for this course yet.</p>
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
import { ref, computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage()
const course = computed(() => page.props.course)
const quizzes = ref([])
const selectedQuiz = ref(null)
const quizHistory = ref([])

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const selectQuiz = (quiz) => {
  selectedQuiz.value = quiz
}

const startQuiz = () => {
  // ... existing code ...
}
</script>

<template>
  <AuthenticatedLayout>
    // ... existing template code ...
  </AuthenticatedLayout>
</template>
