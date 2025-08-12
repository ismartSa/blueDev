<template>
  <div class="dropdown" :class="dropdownClasses" ref="dropdownRef">
    <!-- Trigger -->
    <div
      class="dropdown-trigger"
      :class="triggerClasses"
      @click="toggleDropdown"
      @keydown.enter="toggleDropdown"
      @keydown.space.prevent="toggleDropdown"
      @keydown.escape="closeDropdown"
      @keydown.arrow-down.prevent="openDropdown"
      :tabindex="disabled ? -1 : 0"
      :aria-expanded="isOpen"
      :aria-haspopup="true"
      :aria-disabled="disabled"
    >
      <slot name="trigger" :isOpen="isOpen" :toggle="toggleDropdown">
        <Button
          :variant="triggerVariant"
          :size="triggerSize"
          :disabled="disabled"
          :icon="triggerIcon"
          :icon-position="triggerIconPosition"
        >
          {{ triggerText }}
          <Icon
            name="chevron-down"
            size="sm"
            class="ml-2 transition-transform duration-200"
            :class="{ 'rotate-180': isOpen }"
          />
        </Button>
      </slot>
    </div>

    <!-- Dropdown menu -->
    <Transition
      :name="transitionName"
      @enter="onEnter"
      @leave="onLeave"
    >
      <div
        v-if="isOpen"
        class="dropdown-menu"
        :class="menuClasses"
        :style="menuStyles"
        @click="handleMenuClick"
        @keydown="handleKeydown"
        role="menu"
        :aria-labelledby="triggerId"
      >
        <!-- Header -->
        <div v-if="$slots.header" class="dropdown-header">
          <slot name="header" />
        </div>

        <!-- Menu items -->
        <div class="dropdown-content">
          <slot :close="closeDropdown" :isOpen="isOpen" />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="dropdown-footer">
          <slot name="footer" />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import Button from './Button.vue'
import Icon from './Icon.vue'

