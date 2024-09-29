<template>
  <div>
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
  </div>
</template>

<script>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

export default {
  props: {
    quizId: {
      type: Number,
      required: true
    }
  },
  setup(props) {
    const form = useForm({
      file: null,
      questions: [],
    })
    const jsonInput = ref('')
    const jsonError = ref('')
    const fileInput = ref(null)

    function importQuestions() {
      form.post(route('quizzes.import-questions', props.quizId), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
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

        form.questions = questions
        form.post(route('quizzes.import-questions-json', props.quizId), {
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
      ]
      alert(JSON.stringify(example, null, 2))
    }

    return {
      form,
      jsonInput,
      jsonError,
      fileInput,
      importQuestions,
      importQuestionsFromJson,
      handleFileChange,
      showJsonExample,
    }
  },
}
</script>
