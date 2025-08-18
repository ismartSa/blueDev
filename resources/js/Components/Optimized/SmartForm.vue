<template>
  <form @submit.prevent="handleSubmit" :class="formClass">
    <div v-if="title" class="form-header">
      <h2 class="form-title">{{ title }}</h2>
      <p v-if="description" class="form-description">{{ description }}</p>
    </div>

    <div class="form-body">
      <div
        v-for="field in visibleFields"
        :key="field.name"
        :class="getFieldWrapperClass(field)"
      >
        <!-- Dynamic Field Component -->
        <component
          :is="getFieldComponent(field)"
          v-model="formData[field.name]"
          :field="field"
          :error="errors[field.name]"
          :disabled="disabled || field.disabled"
          :loading="loading"
          @input="handleFieldChange(field.name, $event)"
          @blur="handleFieldBlur(field.name)"
        />
      </div>

      <!-- Custom Fields Slot -->
      <slot name="fields" :form-data="formData" :errors="errors" />
    </div>

    <div v-if="showActions" class="form-actions">
      <button
        v-if="showCancel"
        type="button"
        @click="handleCancel"
        :disabled="loading"
        class="btn btn-secondary"
      >
        {{ cancelText }}
      </button>
      
      <button
        type="submit"
        :disabled="!canSubmit"
        :class="submitButtonClass"
      >
        <span v-if="loading" class="loading-spinner"></span>
        {{ loading ? loadingText : submitText }}
      </button>
      
      <slot name="actions" :form-data="formData" :can-submit="canSubmit" />
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { debounce } from 'lodash-es'

// Field Components
import TextInput from './Fields/TextInput.vue'
import TextareaInput from './Fields/TextareaInput.vue'
import SelectInput from './Fields/SelectInput.vue'
import CheckboxInput from './Fields/CheckboxInput.vue'
import RadioInput from './Fields/RadioInput.vue'
import FileInput from './Fields/FileInput.vue'
import DateInput from './Fields/DateInput.vue'
import NumberInput from './Fields/NumberInput.vue'
import EmailInput from './Fields/EmailInput.vue'
import PasswordInput from './Fields/PasswordInput.vue'
import RichTextInput from './Fields/RichTextInput.vue'
import TagsInput from './Fields/TagsInput.vue'

// Props
const props = defineProps({
  // Form Configuration
  fields: { type: Array, required: true },
  modelValue: { type: Object, default: () => ({}) },
  errors: { type: Object, default: () => ({}) },
  
  // Form State
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  
  // Form Appearance
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  layout: { type: String, default: 'vertical' }, // vertical, horizontal, grid
  columns: { type: Number, default: 1 },
  
  // Form Behavior
  validateOnChange: { type: Boolean, default: true },
  validateOnBlur: { type: Boolean, default: true },
  resetOnSubmit: { type: Boolean, default: false },
  
  // Actions
  showActions: { type: Boolean, default: true },
  showCancel: { type: Boolean, default: false },
  submitText: { type: String, default: 'Submit' },
  cancelText: { type: String, default: 'Cancel' },
  loadingText: { type: String, default: 'Submitting...' },
  
  // Styling
  formClass: { type: String, default: 'smart-form' },
  fieldClass: { type: String, default: '' }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'submit',
  'cancel',
  'field-change',
  'field-blur',
  'validation-change'
])

// Reactive data
const formData = ref({ ...props.modelValue })
const fieldValidation = ref({})
const touchedFields = ref(new Set())

// Field Components Map
const fieldComponents = {
  text: TextInput,
  textarea: TextareaInput,
  select: SelectInput,
  checkbox: CheckboxInput,
  radio: RadioInput,
  file: FileInput,
  date: DateInput,
  datetime: DateInput,
  number: NumberInput,
  email: EmailInput,
  password: PasswordInput,
  richtext: RichTextInput,
  tags: TagsInput
}

// Computed properties
const visibleFields = computed(() => {
  return props.fields.filter(field => {
    if (field.condition && typeof field.condition === 'function') {
      return field.condition(formData.value)
    }
    return field.visible !== false
  })
})

const canSubmit = computed(() => {
  if (props.loading || props.disabled) return false
  
  // Check required fields
  const requiredFields = visibleFields.value.filter(field => field.required)
  const hasRequiredValues = requiredFields.every(field => {
    const value = formData.value[field.name]
    return value !== null && value !== undefined && value !== ''
  })
  
  // Check validation errors
  const hasValidationErrors = Object.keys(fieldValidation.value).some(
    key => fieldValidation.value[key] && fieldValidation.value[key].length > 0
  )
  
  return hasRequiredValues && !hasValidationErrors
})

const submitButtonClass = computed(() => {
  const baseClass = 'btn btn-primary'
  if (!canSubmit.value) return `${baseClass} btn-disabled`
  return baseClass
})

// Methods
const getFieldComponent = (field) => {
  if (field.component) {
    return field.component
  }
  
  return fieldComponents[field.type] || TextInput
}

const getFieldWrapperClass = (field) => {
  const baseClass = 'form-field'
  const layoutClass = `form-field-${props.layout}`
  const columnClass = props.layout === 'grid' ? `col-span-${field.span || 1}` : ''
  const customClass = props.fieldClass || field.class || ''
  
  return [baseClass, layoutClass, columnClass, customClass].filter(Boolean).join(' ')
}

const handleFieldChange = debounce((fieldName, value) => {
  formData.value[fieldName] = value
  emit('update:modelValue', formData.value)
  emit('field-change', { field: fieldName, value, formData: formData.value })
  
  if (props.validateOnChange) {
    validateField(fieldName)
  }
}, 100)

