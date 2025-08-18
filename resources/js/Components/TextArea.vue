<template>
  <div class="space-y-1">
    <InputLabel v-if="label" :for="id">{{ label }}</InputLabel>
    <textarea
      :id="id"
      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary disabled:opacity-50"
      :class="{
        'border-red-500': error,
        'dark:border-gray-600 dark:bg-slate-800 dark:text-gray-300': $page.props.darkMode,
      }"
      :value="modelValue"
      v-bind="$attrs"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <InputError v-if="error" :message="error" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  modelValue: String,
  label: String,
  error: String,
  id: {
    type: String,
    default: () => `textarea-${Math.random().toString(36).substring(2, 9)}`,
  },
});
</script>
