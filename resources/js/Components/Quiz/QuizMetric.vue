<template>
  <div :class="containerClasses">
    <div class="flex items-center mb-1">
      <component :is="iconComponent" :class="iconClasses" />
      <span class="text-xs text-gray-600 dark:text-gray-400">{{ label }}</span>
    </div>
    <p class="font-bold text-gray-900 dark:text-white">{{ value }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  ClockIcon, 
  ChartBarIcon, 
  AcademicCapIcon,
  UserGroupIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  icon: { type: String, required: true },
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  color: { type: String, default: 'blue' }
})

const iconMap = {
  ClockIcon,
  ChartBarIcon,
  AcademicCapIcon,
  UserGroupIcon
}

const colorMap = {
  blue: {
    container: 'bg-blue-50 dark:bg-slate-700 rounded-lg p-3',
    icon: 'w-4 h-4 text-blue-500 mr-2'
  },
  green: {
    container: 'bg-green-50 dark:bg-slate-700 rounded-lg p-3',
    icon: 'w-4 h-4 text-green-500 mr-2'
  },
  purple: {
    container: 'bg-purple-50 dark:bg-slate-700 rounded-lg p-3',
    icon: 'w-4 h-4 text-purple-500 mr-2'
  },
  yellow: {
    container: 'bg-yellow-50 dark:bg-slate-700 rounded-lg p-3',
    icon: 'w-4 h-4 text-yellow-500 mr-2'
  }
}

const iconComponent = computed(() => iconMap[props.icon])
const containerClasses = computed(() => colorMap[props.color]?.container || colorMap.blue.container)
const iconClasses = computed(() => colorMap[props.color]?.icon || colorMap.blue.icon)
</script>