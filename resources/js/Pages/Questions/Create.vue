<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Add New Question to {{ quiz.title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submitForm">
              <div class="mb-4">
                <label for="question_title_en" class="block text-gray-700 text-sm font-bold mb-2">Question Title (English):</label>
                <input
                  id="question_title_en"
                  v-model="form.question_title_en"
                  type="text"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                >
              </div>

              <div class="mb-4">
                <label for="question_title_ar" class="block text-gray-700 text-sm font-bold mb-2">Question Title (Arabic):</label>
                <input
                  id="question_title_ar"
                  v-model="form.question_title_ar"
                  type="text"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                  dir="rtl"
                >
              </div>

              <div class="mb-4">
                <label for="question_type" class="block text-gray-700 text-sm font-bold mb-2">Question Type:</label>
                <select
                  id="question_type"
                  v-model="form.question_type_id"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                >
                  <option value="1">Single Choice</option>
                  <option value="2">Multiple Choice</option>
                  <option value="3">True/False</option>
                </select>
              </div>

              <div class="mb-4">
                <label for="correct_answers_required" class="block text-gray-700 text-sm font-bold mb-2">Correct Answers Required:</label>
                <input
                  id="correct_answers_required"
                  v-model="form.correct_answers_required"
                  type="number"
                  min="1"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                >
              </div>

              <div class="mb-4">
                <h3 class="text-gray-700 text-sm font-bold mb-2">Answers:</h3>
                <div v-for="(answer, index) in form.answers" :key="index" class="mb-2">
                  <div class="flex items-center">
                    <input
                      v-model="answer.answer_en"
                      type="text"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2"
                      :placeholder="`Answer ${index + 1} (English)`"
                      required
                    >
                    <input
                      v-model="answer.answer_ar"
                      type="text"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2"
                      :placeholder="`Answer ${index + 1} (Arabic)`"
                      required
                      dir="rtl"
                    >
                    <input
                      v-model="answer.is_correct"
                      type="checkbox"
                      class="mr-2"
                    >
                    <label class="text-sm">Correct</label>
                    <button @click="removeAnswer(index)" type="button" class="ml-2 text-red-500">Remove</button>
                  </div>
                </div>
                <button @click="addAnswer" type="button" class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                  Add Answer
                </button>
              </div>

              <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                  Add Question
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  components: {
    AuthenticatedLayout,
  },
  props: {
    quiz: Object,
  },
  setup(props) {
    const form = useForm({
      quiz_id: props.quiz.id,
      question_title_en: '',
      question_title_ar: '',
      question_type_id: 1,
      correct_answers_required: 1,
      answers: [
        { answer_en: '', answer_ar: '', is_correct: false },
        { answer_en: '', answer_ar: '', is_correct: false },
      ],
    })

    const addAnswer = () => {
      form.answers.push({ answer_en: '', answer_ar: '', is_correct: false })
    }

    const removeAnswer = (index) => {
      form.answers.splice(index, 1)
    }

    const submitForm = () => {
      form.post(route('quizzes.questions.store', props.quiz.id), {
        preserveState: true,
        preserveScroll: true,
      })
    }

    return {
      form,
      addAnswer,
      removeAnswer,
      submitForm,
    }
  }
}
</script>
