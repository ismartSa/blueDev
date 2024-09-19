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

            <!-- Add Question Button -->
            <div class="mt-6">
              <Link
                :href="route('quizzes.questions.create', quiz.id)"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Add New Question
              </Link>
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

            <!-- Import Questions from Excel section -->
            <div class="mt-8 bg-gray-100 p-6 rounded-lg">
              <h3 class="text-lg font-semibold mb-4">Import Questions from Excel</h3>
              <form @submit.prevent="importQuestions" enctype="multipart/form-data">
                <div class="flex flex-col sm:flex-row items-center">
                  <div class="w-full sm:w-2/3 mb-4 sm:mb-0 sm:mr-4">
                    <label for="file-upload" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                      <span v-if="!form.file">Choose Excel file</span>
                      <span v-else>{{ form.file.name }}</span>
                      <input
                        id="file-upload"
                        type="file"
                        ref="fileInput"
                        accept=".xlsx,.xls"
                        class="sr-only"
                        @change="handleFileChange"
                      >
                    </label>
                  </div>
                  <button
                    type="submit"
                    class="w-full sm:w-1/3 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    :disabled="!form.file"
                  >
                    Import Questions
                  </button>
                </div>
              </form>
              <p v-if="form.errors.file" class="text-red-500 mt-2">{{ form.errors.file }}</p>
              <p class="text-sm text-gray-600 mt-4">
                Please upload an Excel file (.xlsx or .xls) containing your questions.
                <a :href="route('quizzes.template.download')" class="text-blue-500 hover:underline">Download template</a>
              </p>
            </div>

            <!-- Import Questions from JSON section -->
            <div class="mt-8 bg-gray-100 p-6 rounded-lg">
              <h3 class="text-lg font-semibold mb-4">Import Questions from JSON</h3>
              <form @submit.prevent="importQuestionsFromJson">
                <textarea
                  v-model="jsonInput"
                  class="w-full h-40 p-2 border rounded-md mb-4"
                  placeholder="Paste your JSON here..."
                ></textarea>
                <button
                  type="submit"
                  class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded"
                  :disabled="!jsonInput"
                >
                  Import Questions from JSON
                </button>
              </form>
              <p v-if="jsonError" class="text-red-500 mt-2">{{ jsonError }}</p>
              <p class="text-sm text-gray-600 mt-4">
                Please paste a valid JSON array of questions.
                <a href="#" @click.prevent="showJsonExample" class="text-blue-500 hover:underline">See example format</a>
              </p>
            </div>

            <!-- Add more sections for quiz management as needed -->
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  components: {
    AuthenticatedLayout,
    Link,
  },
  props: {
    quiz: Object,
  },
  setup(props) {
    const form = useForm({
      file: null,
      questions: [],
    })
    const jsonInput = ref('')
    const jsonError = ref('')

    const fileInput = ref(null)

    function startQuiz() {
      if (props.quiz.questions.length === 0) {
        alert('This quiz has no questions. Unable to start the quiz.')
        return
      }
      form.post(route('quizzes.start', props.quiz.id))
    }

    function importQuestions() {
      form.post(route('quizzes.import-questions', props.quiz.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          // Reset the file input after successful import
          if (fileInput.value) {
            fileInput.value.value = ''
          }
          form.reset('file')
        },
      })
    }

    function importQuestionsFromJson() {
      try {
        const questions = JSON.parse(jsonInput.value)
        if (!Array.isArray(questions)) {
          throw new Error('JSON must be an array of questions')
        }

        form.questions = questions // Set the questions in the form
        form.post(route('quizzes.import-questions-json', props.quiz.id), {
          preserveState: true,
          preserveScroll: true,
          onSuccess: () => {
            jsonInput.value = ''
            jsonError.value = ''
          },
          onError: (errors) => {
            jsonError.value = errors.questions || 'An error occurred while importing questions'
          }
        })
      } catch (error) {
        jsonError.value = 'Invalid JSON format: ' + error.message
      }
    }

    function handleFileChange(event) {
      form.file = event.target.files[0]
    }

    function showJsonExample() {
      const example = [
        {
          question_title: "What is the capital of France?",
          question_type_id: 1,
          correct_answers_required: 1,
          answers: [
            { answer: "Paris", is_correct: true },
            { answer: "London", is_correct: false },
            { answer: "Berlin", is_correct: false },
            { answer: "Madrid", is_correct: false }
          ]
        },
        // يمكنك إضافة المزيد من الأمثلة هنا
      ]
      alert(JSON.stringify(example, null, 2))
    }

    // يمكنك إزالة هذه الوظيفة لأننا الآن نستخدم رابطًا مباشرًا
    // function downloadTemplate() {
    //   // ...
    // }

    return {
      form,
      jsonInput,
      jsonError,
      fileInput,
      startQuiz,
      importQuestions,
      importQuestionsFromJson,
      handleFileChange,
      showJsonExample,
      // downloadTemplate, // يمكنك إزالة هذا أيضًا
    }
  },
}
</script>
