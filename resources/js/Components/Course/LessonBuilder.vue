<template>
    <div class="lesson-builder">
      <div v-for="(lesson, index) in lessons" :key="index" class="lesson-item">
        <div class="lesson-type">
          <SelectInput
            v-model="lesson.type"
            :options="lessonTypes"
            :label="$t('Lesson Type')"
          />
        </div>

        <TextInput
          v-model="lesson.title"
          :label="$t('Lesson Title')"
        />

        <component
          :is="lessonComponent(lesson.type)"
          v-model="lesson.content"
        />

        <button @click="removeLesson(index)" class="btn-danger">
          {{ $t('Remove Lesson') }}
        </button>
      </div>

      <button @click="addLesson" class="btn-secondary">
        {{ $t('Add Lesson') }}
      </button>
    </div>
  </template>

  <script setup>
  import VideoLesson from '@/Components/Course/LessonTypes/VideoLesson.vue'
  import QuizLesson from '@/Components/Course/LessonTypes/QuizLesson.vue'

  const lessonTypes = [
    { value: 'video', label: 'Video' },
    { value: 'quiz', label: 'Quiz' },
  ]

  const lessonComponent = (type) => {
    return {
      video: VideoLesson,
      quiz: QuizLesson,
    }[type]
  }

  const props = defineProps({
    lessons: Array,
  })

  const addLesson = () => {
    props.lessons.push({
      type: 'video',
      title: '',
      content: null,
      duration: 0,
      order: props.lessons.length + 1
    })
  }

  const removeLesson = (index) => {
    props.lessons.splice(index, 1)
  }
  </script>
