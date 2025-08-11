<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('sections.add_sections') }} - {{ course.title }}</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- نموذج إضافة قسم جديد -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">{{ $t('sections.add_new_section') }}</h3>

            <form @submit.prevent="submitSection">
              <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('sections.title') }}:</label>
                <input id="title" v-model="sectionForm.title" type="text" class="form-input w-full rounded-md shadow-sm" required>
                <div v-if="sectionForm.errors.title" class="text-red-500 text-sm mt-1">{{ sectionForm.errors.title }}</div>
              </div>

              <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('sections.description') }}:</label>
                <textarea id="description" v-model="sectionForm.description" class="form-textarea w-full rounded-md shadow-sm" rows="3" required></textarea>
                <div v-if="sectionForm.errors.description" class="text-red-500 text-sm mt-1">{{ sectionForm.errors.description }}</div>
              </div>

              <div class="mb-4">
                <label for="order" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('sections.order') }}:</label>
                <input id="order" v-model="sectionForm.order" type="number" min="1" class="form-input w-full rounded-md shadow-sm" required>
                <div v-if="sectionForm.errors.order" class="text-red-500 text-sm mt-1">{{ sectionForm.errors.order }}</div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  :disabled="sectionForm.processing"
                >
                  {{ $t('sections.add_section') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- قائمة الأقسام الحالية -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">{{ $t('sections.current_sections') }}</h3>

            <div v-if="sections.length === 0" class="text-gray-500 text-center py-4">
              {{ $t('sections.no_sections_yet') }}
            </div>

            <div v-else class="space-y-4">
              <div v-for="section in sections" :key="section.id" class="border rounded-md p-4">
                <div class="flex justify-between items-center">
                  <div>
                    <h4 class="font-semibold">{{ section.title }}</h4>
                    <p class="text-sm text-gray-600">{{ section.description }}</p>
                    <p class="text-xs text-gray-500">{{ $t('sections.order') }}: {{ section.order }}</p>
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="editSection(section)"
                      class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm"
                    >
                      {{ $t('sections.edit') }}
                    </button>
                    <button
                      @click="deleteSection(section.id)"
                      class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm"
                    >
                      {{ $t('sections.delete') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex justify-between">
              <button
                @click="goToCourse"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              >
                {{ $t('sections.back_to_course') }}
              </button>

              <button
                @click="goToLectures"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              >
                {{ $t('sections.continue_to_lectures') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- نافذة تعديل القسم -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ $t('sections.edit_section') }}</h3>

        <form @submit.prevent="updateSection">
          <div class="mb-4">
            <label for="edit-title" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('sections.title') }}:</label>
            <input id="edit-title" v-model="editSectionForm.title" type="text" class="form-input w-full rounded-md shadow-sm" required>
          </div>

          <div class="mb-4">
            <label for="edit-description" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('sections.description') }}:</label>
            <textarea id="edit-description" v-model="editSectionForm.description" class="form-textarea w-full rounded-md shadow-sm" rows="3" required></textarea>
          </div>

          <div class="mb-4">
            <label for="edit-order" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('sections.order') }}:</label>
            <input id="edit-order" v-model="editSectionForm.order" type="number" min="1" class="form-input w-full rounded-md shadow-sm" required>
          </div>

          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showEditModal = false"
              class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            >
              {{ $t('sections.cancel') }}
            </button>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            >
              {{ $t('sections.update') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  course: Object,
  sections: Array,
})

const sectionForm = useForm({
  title: '',
  description: '',
  order: 1,
  course_id: props.course.id
})

const showEditModal = ref(false)
const currentSectionId = ref(null)
const editSectionForm = useForm({
  title: '',
  description: '',
  order: 1
})

const submitSection = () => {
  sectionForm.post(route('courses.sections.store', { course: props.course.id }), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      sectionForm.reset()
      sectionForm.order = props.sections.length + 1
    }
  })
}

const editSection = (section) => {
  currentSectionId.value = section.id
  editSectionForm.title = section.title
  editSectionForm.description = section.description
  editSectionForm.order = section.order
  showEditModal.value = true
}

const updateSection = () => {
  editSectionForm.put(route('courses.sections.update', { course: props.course.id, section: currentSectionId.value }), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false
    }
  })
}

const deleteSection = (sectionId) => {
  if (confirm('هل أنت متأكد من حذف هذا القسم؟')) {
    useForm().delete(route('courses.sections.destroy', { course: props.course.id, section: sectionId }), {
      preserveState: true,
      preserveScroll: true
    })
  }
}

const goToCourse = () => {
  window.location.href = route('courses.details', { id: props.course.id, courseSlug: props.course.slug })
}

const goToLectures = () => {
  window.location.href = route('courses.lectures.create', { course: props.course.id })
}
</script>
