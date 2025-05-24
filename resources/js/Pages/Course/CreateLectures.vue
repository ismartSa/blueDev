<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('lectures.add_lectures') }} - {{ course.title }}</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- نموذج إضافة محاضرة جديدة -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">{{ $t('lectures.add_new_lecture') }}</h3>

            <form @submit.prevent="submitLecture">
              <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('lectures.title') }}:</label>
                <input id="title" v-model="lectureForm.title" type="text" class="form-input w-full rounded-md shadow-sm" required>
                <div v-if="lectureForm.errors.title" class="text-red-500 text-sm mt-1">{{ lectureForm.errors.title }}</div>
              </div>

              <div class="mb-4">
                <label for="section_id" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('lectures.section') }}:</label>
                <select id="section_id" v-model="lectureForm.section_id" class="form-select w-full rounded-md shadow-sm" required>
                  <option value="">{{ $t('lectures.select_section') }}</option>
                  <option v-for="section in sections" :key="section.id" :value="section.id">
                    {{ section.title }}
                  </option>
                </select>
                <div v-if="lectureForm.errors.section_id" class="text-red-500 text-sm mt-1">{{ lectureForm.errors.section_id }}</div>
              </div>

              <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('lectures.description') }}:</label>
                <textarea id="description" v-model="lectureForm.description" class="form-textarea w-full rounded-md shadow-sm" rows="3"></textarea>
                <div v-if="lectureForm.errors.description" class="text-red-500 text-sm mt-1">{{ lectureForm.errors.description }}</div>
              </div>

              <div class="mb-4">
                <label for="video_url" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('lectures.video_url') }}:</label>
                <input id="video_url" v-model="lectureForm.video_url" type="url" class="form-input w-full rounded-md shadow-sm" required>
                <div v-if="lectureForm.errors.video_url" class="text-red-500 text-sm mt-1">{{ lectureForm.errors.video_url }}</div>
              </div>

              <div class="mb-4">
                <label for="duration" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('lectures.duration') }} ({{ $t('lectures.in_minutes') }}):</label>
                <input id="duration" v-model="lectureForm.duration" type="number" min="1" class="form-input w-full rounded-md shadow-sm" required>
                <div v-if="lectureForm.errors.duration" class="text-red-500 text-sm mt-1">{{ lectureForm.errors.duration }}</div>
              </div>

              <div class="mb-4">
                <label for="order" class="block text-gray-700 text-sm font-bold mb-2">{{ $t('lectures.order') }}:</label>
                <input id="order" v-model="lectureForm.order" type="number" min="1" class="form-input w-full rounded-md shadow-sm" required>
                <div v-if="lectureForm.errors.order" class="text-red-500 text-sm mt-1">{{ lectureForm.errors.order }}</div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  :disabled="lectureForm.processing"
                >
                  {{ $t('lectures.add_lecture') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- قائمة المحاضرات حسب الأقسام -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">{{ $t('lectures.current_lectures') }}</h3>

            <div v-if="sections.length === 0" class="text-gray-500 text-center py-4">
              {{ $t('lectures.no_sections_yet') }}
            </div>

            <div v-else>
              <div v-for="section in sections" :key="section.id" class="mb-6">
                <h4 class="font-semibold text-lg border-b pb-2 mb-3">{{ section.title }}</h4>

                <div v-if="!section.lectures || section.lectures.length === 0" class="text-gray-500 text-sm py-2">
                  {{ $t('lectures.no_lectures_in_section') }}
                </div>

                <div v-else>
                  <!-- Display lectures here -->
                  <ul class="divide-y divide-gray-200">
                    <li v-for="lecture in section.lectures" :key="lecture.id" class="py-3 flex justify-between items-center">
                      <div>
                        <span class="font-medium">{{ lecture.title }}</span>
                        <p class="text-sm text-gray-500">{{ lecture.duration }} {{ $t('lectures.minutes') }}</p>
                      </div>
                      <!-- Add actions if needed -->
                    </li>
                  </ul>
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
// Import necessary components and functions
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Define props
const props = defineProps({
  course: Object,
  sections: Array
});

// Create form for new lecture
const lectureForm = useForm({
  title: '',
  description: '',
  video_url: '',
  duration: '',
  order: '',
  section_id: '',
  course_id: props.course.id
});

// Submit lecture form
const submitLecture = () => {
  lectureForm.post(route('courses.lectures.store', props.course.id), {
    onSuccess: () => {
      // Reset form after successful submission
      lectureForm.reset();
    }
  });
};
</script>
