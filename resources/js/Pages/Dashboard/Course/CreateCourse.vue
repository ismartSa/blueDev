<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('courses.create_new_course') }}</h2>
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
                  {{ __('courses.step') }} {{ step }}: {{ stepTitles[step-1] }}
                </span>
              </div>
            </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${(currentStep / 3) * 100}%` }"></div>
              </div>
            </div>

            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <!-- Step 1: Basic Information -->
              <div v-if="currentStep === 1">
                <div class="mb-4">
                  <label for="title" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.title') }}:</label>
                  <input id="title" v-model="form.title" type="text" class="form-input w-full" required>
                  <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                </div>

                <div class="mb-4">
                  <label for="description" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.description') }}:</label>
                  <textarea id="description" v-model="form.description" class="form-textarea w-full" rows="3" required></textarea>
                  <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="mb-4">
                  <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.category') }}:</label>
                  <select id="category_id" v-model="form.category_id" class="form-select w-full" required>
                    <option value="">{{ $t('courses.select_category') }}</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                </div>
              </div>

              <!-- Step 2: Additional Details -->
              <div v-if="currentStep === 2">
                <div class="mb-4">
                  <label for="body" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.body') }}:</label>
                  <textarea id="body" v-model="form.body" class="form-textarea w-full" rows="5"></textarea>
                  <div v-if="form.errors.body" class="text-red-500 text-sm mt-1">{{ form.errors.body }}</div>
                </div>

                <div class="mb-4">
                  <label for="duration" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.duration') }} ({{ $t('courses.in_minutes') }}):</label>
                  <input id="duration" v-model="form.duration" type="number" class="form-input w-full" required>
                  <div v-if="form.errors.duration" class="text-red-500 text-sm mt-1">{{ form.errors.duration }}</div>
                </div>

                <div class="mb-4">
                  <label for="price" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.price') }}:</label>
                  <input id="price" v-model="form.price" type="number" step="0.01" class="form-input w-full" required>
                  <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">{{ form.errors.price }}</div>
                </div>

                <div class="mb-4">
                  <label for="image" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.image') }}:</label>
                  <input id="image" type="file" @input="form.image = $event.target.files[0]" class="form-input w-full">
                  <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</div>
                </div>

                <div class="mb-4">
                  <label for="status" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.status') }}:</label>
                  <select id="status" v-model="form.status" class="form-select w-full" required>
                    <option value="draft">{{ $t('courses.draft') }}</option>
                    <option value="active">{{ $t('courses.active') }}</option>
                  </select>
                  <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                </div>

                <div class="mb-4">
                  <label for="intro_video" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.intro_video_url') }}:</label>
                  <input id="intro_video" v-model="form.intro_video" type="url" class="form-input w-full">
                  <div v-if="form.errors.intro_video" class="text-red-500 text-sm mt-1">{{ form.errors.intro_video }}</div>
                </div>
              </div>

              <!-- Step 3: Sections & Lectures Planning -->
              <div v-if="currentStep === 3">
                <h3 class="text-lg font-semibold mb-4">{{ $t('courses.sections_planning') }}</h3>

                <div class="mb-6">
                  <div class="flex justify-between items-center mb-2">
                    <h4 class="font-medium">{{ $t('courses.course_sections') }}</h4>
                    <!-- Replace in these buttons: -->
                    <button
                      type="button"
                      @click="addSection"
                      class="bg-blue-500 hover:bg-blue-700 text-white text-sm py-1 px-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      {{ $t('courses.add_section') }}
                    </button>

                    <!-- And in these navigation buttons: -->
                    <button
                      v-if="currentStep > 1"
                      @click="currentStep--"
                      type="button"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                      {{ $t('courses.previous') }}
                    </button>

                    <button
                      v-if="currentStep < 3"
                      @click="nextStep"
                      type="button"
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      {{ $t('courses.next') }}
                    </button>

                    <button
                      v-if="currentStep === 3"
                      type="submit"
                      class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                      :disabled="form.processing"
                    >
                      {{ $t('courses.create_course') }}
                    </button>
                  </div>

                  <div v-for="(section, index) in sections" :key="index" class="border p-4 mb-4 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                      <h5 class="font-medium">{{ $t('courses.section') }} {{ index + 1 }}</h5>
                      <button
                        type="button"
                        @click="removeSection(index)"
                        class="text-red-500 hover:text-red-700"
                      >
                        {{ $t('courses.remove') }}
                      </button>
                    </div>

                    <div class="mb-3">
                      <label :for="`section-title-${index}`" class="block text-gray-700 text-sm font-bold mb-1">{{ $t('courses.title') }}:</label>
                      <input
                        :id="`section-title-${index}`"
                        v-model="section.title"
                        type="text"
                        class="form-input w-full text-sm"
                        required
                      >
                    </div>

                    <div class="mb-3">
                      <label :for="`section-description-${index}`" class="block text-gray-700 text-sm font-bold mb-1">{{ $t('courses.description') }}:</label>
                      <textarea
                        :id="`section-description-${index}`"
                        v-model="section.description"
                        class="form-textarea w-full text-sm"
                        rows="2"
                        required
                      ></textarea>
                    </div>

                    <div class="mb-3">
                      <label :for="`section-order-${index}`" class="block text-gray-700 text-sm font-bold mb-1">{{ $t('courses.order') }}:</label>
                      <input
                        :id="`section-order-${index}`"
                        v-model="section.order"
                        type="number"
                        class="form-input w-full text-sm"
                        required
                      >
                    </div>
                  </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                  <p class="text-sm text-gray-600">{{ $t('courses.sections_note') }}</p>
                </div>

                <h3 class="text-lg font-semibold mb-4">{{ $t('courses.review_course_info') }}</h3>
                <div v-for="(value, key) in form" :key="key" class="mb-2" v-if="key !== 'image' && key !== 'processing' && key !== 'errors' && key !== 'recentlySuccessful'">
                  <strong>{{ formatLabel(key) }}:</strong>
                  <span>{{ value }}</span>
                </div>
                <div class="mb-2" v-if="form.image">
                  <strong>{{ $t('courses.image') }}:</strong>
                  <span>{{ form.image.name }}</span>
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
                  {{ $t('courses.previous') }}
                </button>
                <div v-else></div>

                <button
                  v-if="currentStep < 3"
                  @click="nextStep"
                  type="button"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                  {{ $t('courses.next') }}
                </button>
                <button
                  v-if="currentStep === 3"
                  type="submit"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  :disabled="form.processing"
                >
                  {{ $t('courses.create_course') }}
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
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  categories: Array,
})

const currentStep = ref(1)
const sections = ref([])

const stepTitles = [
  'الأساسيات',
  'التفاصيل الإضافية',
  'الأقسام والمراجعة'
]

const form = useForm({
  title: '',
  description: '',
  body: '',
  duration: null,
  price: 0,
  image: null,
  status: 'draft',
  intro_video: '',
  category_id: '',
})

const nextStep = () => {
  if (currentStep.value === 1) {
    if (!form.title || !form.description || !form.category_id) {
      alert('يرجى ملء جميع الحقول المطلوبة')
      return
    }
  } else if (currentStep.value === 2) {
    if (!form.duration || form.duration <= 0 || form.price < 0) {
      alert('يرجى ملء جميع الحقول المطلوبة بشكل صحيح')
      return
    }
  }

  currentStep.value++
}

const addSection = () => {
  sections.value.push({
    title: '',
    description: '',
    order: sections.value.length + 1
  })
}

const removeSection = (index) => {
  sections.value.splice(index, 1)

  // إعادة ترتيب الأقسام المتبقية
  sections.value.forEach((section, idx) => {
    section.order = idx + 1
  })
}

const formatLabel = (key) => {
  const labels = {
    title: 'العنوان',
    description: 'الوصف',
    body: 'المحتوى',
    duration: 'المدة',
    price: 'السعر',
    status: 'الحالة',
    intro_video: 'رابط الفيديو التعريفي',
    category_id: 'التصنيف'
  }

  return labels[key] || key
}

const submitForm = () => {
  // إضافة الأقسام إلى النموذج
  const formData = new FormData()

  // إضافة بيانات الكورس الأساسية
  Object.keys(form).forEach(key => {
    if (key !== 'errors' && key !== 'processing' && key !== 'recentlySuccessful') {
      if (key === 'image' && form[key]) {
        formData.append(key, form[key])
      } else if (form[key] !== null && form[key] !== undefined) {
        formData.append(key, form[key])
      }
    }
  })

  // إضافة بيانات الأقسام
  formData.append('sections', JSON.stringify(sections.value))

  // إرسال النموذج
  form.post(route('courses.store'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // إعادة تعيين النموذج والأقسام
      form.reset()
      sections.value = []
      currentStep.value = 1
    },
  })
}
</script>

<style scoped>
.form-input, .form-textarea, .form-select {
  @apply shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style>
