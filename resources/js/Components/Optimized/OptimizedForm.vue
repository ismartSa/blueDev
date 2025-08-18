<template>
  <form @submit.prevent="handleSubmit" class="optimized-form">
    <!-- Form Header -->
    <div v-if="title || $slots.header" class="form-header">
      <slot name="header">
        <h2 v-if="title" class="form-title">{{ title }}</h2>
        <p v-if="description" class="form-description">{{ description }}</p>
      </slot>
    </div>

    <!-- Form Fields -->
    <div class="form-body">
      <div
        v-for="(field, index) in visibleFields"
        :key="field.name"
        :class="[
          'form-field',
          field.class,
          {
            'form-field-error': hasError(field.name),
            'form-field-required': field.required,
            'form-field-disabled': field.disabled
          }
        ]"
      >
        <!-- Field Label -->
        <label
          v-if="field.label && field.type !== 'checkbox'"
          :for="field.name"
          class="form-label"
        >
          {{ field.label }}
          <span v-if="field.required" class="required-indicator">*</span>
        </label>

        <!-- Field Input -->
        <div class="form-input-container">
          <!-- Text Input -->
          <input
            v-if="field.type === 'text' || field.type === 'email' || field.type === 'password' || field.type === 'url'"
            :id="field.name"
            :type="field.type"
            :name="field.name"
            :placeholder="field.placeholder"
            :disabled="field.disabled || processing"
            :required="field.required"
            :autocomplete="field.autocomplete"
            :class="[
              'form-input',
              field.inputClass,
              {
                'input-error': hasError(field.name)
              }
            ]"
            :value="data[field.name]"
            @input="handleFieldChange(field.name, $event.target.value)"
            @blur="handleFieldBlur(field.name)"
          />

          <!-- Number Input -->
          <input
            v-else-if="field.type === 'number'"
            :id="field.name"
            type="number"
            :name="field.name"
            :placeholder="field.placeholder"
            :disabled="field.disabled || processing"
            :required="field.required"
            :min="field.min"
            :max="field.max"
            :step="field.step"
            :class="[
              'form-input',
              field.inputClass,
              {
                'input-error': hasError(field.name)
              }
            ]"
            :value="data[field.name]"
            @input="handleFieldChange(field.name, $event.target.value)"
            @blur="handleFieldBlur(field.name)"
          />

          <!-- Textarea -->
          <textarea
            v-else-if="field.type === 'textarea'"
            :id="field.name"
            :name="field.name"
            :placeholder="field.placeholder"
            :disabled="field.disabled || processing"
            :required="field.required"
            :rows="field.rows || 3"
            :class="[
              'form-textarea',
              field.inputClass,
              {
                'input-error': hasError(field.name)
              }
            ]"
            :value="data[field.name]"
            @input="handleFieldChange(field.name, $event.target.value)"
            @blur="handleFieldBlur(field.name)"
          ></textarea>

          <!-- Select -->
          <select
            v-else-if="field.type === 'select'"
            :id="field.name"
            :name="field.name"
            :disabled="field.disabled || processing"
            :required="field.required"
            :class="[
              'form-select',
              field.inputClass,
              {
                'input-error': hasError(field.name)
              }
            ]"
            :value="data[field.name]"
            @change="handleFieldChange(field.name, $event.target.value)"
            @blur="handleFieldBlur(field.name)"
          >
            <option v-if="field.placeholder" value="">{{ field.placeholder }}</option>
            <option
              v-for="option in field.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

          <!-- Checkbox -->
          <div v-else-if="field.type === 'checkbox'" class="checkbox-container">
            <input
              :id="field.name"
              type="checkbox"
              :name="field.name"
              :disabled="field.disabled || processing"
              :required="field.required"
              :class="[
                'form-checkbox',
                field.inputClass
              ]"
              :checked="data[field.name]"
              @change="handleFieldChange(field.name, $event.target.checked)"
              @blur="handleFieldBlur(field.name)"
            />
            <label :for="field.name" class="checkbox-label">
              {{ field.label }}
              <span v-if="field.required" class="required-indicator">*</span>
            </label>
          </div>

          <!-- Radio Group -->
          <div v-else-if="field.type === 'radio'" class="radio-group">
            <div
              v-for="option in field.options"
              :key="option.value"
              class="radio-item"
            >
              <input
                :id="`${field.name}_${option.value}`"
                type="radio"
                :name="field.name"
                :value="option.value"
                :disabled="field.disabled || processing"
                :required="field.required"
                :class="[
                  'form-radio',
                  field.inputClass
                ]"
                :checked="data[field.name] === option.value"
                @change="handleFieldChange(field.name, option.value)"
                @blur="handleFieldBlur(field.name)"
              />
              <label :for="`${field.name}_${option.value}`" class="radio-label">
                {{ option.label }}
              </label>
            </div>
          </div>

          <!-- File Input -->
          <input
            v-else-if="field.type === 'file'"
            :id="field.name"
            type="file"
            :name="field.name"
            :disabled="field.disabled || processing"
            :required="field.required"
            :accept="field.accept"
            :multiple="field.multiple"
            :class="[
              'form-file',
              field.inputClass,
              {
                'input-error': hasError(field.name)
              }
            ]"
            @change="handleFileChange(field.name, $event)"
            @blur="handleFieldBlur(field.name)"
          />

          <!-- Date Input -->
          <input
            v-else-if="field.type === 'date' || field.type === 'datetime-local' || field.type === 'time'"
            :id="field.name"
            :type="field.type"
            :name="field.name"
            :disabled="field.disabled || processing"
            :required="field.required"
            :min="field.min"
            :max="field.max"
            :class="[
              'form-input',
              field.inputClass,
              {
                'input-error': hasError(field.name)
              }
            ]"
            :value="data[field.name]"
            @input="handleFieldChange(field.name, $event.target.value)"
            @blur="handleFieldBlur(field.name)"
          />

          <!-- Custom Component -->
          <component
            v-else-if="field.component"
            :is="field.component"
            :id="field.name"
            :name="field.name"
            :disabled="field.disabled || processing"
            :required="field.required"
            :modelValue="data[field.name]"
            v-bind="field.props"
            @update:modelValue="handleFieldChange(field.name, $event)"
            @blur="handleFieldBlur(field.name)"
          />

          <!-- Field Icon -->
          <div v-if="field.icon" class="field-icon">
            <Icon :name="field.icon" />
          </div>

          <!-- Field Suffix -->
          <div v-if="field.suffix" class="field-suffix">
            {{ field.suffix }}
          </div>
        </div>

        <!-- Field Help Text -->
        <p v-if="field.help && !hasError(field.name)" class="field-help">
          {{ field.help }}
        </p>

        <!-- Field Error -->
        <p v-if="hasError(field.name)" class="field-error">
          {{ getError(field.name) }}
        </p>
      </div>

      <!-- Custom Fields Slot -->
      <slot name="fields" :data="data" :errors="errors" :processing="processing" />
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <slot name="actions" :data="data" :processing="processing" :canSubmit="canSubmit">
        <div class="action-buttons">
          <button
            v-if="showCancel"
            type="button"
            @click="handleCancel"
            :disabled="processing"
            class="btn btn-secondary"
          >
            {{ cancelText }}
          </button>
          
          <button
            v-if="showReset"
            type="button"
            @click="handleReset"
            :disabled="processing"
            class="btn btn-outline"
          >
            {{ resetText }}
          </button>
          
          <button
            type="submit"
            :disabled="!canSubmit || processing"
            :class="[
              'btn btn-primary',
              {
                'btn-loading': processing
              }
            ]"
          >
            <Icon v-if="processing" name="spinner" class="animate-spin" />
            <span>{{ processing ? processingText : submitText }}</span>
          </button>
        </div>
      </slot>
    </div>

    <!-- Form Footer -->
    <div v-if="$slots.footer" class="form-footer">
      <slot name="footer" :data="data" :errors="errors" :processing="processing" />
    </div>
  </form>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue'
