<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('courses.create_new_course') }}</h2>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200">
            <!-- Progress Bar -->
            <div class="mb-6">
              <div class="flex justify-between mb-2">
                <span v-for="step in 3" :key="step"
                      :class="{'text-blue-600 font-semibold': currentStep >= step, 'text-gray-400': currentStep < step}"
                      class="text-sm">
                  {{ $t('courses.step') }} {{ step }}: {{ stepLabels[step-1] }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: `${(currentStep / 3) * 100}%` }"></div>
              </div>
            </div>

            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <!-- الخطوة 1: المعلومات الأساسية -->
              <div v-if="currentStep === 1">
                <div class="mb-4">
                  <label for="title" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.title') }}:</label>
                  <input id="title" v-model="form.title" type="text" class="form-input w-full rounded-md shadow-sm" required>
                  <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                </div>

                <div class="mb-4">
                  <label for="description" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.description') }}:</label>
                  <textarea id="description" v-model="form.description" class="form-textarea w-full rounded-md shadow-sm" rows="3" required></textarea>
                  <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="mb-4">
                  <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.category') }}:</label>
                  <select id="category_id" v-model="form.category_id" class="form-select w-full rounded-md shadow-sm" required>
                    <option value="">{{ $t('courses.select_category') }}</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                </div>

                <div class="mb-4">
                  <label for="price" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.price') }}:</label>
                  <input id="price" v-model="form.price" type="number" min="0" step="0.01" class="form-input w-full rounded-md shadow-sm" required>
                  <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">{{ form.errors.price }}</div>
                </div>
              </div>

              <!-- الخطوة 2: التفاصيل الإضافية -->
              <div v-if="currentStep === 2">
                <div class="mb-4">
                  <label for="duration" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.duration') }} ({{ $t('courses.in_minutes') }}):</label>
                  <input id="duration" v-model="form.duration" type="number" min="1" class="form-input w-full rounded-md shadow-sm" required>
                  <div v-if="form.errors.duration" class="text-red-500 text-sm mt-1">{{ form.errors.duration }}</div>
                </div>

                <div class="mb-4">
                  <label for="image" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.image') }}:</label>
                  <input id="image" type="file" @input="form.image = $event.target.files[0]" class="form-input w-full rounded-md shadow-sm" accept="image/*">
                  <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</div>
                </div>

                <div class="mb-4">
                  <label for="status" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.status') }}:</label>
                  <select id="status" v-model="form.status" class="form-select w-full rounded-md shadow-sm" required>
                    <option value="draft">{{ $t('courses.draft') }}</option>
                    <option value="active">{{ $t('courses.active') }}</option>
                  </select>
                  <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                </div>

                <div class="mb-4">
                  <label for="intro_video" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('courses.intro_video_url') }}:</label>
                  <input id="intro_video" v-model="form.intro_video" type="url" class="form-input w-full rounded-md shadow-sm">
                  <div v-if="form.errors.intro_video" class="text-red-500 text-sm mt-1">{{ form.errors.intro_video }}</div>
                </div>
              </div>

              <!-- Step 3: Review -->
              <div v-if="currentStep === 3">
                <h3 class="text-lg font-semibold mb-3">{{ $t('courses.review_information') }}</h3>
                <div class="bg-gray-50 p-3 rounded-lg mb-3">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="space-y-2">
                      <p class="text-sm"><strong>{{ $t('courses.title') }}:</strong> {{ form.title }}</p>
                      <p class="text-sm"><strong>{{ $t('courses.description') }}:</strong> {{ form.description }}</p>
                      <p class="text-sm"><strong>{{ $t('courses.category') }}:</strong> {{ getCategoryName(form.category_id) }}</p>
                      <p class="text-sm"><strong>{{ $t('courses.price') }}:</strong> {{ form.price }}</p>
                    </div>
                    <div class="space-y-2">
                      <p class="text-sm"><strong>{{ $t('courses.duration') }}:</strong> {{ form.duration }} {{ $t('courses.minutes') }}</p>
                      <p class="text-sm"><strong>{{ $t('courses.image') }}:</strong> {{ form.image ? form.image.name : $t('courses.no_image') }}</p>
                      <p class="text-sm"><strong>{{ $t('courses.status') }}:</strong> {{ form.status }}</p>
                      <p class="text-sm"><strong>{{ $t('courses.intro_video_url') }}:</strong> {{ form.intro_video || $t('courses.none') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex justify-between mt-4">
                <button
                  v-if="currentStep > 1"
                  @click="currentStep--"
                  type="button"
                  class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                  {{ $t('courses.previous') }}
                </button>
                <div v-else></div>

                <button
                  v-if="currentStep < 3"
                  @click="nextStep"
                  type="button"
                  class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  {{ $t('courses.next') }}
                </button>
                <button
                  v-if="currentStep === 3"
                  type="submit"
                  class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500"
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
const stepLabels = [
  'المعلومات الأساسية',
  'التفاصيل الإضافية',
  'المراجعة'
]

const form = useForm({
  title: '',
  description: '',
  category_id: '',
  price: 0,
  duration: 60,
  image: null,
  status: 'draft',
  intro_video: '',
})

const getCategoryName = (categoryId) => {
  if (!categoryId) return 'غير محدد'
  const category = props.categories.find(cat => cat.id === categoryId)
  return category ? category.name : 'غير محدد'
}

const validateStep = () => {
  if (currentStep.value === 1) {
    if (!form.title || !form.description || !form.category_id || form.price === null) {
      return false
    }
  } else if (currentStep.value === 2) {
    if (!form.duration || !form.status) {
      return false
    }
  }
  return true
}

const nextStep = () => {
  if (validateStep()) {
    currentStep.value++
  } else {
    alert('يرجى ملء جميع الحقول المطلوبة')
  }
}

const submitForm = () => {
  form.post(route('courses.store'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // إعادة التوجيه إلى صفحة إضافة الأقسام
      window.location.href = route('courses.sections.create', { course: usePage().props.value.course.id })
    },
  })
}
</script>
