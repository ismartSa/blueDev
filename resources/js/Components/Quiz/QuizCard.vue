<template>
  <div class="bg-white dark:bg-slate-800 rounded-lg shadow border hover:shadow-lg transition-all">
    <div class="p-6">
      <div class="flex items-start justify-between mb-4">
        <input 
          v-if="showCheckbox" 
          type="checkbox" 
          :value="quiz.id" 
          :checked="isSelected"
          @change="$emit('toggle-select', quiz.id)"
          class="rounded border-gray-300 text-purple-600" 
        />
        <QuizBadge :count="quiz.questions_count" label="Questions" />
      </div>
      
      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
        <Link :href="route('quizzes.show', quiz.id)">{{ quiz.title }}</Link>
      </h3>
      
      <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
        {{ quiz.description || 'No description available' }}
      </p>
      
      <div class="grid grid-cols-2 gap-3 mb-4">
        <QuizMetric 
          icon="ClockIcon" 
          label="Duration" 
          :value="`${quiz.time_limit}min`" 
          color="blue" 
        />
        <QuizMetric 
          icon="ChartBarIcon" 
          label="Pass Score" 
          :value="`${quiz.passing_score}%`" 
          color="green" 
        />
      </div>
      
      <div class="flex items-center justify-between pt-4 border-t">
        <Link 
          :href="route('quizzes.show', quiz.id)"
          class="text-sm font-medium text-purple-600 hover:text-purple-800 flex items-center"
        >
          <EyeIcon class="w-4 h-4 mr-1" />View
        </Link>
        
        <QuizActions 
          :quiz="quiz" 
          @edit="$emit('edit', quiz)"
          @delete="$emit('delete', quiz)" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { EyeIcon } from '@heroicons/vue/24/outline'
import QuizBadge from './QuizBadge.vue'
import QuizMetric from './QuizMetric.vue'
import QuizActions from './QuizActions.vue'

defineProps({
  quiz: { type: Object, required: true },
  showCheckbox: { type: Boolean, default: false },
  isSelected: { type: Boolean, default: false }
})

defineEmits(['toggle-select', 'edit', 'delete'])
</script>

<style scoped>
.line-clamp-2 { 
  display: -webkit-box; 
  -webkit-line-clamp: 2; 
  -webkit-box-orient: vertical; 
  overflow: hidden; 
}
</style>