<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Course</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Progress Bar -->
            <div class="mb-8">
              <div class="flex justify-between mb-1">
                <span v-for="step in 3" :key="step"
                      :class="{'text-blue-600 font-bold': currentStep >= step, 'text-gray-400': currentStep < step}">
                  Step {{ step }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${(currentStep / 3) * 100}%` }"></div>
              </div>
            </div>

            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <!-- Step 1: Basic Information -->
              <div v-if="currentStep === 1">
                <div class="mb-4">
                  <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                  <input id="title" v-model="form.title" type="text" class="form-input w-full" required>
                </div>

                <div class="mb-4">
                  <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                  <input id="name" v-model="form.name" type="text" class="form-input w-full" required>
                </div>

                <div class="mb-4">
                  <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                  <textarea id="description" v-model="form.description" class="form-textarea w-full" rows="3"></textarea>
                </div>
              </div>

              <!-- Step 2: Additional Details -->
              <div v-if="currentStep === 2">
                <div class="mb-4">
                  <label for="body" class="block text-gray-700 text-sm font-bold mb-2">Body:</label>
                  <textarea id="body" v-model="form.body" class="form-textarea w-full" rows="5"></textarea>
                </div>

                <div class="mb-4">
                  <label for="duration" class="block text-gray-700 text-sm font-bold mb-2">Duration (in minutes):</label>
                  <input id="duration" v-model="form.duration" type="number" class="form-input w-full">
                </div>

                <div class="mb-4">
                  <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
                  <input id="image" type="file" @input="form.image = $event.target.files[0]" class="form-input w-full">
                </div>

                <div class="mb-4">
                  <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                  <select id="status" v-model="form.status" class="form-select w-full" required>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                  </select>
                </div>

                <div class="mb-4">
                  <label for="intro_video" class="block text-gray-700 text-sm font-bold mb-2">Intro Video URL:</label>
                  <input id="intro_video" v-model="form.intro_video" type="url" class="form-input w-full">
                </div>
              </div>

              <!-- Step 3: Review -->
              <div v-if="currentStep === 3">
                <h3 class="text-lg font-semibold mb-4">Review Your Course Information</h3>
                <div v-for="(value, key) in form" :key="key" class="mb-2">
                  <strong>{{ key.charAt(0).toUpperCase() + key.slice(1) }}:</strong>
                  <span v-if="key !== 'image'">{{ value }}</span>
                  <span v-else>{{ value ? value.name : 'No image selected' }}</span>
                </div>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex justify-between mt-6">
                <button
                  v-if="currentStep > 1"
                  @click="currentStep--"
                  type="button"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                  Previous
                </button>
                <button
                  v-if="currentStep < 3"
                  @click="currentStep++"
                  type="button"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                  Next
                </button>
                <button
                  v-if="currentStep === 3"
                  type="submit"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  :disabled="form.processing"
                >
                  Create Course
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
import { defineComponent, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default defineComponent({
  components: {
    AuthenticatedLayout,
  },
  setup() {
    const currentStep = ref(1)
    const form = useForm({
      title: '',
      name: '',
      description: '',
      body: '',
      duration: null,
      image: null,
      status: 'draft',
      intro_video: '',
    })

    const submitForm = () => {
      form.post(route('courses.store'), {
        preserveState: true,
        preserveScroll: true,
      })
    }

    return { form, submitForm, currentStep }
  },
})
</script>
