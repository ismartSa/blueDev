<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        نتائج الكويز: {{ quiz.title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="mb-8">
              <h3 class="text-2xl font-bold mb-2">ملخص النتائج</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                  <p class="text-sm text-blue-800">الدرجة</p>
                  <p class="text-3xl font-bold text-blue-600">{{ result.score }}%</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                  <p class="text-sm text-green-800">الإجابات الصحيحة</p>
                  <p class="text-3xl font-bold text-green-600">{{ result.correct_answers }}/{{ result.total_questions }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                  <p class="text-sm text-purple-800">الوقت المستغرق</p>
                  <p class="text-3xl font-bold text-purple-600">{{ result.time_taken }} دقيقة</p>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-xl font-bold mb-4">تفاصيل الإجابات</h3>
              <div v-for="(question, index) in result.questions" :key="index" class="mb-6 border-b pb-4">
                <p class="font-medium mb-2">{{ index + 1 }}. {{ question.text }}</p>
                <div v-for="answer in question.answers" :key="answer.id" class="ml-4">
                  <div
                    class="flex items-center"
                    :class="{
                      'text-green-600': answer.is_correct,
                      'text-red-600': answer.is_selected && !answer.is_correct
                    }"
                  >
                    <input
                      type="checkbox"
                      :checked="answer.is_selected"
                      class="mr-2"
                      disabled
                    >
                    <span>{{ answer.text }}</span>
                  </div>
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  quiz: Object,
  result: Object
})
</script>
