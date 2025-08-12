<template>
  <div class="flex space-x-1">
    <Link 
      :href="route('quizzes.edit', quiz.id)"
      class="p-2 text-gray-500 hover:text-purple-600 rounded transition-colors"
      :title="'Edit ' + quiz.title"
    >
      <PencilIcon class="w-4 h-4" />
    </Link>
    
    <button 
      @click="$emit('delete', quiz)"
      class="p-2 text-gray-500 hover:text-red-600 rounded transition-colors"
      :title="'Delete ' + quiz.title"
    >
      <TrashIcon class="w-4 h-4" />
    </button>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

defineProps({
  quiz: { type: Object, required: true }
})

defineEmits(['delete'])
</script>