<template>
  <span class="badge" :class="badgeClasses">
    <Icon
      v-if="icon && iconPosition === 'left'"
      :name="icon"
      :size="iconSize"
      class="badge-icon badge-icon-left"
    />
    
    <span v-if="$slots.default" class="badge-content">
      <slot />
    </span>
    
    <Icon
      v-if="icon && iconPosition === 'right'"
      :name="icon"
      :size="iconSize"
      class="badge-icon badge-icon-right"
    />
    
    <button
      v-if="dismissible"
      type="button"
      class="badge-dismiss"
      @click="handleDismiss"
    >
      <Icon name="x" :size="dismissIconSize" />
    </button>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

// Props
const props = defineProps({
  // Content
  icon: {
    type: String,
    default: null
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  
  // Appearance
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'danger', 'warning', 'info',
      'light', 'dark', 'outline-primary', 'outline-secondary', 'outline-success',
      'outline-danger', 'outline-warning', 'outline-info', 'ghost'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  rounded: {
    type: [Boolean, String],
    default: true
  },
  
  // Behavior
  dismissible: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  
  // States
  pulse: {
    type: Boolean,
    default: false
  },
  
  // Custom classes
  customClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['dismiss', 'click'])

// Computed properties
const iconSize = computed(() => {
  const sizeMap = {
    xs: 'xs',
    sm: 'xs',
    md: 'sm',
    lg: 'sm',
    xl: 'md'
  }
  return sizeMap[props.size] || 'sm'
})

const dismissIconSize = computed(() => {
  const sizeMap = {
    xs: 'xs',
    sm: 'xs',
    md: 'xs',
    lg: 'sm',
    xl: 'sm'
  }
  return sizeMap[props.size] || 'xs'
})

const badgeClasses = computed(() => {
  const classes = [
    'badge',
    `badge-${props.variant}`,
    `badge-${props.size}`,
    {
      'badge-clickable': props.clickable,
      'badge-dismissible': props.dismissible,
      'badge-pulse': props.pulse,
      'badge-rounded': props.rounded === true,
      'badge-rounded-full': props.rounded === 'full',
      'badge-rounded-none': props.rounded === false,
      'badge-with-icon': props.icon,
      'badge-icon-only': props.icon && !slots.default
    }
  ]
  
  if (props.customClass) {
    classes.push(props.customClass)
  }
  
  return classes
})

// Get slots
const slots = defineSlots()

// Methods
const handleDismiss = (event) => {
  event.stopPropagation()
  emit('dismiss')
}

const handleClick = (event) => {
  if (props.clickable) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* Base badge styles */
.badge {
  @apply inline-flex items-center font-medium transition-all duration-200;
  @apply focus:outline-none focus:ring-2 focus:ring-offset-2;
}

/* Badge sizes */
.badge-xs {
  @apply px-2 py-0.5 text-xs;
}

.badge-sm {
  @apply px-2.5 py-0.5 text-xs;
}

.badge-md {
  @apply px-3 py-1 text-sm;
}

.badge-lg {
  @apply px-3.5 py-1.5 text-sm;
}

.badge-xl {
  @apply px-4 py-2 text-base;
}

/* Badge variants */
.badge-primary {
  @apply bg-blue-100 text-blue-800;
}

.badge-secondary {
  @apply bg-gray-100 text-gray-800;
}

.badge-success {
  @apply bg-green-100 text-green-800;
}

.badge-danger {
  @apply bg-red-100 text-red-800;
}

.badge-warning {
  @apply bg-yellow-100 text-yellow-800;
}

.badge-info {
  @apply bg-cyan-100 text-cyan-800;
}

.badge-light {
  @apply bg-gray-50 text-gray-600;
}

.badge-dark {
  @apply bg-gray-800 text-white;
}

/* Outline variants */
.badge-outline-primary {
  @apply bg-transparent text-blue-600 border border-blue-600;
}

.badge-outline-secondary {
  @apply bg-transparent text-gray-600 border border-gray-600;
}

.badge-outline-success {
  @apply bg-transparent text-green-600 border border-green-600;
}

.badge-outline-danger {
  @apply bg-transparent text-red-600 border border-red-600;
}

.badge-outline-warning {
  @apply bg-transparent text-yellow-600 border border-yellow-600;
}

.badge-outline-info {
  @apply bg-transparent text-cyan-600 border border-cyan-600;
}

/* Ghost variant */
.badge-ghost {
  @apply bg-transparent text-gray-600;
}

/* Badge states */
.badge-clickable {
  @apply cursor-pointer;
}

.badge-clickable:hover {
  @apply opacity-80;
}

.badge-pulse {
  @apply animate-pulse;
}

/* Rounded variants */
.badge-rounded {
  @apply rounded-md;
}

.badge-rounded-full {
  @apply rounded-full;
}

.badge-rounded-none {
  @apply rounded-none;
}

/* Icon styles */
.badge-icon {
  @apply flex-shrink-0;
}

.badge-icon-left {
  @apply mr-1;
}

.badge-icon-right {
  @apply ml-1;
}

.badge-icon-only {
  @apply p-1;
}

.badge-icon-only .badge-icon {
  @apply m-0;
}

/* Content */
.badge-content {
  @apply whitespace-nowrap;
}

/* Dismiss button */
.badge-dismiss {
  @apply ml-1 flex-shrink-0 p-0.5 rounded-full;
  @apply hover:bg-black hover:bg-opacity-10;
  @apply focus:outline-none focus:bg-black focus:bg-opacity-10;
}

.badge-dismissible {
  @apply pr-1;
}

/* Focus styles for different variants */
.badge-primary.badge-clickable {
  @apply focus:ring-blue-500;
}

.badge-secondary.badge-clickable {
  @apply focus:ring-gray-500;
}

.badge-success.badge-clickable {
  @apply focus:ring-green-500;
}

.badge-danger.badge-clickable {
  @apply focus:ring-red-500;
}

.badge-warning.badge-clickable {
  @apply focus:ring-yellow-500;
}

.badge-info.badge-clickable {
  @apply focus:ring-cyan-500;
}

.badge-light.badge-clickable {
  @apply focus:ring-gray-500;
}

.badge-dark.badge-clickable {
  @apply focus:ring-gray-700;
}
</style>