<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :href="tag === 'a' ? href : undefined"
    :to="tag === 'router-link' ? to : undefined"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <Icon
      v-if="loading"
      name="spinner"
      :size="iconSize"
      spin
      class="mr-2"
    />
    <Icon
      v-else-if="icon && iconPosition === 'left'"
      :name="icon"
      :size="iconSize"
      class="mr-2"
    />
    
    <span v-if="$slots.default" class="button-content">
      <slot />
    </span>
    
    <Icon
      v-if="!loading && icon && iconPosition === 'right'"
      :name="icon"
      :size="iconSize"
      class="ml-2"
    />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

// Props
const props = defineProps({
  // Button content
  icon: {
    type: String,
    default: null
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  
  // Button behavior
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  
  // Button appearance
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'danger', 'warning', 'info',
      'light', 'dark', 'outline-primary', 'outline-secondary', 'outline-success',
      'outline-danger', 'outline-warning', 'outline-info', 'ghost', 'link'
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
  block: {
    type: Boolean,
    default: false
  },
  
  // Link props
  href: {
    type: String,
    default: null
  },
  to: {
    type: [String, Object],
    default: null
  },
  
  // Custom classes
  customClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['click'])

// Computed properties
const tag = computed(() => {
  if (props.href) return 'a'
  if (props.to) return 'router-link'
  return 'button'
})

const iconSize = computed(() => {
  const sizeMap = {
    xs: 'xs',
    sm: 'sm',
    md: 'sm',
    lg: 'md',
    xl: 'lg'
  }
  return sizeMap[props.size] || 'sm'
})

const buttonClasses = computed(() => {
  const classes = [
    'btn',
    `btn-${props.variant}`,
    `btn-${props.size}`,
    {
      'btn-block': props.block,
      'btn-loading': props.loading,
      'btn-disabled': props.disabled,
      'btn-rounded': props.rounded === true,
      'btn-rounded-full': props.rounded === 'full',
      'btn-rounded-none': props.rounded === false
    }
  ]
  
  if (props.customClass) {
    classes.push(props.customClass)
  }
  
  return classes
})

// Methods
const handleClick = (event) => {
  if (props.disabled || props.loading) {
    event.preventDefault()
    return
  }
  emit('click', event)
}
</script>

<style scoped>
/* Base button styles */
.btn {
  @apply inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out;
  @apply focus:outline-none focus:ring-2 focus:ring-offset-2;
  @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

/* Button sizes */
.btn-xs {
  @apply px-2 py-1 text-xs;
}

.btn-sm {
  @apply px-3 py-1.5 text-sm;
}

.btn-md {
  @apply px-4 py-2 text-sm;
}

.btn-lg {
  @apply px-6 py-3 text-base;
}

.btn-xl {
  @apply px-8 py-4 text-lg;
}

/* Button variants */
.btn-primary {
  @apply bg-blue-600 text-white border border-blue-600;
  @apply hover:bg-blue-700 hover:border-blue-700;
  @apply focus:ring-blue-500;
}

.btn-secondary {
  @apply bg-gray-600 text-white border border-gray-600;
  @apply hover:bg-gray-700 hover:border-gray-700;
  @apply focus:ring-gray-500;
}

.btn-success {
  @apply bg-green-600 text-white border border-green-600;
  @apply hover:bg-green-700 hover:border-green-700;
  @apply focus:ring-green-500;
}

.btn-danger {
  @apply bg-red-600 text-white border border-red-600;
  @apply hover:bg-red-700 hover:border-red-700;
  @apply focus:ring-red-500;
}

.btn-warning {
  @apply bg-yellow-600 text-white border border-yellow-600;
  @apply hover:bg-yellow-700 hover:border-yellow-700;
  @apply focus:ring-yellow-500;
}

.btn-info {
  @apply bg-cyan-600 text-white border border-cyan-600;
  @apply hover:bg-cyan-700 hover:border-cyan-700;
  @apply focus:ring-cyan-500;
}

.btn-light {
  @apply bg-gray-100 text-gray-900 border border-gray-300;
  @apply hover:bg-gray-200 hover:border-gray-400;
  @apply focus:ring-gray-500;
}

.btn-dark {
  @apply bg-gray-900 text-white border border-gray-900;
  @apply hover:bg-gray-800 hover:border-gray-800;
  @apply focus:ring-gray-700;
}

/* Outline variants */
.btn-outline-primary {
  @apply bg-transparent text-blue-600 border border-blue-600;
  @apply hover:bg-blue-600 hover:text-white;
  @apply focus:ring-blue-500;
}

.btn-outline-secondary {
  @apply bg-transparent text-gray-600 border border-gray-600;
  @apply hover:bg-gray-600 hover:text-white;
  @apply focus:ring-gray-500;
}

.btn-outline-success {
  @apply bg-transparent text-green-600 border border-green-600;
  @apply hover:bg-green-600 hover:text-white;
  @apply focus:ring-green-500;
}

.btn-outline-danger {
  @apply bg-transparent text-red-600 border border-red-600;
  @apply hover:bg-red-600 hover:text-white;
  @apply focus:ring-red-500;
}

.btn-outline-warning {
  @apply bg-transparent text-yellow-600 border border-yellow-600;
  @apply hover:bg-yellow-600 hover:text-white;
  @apply focus:ring-yellow-500;
}

.btn-outline-info {
  @apply bg-transparent text-cyan-600 border border-cyan-600;
  @apply hover:bg-cyan-600 hover:text-white;
  @apply focus:ring-cyan-500;
}

/* Ghost and link variants */
.btn-ghost {
  @apply bg-transparent text-gray-600 border border-transparent;
  @apply hover:bg-gray-100 hover:text-gray-900;
  @apply focus:ring-gray-500;
}

.btn-link {
  @apply bg-transparent text-blue-600 border border-transparent;
  @apply hover:text-blue-800 hover:underline;
  @apply focus:ring-blue-500;
  @apply p-0;
}

/* Button states */
.btn-block {
  @apply w-full;
}

.btn-loading {
  @apply cursor-wait;
}

.btn-disabled {
  @apply opacity-50 cursor-not-allowed;
}

/* Rounded variants */
.btn-rounded {
  @apply rounded-md;
}

.btn-rounded-full {
  @apply rounded-full;
}

.btn-rounded-none {
  @apply rounded-none;
}

/* Button content */
.button-content {
  @apply flex items-center;
}

/* Loading state animation */
.btn-loading .button-content {
  @apply opacity-75;
}
</style>