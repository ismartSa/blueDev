<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Quizzes
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Add Quiz Button -->
            <div class="mb-6">
              <Link
                :href="route('quizzes.create')"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
              >
                Add New Quiz
              </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="quiz in quizzes" :key="quiz.id" class="bg-white shadow-md rounded-lg overflow-hidden">
                <img v-if="quiz.image" :src="quiz.image" :alt="quiz.title" class="w-full h-48 object-cover">
                <div class="p-4">
                  <h2 class="text-xl font-semibold mb-2">{{ quiz.title }}</h2>
                  <p class="text-gray-600 mb-2">{{ quiz.description }}</p>
                  <div class="text-sm text-gray-500">
                    <p>Questions: {{ quiz.questions_count }}</p>
                    <p>Time Limit: {{ quiz.time_limit }} minutes</p>
                    <p>Passing Score: {{ quiz.passing_score }}%</p>
                  </div>
                  <Link :href="route('quizzes.show', quiz.id)" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    View Details
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

export default {
  components: {
    AuthenticatedLayout,
    Link,
  },
  props: {
    quizzes: Array,
  },
}
</script>
