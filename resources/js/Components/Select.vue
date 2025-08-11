<template>
  <select
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
  >
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>

<script setup>
const props = defineProps({
  modelValue: [String, Number],
  options: {
    type: Array,
    required: true,
    // [{ value: '1', label: 'Option 1' }, ...]
  },
});
</script>