// Props
const props = defineProps({
  // Trigger
  triggerText: {
    type: String,
    default: 'Dropdown'
  },
  triggerIcon: {
    type: String,
    default: null
  },
  triggerIconPosition: {
    type: String,
    default: 'left'
  },
  triggerVariant: {
    type: String,
    default: 'secondary'
  },
  triggerSize: {
    type: String,
    default: 'md'
  },
  
  // Behavior
  disabled: {
    type: Boolean,
    default: false
  },
  closeOnClick: {
    type: Boolean,
    default: true
  },
  closeOnOutsideClick: {
    type: Boolean,
    default: true
  },
  
  // Positioning
  placement: {
    type: String,
    default: 'bottom-start',
    validator: (value) => [
      'top-start', 'top', 'top-end',
      'bottom-start', 'bottom', 'bottom-end',
      'left-start', 'left', 'left-end',
      'right-start', 'right', 'right-end'
    ].includes(value)
  },
  offset: {
    type: Number,
    default: 8
  },
  
  // Appearance
  width: {
    type: [String, Number],
    default: 'auto'
  },
  maxWidth: {
    type: [String, Number],
    default: null
  },
  maxHeight: {
    type: [String, Number],
    default: '300px'
  },
  
  // Animation
  transition: {
    type: String,
    default: 'dropdown',
    validator: (value) => ['dropdown', 'fade', 'slide', 'scale'].includes(value)
  },
  
  // Custom classes
  customClass: {
    type: String,
    default: ''
  },
  menuClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['open', 'close', 'toggle'])

// Refs
const dropdownRef = ref(null)
const isOpen = ref(false)
const triggerId = ref(`dropdown-trigger-${Math.random().toString(36).substr(2, 9)}`)

// Computed properties
const dropdownClasses = computed(() => {
  const classes = [
    'dropdown',
    {
      'dropdown-open': isOpen.value,
      'dropdown-disabled': props.disabled
    }
  ]
  
  if (props.customClass) {
    classes.push(props.customClass)
  }
  
  return classes
})

const triggerClasses = computed(() => {
  return {
    'dropdown-trigger-disabled': props.disabled
  }
})

const menuClasses = computed(() => {
  const classes = [
    'dropdown-menu',
    `dropdown-menu-${props.placement}`,
    {
      'dropdown-menu-scrollable': props.maxHeight
    }
  ]
  
  if (props.menuClass) {
    classes.push(props.menuClass)
  }
  
  return classes
})

const menuStyles = computed(() => {
  const styles = {}
  
  if (props.width !== 'auto') {
    styles.width = typeof props.width === 'number' ? `${props.width}px` : props.width
  }
  
  if (props.maxWidth) {
    styles.maxWidth = typeof props.maxWidth === 'number' ? `${props.maxWidth}px` : props.maxWidth
  }
  
  if (props.maxHeight) {
    styles.maxHeight = typeof props.maxHeight === 'number' ? `${props.maxHeight}px` : props.maxHeight
  }
  
  return styles
})

const transitionName = computed(() => {
  return `dropdown-${props.transition}`
})

// Methods
const openDropdown = () => {
  if (props.disabled) return
  
  isOpen.value = true
  emit('open')
  emit('toggle', true)
  
  nextTick(() => {
    focusFirstMenuItem()
  })
}

const closeDropdown = () => {
  isOpen.value = false
  emit('close')
  emit('toggle', false)
}

const toggleDropdown = () => {
  if (isOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
  }
}

const handleOutsideClick = (event) => {
  if (!props.closeOnOutsideClick) return
  
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const handleMenuClick = (event) => {
  if (props.closeOnClick) {
    // Check if clicked element is a menu item
    const menuItem = event.target.closest('.dropdown-item')
    if (menuItem && !menuItem.disabled) {
      closeDropdown()
    }
  }
}

const handleKeydown = (event) => {
  switch (event.key) {
    case 'Escape':
      closeDropdown()
      break
    case 'ArrowDown':
      event.preventDefault()
      focusNextMenuItem()
      break
    case 'ArrowUp':
      event.preventDefault()
      focusPreviousMenuItem()
      break
    case 'Home':
      event.preventDefault()
      focusFirstMenuItem()
      break
    case 'End':
      event.preventDefault()
      focusLastMenuItem()
      break
  }
}

const focusFirstMenuItem = () => {
  const menuItems = getMenuItems()
  if (menuItems.length > 0) {
    menuItems[0].focus()
  }
}

const focusLastMenuItem = () => {
  const menuItems = getMenuItems()
  if (menuItems.length > 0) {
    menuItems[menuItems.length - 1].focus()
  }
}

const focusNextMenuItem = () => {
  const menuItems = getMenuItems()
  const currentIndex = menuItems.findIndex(item => item === document.activeElement)
  const nextIndex = currentIndex < menuItems.length - 1 ? currentIndex + 1 : 0
  menuItems[nextIndex]?.focus()
}

const focusPreviousMenuItem = () => {
  const menuItems = getMenuItems()
  const currentIndex = menuItems.findIndex(item => item === document.activeElement)
  const previousIndex = currentIndex > 0 ? currentIndex - 1 : menuItems.length - 1
  menuItems[previousIndex]?.focus()
}

const getMenuItems = () => {
  if (!dropdownRef.value) return []
  return Array.from(dropdownRef.value.querySelectorAll('.dropdown-item:not([disabled])'))
}

const onEnter = (el) => {
  el.style.height = '0'
  el.offsetHeight // Force reflow
  el.style.height = el.scrollHeight + 'px'
}

const onLeave = (el) => {
  el.style.height = el.scrollHeight + 'px'
  el.offsetHeight // Force reflow
  el.style.height = '0'
}

// Lifecycle
onMounted(() => {
  if (props.closeOnOutsideClick) {
    document.addEventListener('click', handleOutsideClick)
  }
})

onUnmounted(() => {
  if (props.closeOnOutsideClick) {
    document.removeEventListener('click', handleOutsideClick)
  }
})

// Expose methods
defineExpose({
  open: openDropdown,
  close: closeDropdown,
  toggle: toggleDropdown,
  isOpen
})
</script>

<style scoped>
/* Base dropdown styles */
.dropdown {
  @apply relative inline-block;
}

.dropdown-disabled {
  @apply opacity-50 pointer-events-none;
}

/* Trigger styles */
.dropdown-trigger {
  @apply focus:outline-none;
}

.dropdown-trigger-disabled {
  @apply cursor-not-allowed;
}

/* Menu styles */
.dropdown-menu {
  @apply absolute z-50 bg-white border border-gray-200 rounded-lg shadow-lg;
  @apply min-w-[160px] py-1;
}

.dropdown-menu-scrollable {
  @apply overflow-y-auto;
}

/* Menu positioning */
.dropdown-menu-top-start {
  @apply bottom-full left-0 mb-2;
}

.dropdown-menu-top {
  @apply bottom-full left-1/2 transform -translate-x-1/2 mb-2;
}

.dropdown-menu-top-end {
  @apply bottom-full right-0 mb-2;
}

.dropdown-menu-bottom-start {
  @apply top-full left-0 mt-2;
}

.dropdown-menu-bottom {
  @apply top-full left-1/2 transform -translate-x-1/2 mt-2;
}

.dropdown-menu-bottom-end {
  @apply top-full right-0 mt-2;
}

.dropdown-menu-left-start {
  @apply right-full top-0 mr-2;
}

.dropdown-menu-left {
  @apply right-full top-1/2 transform -translate-y-1/2 mr-2;
}

.dropdown-menu-left-end {
  @apply right-full bottom-0 mr-2;
}

.dropdown-menu-right-start {
  @apply left-full top-0 ml-2;
}

.dropdown-menu-right {
  @apply left-full top-1/2 transform -translate-y-1/2 ml-2;
}

.dropdown-menu-right-end {
  @apply left-full bottom-0 ml-2;
}

/* Header and footer */
.dropdown-header {
  @apply px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100;
}

.dropdown-content {
  @apply py-1;
}

.dropdown-footer {
  @apply px-4 py-2 border-t border-gray-100;
}

/* Transitions */
.dropdown-dropdown-enter-active,
.dropdown-dropdown-leave-active {
  @apply transition-all duration-200 ease-out;
}

.dropdown-dropdown-enter-from {
  @apply opacity-0 transform scale-95 -translate-y-1;
}

.dropdown-dropdown-leave-to {
  @apply opacity-0 transform scale-95 -translate-y-1;
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  @apply transition-opacity duration-200;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  @apply opacity-0;
}

.dropdown-slide-enter-active,
.dropdown-slide-leave-active {
  @apply transition-all duration-200 ease-out;
  overflow: hidden;
}

.dropdown-slide-enter-from,
.dropdown-slide-leave-to {
  @apply opacity-0;
  height: 0;
}

.dropdown-scale-enter-active,
.dropdown-scale-leave-active {
  @apply transition-all duration-200 ease-out;
}

.dropdown-scale-enter-from,
.dropdown-scale-leave-to {
  @apply opacity-0 transform scale-75;
}
</style>