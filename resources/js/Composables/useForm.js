import { ref, reactive, computed, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Composable for form handling with validation and state management
 * Implements DRY principle for common form patterns
 */
export function useForm(initialData = {}, options = {}) {
  // Default options
  const defaultOptions = {
    resetOnSuccess: true,
    validateOnChange: true,
    validateOnBlur: true,
    debounceValidation: 300,
    preserveScroll: false,
    preserveState: false,
    ...options
  }

  // Form data
  const data = reactive({ ...initialData })
  const originalData = { ...initialData }
  
  // Form state
  const state = reactive({
    processing: false,
    wasSuccessful: false,
    recentlySuccessful: false,
    errors: {},
    touched: {},
    dirty: false,
    valid: true
  })

  // Validation rules
  const rules = ref({})
  const customMessages = ref({})
  
  // Debounce timer for validation
  let validationTimer = null

  // Computed properties
  const isDirty = computed(() => {
    return JSON.stringify(data) !== JSON.stringify(originalData)
  })

  const hasErrors = computed(() => {
    return Object.keys(state.errors).length > 0
  })

  const isValid = computed(() => {
    return !hasErrors.value && Object.keys(state.touched).length > 0
  })

  const canSubmit = computed(() => {
    return !state.processing && isValid.value && isDirty.value
  })

  /**
   * Set validation rules
   */
  const setRules = (newRules) => {
    rules.value = { ...rules.value, ...newRules }
  }

  /**
   * Set custom validation messages
   */
  const setMessages = (messages) => {
    customMessages.value = { ...customMessages.value, ...messages }
  }

  /**
   * Validate a single field
   */
  const validateField = (field, value = data[field]) => {
    const fieldRules = rules.value[field]
    if (!fieldRules) return true

    const errors = []
    
    // Convert single rule to array
    const rulesArray = Array.isArray(fieldRules) ? fieldRules : [fieldRules]
    
    for (const rule of rulesArray) {
      if (typeof rule === 'string') {
        // Handle string rules like 'required', 'email', etc.
        const error = validateStringRule(field, value, rule)
        if (error) errors.push(error)
      } else if (typeof rule === 'function') {
        // Handle custom validation functions
        const error = rule(value, data)
        if (error) errors.push(error)
      } else if (typeof rule === 'object') {
        // Handle rule objects with parameters
        const error = validateRuleObject(field, value, rule)
        if (error) errors.push(error)
      }
    }

    if (errors.length > 0) {
      state.errors[field] = errors[0] // Show first error
      return false
    } else {
      delete state.errors[field]
      return true
    }
  }

  /**
   * Validate string rules
   */
  const validateStringRule = (field, value, rule) => {
    const customMessage = customMessages.value[`${field}.${rule}`] || customMessages.value[rule]
    
    switch (rule) {
      case 'required':
        if (!value || (typeof value === 'string' && !value.trim())) {
          return customMessage || `${field} is required`
        }
        break
        
      case 'email':
        if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          return customMessage || `${field} must be a valid email`
        }
        break
        
      case 'numeric':
        if (value && !/^\d+$/.test(value)) {
          return customMessage || `${field} must be numeric`
        }
        break
        
      case 'url':
        if (value && !/^https?:\/\/.+/.test(value)) {
          return customMessage || `${field} must be a valid URL`
        }
        break
        
      default:
        return null
    }
    
    return null
  }

  /**
   * Validate rule objects
   */
  const validateRuleObject = (field, value, rule) => {
    const { type, ...params } = rule
    const customMessage = customMessages.value[`${field}.${type}`] || customMessages.value[type]
    
    switch (type) {
      case 'min':
        if (value && value.length < params.value) {
          return customMessage || `${field} must be at least ${params.value} characters`
        }
        break
        
      case 'max':
        if (value && value.length > params.value) {
          return customMessage || `${field} must not exceed ${params.value} characters`
        }
        break
        
      case 'minValue':
        if (value && Number(value) < params.value) {
          return customMessage || `${field} must be at least ${params.value}`
        }
        break
        
      case 'maxValue':
        if (value && Number(value) > params.value) {
          return customMessage || `${field} must not exceed ${params.value}`
        }
        break
        
      case 'pattern':
        if (value && !params.value.test(value)) {
          return customMessage || `${field} format is invalid`
        }
        break
        
      case 'confirmed':
        const confirmField = params.field || `${field}_confirmation`
        if (value !== data[confirmField]) {
          return customMessage || `${field} confirmation does not match`
        }
        break
        
      default:
        return null
    }
    
    return null
  }

  /**
   * Validate all fields
   */
  const validate = () => {
    let isFormValid = true
    
    Object.keys(rules.value).forEach(field => {
      const fieldValid = validateField(field)
      if (!fieldValid) isFormValid = false
    })
    
    state.valid = isFormValid
    return isFormValid
  }

  /**
   * Debounced validation
   */
  const debouncedValidate = (field) => {
    if (validationTimer) {
      clearTimeout(validationTimer)
    }
    
    validationTimer = setTimeout(() => {
      validateField(field)
    }, defaultOptions.debounceValidation)
  }

  /**
   * Handle field change
   */
  const handleFieldChange = (field, value) => {
    data[field] = value
    state.touched[field] = true
    state.dirty = isDirty.value
    
    if (defaultOptions.validateOnChange) {
      debouncedValidate(field)
    }
  }

  /**
   * Handle field blur
   */
  const handleFieldBlur = (field) => {
    state.touched[field] = true
    
    if (defaultOptions.validateOnBlur) {
      validateField(field)
    }
  }

  /**
   * Set field value
   */
  const setField = (field, value) => {
    handleFieldChange(field, value)
  }

  /**
   * Set multiple fields
   */
  const setFields = (fields) => {
    Object.keys(fields).forEach(field => {
      setField(field, fields[field])
    })
  }

  /**
   * Get field error
   */
  const getError = (field) => {
    return state.errors[field] || null
  }

  /**
   * Check if field has error
   */
  const hasError = (field) => {
    return !!state.errors[field]
  }

  /**
   * Check if field is touched
   */
  const isTouched = (field) => {
    return !!state.touched[field]
  }

  /**
   * Set errors (usually from server response)
   */
  const setErrors = (errors) => {
    state.errors = { ...errors }
  }

  /**
   * Clear errors
   */
  const clearErrors = (fields = null) => {
    if (fields) {
      const fieldsArray = Array.isArray(fields) ? fields : [fields]
      fieldsArray.forEach(field => {
        delete state.errors[field]
      })
    } else {
      state.errors = {}
    }
  }

  /**
   * Reset form to initial state
   */
  const reset = (newData = null) => {
    const resetData = newData || originalData
    
    Object.keys(data).forEach(key => {
      delete data[key]
    })
    
    Object.assign(data, resetData)
    
    state.processing = false
    state.wasSuccessful = false
    state.recentlySuccessful = false
    state.errors = {}
    state.touched = {}
    state.dirty = false
    state.valid = true
  }

  /**
   * Transform data before submission
   */
  const transform = (callback) => {
    return callback(data)
  }

  /**
   * Submit form using Inertia
   */
  const submit = (method, url, options = {}) => {
    if (!validate()) {
      return Promise.reject(new Error('Form validation failed'))
    }

    state.processing = true
    state.wasSuccessful = false
    state.recentlySuccessful = false
    
    const submitOptions = {
      preserveScroll: defaultOptions.preserveScroll,
      preserveState: defaultOptions.preserveState,
      onSuccess: (page) => {
        state.processing = false
        state.wasSuccessful = true
        state.recentlySuccessful = true
        
        if (defaultOptions.resetOnSuccess) {
          reset()
        }
        
        // Hide success message after delay
        setTimeout(() => {
          state.recentlySuccessful = false
        }, 2000)
        
        if (options.onSuccess) {
          options.onSuccess(page)
        }
      },
      onError: (errors) => {
        state.processing = false
        state.wasSuccessful = false
        setErrors(errors)
        
        if (options.onError) {
          options.onError(errors)
        }
      },
      onFinish: () => {
        if (options.onFinish) {
          options.onFinish()
        }
      },
      ...options
    }

    return router[method](url, data, submitOptions)
  }

  /**
   * Submit shortcuts
   */
  const post = (url, options = {}) => submit('post', url, options)
  const put = (url, options = {}) => submit('put', url, options)
  const patch = (url, options = {}) => submit('patch', url, options)
  const del = (url, options = {}) => submit('delete', url, options)

  /**
   * File upload helper
   */
  const uploadFile = (field, file) => {
    if (file instanceof File) {
      data[field] = file
      state.touched[field] = true
      
      if (defaultOptions.validateOnChange) {
        validateField(field)
      }
    }
  }

  /**
   * Watch for data changes
   */
  watch(
    () => data,
    () => {
      state.dirty = isDirty.value
    },
    { deep: true }
  )

  return {
    // Data
    data,
    
    // State
    processing: computed(() => state.processing),
    wasSuccessful: computed(() => state.wasSuccessful),
    recentlySuccessful: computed(() => state.recentlySuccessful),
    errors: computed(() => state.errors),
    touched: computed(() => state.touched),
    dirty: computed(() => state.dirty),
    valid: computed(() => state.valid),
    
    // Computed
    isDirty,
    hasErrors,
    isValid,
    canSubmit,
    
    // Methods
    setRules,
    setMessages,
    validate,
    validateField,
    setField,
    setFields,
    getError,
    hasError,
    isTouched,
    setErrors,
    clearErrors,
    reset,
    transform,
    
    // Submission
    submit,
    post,
    put,
    patch,
    delete: del,
    
    // Utilities
    uploadFile,
    handleFieldChange,
    handleFieldBlur
  }
}

