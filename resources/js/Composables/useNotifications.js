import { ref, reactive, computed } from 'vue'

/**
 * Composable for notification management
 * Implements DRY principle for common notification patterns
 */
export function useNotifications(options = {}) {
  // Default options
  const defaultOptions = {
    maxNotifications: 5,
    defaultDuration: 5000,
    position: 'top-right',
    showProgress: true,
    pauseOnHover: true,
    ...options
  }

  // Notifications state
  const notifications = ref([])
  const nextId = ref(1)

  // Notification types with default styles
  const notificationTypes = {
    success: {
      icon: 'check-circle',
      class: 'bg-green-500 text-white',
      duration: 4000
    },
    error: {
      icon: 'x-circle',
      class: 'bg-red-500 text-white',
      duration: 6000
    },
    warning: {
      icon: 'exclamation-triangle',
      class: 'bg-yellow-500 text-white',
      duration: 5000
    },
    info: {
      icon: 'info-circle',
      class: 'bg-blue-500 text-white',
      duration: 4000
    }
  }

  // Computed properties
  const hasNotifications = computed(() => notifications.value.length > 0)
  const notificationCount = computed(() => notifications.value.length)

  /**
   * Add notification
   */
  const addNotification = (notification) => {
    const id = nextId.value++
    const type = notification.type || 'info'
    const typeConfig = notificationTypes[type] || notificationTypes.info
    
    const newNotification = {
      id,
      title: notification.title || '',
      message: notification.message || '',
      type,
      icon: notification.icon || typeConfig.icon,
      class: notification.class || typeConfig.class,
      duration: notification.duration !== undefined ? notification.duration : typeConfig.duration,
      persistent: notification.persistent || false,
      actions: notification.actions || [],
      data: notification.data || {},
      createdAt: Date.now(),
      progress: 100,
      paused: false,
      timer: null
    }

    // Remove oldest notification if max limit reached
    if (notifications.value.length >= defaultOptions.maxNotifications) {
      const oldest = notifications.value[0]
      removeNotification(oldest.id)
    }

    notifications.value.push(newNotification)

    // Auto-remove notification if not persistent
    if (!newNotification.persistent && newNotification.duration > 0) {
      startTimer(newNotification)
    }

    return newNotification
  }

  /**
   * Remove notification
   */
  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index !== -1) {
      const notification = notifications.value[index]
      if (notification.timer) {
        clearInterval(notification.timer)
      }
      notifications.value.splice(index, 1)
    }
  }

  /**
   * Clear all notifications
   */
  const clearAll = () => {
    notifications.value.forEach(notification => {
      if (notification.timer) {
        clearInterval(notification.timer)
      }
    })
    notifications.value = []
  }

  /**
   * Start timer for auto-removal
   */
  const startTimer = (notification) => {
    if (notification.timer) {
      clearInterval(notification.timer)
    }

    const interval = 50 // Update every 50ms for smooth progress
    const decrement = (interval / notification.duration) * 100

    notification.timer = setInterval(() => {
      if (!notification.paused) {
        notification.progress -= decrement
        
        if (notification.progress <= 0) {
          removeNotification(notification.id)
        }
      }
    }, interval)
  }

  /**
   * Pause notification timer
   */
  const pauseNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.paused = true
    }
  }

  /**
   * Resume notification timer
   */
  const resumeNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.paused = false
    }
  }

  /**
   * Update notification
   */
  const updateNotification = (id, updates) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      Object.assign(notification, updates)
      
      // Restart timer if duration changed
      if (updates.duration !== undefined && !notification.persistent) {
        startTimer(notification)
      }
    }
  }

  /**
   * Success notification shortcut
   */
  const success = (message, options = {}) => {
    return addNotification({
      type: 'success',
      message,
      ...options
    })
  }

  /**
   * Error notification shortcut
   */
  const error = (message, options = {}) => {
    return addNotification({
      type: 'error',
      message,
      ...options
    })
  }

  /**
   * Warning notification shortcut
   */
  const warning = (message, options = {}) => {
    return addNotification({
      type: 'warning',
      message,
      ...options
    })
  }

  /**
   * Info notification shortcut
   */
  const info = (message, options = {}) => {
    return addNotification({
      type: 'info',
      message,
      ...options
    })
  }

  /**
   * Loading notification
   */
  const loading = (message, options = {}) => {
    return addNotification({
      type: 'info',
      message,
      persistent: true,
      icon: 'spinner',
      class: 'bg-gray-500 text-white',
      ...options
    })
  }

  /**
   * Confirmation notification with actions
   */
  const confirm = (message, onConfirm, onCancel = null, options = {}) => {
    return addNotification({
      type: 'warning',
      message,
      persistent: true,
      actions: [
        {
          label: 'Confirm',
          class: 'bg-red-500 hover:bg-red-600 text-white',
          handler: () => {
            if (onConfirm) onConfirm()
          }
        },
        {
          label: 'Cancel',
          class: 'bg-gray-500 hover:bg-gray-600 text-white',
          handler: () => {
            if (onCancel) onCancel()
          }
        }
      ],
      ...options
    })
  }

  /**
   * Handle notification action
   */
  const handleAction = (notificationId, action) => {
    if (action.handler) {
      action.handler()
    }
    
    // Remove notification after action unless specified otherwise
    if (action.keepOpen !== true) {
      removeNotification(notificationId)
    }
  }

  /**
   * Get notifications by type
   */
  const getByType = (type) => {
    return notifications.value.filter(n => n.type === type)
  }

  /**
   * Get notification by id
   */
  const getById = (id) => {
    return notifications.value.find(n => n.id === id)
  }

  return {
    // State
    notifications: computed(() => notifications.value),
    hasNotifications,
    notificationCount,
    
    // Methods
    addNotification,
    removeNotification,
    clearAll,
    updateNotification,
    pauseNotification,
    resumeNotification,
    handleAction,
    
    // Shortcuts
    success,
    error,
    warning,
    info,
    loading,
    confirm,
    
    // Utilities
    getByType,
    getById
  }
}

