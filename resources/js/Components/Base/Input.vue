<template>
  <div class="input-wrapper" :class="wrapperClasses">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="input-label"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Input container -->
    <div class="input-container" :class="containerClasses">
      <!-- Left icon -->
      <Icon
        v-if="leftIcon"
        :name="leftIcon"
        :size="iconSize"
        class="input-icon input-icon-left"
        :class="leftIconClasses"
      />

      <!-- Input element -->
      <component
        :is="inputComponent"
        :id="inputId"
        ref="inputRef"
        v-model="inputValue"
        :type="inputType"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :rows="rows"
        :cols="cols"
        :maxlength="maxlength"
        :autocomplete="autocomplete"
        :class="inputClasses"
        v-bind="$attrs"
        @input="handleInput"
        @change="handleChange"
        @focus="handleFocus"
        @blur="handleBlur"
        @keydown="handleKeydown"
      />

      <!-- Right icon -->
      <Icon
        v-if="rightIcon"
        :name="rightIcon"
        :size="iconSize"
        class="input-icon input-icon-right"
        :class="rightIconClasses"
        @click="handleRightIconClick"
      />

      <!-- Clear button -->
      <button
        v-if="clearable && inputValue && !disabled && !readonly"
        type="button"
        class="input-clear"
        @click="clearInput"
      >
        <Icon name="x" :size="iconSize" />
      </button>

      <!-- Password toggle -->
      <button
        v-if="type === 'password' && passwordToggle"
        type="button"
        class="input-password-toggle"
        @click="togglePasswordVisibility"
      >
        <Icon :name="showPassword ? 'eye' : 'eye'" :size="iconSize" />
      </button>
    </div>

    <!-- Help text -->
    <div v-if="helpText || error" class="input-help" :class="helpClasses">
      <Icon
        v-if="error"
        name="exclamation-triangle"
        size="xs"
        class="text-red-500 mr-1"
      />
      <span>{{ error || helpText }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick, watch } from 'vue'
import Icon from './Icon.vue'

// Props
const props = defineProps({
  // Value
  modelValue: {
    type: [String, Number, Boolean],
    default: ''
  },
  
  // Input attributes
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'tel', 'url', 'search',
      'textarea', 'select', 'date', 'time', 'datetime-local', 'file'
    ].includes(value)
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  
  // Number input attributes
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  step: {
    type: [String, Number],
    default: null
  },
  
  // Textarea attributes
  rows: {
    type: Number,
    default: 3
  },
  cols: {
    type: Number,
    default: null
  },
  
  // Other attributes
  maxlength: {
    type: Number,
    default: null
  },
  autocomplete: {
    type: String,
    default: null
  },
  
  // Appearance
  label: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'filled', 'outlined'].includes(value)
  },
  
  // Icons
  leftIcon: {
    type: String,
    default: null
  },
  rightIcon: {
    type: String,
    default: null
  },
  
  // Features
  clearable: {
    type: Boolean,
    default: false
  },
  passwordToggle: {
    type: Boolean,
    default: true
  },
  
  // Custom classes
  customClass: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'input',
  'change',
  'focus',
  'blur',
  'keydown',
  'clear',
  'right-icon-click'
])

// Refs
const inputRef = ref(null)
const showPassword = ref(false)
const isFocused = ref(false)

// Computed properties
const inputId = computed(() => {
  return `input-${Math.random().toString(36).substr(2, 9)}`
})

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const inputType = computed(() => {
  if (props.type === 'password' && showPassword.value) {
    return 'text'
  }
  return props.type === 'textarea' ? 'text' : props.type
})

const inputComponent = computed(() => {
  return props.type === 'textarea' ? 'textarea' : 'input'
})

const iconSize = computed(() => {
  const sizeMap = {
    sm: 'xs',
    md: 'sm',
    lg: 'md'
  }
  return sizeMap[props.size] || 'sm'
})

const wrapperClasses = computed(() => {
  return [
    'input-wrapper',
    `input-wrapper-${props.size}`,
    {
      'input-wrapper-disabled': props.disabled,
      'input-wrapper-error': props.error,
      'input-wrapper-focused': isFocused.value
    },
    props.customClass
  ]
})

const labelClasses = computed(() => {
  return [
    'input-label',
    `input-label-${props.size}`,
    {
      'input-label-disabled': props.disabled,
      'input-label-error': props.error,
      'input-label-required': props.required
    }
  ]
})

const containerClasses = computed(() => {
  return [
    'input-container',
    `input-container-${props.variant}`,
    `input-container-${props.size}`,
    {
      'input-container-disabled': props.disabled,
      'input-container-error': props.error,
      'input-container-focused': isFocused.value,
      'input-container-with-left-icon': props.leftIcon,
      'input-container-with-right-icon': props.rightIcon || props.clearable || (props.type === 'password' && props.passwordToggle)
    }
  ]
})

