<template>
  <span :class="badgeClasses">
    {{ count }} {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  count: { type: [Number, String], required: true },
  label: { type: String, required: true },
  color: { type: String, default: 'purple' }
})

const colorMap = {
  purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
  blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
  green: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
  indigo: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200'
}

const badgeClasses = computed(() => 
  `px-3 py-1 text-xs font-medium rounded-full ${colorMap[props.color] || colorMap.purple}`
)
</script>