/**
 * Composable for multi-step forms
 */
export function useMultiStepForm(steps = [], initialData = {}) {
  const currentStep = ref(0)
  const completedSteps = ref(new Set())
  
  const form = useForm(initialData, {
    resetOnSuccess: false
  })
  
  const totalSteps = computed(() => steps.length)
  const isFirstStep = computed(() => currentStep.value === 0)
  const isLastStep = computed(() => currentStep.value === totalSteps.value - 1)
  const currentStepData = computed(() => steps[currentStep.value] || {})
  const progress = computed(() => ((currentStep.value + 1) / totalSteps.value) * 100)
  
  /**
   * Go to next step
   */
  const nextStep = () => {
    if (!isLastStep.value && validateCurrentStep()) {
      completedSteps.value.add(currentStep.value)
      currentStep.value++
    }
  }
  
  /**
   * Go to previous step
   */
  const prevStep = () => {
    if (!isFirstStep.value) {
      currentStep.value--
    }
  }
  
  /**
   * Go to specific step
   */
  const goToStep = (step) => {
    if (step >= 0 && step < totalSteps.value) {
      currentStep.value = step
    }
  }
  
  /**
   * Validate current step
   */
  const validateCurrentStep = () => {
    const stepFields = currentStepData.value.fields || []
    let isValid = true
    
    stepFields.forEach(field => {
      if (!form.validateField(field)) {
        isValid = false
      }
    })
    
    return isValid
  }
  
  /**
   * Check if step is completed
   */
  const isStepCompleted = (step) => {
    return completedSteps.value.has(step)
  }
  
  /**
   * Submit multi-step form
   */
  const submitForm = (method, url, options = {}) => {
    if (validateCurrentStep()) {
      return form.submit(method, url, options)
    }
    return Promise.reject(new Error('Current step validation failed'))
  }
  
  return {
    ...form,
    
    // Step management
    currentStep: computed(() => currentStep.value),
    totalSteps,
    isFirstStep,
    isLastStep,
    currentStepData,
    progress,
    completedSteps: computed(() => completedSteps.value),
    
    // Step methods
    nextStep,
    prevStep,
    goToStep,
    validateCurrentStep,
    isStepCompleted,
    submitForm
  }
}