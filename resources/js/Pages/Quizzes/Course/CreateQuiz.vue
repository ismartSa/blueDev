<template>
  <Head :title="`Create Quiz - ${course.title}`" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :title="`Create Quiz - ${course.title}`" :breadcrumbs="breadcrumbs" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submitForm">
              <div class="mb-4">
                <InputLabel for="title" value="Quiz Title" />
                <TextInput
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                />
                <InputError :message="form.errors.title" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="description" value="Description" />
                <textarea
                  id="description"
                  v-model="form.description"
                  class="mt-1 block w-full border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  rows="3"
                ></textarea>
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <InputLabel for="time_limit" value="Time Limit (minutes)" />
                  <TextInput
                    id="time_limit"
                    v-model.number="form.time_limit"
                    type="number"
                    min="1"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.time_limit" class="mt-2" />
                </div>
                <div>
                  <InputLabel for="passing_score" value="Passing Score (%)" />
                  <TextInput
                    id="passing_score"
                    v-model.number="form.passing_score"
                    type="number"
                    min="1"
                    max="100"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.passing_score" class="mt-2" />
                </div>
              </div>

              <div class="flex justify-end">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Create Quiz
                </PrimaryButton>
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
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  breadcrumbs: {
    type: Array,
    required: true
  }
})

const form = useForm({
  title: '',
  description: '',
  time_limit: 10,
  passing_score: 70,
  course_id: props.course.id
})

const submitForm = () => {
  form.post(route('quizzes.store'), {
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>
