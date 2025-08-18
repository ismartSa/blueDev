<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div v-for="field in fields" :key="field.name" class="space-y-2">
      <!-- Field Label -->
      <label 
        :for="field.name" 
        class="block text-sm font-medium text-gray-700"
        :class="{ 'text-red-600': hasError(field.name) }"
      >
        {{ field.label }}
        <span v-if="field.required" class="text-red-500">*</span>
      </label>

      <!-- Text Input -->
      <input
        v-if="field.type === 'text' || field.type === 'email' || field.type === 'password'"
        :id="field.name"
        :type="field.type"
        :name="field.name"
        v-model="formData[field.name]"
        :placeholder="field.placeholder"
        :disabled="field.disabled || processing"
        :class="getInputClasses(field.name)"
        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />

      <!-- Textarea -->
      <textarea
        v-else-if="field.type === 'textarea'"
        :id="field.name"
        :name="field.name"
        v-model="formData[field.name]"
        :placeholder="field.placeholder"
        :rows="field.rows || 4"
        :disabled="field.disabled || processing"
        :class="getInputClasses(field.name)"
        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
      />

      <!-- Select -->
      <select
        v-else-if="field.type === 'select'"
        :id="field.name"
        :name="field.name"
        v-model="formData[field.name]"
        :disabled="field.disabled || processing"
        :class="getInputClasses(field.name)"
        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      >
        <option value="" v-if="field.placeholder">{{ field.placeholder }}</option>
        <option 
          v-for="option in field.options" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>

      <!-- Number Input -->
      <input
        v-else-if="field.type === 'number'"
        :id="field.name"
        type="number"
        :name="field.name"
        v-model.number="formData[field.name]"
        :placeholder="field.placeholder"
        :min="field.min"
        :max="field.max"
        :step="field.step"
        :disabled="field.disabled || processing"
        :class="getInputClasses(field.name)"
        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />

      <!-- Checkbox -->
      <div v-else-if="field.type === 'checkbox'" class="flex items-center">
        <input
          :id="field.name"
          type="checkbox"
          :name="field.name"
          v-model="formData[field.name]"
          :disabled="field.disabled || processing"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label :for="field.name" class="ml-2 block text-sm text-gray-900">
          {{ field.checkboxLabel || field.label }}
        </label>
      </div>

      <!-- File Input -->
      <input
        v-else-if="field.type === 'file'"
        :id="field.name"
        type="file"
        :name="field.name"
        @change="handleFileChange(field.name, $event)"
        :accept="field.accept"
        :multiple="field.multiple"
        :disabled="field.disabled || processing"
        :class="getInputClasses(field.name)"
        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />

      <!-- Custom Slot -->
      <div v-else-if="field.type === 'custom'">
        <slot :name="`field-${field.name}`" :field="field" :value="formData[field.name]" :error="getError(field.name)" />
      </div>

      <!-- Error Message -->
      <p v-if="hasError(field.name)" class="text-sm text-red-600">
        {{ getError(field.name) }}
      </p>

      <!-- Help Text -->
      <p v-if="field.help" class="text-sm text-gray-500">
        {{ field.help }}
      </p>
    </div>

    <!-- Form Actions -->
    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
      <button
        v-if="showCancel"
        type="button"
        @click="handleCancel"
        :disabled="processing"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ cancelText }}
      </button>
      
      <button
        type="submit"
        :disabled="processing || !isValid"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
      >
        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ processing ? processingText : submitText }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// Props
const props = defineProps({
  fields: { type: Array, required: true },
  initialData: { type: Object, default: () => ({}) },
  errors: { type: Object, default: () => ({}) },
  processing: { type: Boolean, default: false },
  submitText: { type: String, default: 'Save' },
  cancelText: { type: String, default: 'Cancel' },
  processingText: { type: String, default: 'Saving...' },
  showCancel: { type: Boolean, default: true },
  validateOnChange: { type: Boolean, default: true }
})

// Emits
const emit = defineEmits(['submit', 'cancel', 'change'])

// Reactive data
const formData = ref({ ...props.initialData })

// Computed
const isValid = computed(() => {
  if (!props.validateOnChange) return true
  
  return props.fields.every(field => {
    if (!field.required) return true
    const value = formData.value[field.name]
    return value !== null && value !== undefined && value !== ''
  })
})

// Methods
const hasError = (fieldName) => {
  return props.errors && props.errors[fieldName]
}

const getError = (fieldName) => {
  return props.errors?.[fieldName]?.[0] || props.errors?.[fieldName]
}

const getInputClasses = (fieldName) => {
  return {
    'border-red-300 focus:ring-red-500 focus:border-red-500': hasError(fieldName),
    'border-gray-300': !hasError(fieldName)
  }
}

const handleSubmit = () => {
  emit('submit', { ...formData.value })
}

const handleCancel = () => {
  emit('cancel')
}

const handleFileChange = (fieldName, event) => {
  const files = event.target.files
  formData.value[fieldName] = files.length > 1 ? Array.from(files) : files[0]
  emit('change', { field: fieldName, value: formData.value[fieldName] })
}

// Watch for changes
watch(
  () => formData.value,
  (newData) => {
    emit('change', newData)
  },
  { deep: true }
)

// Watch for initial data changes
watch(
  () => props.initialData,
  (newData) => {
    formData.value = { ...newData }
  },
  { deep: true }
)
</script>