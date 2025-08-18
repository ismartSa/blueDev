<template>
  <component
    :is="tag"
    :href="tag === 'a' ? href : undefined"
    :to="tag === 'router-link' ? to : undefined"
    :disabled="disabled"
    class="dropdown-item"
    :class="itemClasses"
    :tabindex="disabled ? -1 : 0"
    :role="role"
    @click="handleClick"
    @keydown.enter="handleClick"
    @keydown.space.prevent="handleClick"
  >
    <Icon
      v-if="icon && iconPosition === 'left'"
      :name="icon"
      :size="iconSize"
      class="dropdown-item-icon dropdown-item-icon-left"
    />
    
    <div class="dropdown-item-content">
      <div v-if="title" class="dropdown-item-title">
        {{ title }}
      </div>
      
      <div v-if="description" class="dropdown-item-description">
        {{ description }}
      </div>
      
      <slot />
    </div>
    
    <Icon
      v-if="icon && iconPosition === 'right'"
      :name="icon"
      :size="iconSize"
      class="dropdown-item-icon dropdown-item-icon-right"
    />
    
    <Badge
      v-if="badge"
      :variant="badgeVariant"
      :size="badgeSize"
      class="dropdown-item-badge"
    >
      {{ badge }}
    </Badge>
    
    <Icon
      v-if="hasSubmenu"
      name="chevron-right"
      size="sm"
      class="dropdown-item-submenu-icon"
    />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'
import Badge from './Badge.vue'

// Props
const props = defineProps({
  // Content
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: null
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  badge: {
    type: [String, Number],
    default: null
  },
  badgeVariant: {
    type: String,
    default: 'secondary'
  },
  
  // Behavior
  disabled: {
    type: Boolean,
    default: false
  },
  active: {
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
  
  // Appearance
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'danger', 'success', 'warning'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // Features
  hasSubmenu: {
    type: Boolean,
    default: false
  },
  divider: {
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
const emit = defineEmits(['click'])

// Computed properties
const tag = computed(() => {
  if (props.href) return 'a'
  if (props.to) return 'router-link'
  return 'button'
})

const role = computed(() => {
  return props.hasSubmenu ? 'menuitem' : 'menuitem'
})

const iconSize = computed(() => {
  const sizeMap = {
    sm: 'xs',
    md: 'sm',
    lg: 'md'
  }
  return sizeMap[props.size] || 'sm'
})

const badgeSize = computed(() => {
  const sizeMap = {
    sm: 'xs',
    md: 'xs',
    lg: 'sm'
  }
  return sizeMap[props.size] || 'xs'
})

const itemClasses = computed(() => {
  const classes = [
    'dropdown-item',
    `dropdown-item-${props.variant}`,
    `dropdown-item-${props.size}`,
    {
      'dropdown-item-disabled': props.disabled,
      'dropdown-item-active': props.active,
      'dropdown-item-with-icon': props.icon,
      'dropdown-item-with-badge': props.badge,
      'dropdown-item-with-submenu': props.hasSubmenu,
      'dropdown-item-divider': props.divider,
      'dropdown-item-has-description': props.description
    }
  ]
  
  if (props.customClass) {
    classes.push(props.customClass)
  }
  
  return classes
})

// Methods
const handleClick = (event) => {
  if (props.disabled) {
    event.preventDefault()
    return
  }
  
  emit('click', event)
}
</script>

<style scoped>
/* Base item styles */
.dropdown-item {
  @apply w-full flex items-center text-left transition-colors duration-150;
  @apply focus:outline-none focus:bg-gray-50;
  @apply border-none bg-transparent;
}

/* Item sizes */
.dropdown-item-sm {
  @apply px-3 py-1.5 text-sm;
}

.dropdown-item-md {
  @apply px-4 py-2 text-sm;
}

.dropdown-item-lg {
  @apply px-5 py-3 text-base;
}

/* Item variants */
.dropdown-item-default {
  @apply text-gray-700 hover:bg-gray-50;
}

.dropdown-item-danger {
  @apply text-red-700 hover:bg-red-50;
}

.dropdown-item-success {
  @apply text-green-700 hover:bg-green-50;
}

.dropdown-item-warning {
  @apply text-yellow-700 hover:bg-yellow-50;
}

/* Item states */
.dropdown-item-disabled {
  @apply opacity-50 cursor-not-allowed pointer-events-none;
}

.dropdown-item-active {
  @apply bg-blue-50 text-blue-700;
}

.dropdown-item-divider {
  @apply border-t border-gray-100 mt-1 pt-1;
}

/* Content layout */
.dropdown-item-content {
  @apply flex-1 min-w-0;
}

.dropdown-item-title {
  @apply font-medium truncate;
}

.dropdown-item-description {
  @apply text-xs text-gray-500 mt-0.5 truncate;
}

.dropdown-item-has-description .dropdown-item-title {
  @apply text-sm;
}

/* Icon styles */
.dropdown-item-icon {
  @apply flex-shrink-0 text-gray-400;
}

.dropdown-item-icon-left {
  @apply mr-3;
}

.dropdown-item-icon-right {
  @apply ml-3;
}

.dropdown-item-active .dropdown-item-icon {
  @apply text-blue-500;
}

.dropdown-item-danger .dropdown-item-icon {
  @apply text-red-500;
}

.dropdown-item-success .dropdown-item-icon {
  @apply text-green-500;
}

.dropdown-item-warning .dropdown-item-icon {
  @apply text-yellow-500;
}

/* Badge styles */
.dropdown-item-badge {
  @apply ml-auto flex-shrink-0;
}

/* Submenu icon */
.dropdown-item-submenu-icon {
  @apply ml-auto flex-shrink-0 text-gray-400;
}

/* Focus styles */
.dropdown-item:focus {
  @apply ring-2 ring-blue-500 ring-inset;
}

.dropdown-item-danger:focus {
  @apply ring-red-500;
}

.dropdown-item-success:focus {
  @apply ring-green-500;
}

.dropdown-item-warning:focus {
  @apply ring-yellow-500;
}

/* Hover effects for different variants */
.dropdown-item-default:hover:not(.dropdown-item-disabled) {
  @apply bg-gray-50;
}

.dropdown-item-danger:hover:not(.dropdown-item-disabled) {
  @apply bg-red-50;
}

.dropdown-item-success:hover:not(.dropdown-item-disabled) {
  @apply bg-green-50;
}

.dropdown-item-warning:hover:not(.dropdown-item-disabled) {
  @apply bg-yellow-50;
}

/* Active state for different variants */
.dropdown-item-danger.dropdown-item-active {
  @apply bg-red-50 text-red-700;
}

.dropdown-item-success.dropdown-item-active {
  @apply bg-green-50 text-green-700;
}

.dropdown-item-warning.dropdown-item-active {
  @apply bg-yellow-50 text-yellow-700;
}
</style>