/**
 * Composable for toast notifications (simpler version)
 */
export function useToast(options = {}) {
  const notifications = useNotifications({
    maxNotifications: 3,
    position: 'bottom-right',
    ...options
  })

  // Simplified API for toast notifications
  const toast = (message, type = 'info', duration = 3000) => {
    return notifications.addNotification({
      message,
      type,
      duration
    })
  }

  return {
    ...notifications,
    toast,
    
    // Simplified shortcuts
    success: (message, duration) => toast(message, 'success', duration),
    error: (message, duration) => toast(message, 'error', duration),
    warning: (message, duration) => toast(message, 'warning', duration),
    info: (message, duration) => toast(message, 'info', duration)
  }
}

/**
 * Global notification instance (singleton)
 */
let globalNotifications = null

export function useGlobalNotifications() {
  if (!globalNotifications) {
    globalNotifications = useNotifications()
  }
  return globalNotifications
}

/**
 * Plugin for Vue app
 */
export const NotificationPlugin = {
  install(app, options = {}) {
    const notifications = useNotifications(options)
    
    app.config.globalProperties.$notify = notifications
    app.provide('notifications', notifications)
  }
}

/**
 * Composable for handling API response notifications
 */
export function useApiNotifications() {
  const notifications = useGlobalNotifications()
  
  /**
   * Handle success response
   */
  const handleSuccess = (response, message = null) => {
    const successMessage = message || 
                          response?.data?.message || 
                          response?.message || 
                          'Operation completed successfully'
    
    notifications.success(successMessage)
  }
  
  /**
   * Handle error response
   */
  const handleError = (error, message = null) => {
    let errorMessage = message
    
    if (!errorMessage) {
      if (error?.response?.data?.message) {
        errorMessage = error.response.data.message
      } else if (error?.message) {
        errorMessage = error.message
      } else {
        errorMessage = 'An error occurred'
      }
    }
    
    notifications.error(errorMessage)
    
    // Handle validation errors
    if (error?.response?.status === 422 && error?.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(field => {
        const fieldErrors = validationErrors[field]
        if (Array.isArray(fieldErrors)) {
          fieldErrors.forEach(fieldError => {
            notifications.error(fieldError, { duration: 6000 })
          })
        }
      })
    }
  }
  
  /**
   * Handle loading state
   */
  const handleLoading = (message = 'Loading...') => {
    return notifications.loading(message)
  }
  
  /**
   * Handle confirmation
   */
  const handleConfirm = (message, onConfirm, onCancel = null) => {
    return notifications.confirm(message, onConfirm, onCancel)
  }
  
  return {
    handleSuccess,
    handleError,
    handleLoading,
    handleConfirm,
    ...notifications
  }
}