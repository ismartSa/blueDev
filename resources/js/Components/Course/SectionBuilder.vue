<template>
    <div class="section-builder">
      <div v-for="(section, index) in sections" :key="index" class="section-item">
        <div class="section-header">
          <TextInput
            v-model="section.title"
            :label="$t('Section Title')"
          />

          <button @click="removeSection(index)" class="btn-danger">
            {{ $t('Remove Section') }}
          </button>
        </div>

        <LessonBuilder
          :lessons="section.lessons"
          @add-lesson="addLesson(section)"
          @remove-lesson="removeLesson(section, lessonIndex)"
        />
      </div>

      <button @click="addSection" class="btn-primary">
        {{ $t('Add New Section') }}
      </button>
    </div>
  </template>

  <script setup>
  import LessonBuilder from '@/Components/Course/LessonBuilder.vue'

  const props = defineProps({
    sections: Array,
  })

  const emit = defineEmits(['add-section', 'remove-section'])

  const addSection = () => {
    emit('add-section')
  }

  const removeSection = (index) => {
    emit('remove-section', index)
  }
  </script>
