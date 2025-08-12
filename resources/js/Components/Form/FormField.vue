<template>
  <div class="mb-4">
    <label 
      :for="fieldId" 
      class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <!-- Text Input -->
    <input 
      v-if="type === 'text' || type === 'number'"
      :id="fieldId"
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :class="inputClasses"
    />
    
    <!-- Textarea -->
    <textarea 
      v-else-if="type === 'textarea'"
      :id="fieldId"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :rows="rows"
      :class="inputClasses"
    ></textarea>
    
    <!-- Select -->
    <select 
      v-else-if="type === 'select'"
      :id="fieldId"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :class="inputClasses"
    >
      <option value="" v-if="placeholder">{{ placeholder }}</option>
      <option 
        v-for="option in options" 
        :key="option.value" 
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    
    <!-- Checkbox -->
    <label 
      v-else-if="type === 'checkbox'"
      class="flex items-center cursor-pointer"
    >
      <input 
        :id="fieldId"
        type="checkbox"
        :checked="modelValue"
        @change="$emit('update:modelValue', $event.target.checked)"
        class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
      />
      <span class="text-sm text-gray-700 dark:text-gray-300">{{ checkboxLabel }}</span>
    </label>
    
    <!-- Error Message -->
    <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    
    <!-- Help Text -->
    <p v-if="help" class="text-gray-500 text-xs mt-1">{{ help }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number, Boolean], default: '' },
  type: { type: String, default: 'text' },
  label: { type: String, required: true },
  fieldId: { type: String, required: true },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
  error: { type: String, default: '' },
  help: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  rows: { type: Number, default: 3 },
  checkboxLabel: { type: String, default: '' }
})

defineEmits(['update:modelValue'])

const inputClasses = computed(() => {
  const baseClasses = 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-slate-700 dark:border-slate-600 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500'
  const errorClasses = props.error ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'
  return `${baseClasses} ${errorClasses}`
})
</script>