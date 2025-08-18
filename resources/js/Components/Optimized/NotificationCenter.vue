<template>
  <teleport to="body">
    <div
      v-if="hasNotifications"
      :class="[
        'notification-container',
        positionClasses
      ]"
    >
      <transition-group
        name="notification"
        tag="div"
        class="notification-list"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'notification',
            notification.class,
            `notification-${notification.type}`
          ]"
          @mouseenter="pauseNotification(notification.id)"
          @mouseleave="resumeNotification(notification.id)"
        >
          <!-- Notification Content -->
          <div class="notification-content">
            <!-- Icon -->
            <div v-if="notification.icon" class="notification-icon">
              <Icon :name="notification.icon" />
            </div>
            
            <!-- Text Content -->
            <div class="notification-text">
              <h4 v-if="notification.title" class="notification-title">
                {{ notification.title }}
              </h4>
              <p class="notification-message">
                {{ notification.message }}
              </p>
            </div>
            
            <!-- Close Button -->
            <button
              @click="removeNotification(notification.id)"
              class="notification-close"
              aria-label="Close notification"
            >
              <Icon name="x" />
            </button>
          </div>
          
          <!-- Actions -->
          <div v-if="notification.actions && notification.actions.length > 0" class="notification-actions">
            <button
              v-for="action in notification.actions"
              :key="action.label"
              @click="handleAction(notification.id, action)"
              :class="['notification-action', action.class]"
            >
              {{ action.label }}
            </button>
          </div>
          
          <!-- Progress Bar -->
          <div
            v-if="showProgress && !notification.persistent && notification.duration > 0"
            class="notification-progress"
          >
            <div
              class="notification-progress-bar"
              :style="{ width: `${notification.progress}%` }"
            ></div>
          </div>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useGlobalNotifications } from '@/Composables/useNotifications'
import Icon from '@/Components/Base/Icon.vue'

// Props
const props = defineProps({
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => [
      'top-left',
      'top-center',
      'top-right',
      'bottom-left',
      'bottom-center',
      'bottom-right'
    ].includes(value)
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  maxWidth: {
    type: String,
    default: '400px'
  }
})

// Composables
const {
  notifications,
  hasNotifications,
  removeNotification,
  pauseNotification,
  resumeNotification,
  handleAction
} = useGlobalNotifications()

// Computed properties
const positionClasses = computed(() => {
  const positions = {
    'top-left': 'top-4 left-4',
    'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
    'top-right': 'top-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2',
    'bottom-right': 'bottom-4 right-4'
  }
  
  return positions[props.position] || positions['top-right']
})
</script>

<style scoped>
.notification-container {
  @apply fixed z-50 pointer-events-none;
  max-width: v-bind(maxWidth);
}

.notification-list {
  @apply space-y-2;
}

.notification {
  @apply bg-white rounded-lg shadow-lg border pointer-events-auto overflow-hidden;
  min-width: 300px;
}

.notification-success {
  @apply border-green-200 bg-green-50;
}

.notification-error {
  @apply border-red-200 bg-red-50;
}

.notification-warning {
  @apply border-yellow-200 bg-yellow-50;
}

.notification-info {
  @apply border-blue-200 bg-blue-50;
}

.notification-content {
  @apply flex items-start p-4;
}

.notification-icon {
  @apply flex-shrink-0 mr-3;
}

.notification-success .notification-icon {
  @apply text-green-500;
}

.notification-error .notification-icon {
  @apply text-red-500;
}

.notification-warning .notification-icon {
  @apply text-yellow-500;
}

.notification-info .notification-icon {
  @apply text-blue-500;
}

.notification-text {
  @apply flex-1 min-w-0;
}

.notification-title {
  @apply text-sm font-medium text-gray-900 mb-1;
}

.notification-message {
  @apply text-sm text-gray-700;
}

.notification-close {
  @apply flex-shrink-0 ml-3 text-gray-400 hover:text-gray-600 transition-colors;
}

.notification-actions {
  @apply flex items-center justify-end space-x-2 px-4 pb-3;
}

.notification-action {
  @apply px-3 py-1 text-xs font-medium rounded transition-colors;
}

.notification-progress {
  @apply h-1 bg-gray-200;
}

.notification-progress-bar {
  @apply h-full transition-all duration-100 ease-linear;
}

.notification-success .notification-progress-bar {
  @apply bg-green-500;
}

.notification-error .notification-progress-bar {
  @apply bg-red-500;
}

.notification-warning .notification-progress-bar {
  @apply bg-yellow-500;
}

.notification-info .notification-progress-bar {
  @apply bg-blue-500;
}

/* Transitions */
.notification-enter-active {
  transition: all 0.3s ease-out;
}

.notification-leave-active {
  transition: all 0.3s ease-in;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Position-specific enter animations */
.notification-container.top-4.left-4 .notification-enter-from,
.notification-container.bottom-4.left-4 .notification-enter-from {
  transform: translateX(-100%);
}

.notification-container.top-4.left-4 .notification-leave-to,
.notification-container.bottom-4.left-4 .notification-leave-to {
  transform: translateX(-100%);
}

.notification-container.top-4.left-1\/2 .notification-enter-from,
.notification-container.bottom-4.left-1\/2 .notification-enter-from {
  transform: translateX(-50%) translateY(-100%);
}

.notification-container.top-4.left-1\/2 .notification-leave-to,
.notification-container.bottom-4.left-1\/2 .notification-leave-to {
  transform: translateX(-50%) translateY(-100%);
}
</style>