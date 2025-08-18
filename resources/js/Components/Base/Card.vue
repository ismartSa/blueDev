<template>
  <div class="card" :class="cardClasses">
    <!-- Header -->
    <div v-if="hasHeader" class="card-header" :class="headerClasses">
      <div class="card-header-content">
        <Icon
          v-if="headerIcon"
          :name="headerIcon"
          :size="iconSize"
          class="card-header-icon"
        />
        
        <div class="card-header-text">
          <h3 v-if="title" class="card-title" :class="titleClasses">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="card-subtitle" :class="subtitleClasses">
            {{ subtitle }}
          </p>
        </div>
      </div>
      
      <div v-if="$slots.headerActions" class="card-header-actions">
        <slot name="headerActions" />
      </div>
    </div>

    <!-- Body -->
    <div v-if="hasBody" class="card-body" :class="bodyClasses">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="card-footer" :class="footerClasses">
      <slot name="footer" />
    </div>

    <!-- Loading overlay -->
    <div v-if="loading" class="card-loading">
      <Icon name="spinner" size="lg" spin class="text-blue-500" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

// Props
const props = defineProps({
  // Content
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  headerIcon: {
    type: String,
    default: null
  },
  
  // Appearance
  variant: {
    type: String,
    default: 'default',
    validator: (value) => [
      'default', 'outlined', 'elevated', 'filled', 'gradient'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  rounded: {
    type: [Boolean, String],
    default: true
  },
  shadow: {
    type: [Boolean, String],
    default: true
  },
  
  // Layout
  padding: {
    type: [Boolean, String],
    default: true
  },
  headerPadding: {
    type: [Boolean, String],
    default: true
  },
  bodyPadding: {
    type: [Boolean, String],
    default: true
  },
  footerPadding: {
    type: [Boolean, String],
    default: true
  },
  
  // States
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  hoverable: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  
  // Colors
  color: {
    type: String,
    default: 'white'
  },
  headerColor: {
    type: String,
    default: null
  },
  
  // Custom classes
  customClass: {
    type: String,
    default: ''
  },
  headerClass: {
    type: String,
    default: ''
  },
  bodyClass: {
    type: String,
    default: ''
  },
  footerClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['click'])

// Computed properties
const hasHeader = computed(() => {
  return props.title || props.subtitle || props.headerIcon || slots.headerActions
})

const hasBody = computed(() => {
  return slots.default
})

const iconSize = computed(() => {
  const sizeMap = {
    sm: 'sm',
    md: 'md',
    lg: 'lg',
    xl: 'xl'
  }
  return sizeMap[props.size] || 'md'
})

const cardClasses = computed(() => {
  const classes = [
    'card',
    `card-${props.variant}`,
    `card-${props.size}`,
    `card-${props.color}`,
    {
      'card-loading-state': props.loading,
      'card-disabled': props.disabled,
      'card-hoverable': props.hoverable,
      'card-clickable': props.clickable,
      'card-rounded': props.rounded === true,
      'card-rounded-lg': props.rounded === 'lg',
      'card-rounded-xl': props.rounded === 'xl',
      'card-rounded-none': props.rounded === false,
      'card-shadow': props.shadow === true,
      'card-shadow-sm': props.shadow === 'sm',
      'card-shadow-md': props.shadow === 'md',
      'card-shadow-lg': props.shadow === 'lg',
      'card-shadow-xl': props.shadow === 'xl',
      'card-shadow-none': props.shadow === false
    }
  ]
  
  if (props.customClass) {
    classes.push(props.customClass)
  }
  
  return classes
})

const headerClasses = computed(() => {
  const classes = [
    'card-header',
    {
      'card-header-padded': props.headerPadding === true,
      'card-header-padding-sm': props.headerPadding === 'sm',
      'card-header-padding-md': props.headerPadding === 'md',
      'card-header-padding-lg': props.headerPadding === 'lg',
      'card-header-no-padding': props.headerPadding === false
    }
  ]
  
  if (props.headerColor) {
    classes.push(`bg-${props.headerColor}`)
  }
  
  if (props.headerClass) {
    classes.push(props.headerClass)
  }
  
  return classes
})

const bodyClasses = computed(() => {
  const classes = [
    'card-body',
    {
      'card-body-padded': props.bodyPadding === true,
      'card-body-padding-sm': props.bodyPadding === 'sm',
      'card-body-padding-md': props.bodyPadding === 'md',
      'card-body-padding-lg': props.bodyPadding === 'lg',
      'card-body-no-padding': props.bodyPadding === false
    }
  ]
  
  if (props.bodyClass) {
    classes.push(props.bodyClass)
  }
  
  return classes
})

const footerClasses = computed(() => {
  const classes = [
    'card-footer',
    {
      'card-footer-padded': props.footerPadding === true,
      'card-footer-padding-sm': props.footerPadding === 'sm',
      'card-footer-padding-md': props.footerPadding === 'md',
      'card-footer-padding-lg': props.footerPadding === 'lg',
      'card-footer-no-padding': props.footerPadding === false
    }
  ]
  
  if (props.footerClass) {
    classes.push(props.footerClass)
  }
  
  return classes
})

const titleClasses = computed(() => {
  const sizeClasses = {
    sm: 'text-sm font-medium',
    md: 'text-base font-semibold',
    lg: 'text-lg font-semibold',
    xl: 'text-xl font-bold'
  }
  
  return sizeClasses[props.size] || sizeClasses.md
})

const subtitleClasses = computed(() => {
  const sizeClasses = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
    xl: 'text-lg'
  }
  
  return sizeClasses[props.size] || sizeClasses.md
})

// Get slots
const slots = defineSlots()

// Methods
const handleClick = (event) => {
  if (props.disabled || props.loading) {
    return
  }
  
  if (props.clickable) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* Base card styles */
.card {
  @apply relative bg-white overflow-hidden transition-all duration-200;
}

/* Card variants */
.card-default {
  @apply border border-gray-200;
}

.card-outlined {
  @apply border-2 border-gray-300;
}

.card-elevated {
  @apply border-0;
}

.card-filled {
  @apply bg-gray-50 border border-gray-200;
}

.card-gradient {
  @apply bg-gradient-to-br from-blue-50 to-indigo-100 border border-blue-200;
}

/* Card sizes */
.card-sm {
  @apply text-sm;
}

.card-md {
  @apply text-base;
}

.card-lg {
  @apply text-lg;
}

.card-xl {
  @apply text-xl;
}

/* Card colors */
.card-white {
  @apply bg-white;
}

.card-gray {
  @apply bg-gray-50;
}

.card-blue {
  @apply bg-blue-50;
}

.card-green {
  @apply bg-green-50;
}

.card-red {
  @apply bg-red-50;
}

.card-yellow {
  @apply bg-yellow-50;
}

/* Card states */
.card-loading-state {
  @apply pointer-events-none;
}

.card-disabled {
  @apply opacity-50 pointer-events-none;
}

.card-hoverable:hover {
  @apply transform -translate-y-1 shadow-lg;
}

.card-clickable {
  @apply cursor-pointer;
}

.card-clickable:hover {
  @apply shadow-md;
}

.card-clickable:active {
  @apply transform scale-95;
}

/* Rounded variants */
.card-rounded {
  @apply rounded-lg;
}

.card-rounded-lg {
  @apply rounded-lg;
}

.card-rounded-xl {
  @apply rounded-xl;
}

.card-rounded-none {
  @apply rounded-none;
}

/* Shadow variants */
.card-shadow {
  @apply shadow-md;
}

.card-shadow-sm {
  @apply shadow-sm;
}

.card-shadow-md {
  @apply shadow-md;
}

.card-shadow-lg {
  @apply shadow-lg;
}

.card-shadow-xl {
  @apply shadow-xl;
}

.card-shadow-none {
  @apply shadow-none;
}

/* Header styles */
.card-header {
  @apply border-b border-gray-200;
}

.card-header-content {
  @apply flex items-center;
}

.card-header-icon {
  @apply mr-3 text-gray-600;
}

.card-header-text {
  @apply flex-1;
}

.card-header-actions {
  @apply flex items-center space-x-2;
}

.card-title {
  @apply text-gray-900 leading-tight;
}

.card-subtitle {
  @apply text-gray-600 mt-1;
}

/* Padding variants */
.card-header-padded {
  @apply px-6 py-4;
}

.card-header-padding-sm {
  @apply px-4 py-3;
}

.card-header-padding-md {
  @apply px-6 py-4;
}

.card-header-padding-lg {
  @apply px-8 py-6;
}

.card-header-no-padding {
  @apply p-0;
}

.card-body-padded {
  @apply px-6 py-4;
}

.card-body-padding-sm {
  @apply px-4 py-3;
}

.card-body-padding-md {
  @apply px-6 py-4;
}

.card-body-padding-lg {
  @apply px-8 py-6;
}

.card-body-no-padding {
  @apply p-0;
}

.card-footer {
  @apply border-t border-gray-200;
}

.card-footer-padded {
  @apply px-6 py-4;
}

.card-footer-padding-sm {
  @apply px-4 py-3;
}

.card-footer-padding-md {
  @apply px-6 py-4;
}

.card-footer-padding-lg {
  @apply px-8 py-6;
}

.card-footer-no-padding {
  @apply p-0;
}

/* Loading overlay */
.card-loading {
  @apply absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10;
}
</style>