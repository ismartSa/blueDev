<template>
  <div :class="containerClasses">
    <!-- Label (optional) -->
    <label v-if="label" :for="id" :class="UI.LABEL">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <!-- Status Text (optional) -->
    <span v-if="showStatus" :class="statusTextClasses">
      {{ statusText }}
    </span>
    
    <!-- Toggle Switch -->
    <button
      type="button"
      :id="id"
      @click="toggle"
      :disabled="disabled"
      :class="switchClasses"
      role="switch"
      :aria-checked="modelValue"
      :aria-labelledby="label ? id + '-label' : undefined"
      :aria-describedby="description ? id + '-desc' : undefined"
    >
      <!-- Switch Handle -->
      <span :class="handleClasses" />
      
      <!-- Icons (optional) -->
      <span v-if="showIcons" :class="iconContainerClasses">
        <component 
          :is="modelValue ? onIcon : offIcon" 
          :class="iconClasses"
        />
      </span>
    </button>
    
    <!-- Description (optional) -->
    <p v-if="description" :id="id + '-desc'" :class="UI.DESCRIPTION">
      {{ description }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CheckIcon, XMarkIcon } from '@heroicons/vue/20/solid'

// Props with defaults
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  description: { type: String, default: '' },
  size: { type: String, default: 'md', validator: v => ['sm', 'md', 'lg'].includes(v) },
  variant: { type: String, default: 'primary', validator: v => ['primary', 'success', 'warning', 'danger'].includes(v) },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  showStatus: { type: Boolean, default: false },
  showIcons: { type: Boolean, default: false },
  onIcon: { type: [String, Object], default: () => CheckIcon },
  offIcon: { type: [String, Object], default: () => XMarkIcon },
  onText: { type: String, default: 'On' },
  offText: { type: String, default: 'Off' },
  id: { type: String, default: () => `switch-${Math.random().toString(36).substr(2, 9)}` }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Consolidated UI classes - DRY principle
const UI = {
  CONTAINER: 'flex items-center gap-3',
  CONTAINER_VERTICAL: 'space-y-2',
  LABEL: 'text-sm font-medium text-gray-700 dark:text-gray-300',
  DESCRIPTION: 'text-xs text-gray-500 dark:text-gray-400',
  STATUS_BASE: 'text-xs font-medium px-2 py-1 rounded-full',
  SWITCH_BASE: 'relative inline-flex flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
  HANDLE_BASE: 'pointer-events-none inline-block transform rounded-full bg-white shadow-lg ring-0 transition-all duration-200 ease-in-out',
  ICON_CONTAINER: 'absolute inset-0 flex items-center justify-center',
  ICON_BASE: 'transition-opacity duration-200'
}

// Size configurations
const SIZES = {
  sm: {
    switch: 'h-5 w-9',
    handle: 'h-4 w-4',
    translate: 'translate-x-4',
    ring: 'focus:ring-1',
    icon: 'h-3 w-3'
  },
  md: {
    switch: 'h-6 w-11',
    handle: 'h-5 w-5',
    translate: 'translate-x-5',
    ring: 'focus:ring-2',
    icon: 'h-4 w-4'
  },
  lg: {
    switch: 'h-7 w-13',
    handle: 'h-6 w-6',
    translate: 'translate-x-6',
    ring: 'focus:ring-3',
    icon: 'h-5 w-5'
  }
}

// Variant configurations
const VARIANTS = {
  primary: {
    on: 'bg-blue-600 focus:ring-blue-500',
    off: 'bg-gray-200 dark:bg-gray-700 focus:ring-gray-500',
    status: { on: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', off: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }
  },
  success: {
    on: 'bg-green-600 focus:ring-green-500',
    off: 'bg-gray-200 dark:bg-gray-700 focus:ring-gray-500',
    status: { on: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', off: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }
  },
  warning: {
    on: 'bg-yellow-600 focus:ring-yellow-500',
    off: 'bg-gray-200 dark:bg-gray-700 focus:ring-gray-500',
    status: { on: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', off: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }
  },
  danger: {
    on: 'bg-red-600 focus:ring-red-500',
    off: 'bg-gray-200 dark:bg-gray-700 focus:ring-gray-500',
    status: { on: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', off: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }
  }
}

// Computed classes - Dynamic rendering
const containerClasses = computed(() => [
  props.label || props.description ? UI.CONTAINER_VERTICAL : UI.CONTAINER
])

const statusText = computed(() => props.modelValue ? props.onText : props.offText)

const statusTextClasses = computed(() => [
  UI.STATUS_BASE,
  VARIANTS[props.variant].status[props.modelValue ? 'on' : 'off']
])

const switchClasses = computed(() => [
  UI.SWITCH_BASE,
  SIZES[props.size].switch,
  SIZES[props.size].ring,
  props.modelValue ? VARIANTS[props.variant].on : VARIANTS[props.variant].off
])

const handleClasses = computed(() => [
  UI.HANDLE_BASE,
  SIZES[props.size].handle,
  props.modelValue ? SIZES[props.size].translate : 'translate-x-0'
])

const iconContainerClasses = computed(() => [
  UI.ICON_CONTAINER,
  props.modelValue ? 'justify-start pl-1' : 'justify-end pr-1'
])

const iconClasses = computed(() => [
  UI.ICON_BASE,
  SIZES[props.size].icon,
  'text-white'
])

// Methods
const toggle = () => {
  if (props.disabled) return
  
  const newValue = !props.modelValue
  emit('update:modelValue', newValue)
  emit('change', newValue)
}
</script>