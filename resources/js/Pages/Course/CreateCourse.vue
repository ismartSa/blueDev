<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('courses.create_new_course') }}</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- شريط التقدم -->
            <div class="mb-8">
              <div class="flex justify-between mb-1">
                <span v-for="step in 3" :key="step"
                      :class="{'text-blue-600 font-bold': currentStep >= step, 'text-gray-400': currentStep < step}">
                  {{ $t('courses.step') }} {{ step }}: {{ stepLabels[step-1] }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${(currentStep / 3) * 100}%` }"></div>
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

              <!-- الخطوة 3: المراجعة -->
              <div v-if="currentStep === 3">
                <h3 class="text-lg font-semibold mb-4">{{ $t('courses.review_information') }}</h3>
                <div class="bg-gray-50 p-4 rounded-md mb-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <p><strong>{{ $t('courses.title') }}:</strong> {{ form.title }}</p>
                      <p><strong>{{ $t('courses.description') }}:</strong> {{ form.description }}</p>
                      <p><strong>{{ $t('courses.category') }}:</strong> {{ getCategoryName(form.category_id) }}</p>
                      <p><strong>{{ $t('courses.price') }}:</strong> {{ form.price }}</p>
                    </div>
                    <div>
                      <p><strong>{{ $t('courses.duration') }}:</strong> {{ form.duration }} {{ $t('courses.minutes') }}</p>
                      <p><strong>{{ $t('courses.image') }}:</strong> {{ form.image ? form.image.name : $t('courses.no_image') }}</p>
                      <p><strong>{{ $t('courses.status') }}:</strong> {{ form.status }}</p>
                      <p><strong>{{ $t('courses.intro_video_url') }}:</strong> {{ form.intro_video || $t('courses.none') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- أزرار التنقل -->
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
