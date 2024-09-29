<template>
  <AuthenticatedLayout>
       <!-- Breadcrumb component -->
       <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Questions for {{ quiz.title }}
      </h2>
    </template>
<!-- Back to Quizzes List Button -->
<div class="mb-4">
              <Link
                :href="route('quizzes.index')"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Quizzes List
              </Link>
            </div>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">


            <div class="mb-6">
              <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ quiz.title }}</h1>
              <p class="text-gray-600">{{ quiz.description }}</p>
            </div>



            <div class="mb-4 flex justify-between items-center">
              <button
                @click="toggleShowAnswers"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                {{ showAnswers ? 'Hide Correct Answers' : 'Show Correct Answers' }}
              </button>
              <p class="text-gray-600">Total Questions: {{ quiz.questions.length }}</p>
            </div>

            <div v-if="quiz.questions.length > 0">
              <div v-for="(question, index) in quiz.questions" :key="question.id" class="mb-6 p-4 bg-gray-100 rounded-lg">
                <h4 class="font-semibold text-lg">{{ index + 1 }}. {{ question.question_title }}</h4>
                <p class="text-sm text-gray-600 mt-1">Type: {{ getQuestionTypeName(question.question_type_id) }}</p>
                <ul class="mt-2 ml-6 list-disc">
                  <li v-for="answer in question.answers" :key="answer.id"
                      :class="{'text-green-600 font-semibold': showAnswers && answer.is_correct}">
                    {{ answer.answer }}
                    <span v-if="showAnswers && answer.is_correct" class="text-xs ml-2">(Correct)</span>
                  </li>
                </ul>
              </div>
            </div>
            <p v-else class="text-gray-600">No questions added to this quiz yet.</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'

export default {
  components: {
    AuthenticatedLayout,
    Breadcrumb,
    Link,
  },
  props: {
    quiz: Object,
  },
  setup(props) {
    const showAnswers = ref(false)

    const title = computed(() => `Questions for ${props.quiz.title}`)
    const breadcrumbs = computed(() => [
      { name: 'Dashboard', href: route('dashboard') },
      { name: 'Quizzes', href: route('quizzes.index') },
      { name: props.quiz.title, href: route('quizzes.show', props.quiz.id) },
      { name: 'Questions', href: '#' },
    ])

    function toggleShowAnswers() {
      showAnswers.value = !showAnswers.value
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
      showAnswers,
      toggleShowAnswers,
      getQuestionTypeName,
      title,
      breadcrumbs,
    }
  },
}
</script>
