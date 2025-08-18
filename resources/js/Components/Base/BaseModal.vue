<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 scale-95 translate-y-4"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-300"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-4"
          >
            <div
              v-if="show"
              :class="modalClasses"
              class="relative bg-white rounded-lg shadow-xl transform transition-all"
              @click.stop
            >
              <!-- Header -->
              <div v-if="showHeader" class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                  <!-- Icon -->
                  <div v-if="icon" :class="iconClasses" class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full">
                    <component :is="icon" class="w-6 h-6" />
                  </div>
                  
                  <!-- Title -->
                  <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
                    <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
                  </div>
                </div>
                
                <!-- Close Button -->
                <button
                  v-if="closable"
                  @click="handleClose"
                  class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Body -->
              <div :class="bodyClasses" class="p-6">
                <slot />
              </div>
              
              <!-- Footer -->
              <div v-if="showFooter" class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <slot name="footer">
                  <!-- Default Footer Buttons -->
                  <button
                    v-if="showCancel"
                    @click="handleCancel"
                    :disabled="processing"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{ cancelText }}
                  </button>
                  
                  <button
                    v-if="showConfirm"
                    @click="handleConfirm"
                    :disabled="processing || !canConfirm"
                    :class="confirmButtonClasses"
                    class="px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                  >
                    <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ processing ? processingText : confirmText }}
                  </button>
                </slot>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'

// Props
const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  size: { type: String, default: 'md', validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value) },
  type: { type: String, default: 'default', validator: (value) => ['default', 'success', 'warning', 'danger', 'info'].includes(value) },
  icon: { type: [String, Object], default: null },
  closable: { type: Boolean, default: true },
  closeOnBackdrop: { type: Boolean, default: true },
  showHeader: { type: Boolean, default: true },
  showFooter: { type: Boolean, default: true },
  showCancel: { type: Boolean, default: true },
  showConfirm: { type: Boolean, default: true },
  cancelText: { type: String, default: 'Cancel' },
  confirmText: { type: String, default: 'Confirm' },
  processingText: { type: String, default: 'Processing...' },
  processing: { type: Boolean, default: false },
  canConfirm: { type: Boolean, default: true }
})

// Emits
const emit = defineEmits(['close', 'cancel', 'confirm'])

// Computed
const modalClasses = computed(() => {
  const sizeClasses = {
    xs: 'max-w-xs',
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    full: 'max-w-full mx-4'
  }
  
  return [
    'w-full',
    sizeClasses[props.size]
  ]
})

const iconClasses = computed(() => {
  const typeClasses = {
    default: 'bg-gray-100 text-gray-600',
    success: 'bg-green-100 text-green-600',
    warning: 'bg-yellow-100 text-yellow-600',
    danger: 'bg-red-100 text-red-600',
    info: 'bg-blue-100 text-blue-600'
  }
  
  return typeClasses[props.type]
})

const confirmButtonClasses = computed(() => {
  const typeClasses = {
    default: 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    success: 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    warning: 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
    danger: 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    info: 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500'
  }
  
  return typeClasses[props.type]
})

const bodyClasses = computed(() => {
  return {
    'pt-0': !props.showHeader,
    'pb-0': !props.showFooter
  }
})

// Methods
const handleClose = () => {
  emit('close')
}

const handleCancel = () => {
  emit('cancel')
  emit('close')
}

const handleConfirm = () => {
  emit('confirm')
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop && props.closable) {
    handleClose()
  }
}

const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && props.show && props.closable) {
    handleClose()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey)
})
</script>