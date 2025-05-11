<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        إنشاء كويز جديد
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submitForm">
              <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">عنوان الكويز:</label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="form-input w-full"
                  required
                >
              </div>

              <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">الوصف:</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  class="form-textarea w-full"
                  rows="3"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label for="time_limit" class="block text-gray-700 text-sm font-bold mb-2">الوقت المحدد (دقائق):</label>
                  <input
                    id="time_limit"
                    v-model.number="form.time_limit"
                    type="number"
                    min="1"
                    class="form-input w-full"
                    required
                  >
                </div>
                <div>
                  <label for="passing_score" class="block text-gray-700 text-sm font-bold mb-2">درجة النجاح (%):</label>
                  <input
                    id="passing_score"
                    v-model.number="form.passing_score"
                    type="number"
                    min="1"
                    max="100"
                    class="form-input w-full"
                    required
                  >
                </div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                  حفظ الكويز
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
  title: '',
  description: '',
  time_limit: 10,
  passing_score: 70,
  course_id: null
})

const submitForm = () => {
  form.post(route('quizzes.store'), {
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>