const handleFieldBlur = (fieldName) => {
  touchedFields.value.add(fieldName)
  emit('field-blur', { field: fieldName, formData: formData.value })
  
  if (props.validateOnBlur) {
    validateField(fieldName)
  }
}

const validateField = async (fieldName) => {
  const field = visibleFields.value.find(f => f.name === fieldName)
  if (!field || !field.validation) return
  
  const value = formData.value[fieldName]
  const errors = []
  
  // Required validation
  if (field.required && (value === null || value === undefined || value === '')) {
    errors.push(field.requiredMessage || `${field.label} is required`)
  }
  
  // Custom validation rules
  if (field.validation && typeof field.validation === 'function') {
    try {
      const result = await field.validation(value, formData.value)
      if (result !== true && typeof result === 'string') {
        errors.push(result)
      }
    } catch (error) {
      errors.push(error.message || 'Validation error')
    }
  }
  
  // Built-in validation rules
  if (field.rules) {
    for (const rule of field.rules) {
      const error = validateRule(rule, value, field)
      if (error) errors.push(error)
    }
  }
  
  fieldValidation.value[fieldName] = errors
  emit('validation-change', { field: fieldName, errors, isValid: errors.length === 0 })
}

const validateRule = (rule, value, field) => {
  if (!value && !field.required) return null
  
  switch (rule.type) {
    case 'min':
      if (typeof value === 'string' && value.length < rule.value) {
        return rule.message || `${field.label} must be at least ${rule.value} characters`
      }
      if (typeof value === 'number' && value < rule.value) {
        return rule.message || `${field.label} must be at least ${rule.value}`
      }
      break
      
    case 'max':
      if (typeof value === 'string' && value.length > rule.value) {
        return rule.message || `${field.label} must not exceed ${rule.value} characters`
      }
      if (typeof value === 'number' && value > rule.value) {
        return rule.message || `${field.label} must not exceed ${rule.value}`
      }
      break
      
    case 'email':
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(value)) {
        return rule.message || 'Please enter a valid email address'
      }
      break
      
    case 'url':
      try {
        new URL(value)
      } catch {
        return rule.message || 'Please enter a valid URL'
      }
      break
      
    case 'pattern':
      const regex = new RegExp(rule.value)
      if (!regex.test(value)) {
        return rule.message || `${field.label} format is invalid`
      }
      break
  }
  
  return null
}

const validateForm = async () => {
  const validationPromises = visibleFields.value.map(field => validateField(field.name))
  await Promise.all(validationPromises)
  
  const hasErrors = Object.values(fieldValidation.value).some(errors => errors.length > 0)
  return !hasErrors
}

const handleSubmit = async () => {
  // Mark all fields as touched
  visibleFields.value.forEach(field => {
    touchedFields.value.add(field.name)
  })
  
  const isValid = await validateForm()
  
  if (isValid) {
    emit('submit', formData.value)
    
    if (props.resetOnSubmit) {
      resetForm()
    }
  }
}

const handleCancel = () => {
  emit('cancel')
}

const resetForm = () => {
  formData.value = { ...props.modelValue }
  fieldValidation.value = {}
  touchedFields.value.clear()
}

const setFieldValue = (fieldName, value) => {
  formData.value[fieldName] = value
  emit('update:modelValue', formData.value)
}

const getFieldValue = (fieldName) => {
  return formData.value[fieldName]
}

const getFieldError = (fieldName) => {
  return fieldValidation.value[fieldName] || props.errors[fieldName]
}

const focusField = async (fieldName) => {
  await nextTick()
  const fieldElement = document.querySelector(`[name="${fieldName}"]`)
  if (fieldElement) {
    fieldElement.focus()
  }
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  formData.value = { ...newValue }
}, { deep: true })

watch(() => props.errors, (newErrors) => {
  // Merge server errors with client validation
  Object.keys(newErrors).forEach(fieldName => {
    if (!fieldValidation.value[fieldName]) {
      fieldValidation.value[fieldName] = []
    }
  })
}, { deep: true })

// Lifecycle
onMounted(() => {
  // Initialize form data with default values
  visibleFields.value.forEach(field => {
    if (field.default !== undefined && formData.value[field.name] === undefined) {
      formData.value[field.name] = field.default
    }
  })
  
  emit('update:modelValue', formData.value)
})

// Expose methods for parent components
defineExpose({
  validateForm,
  resetForm,
  setFieldValue,
  getFieldValue,
  getFieldError,
  focusField,
  formData: readonly(formData),
  canSubmit
})
</script>

<style scoped>
.smart-form {
  @apply space-y-6;
}

.form-header {
  @apply mb-6;
}

.form-title {
  @apply text-2xl font-bold text-gray-900 mb-2;
}

.form-description {
  @apply text-gray-600;
}

.form-body {
  @apply space-y-4;
}

/* Layout Styles */
.form-field-vertical {
  @apply w-full;
}

.form-field-horizontal {
  @apply flex items-center space-x-4;
}

.form-field-grid {
  @apply w-full;
}

.smart-form[data-layout="grid"] .form-body {
  @apply grid gap-4;
  grid-template-columns: repeat(var(--form-columns, 1), 1fr);
}

.form-actions {
  @apply flex justify-end space-x-3 pt-6 border-t border-gray-200;
}

.btn {
  @apply px-4 py-2 rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
}

.btn-secondary {
  @apply bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500;
}

.btn-disabled {
  @apply opacity-50 cursor-not-allowed;
}

.loading-spinner {
  @apply inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2;
}

/* Responsive Design */
@media (max-width: 640px) {
  .form-actions {
    @apply flex-col space-x-0 space-y-3;
  }
  
  .smart-form[data-layout="grid"] .form-body {
    @apply grid-cols-1;
  }
}
</style>