const inputClasses = computed(() => {
  return [
    'input-field',
    `input-field-${props.size}`,
    {
      'input-field-with-left-icon': props.leftIcon,
      'input-field-with-right-icon': props.rightIcon || props.clearable || (props.type === 'password' && props.passwordToggle)
    }
  ]
})

const leftIconClasses = computed(() => {
  return {
    'text-gray-400': !props.error && !isFocused.value,
    'text-blue-500': isFocused.value && !props.error,
    'text-red-500': props.error
  }
})

const rightIconClasses = computed(() => {
  return {
    'text-gray-400': !props.error && !isFocused.value,
    'text-blue-500': isFocused.value && !props.error,
    'text-red-500': props.error,
    'cursor-pointer': props.rightIcon
  }
})

const helpClasses = computed(() => {
  return [
    'input-help',
    {
      'input-help-error': props.error,
      'input-help-normal': !props.error
    }
  ]
})

// Methods
const handleInput = (event) => {
  const value = event.target.value
  emit('update:modelValue', value)
  emit('input', event)
}

const handleChange = (event) => {
  emit('change', event)
}

const handleFocus = (event) => {
  isFocused.value = true
  emit('focus', event)
}

const handleBlur = (event) => {
  isFocused.value = false
  emit('blur', event)
}

const handleKeydown = (event) => {
  emit('keydown', event)
}

const clearInput = () => {
  emit('update:modelValue', '')
  emit('clear')
  nextTick(() => {
    inputRef.value?.focus()
  })
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const handleRightIconClick = () => {
  emit('right-icon-click')
}

// Public methods
const focus = () => {
  inputRef.value?.focus()
}

const blur = () => {
  inputRef.value?.blur()
}

const select = () => {
  inputRef.value?.select()
}

// Expose methods
defineExpose({
  focus,
  blur,
  select,
  inputRef
})
</script>

<style scoped>
/* Input wrapper */
.input-wrapper {
  @apply w-full;
}

.input-wrapper-sm {
  @apply text-sm;
}

.input-wrapper-md {
  @apply text-base;
}

.input-wrapper-lg {
  @apply text-lg;
}

/* Label styles */
.input-label {
  @apply block font-medium text-gray-700 mb-1;
}

.input-label-sm {
  @apply text-sm;
}

.input-label-md {
  @apply text-sm;
}

.input-label-lg {
  @apply text-base;
}

.input-label-disabled {
  @apply text-gray-400;
}

.input-label-error {
  @apply text-red-700;
}

/* Container styles */
.input-container {
  @apply relative flex items-center;
}

.input-container-default {
  @apply border border-gray-300 rounded-md;
}

.input-container-filled {
  @apply bg-gray-50 border border-transparent rounded-md;
}

.input-container-outlined {
  @apply border-2 border-gray-300 rounded-md;
}

.input-container-sm {
  @apply min-h-[32px];
}

.input-container-md {
  @apply min-h-[40px];
}

.input-container-lg {
  @apply min-h-[48px];
}

.input-container-focused {
  @apply ring-2 ring-blue-500 ring-opacity-50 border-blue-500;
}

.input-container-error {
  @apply border-red-500 ring-2 ring-red-500 ring-opacity-50;
}

.input-container-disabled {
  @apply bg-gray-100 border-gray-200;
}

/* Input field styles */
.input-field {
  @apply w-full bg-transparent border-none outline-none;
  @apply placeholder-gray-400 text-gray-900;
  @apply disabled:text-gray-500 disabled:cursor-not-allowed;
}

.input-field-sm {
  @apply px-3 py-1.5 text-sm;
}

.input-field-md {
  @apply px-3 py-2 text-base;
}

.input-field-lg {
  @apply px-4 py-3 text-lg;
}

.input-field-with-left-icon {
  @apply pl-10;
}

.input-field-with-right-icon {
  @apply pr-10;
}

/* Icon styles */
.input-icon {
  @apply absolute pointer-events-none;
}

.input-icon-left {
  @apply left-3;
}

.input-icon-right {
  @apply right-3;
}

.input-icon.cursor-pointer {
  @apply pointer-events-auto;
}

/* Clear button */
.input-clear {
  @apply absolute right-3 p-1 text-gray-400 hover:text-gray-600;
  @apply focus:outline-none focus:text-gray-600;
}

/* Password toggle */
.input-password-toggle {
  @apply absolute right-3 p-1 text-gray-400 hover:text-gray-600;
  @apply focus:outline-none focus:text-gray-600;
}

/* Help text */
.input-help {
  @apply flex items-center mt-1 text-sm;
}

.input-help-normal {
  @apply text-gray-600;
}

.input-help-error {
  @apply text-red-600;
}

/* Textarea specific */
textarea.input-field {
  @apply resize-y;
}
</style>