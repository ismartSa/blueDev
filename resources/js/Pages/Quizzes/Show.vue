<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ quiz.title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Quiz details -->
            <h3 class="text-lg font-semibold mb-4">Quiz Details</h3>
            <div class="mb-4">
              <p class="text-gray-700 dark:text-gray-300 mb-4">{{ quiz.description }}</p>
              <div class="flex flex-wrap gap-3">
                <QuizBadge :count="quiz.time_limit" label="minutes" color="blue" />
                <QuizBadge :count="quiz.passing_score" label="% passing" color="green" />
                <QuizBadge :count="quiz.questions.length" label="questions" color="purple" />
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-wrap gap-3">
              <Link :href="route('quizzes.questions.list', quiz.id)">
                <PrimaryButton class="bg-blue-600 hover:bg-blue-700">
                  View Questions
                </PrimaryButton>
              </Link>
              <Link :href="route('quizzes.questions.create', quiz.id)">
                <PrimaryButton class="bg-green-600 hover:bg-green-700">
                  Add New Question
                </PrimaryButton>
              </Link>
              <PrimaryButton 
                @click="toggleImportQuestions"
                class="bg-purple-600 hover:bg-purple-700"
              >
                {{ showImportQuestions ? 'Hide Import Options' : 'Import Questions' }}
              </PrimaryButton>
            </div>

            <!-- Start Quiz Section -->
            <div class="mt-4">
              <PrimaryButton 
                v-if="quiz.questions.length > 0"
                @click="startQuiz"
                class="bg-green-600 hover:bg-green-700"
              >
                Start Quiz
              </PrimaryButton>
              <div v-else class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-600 dark:text-red-400">
                  This quiz has no questions yet. Please add questions or import them.
                </p>
              </div>
            </div>

            <!-- Import Questions Component -->
            <QuizImport v-if="showImportQuestions" :quizId="quiz.id" />

            <!-- Edit Quiz Button -->
            <QuizEdit
                :show="showEditQuiz"
                @close="showEditQuiz = false"
                :quiz="quiz"
                :title="quiz.title"
                :description="quiz.description"
                :time_limit="quiz.time_limit"
                :passing_score="quiz.passing_score"
                :course_id="quiz.course_id"
                :section_id="quiz.section_id"
            />
            

          </div>
        </div>
      </div>
    </div>
    <!-- End of Quiz -->
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuizImport from './QuizImport.vue'
import QuizEdit from './Edit.vue'
import QuizBadge from '@/Components/Quiz/QuizBadge.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

export default {
  components: {
    AuthenticatedLayout,
    Link,
    QuizImport,
    QuizEdit,
    QuizBadge,
    PrimaryButton,
  },
  props: {
    quiz: Object,
  },
  setup(props) {
    const form = useForm({})
    const showImportQuestions = ref(false)
    const showEditQuiz = ref(false)
    function startQuiz() {
      if (props.quiz.questions.length === 0) {
        alert('This quiz has no questions. Unable to start the quiz.')
        return
      }
      form.post(route('quizzes.start', props.quiz.id))
    }

    function toggleImportQuestions() {
      showImportQuestions.value = !showImportQuestions.value
    }

    function getQuestionTypeName(typeId) {
      const types = {
        1: 'Multiple Choice',
        2: 'True/False',
        3: 'Short Answer',
        // Add more types as needed
      }
      return types[typeId] || 'Unknown'
    }

    return {
      startQuiz,
      showImportQuestions,
      toggleImportQuestions,
      getQuestionTypeName,
      showEditQuiz,
    }
  },
}
</script>
