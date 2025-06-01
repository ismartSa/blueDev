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
            <p><strong>Description:</strong> {{ quiz.description }}</p>
            <p><strong>Time Limit:</strong> {{ quiz.time_limit }} minutes</p>
            <p><strong>Passing Score:</strong> {{ quiz.passing_score }}%</p>
            <p><strong>Total Questions:</strong> {{ quiz.questions.length }}</p>

            <!-- View Questions Button -->
            <div class="mt-6">
              <Link
                :href="route('quizzes.questions.list', quiz.id)"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2"
              >
                View Questions
              </Link>
              <Link
                :href="route('quizzes.questions.create', quiz.id)"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2"
              >
                Add New Question
              </Link>
              <button
                @click="toggleImportQuestions"
                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded"
              >
                {{ showImportQuestions ? 'Hide Import Options' : 'Import Questions' }}
              </button>
            </div>

            <!-- Start Quiz button -->
            <button
              v-if="quiz.questions.length > 0"
              @click="startQuiz"
              class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
            >
              Start Quiz
            </button>
            <p v-else class="mt-4 text-red-600">
              This quiz has no questions yet. Please add questions or import them.
            </p>

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
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import QuizImport from './QuizImport.vue'
import QuizEdit from './Edit.vue'

export default {
  components: {
    AuthenticatedLayout,
    Link,
    QuizImport,
    QuizEdit,
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