import { useForm } from '@/Composables/useForm'
import { useNotifications } from '@/Composables/useNotifications'
import Icon from '@/Components/Base/Icon.vue'

// Props
const props = defineProps({
  fields: {
    type: Array,
    required: true
  },
  initialData: {
    type: Object,
    default: () => ({})
  },
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  method: {
    type: String,
    default: 'post'
  },
  action: {
    type: String,
    required: true
  },
  submitText: {
    type: String,
    default: 'Submit'
  },
  processingText: {
    type: String,
    default: 'Processing...'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  resetText: {
    type: String,
    default: 'Reset'
  },
  showCancel: {
    type: Boolean,
    default: false
  },
  showReset: {
    type: Boolean,
    default: false
  },
  validateOnChange: {
    type: Boolean,
    default: true
  },
  validateOnBlur: {
    type: Boolean,
    default: true
  },
  resetOnSuccess: {
    type: Boolean,
    default: false
  },
  preserveScroll: {
    type: Boolean,
    default: false
  },
  formOptions: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits([
  'submit',
  'success',
  'error',
  'cancel',
  'reset',
  'field-change'
])

// Composables
const notifications = useNotifications()
const form = useForm(props.initialData, {
  validateOnChange: props.validateOnChange,
  validateOnBlur: props.validateOnBlur,
  resetOnSuccess: props.resetOnSuccess,
  preserveScroll: props.preserveScroll,
  ...props.formOptions
})

// Destructure form composable
const {
  data,
  processing,
  errors,
  hasErrors,
  canSubmit,
  setRules,
  setMessages,
  handleFieldChange: formHandleFieldChange,
  handleFieldBlur,
  hasError,
  getError,
  reset,
  submit
} = form

// Computed properties
const visibleFields = computed(() => {
  return props.fields.filter(field => field.visible !== false)
})

// Methods
const handleFieldChange = (fieldName, value) => {
  formHandleFieldChange(fieldName, value)
  emit('field-change', { field: fieldName, value, data: data })
}

const handleFileChange = (fieldName, event) => {
  const files = event.target.files
  const field = props.fields.find(f => f.name === fieldName)
  
  if (field?.multiple) {
    handleFieldChange(fieldName, Array.from(files))
  } else {
    handleFieldChange(fieldName, files[0] || null)
  }
}

const handleSubmit = async () => {
  try {
    emit('submit', data)
    
    await submit(props.method, props.action, {
      onSuccess: (page) => {
        notifications.success('Form submitted successfully')
        emit('success', { data, page })
      },
      onError: (errors) => {
        const errorCount = Object.keys(errors).length
        const message = errorCount === 1 
          ? 'Please fix the validation error below.'
          : `Please fix the ${errorCount} validation errors below.`
        notifications.error(message)
        emit('error', { errors, data })
      }
    })
  } catch (error) {
    notifications.error(error.message || 'Form submission failed')
    emit('error', { error, data })
  }
}

const handleCancel = () => {
  emit('cancel', data)
}

const handleReset = () => {
  reset()
  emit('reset')
}

// Setup validation rules from fields
const setupValidation = () => {
  const rules = {}
  const messages = {}
  
  props.fields.forEach(field => {
    if (field.rules) {
      rules[field.name] = field.rules
    }
    
    if (field.messages) {
      Object.keys(field.messages).forEach(rule => {
        messages[`${field.name}.${rule}`] = field.messages[rule]
      })
    }
    
    // Auto-generate rules from field properties
    const autoRules = []
    
    if (field.required) {
      autoRules.push('required')
    }
    
    if (field.type === 'email') {
      autoRules.push('email')
    }
    
    if (field.type === 'url') {
      autoRules.push('url')
    }
    
    if (field.type === 'number') {
      autoRules.push('numeric')
    }
    
    if (field.min !== undefined) {
      if (field.type === 'number') {
        autoRules.push({ type: 'minValue', value: field.min })
      } else {
        autoRules.push({ type: 'min', value: field.min })
      }
    }
    
    if (field.max !== undefined) {
      if (field.type === 'number') {
        autoRules.push({ type: 'maxValue', value: field.max })
      } else {
        autoRules.push({ type: 'max', value: field.max })
      }
    }
    
    if (field.pattern) {
      autoRules.push({ type: 'pattern', value: new RegExp(field.pattern) })
    }
    
    if (autoRules.length > 0) {
      rules[field.name] = field.rules ? [...autoRules, ...field.rules] : autoRules
    }
  })
  
  setRules(rules)
  setMessages(messages)
}

// Watch for field changes
watch(() => props.fields, setupValidation, { deep: true, immediate: true })

// Watch for initial data changes
watch(() => props.initialData, (newData) => {
  Object.assign(data, newData)
}, { deep: true })

// Setup validation on mount
onMounted(() => {
  setupValidation()
})
</script>

<style scoped>
.optimized-form {
  @apply space-y-6;
}

.form-header {
  @apply border-b border-gray-200 pb-4;
}

.form-title {
  @apply text-xl font-semibold text-gray-900;
}

.form-description {
  @apply mt-1 text-sm text-gray-600;
}

.form-body {
  @apply space-y-4;
}

.form-field {
  @apply space-y-1;
}

.form-field-error {
  @apply space-y-2;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.required-indicator {
  @apply text-red-500 ml-1;
}

.form-input-container {
  @apply relative;
}

.form-input,
.form-textarea,
.form-select,
.form-file {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors;
}

.form-textarea {
  @apply resize-vertical;
}

.input-error {
  @apply border-red-300 focus:ring-red-500;
}

.checkbox-container {
  @apply flex items-center space-x-2;
}

.form-checkbox,
.form-radio {
  @apply rounded border-gray-300 text-blue-600 focus:ring-blue-500;
}

.checkbox-label,
.radio-label {
  @apply text-sm text-gray-700 cursor-pointer;
}

.radio-group {
  @apply space-y-2;
}

.radio-item {
  @apply flex items-center space-x-2;
}

.field-icon {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400;
}

.field-suffix {
  @apply absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm;
}

.field-help {
  @apply text-xs text-gray-500;
}

.field-error {
  @apply text-xs text-red-600;
}

.form-actions {
  @apply border-t border-gray-200 pt-4;
}

.action-buttons {
  @apply flex items-center justify-end space-x-3;
}

.btn {
  @apply px-4 py-2 text-sm font-medium rounded-md transition-colors focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
}

.btn-secondary {
  @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
}

.btn-outline {
  @apply border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500;
}

.btn-loading {
  @apply cursor-not-allowed;
}

.form-footer {
  @apply border-t border-gray-200 pt-4;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>