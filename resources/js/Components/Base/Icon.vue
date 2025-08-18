<template>
  <svg
    :class="[
      'icon',
      sizeClass,
      colorClass,
      {
        'animate-spin': spin,
        'animate-pulse': pulse,
        'animate-bounce': bounce
      }
    ]"
    :width="actualSize"
    :height="actualSize"
    :viewBox="viewBox"
    fill="currentColor"
    xmlns="http://www.w3.org/2000/svg"
  >
    <!-- Heroicons Outline -->
    <template v-if="iconSet === 'heroicons-outline'">
      <!-- Search -->
      <path v-if="name === 'search'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" fill="none" stroke="currentColor" />
      
      <!-- X (Close) -->
      <path v-else-if="name === 'x'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" fill="none" stroke="currentColor" />
      
      <!-- Check Circle -->
      <path v-else-if="name === 'check-circle'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" fill="none" stroke="currentColor" />
      
      <!-- X Circle -->
      <path v-else-if="name === 'x-circle'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" fill="none" stroke="currentColor" />
      
      <!-- Exclamation Triangle -->
      <path v-else-if="name === 'exclamation-triangle'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" fill="none" stroke="currentColor" />
      
      <!-- Info Circle -->
      <path v-else-if="name === 'info-circle'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" fill="none" stroke="currentColor" />
      
      <!-- Refresh -->
      <path v-else-if="name === 'refresh'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" fill="none" stroke="currentColor" />
      
      <!-- Download -->
      <path v-else-if="name === 'download'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" fill="none" stroke="currentColor" />
      
      <!-- Chevron Left -->
      <path v-else-if="name === 'chevron-left'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 8.25 12l7.5-7.5" fill="none" stroke="currentColor" />
      
      <!-- Chevron Right -->
      <path v-else-if="name === 'chevron-right'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5" fill="none" stroke="currentColor" />
      
      <!-- Chevron Up -->
      <path v-else-if="name === 'chevron-up'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 15.75 7.5-7.5 7.5 7.5" fill="none" stroke="currentColor" />
      
      <!-- Chevron Down -->
      <path v-else-if="name === 'chevron-down'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" fill="none" stroke="currentColor" />
      
      <!-- Sort -->
      <path v-else-if="name === 'sort'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" fill="none" stroke="currentColor" />
      
      <!-- Sort Up -->
      <path v-else-if="name === 'sort-up'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18" fill="none" stroke="currentColor" />
      
      <!-- Sort Down -->
      <path v-else-if="name === 'sort-down'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 17.25 12 21m0 0-3.75-3.75M12 21V3" fill="none" stroke="currentColor" />
      
      <!-- Inbox -->
      <path v-else-if="name === 'inbox'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2.25 13.5 8.5-8.5a2.25 2.25 0 0 1 3.182 0l8.5 8.5M6.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25V9a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 9v10.5a2.25 2.25 0 0 0 2.25 2.25Z" fill="none" stroke="currentColor" />
      
      <!-- Plus -->
      <path v-else-if="name === 'plus'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" fill="none" stroke="currentColor" />
      
      <!-- Minus -->
      <path v-else-if="name === 'minus'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" fill="none" stroke="currentColor" />
      
      <!-- Edit -->
      <path v-else-if="name === 'edit'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" fill="none" stroke="currentColor" />
      
      <!-- Trash -->
      <path v-else-if="name === 'trash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" fill="none" stroke="currentColor" />
      
      <!-- Eye -->
      <path v-else-if="name === 'eye'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" fill="none" stroke="currentColor" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="none" stroke="currentColor" />
      
      <!-- Settings -->
      <path v-else-if="name === 'settings'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" fill="none" stroke="currentColor" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="none" stroke="currentColor" />
    </template>
    
    <!-- Heroicons Solid -->
    <template v-else-if="iconSet === 'heroicons-solid'">
      <!-- Spinner -->
      <path v-if="name === 'spinner'" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      
      <!-- Default solid fallback -->
      <circle v-else cx="12" cy="12" r="10" fill="currentColor" />
    </template>
    
    <!-- Custom Icons -->
    <template v-else>
      <!-- Default fallback icon -->
      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
      <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
    </template>
  </svg>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  name: {
    type: String,
    required: true
  },
  size: {
    type: [String, Number],
    default: 'md',
    validator: (value) => {
      if (typeof value === 'number') return value > 0
      return ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
    }
  },
  color: {
    type: String,
    default: 'current'
  },
  spin: {
    type: Boolean,
    default: false
  },
  pulse: {
    type: Boolean,
    default: false
  },
  bounce: {
    type: Boolean,
    default: false
  },
  iconSet: {
    type: String,
    default: 'heroicons-outline',
    validator: (value) => ['heroicons-outline', 'heroicons-solid', 'custom'].includes(value)
  }
})

// Computed properties
const sizeClass = computed(() => {
  if (typeof props.size === 'number') {
    return ''
  }
  
  const sizeClasses = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
    xl: 'w-8 h-8',
    '2xl': 'w-10 h-10'
  }
  
  return sizeClasses[props.size] || sizeClasses.md
})

const colorClass = computed(() => {
  if (props.color === 'current') {
    return 'text-current'
  }
  
  const colorClasses = {
    primary: 'text-blue-500',
    secondary: 'text-gray-500',
    success: 'text-green-500',
    danger: 'text-red-500',
    warning: 'text-yellow-500',
    info: 'text-blue-500',
    light: 'text-gray-300',
    dark: 'text-gray-800',
    white: 'text-white',
    black: 'text-black'
  }
  
  return colorClasses[props.color] || `text-${props.color}`
})

const actualSize = computed(() => {
  if (typeof props.size === 'number') {
    return props.size
  }
  
  const sizes = {
    xs: 12,
    sm: 16,
    md: 20,
    lg: 24,
    xl: 32,
    '2xl': 40
  }
  
  return sizes[props.size] || sizes.md
})

const viewBox = computed(() => {
  // Most icons use 24x24 viewBox, spinner uses different
  if (props.name === 'spinner') {
    return '0 0 24 24'
  }
  return '0 0 24 24'
})
</script>

<style scoped>
.icon {
  @apply inline-block flex-shrink-0;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-bounce {
  animation: bounce 1s infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(-25%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: translateY(0);
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}
</style>