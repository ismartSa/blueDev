<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div 
      v-for="stat in statsData" 
      :key="stat.label"
      :class="getStatClasses(stat.color)"
    >
      <component 
        v-if="stat.icon" 
        :is="stat.icon" 
        class="w-6 h-6 mb-2" 
      />
      <h3 class="text-sm font-medium">{{ stat.label }}</h3>
      <p class="text-2xl font-bold">{{ formatValue(stat.value) }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  AcademicCapIcon, 
  ChartBarIcon, 
  ClockIcon,
  UserGroupIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: { type: Object, required: true },
  totalQuestions: { type: Number, default: 0 }
})

const colorClasses = {
  blue: 'p-4 rounded-lg border bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400',
  green: 'p-4 rounded-lg border bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-600 dark:text-green-400',
  yellow: 'p-4 rounded-lg border bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-600 dark:text-yellow-400',
  purple: 'p-4 rounded-lg border bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400'
}

const statsData = computed(() => [
  { 
    label: 'Total Quizzes', 
    value: props.stats.total || 0, 
    icon: AcademicCapIcon, 
    color: 'blue' 
  },
  { 
    label: 'Questions', 
    value: props.totalQuestions, 
    icon: ChartBarIcon, 
    color: 'green' 
  },
  { 
    label: 'Active', 
    value: props.stats.active || 0, 
    color: 'yellow' 
  },
  { 
    label: 'Inactive', 
    value: props.stats.inactive || 0, 
    color: 'purple' 
  }
])

const getStatClasses = (color) => colorClasses[color] || colorClasses.blue

const formatValue = (value) => {
  if (typeof value === 'number' && value >= 1000) {
    return (value / 1000).toFixed(1) + 'k'
  }
  return value
}
</script>