<template>
  <Head :title="`Import Questions - ${quiz.title}`" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="`Import Questions - ${quiz.title}`" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Excel Import Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Import Questions from Excel
              </h3>
              <form @submit.prevent="importQuestions" enctype="multipart/form-data">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                  <div class="w-full sm:w-2/3">
                    <label for="file-upload"
                           class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 cursor-pointer transition-colors">
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
                    <InputError :message="form.errors.file" class="mt-2" />
                  </div>
                  <PrimaryButton
                    type="submit"
                    class="w-full sm:w-1/3"
                    :disabled="!form.file"
                  >
                    Import Questions
                  </PrimaryButton>
                </div>
              </form>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                Please upload an Excel file (.xlsx or .xls) containing your questions.
                <a :href="route('quizzes.template.download')"
                   class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300">
                  Download template
                </a>
              </p>
            </div>

            <!-- JSON Import Section -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Import Questions from JSON
              </h3>
              <form @submit.prevent="importQuestionsFromJson">
                <div class="mb-4">
                  <TextInput
                    v-model="jsonInput"
                    type="textarea"
                    class="w-full h-40"
                    placeholder="Paste your JSON here..."
                  />
                  <InputError :message="jsonError" class="mt-2" />
                </div>
                <div class="flex justify-between items-center">
                  <PrimaryButton
                    type="submit"
                    class="bg-purple-600 hover:bg-purple-700"
                    :disabled="!jsonInput"
                  >
                    Import Questions from JSON
                  </PrimaryButton>
                  <button
                    type="button"
                    @click="showJsonExample"
                    class="text-sm text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300"
                  >
                    See example format
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    quizId: {
        type: Number,
        required: true
    },
    quiz: {
        type: Object,
        required: true
    },
    breadcrumbs: {
        type: Array,
        required: true
    }
})

const form = useForm({
    file: null,
    questions: []
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
        }
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
        }
    ]
    alert(JSON.stringify(example, null, 2))
}
</script>
