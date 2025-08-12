<template>
  <div :class="cardClasses" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 transition-all duration-200">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <!-- Icon -->
        <div v-if="icon" :class="iconClasses" class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg">
          <component :is="icon" class="w-6 h-6" />
        </div>
        
        <!-- Title -->
        <div>
          <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ title }}</h3>
          <p v-if="subtitle" class="text-xs text-gray-400 mt-1">{{ subtitle }}</p>
        </div>
      </div>
      
      <!-- Actions -->
      <div v-if="$slots.actions" class="flex items-center space-x-2">
        <slot name="actions" />
      </div>
    </div>
    
    <!-- Value -->
    <div class="mt-4">
      <div class="flex items-baseline space-x-2">
        <span :class="valueClasses" class="text-3xl font-bold">
          {{ formattedValue }}
        </span>
        
        <!-- Change Indicator -->
        <div v-if="change !== null" :class="changeClasses" class="flex items-center text-sm font-medium">
          <svg v-if="changeDirection === 'up'" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
          <svg v-else-if="changeDirection === 'down'" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          <span>{{ Math.abs(change) }}{{ changeUnit }}</span>
        </div>
      </div>
      
      <!-- Description -->
      <p v-if="description" class="text-sm text-gray-600 mt-2">{{ description }}</p>
    </div>
    
    <!-- Progress Bar -->
    <div v-if="showProgress" class="mt-4">
      <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
        <span>Progress</span>
        <span>{{ progressPercentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div 
          :class="progressClasses" 
          class="h-2 rounded-full transition-all duration-300"
          :style="{ width: `${progressPercentage}%` }"
        />
      </div>
    </div>
    
    <!-- Footer -->
    <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-gray-100">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  value: { type: [String, Number], required: true },
  description: { type: String, default: '' },
  icon: { type: [String, Object], default: null },
  color: { type: String, default: 'blue', validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'indigo', 'pink', 'gray'].includes(value) },
  change: { type: Number, default: null },
  changeUnit: { type: String, default: '%' },
  format: { type: String, default: 'number', validator: (value) => ['number', 'currency', 'percentage'].includes(value) },
  currency: { type: String, default: 'USD' },
  locale: { type: String, default: 'en-US' },
  showProgress: { type: Boolean, default: false },
  progress: { type: Number, default: 0 },
  maxProgress: { type: Number, default: 100 },
  clickable: { type: Boolean, default: false },
  loading: { type: Boolean, default: false }
})

// Emits
const emit = defineEmits(['click'])

// Computed
const cardClasses = computed(() => ({
  'hover:shadow-md cursor-pointer': props.clickable,
  'opacity-50': props.loading
}))

const iconClasses = computed(() => {
  const colorClasses = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    red: 'bg-red-100 text-red-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    purple: 'bg-purple-100 text-purple-600',
    indigo: 'bg-indigo-100 text-indigo-600',
    pink: 'bg-pink-100 text-pink-600',
    gray: 'bg-gray-100 text-gray-600'
  }
  
  return colorClasses[props.color]
})

const valueClasses = computed(() => {
  const colorClasses = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    red: 'text-red-600',
    yellow: 'text-yellow-600',
    purple: 'text-purple-600',
    indigo: 'text-indigo-600',
    pink: 'text-pink-600',
    gray: 'text-gray-900'
  }
  
  return colorClasses[props.color]
})

const changeDirection = computed(() => {
  if (props.change === null || props.change === 0) return null
  return props.change > 0 ? 'up' : 'down'
})

const changeClasses = computed(() => {
  if (changeDirection.value === 'up') {
    return 'text-green-600'
  } else if (changeDirection.value === 'down') {
    return 'text-red-600'
  }
  return 'text-gray-600'
})

const progressPercentage = computed(() => {
  return Math.min(Math.max((props.progress / props.maxProgress) * 100, 0), 100)
})

const progressClasses = computed(() => {
  const colorClasses = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    red: 'bg-red-500',
    yellow: 'bg-yellow-500',
    purple: 'bg-purple-500',
    indigo: 'bg-indigo-500',
    pink: 'bg-pink-500',
    gray: 'bg-gray-500'
  }
  
  return colorClasses[props.color]
})

const formattedValue = computed(() => {
  if (props.loading) return '---'
  
  const numValue = typeof props.value === 'string' ? parseFloat(props.value) : props.value
  
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat(props.locale, {
        style: 'currency',
        currency: props.currency
      }).format(numValue)
      
    case 'percentage':
      return new Intl.NumberFormat(props.locale, {
        style: 'percent',
        minimumFractionDigits: 0,
        maximumFractionDigits: 1
      }).format(numValue / 100)
      
    case 'number':
    default:
      if (typeof props.value === 'string' && isNaN(numValue)) {
        return props.value
      }
      return new Intl.NumberFormat(props.locale).format(numValue)
  }
})

// Methods
const handleClick = () => {
  if (props.clickable && !props.loading) {
    emit('click')
  }
}
</script>