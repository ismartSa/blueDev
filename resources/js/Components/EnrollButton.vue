<template>
  <button 
    @click="$emit('enroll')" 
    :disabled="isEnrolling"
    :class="buttonClasses"
  >
    <span v-if="isEnrolling" class="loading-content">
      <LoadingSpinner />
      {{ enrollingText }}
    </span>
    <span v-else>{{ defaultText }}</span>
  </button>
</template>

<script>
import LoadingSpinner from './LoadingSpinner.vue'

export default {
  name: 'EnrollButton',
  components: {
    LoadingSpinner
  },
  props: {
    isEnrolling: {
      type: Boolean,
      default: false
    },
    text: {
      type: Object,
      default: () => ({
        enrolling: 'جاري التسجيل...',
        default: 'سجل الآن'
      })
    },
    variant: {
      type: String,
      default: 'primary'
    }
  },
  emits: ['enroll'],
  computed: {
    enrollingText() {
      return this.text.enrolling || 'جاري التسجيل...'
    },
    defaultText() {
      return this.text.default || 'سجل الآن'
    },
    buttonClasses() {
      const baseClasses = 'btn-enroll transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed'
      const variantClasses = {
        primary: 'bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-lg font-bold shadow-lg',
        secondary: 'enroll-button'
      }
      return `${baseClasses} ${variantClasses[this.variant] || variantClasses.primary}`
    }
  }
}
</script>

<style scoped>
.loading-content {
  @apply flex items-center justify-center;
}

.btn-enroll {
  @apply font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500;
}
</style>