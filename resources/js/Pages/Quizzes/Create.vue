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
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Basic Information</h3>
              <FormField
                v-model="form.title"
                type="text"
                label="Title"
                field-id="title"
                placeholder="Enter quiz title"
                required
              />
              <FormField
                v-model="form.description"
                type="textarea"
                label="Description"
                field-id="description"
                placeholder="Enter quiz description"
                :rows="4"
              />
            </div>

            <!-- Step 2: Quiz Settings -->
            <div v-if="currentStep === 2">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Quiz Settings</h3>
              <FormField
                v-model="form.time_limit"
                type="number"
                label="Time Limit (minutes)"
                field-id="time_limit"
                placeholder="Enter time limit in minutes"
                required
              />
              <FormField
                v-model="form.passing_score"
                type="number"
                label="Passing Score (%)"
                field-id="passing_score"
                placeholder="Enter passing score percentage"
                required
              />
              <FormField
                v-model="form.is_active"
                type="checkbox"
                label="Quiz Status"
                field-id="is_active"
                checkbox-label="Active Quiz"
              />
            </div>

            <!-- Step 3: Course Section (Optional) -->
            <div v-if="currentStep === 3">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Course Section (Optional)</h3>
              <FormField
                v-model="form.course_id"
                type="select"
                label="Course (Optional)"
                field-id="course_id"
                placeholder="Select a course (optional)"
                :options="courseOptions"
              />
              <FormField
                v-if="form.course_id"
                v-model="form.section_id"
                type="select"
                label="Section (Optional)"
                field-id="section_id"
                placeholder="Select a section (optional)"
                :options="sectionOptions"
              />
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
import FormField from '@/Components/Form/FormField.vue'
import { useForm } from '@inertiajs/vue3'

export default {
  components: {
    AuthenticatedLayout,
    FormField,
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

    const courseOptions = computed(() => 
      props.courses.map(course => ({ value: course.id, label: course.title }))
    )

    const sectionOptions = computed(() => {
      if (!form.course_id) return []
      const course = props.courses.find(c => c.id === form.course_id)
      return course?.sections?.map(section => ({ value: section.id, label: section.title })) || []
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
      courseOptions,
      sectionOptions,
      currentCourseSections,
      getCourseTitle,
      getSectionTitle
    }
  }
}
</script>
