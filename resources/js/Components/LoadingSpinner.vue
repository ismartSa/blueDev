<template>
  <div class="flex items-center justify-center">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
  </div>
</template>

<script setup>
// Optional props for customization
defineProps({
  size: {
    type: String,
    default: 'h-12 w-12'
  },
  color: {
    type: String,
    default: 'border-indigo-600'
  }
});
</script>
