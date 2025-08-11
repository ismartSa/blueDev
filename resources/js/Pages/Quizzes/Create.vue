<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Create New Quiz
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Progress bar -->
            <div class="mb-4">
              <div class="flex justify-between mb-1">
                <span v-for="(step, index) in steps" :key="index"
                      :class="{'font-bold': currentStep === index + 1}">
                  Step {{ index + 1 }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full"
                     :style="{ width: `${(currentStep / steps.length) * 100}%` }"></div>
              </div>
            </div>

            <!-- Step 1: Basic Information -->
            <div v-if="currentStep === 1">
              <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
              <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                <input v-model="form.title" type="text" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              </div>
              <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                <textarea v-model="form.description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
              </div>
            </div>

            <!-- Step 2: Quiz Settings -->
            <div v-if="currentStep === 2">
              <h3 class="text-lg font-semibold mb-4">Quiz Settings</h3>
              <div class="mb-4">
                <label for="time_limit" class="block text-gray-700 text-sm font-bold mb-2">Time Limit (minutes)</label>
                <input v-model="form.time_limit" type="number" id="time_limit" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              </div>
              <div class="mb-4">
                <label for="passing_score" class="block text-gray-700 text-sm font-bold mb-2">Passing Score (%)</label>
                <input v-model="form.passing_score" type="number" id="passing_score" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              </div>
              <div class="mb-4">
                <label for="is_active" class="block text-gray-700 text-sm font-bold mb-2">
                  <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2">
                  Active Quiz
                </label>
              </div>
            </div>

            <!-- Step 3: Course Section (Optional) -->
            <div v-if="currentStep === 3">
              <h3 class="text-lg font-semibold mb-4">Course Section (Optional)</h3>
              <div class="mb-4">
                <label for="course_id" class="block text-gray-700 text-sm font-bold mb-2">Course (Optional)</label>
                <select v-model="form.course_id" id="course_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  <option value="">Select a course (optional)</option>
                  <option v-for="course in courses" :key="course.id" :value="course.id">
                    {{ course.title }}
                  </option>
                </select>
              </div>
              <div v-if="form.course_id" class="mb-4">
                <label for="section_id" class="block text-gray-700 text-sm font-bold mb-2">Section (Optional)</label>
                <select v-model="form.section_id" id="section_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  <option value="">Select a section (optional)</option>
                  <option v-for="section in currentCourseSections" :key="section.id" :value="section.id">
                    {{ section.title }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Step 4: Review -->
            <div v-if="currentStep === 4">
              <h3 class="text-lg font-semibold mb-4">Review</h3>
              <div class="mb-4">
                <p><strong>Title:</strong> {{ form.title }}</p>
                <p><strong>Description:</strong> {{ form.description }}</p>
                <p><strong>Time Limit:</strong> {{ form.time_limit }} minutes</p>
                <p><strong>Passing Score:</strong> {{ form.passing_score }}%</p>
                <p><strong>Active:</strong> {{ form.is_active ? 'Yes' : 'No' }}</p>
                <p><strong>Course:</strong> {{ getCourseTitle(form.course_id) }}</p>
                <p><strong>Section:</strong> {{ getSectionTitle(form.section_id) }}</p>
              </div>
            </div>

            <!-- Navigation buttons -->
            <div class="flex justify-between mt-6">
              <button v-if="currentStep > 1" @click="previousStep"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Previous
              </button>
              <button v-if="currentStep < steps.length" @click="nextStep"
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Next
              </button>
              <button v-if="currentStep === steps.length" @click="submit"
                      class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Create Quiz
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

export default {
  components: {
    AuthenticatedLayout,
  },
  props: {
    courses: Array,
  },
  setup(props) {
    const steps = ['Basic Information', 'Quiz Settings', 'Course Section', 'Review']
    const currentStep = ref(1)
    const form = useForm({
      title: '',
      description: '',
      time_limit: null,
      passing_score: null,
      is_active: true,
      course_id: '',
      section_id: '',
    })

    const currentCourseSections = computed(() => {
      if (!form.course_id) return []
      const course = props.courses.find(c => c.id === form.course_id)
      return course ? course.sections : []
    })

    function nextStep() {
      if (currentStep.value < steps.length) {
        currentStep.value++
      }
    }

    function previousStep() {
      if (currentStep.value > 1) {
        currentStep.value--
      }
    }

    function submit() {
      form.post(route('quizzes.store'))
    }

    function getCourseTitle(courseId) {
      if (!courseId) return 'Not specified'
      const course = props.courses.find(c => c.id === courseId)
      return course ? course.title : 'Unknown Course'
    }

    function getSectionTitle(sectionId) {
      if (!sectionId) return 'Not specified'
      const course = props.courses.find(c => c.id === form.course_id)
      if (!course) return 'Unknown Section'
      const section = course.sections.find(s => s.id === sectionId)
      return section ? section.title : 'Unknown Section'
    }

    return {
      steps,
      currentStep,
      form,
      nextStep,
      previousStep,
      submit,
      courses: props.courses,
      currentCourseSections,
      getCourseTitle,
      getSectionTitle
    }
  }
}
</script>
