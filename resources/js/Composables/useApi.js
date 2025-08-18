import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

/**
 * Composable for API operations with caching and state management
 * Implements DRY principle for common API patterns
 */
export function useApi(baseUrl = '', options = {}) {
  // Default options
  const defaultOptions = {
    cache: true,
    cacheTtl: 300000, // 5 minutes
    retries: 3,
    retryDelay: 1000,
    timeout: 10000,
    showNotifications: true,
    ...options
  }

  // Reactive state
  const state = reactive({
    loading: false,
    error: null,
    data: null,
    meta: null,
    lastFetch: null
  })

  // Cache storage
  const cache = new Map()

  // Loading states for different operations
  const loadingStates = reactive({
    fetching: false,
    creating: false,
    updating: false,
    deleting: false
  })

  // Computed properties
  const isLoading = computed(() => Object.values(loadingStates).some(Boolean))
  const hasError = computed(() => !!state.error)
  const hasData = computed(() => !!state.data)

  /**
   * Generate cache key
   */
  const getCacheKey = (url, params = {}) => {
    const paramString = Object.keys(params)
      .sort()
      .map(key => `${key}=${params[key]}`)
      .join('&')
    return `${url}${paramString ? '?' + paramString : ''}`
  }

  /**
   * Check if cache is valid
   */
  const isCacheValid = (cacheEntry) => {
    if (!cacheEntry) return false
    return Date.now() - cacheEntry.timestamp < defaultOptions.cacheTtl
  }

  /**
   * Get from cache
   */
  const getFromCache = (key) => {
    const cacheEntry = cache.get(key)
    if (isCacheValid(cacheEntry)) {
      return cacheEntry.data
    }
    cache.delete(key)
    return null
  }

  /**
   * Set cache
   */
  const setCache = (key, data) => {
    if (defaultOptions.cache) {
      cache.set(key, {
        data,
        timestamp: Date.now()
      })
    }
  }

  /**
   * Clear cache
   */
  const clearCache = (pattern = null) => {
    if (pattern) {
      const regex = new RegExp(pattern)
      for (const [key] of cache) {
        if (regex.test(key)) {
          cache.delete(key)
        }
      }
    } else {
      cache.clear()
    }
  }

  /**
   * Handle API errors
   */
  const handleError = (error, operation = 'API call') => {
    console.error(`Error in ${operation}:`, error)
    
    let errorMessage = 'An unexpected error occurred'
    
    if (error.response) {
      // Server responded with error status
      const { status, data } = error.response
      
      switch (status) {
        case 400:
          errorMessage = data.message || 'Bad request'
          break
        case 401:
          errorMessage = 'Unauthorized access'
          // Redirect to login if needed
          if (defaultOptions.redirectOnUnauth) {
            router.visit('/login')
          }
          break
        case 403:
          errorMessage = 'Access forbidden'
          break
        case 404:
          errorMessage = 'Resource not found'
          break
        case 422:
          errorMessage = 'Validation failed'
          state.validationErrors = data.errors || {}
          break
        case 500:
          errorMessage = 'Server error occurred'
          break
        default:
          errorMessage = data.message || `HTTP ${status} error`
      }
    } else if (error.request) {
      // Network error
      errorMessage = 'Network error - please check your connection'
    } else {
      // Other error
      errorMessage = error.message || errorMessage
    }
    
    state.error = {
      message: errorMessage,
      status: error.response?.status,
      data: error.response?.data,
      original: error
    }
    
    if (defaultOptions.showNotifications) {
      // Show notification (implement based on your notification system)
      console.error(errorMessage)
    }
    
    return Promise.reject(state.error)
  }

  /**
   * Make HTTP request with retry logic
   */
  const makeRequest = async (config, retryCount = 0) => {
    try {
      const response = await axios({
        timeout: defaultOptions.timeout,
        ...config
      })
      return response
    } catch (error) {
      if (retryCount < defaultOptions.retries && error.code !== 'ECONNABORTED') {
        await new Promise(resolve => 
          setTimeout(resolve, defaultOptions.retryDelay * (retryCount + 1))
        )
        return makeRequest(config, retryCount + 1)
      }
      throw error
    }
  }

  /**
   * GET request
   */
  const get = async (url, params = {}, options = {}) => {
    const fullUrl = baseUrl + url
    const cacheKey = getCacheKey(fullUrl, params)
    
    // Check cache first
    if (defaultOptions.cache && !options.fresh) {
      const cachedData = getFromCache(cacheKey)
      if (cachedData) {
        state.data = cachedData.data
        state.meta = cachedData.meta
        return cachedData
      }
    }
    
    loadingStates.fetching = true
    state.loading = true
    state.error = null
    
    try {
      const response = await makeRequest({
        method: 'GET',
        url: fullUrl,
        params
      })
      
      const result = {
        data: response.data.data || response.data,
        meta: response.data.meta || null,
        status: response.status
      }
      
      state.data = result.data
      state.meta = result.meta
      state.lastFetch = Date.now()
      
      // Cache the result
      setCache(cacheKey, result)
      
      return result
    } catch (error) {
      return handleError(error, `GET ${fullUrl}`)
    } finally {
      loadingStates.fetching = false
      state.loading = false
    }
  }

  /**
   * POST request
   */
  const post = async (url, data = {}, options = {}) => {
    const fullUrl = baseUrl + url
    
    loadingStates.creating = true
    state.loading = true
    state.error = null
    
    try {
      const response = await makeRequest({
        method: 'POST',
        url: fullUrl,
        data,
        ...options
      })
      
      const result = {
        data: response.data.data || response.data,
        meta: response.data.meta || null,
        status: response.status
      }
      
      // Clear related cache
      clearCache(baseUrl.replace(/\/$/, ''))
      
      if (defaultOptions.showNotifications) {
        console.log('Resource created successfully')
      }
      
      return result
    } catch (error) {
      return handleError(error, `POST ${fullUrl}`)
    } finally {
      loadingStates.creating = false
      state.loading = false
    }
  }

  /**
   * PUT request
   */
  const put = async (url, data = {}, options = {}) => {
    const fullUrl = baseUrl + url
    
    loadingStates.updating = true
    state.loading = true
    state.error = null
    
    try {
      const response = await makeRequest({
        method: 'PUT',
        url: fullUrl,
        data,
        ...options
      })
      
      const result = {
        data: response.data.data || response.data,
        meta: response.data.meta || null,
        status: response.status
      }
      
      // Clear related cache
      clearCache(baseUrl.replace(/\/$/, ''))
      
      if (defaultOptions.showNotifications) {
        console.log('Resource updated successfully')
      }
      
      return result
    } catch (error) {
      return handleError(error, `PUT ${fullUrl}`)
    } finally {
      loadingStates.updating = false
      state.loading = false
    }
  }

  /**
   * PATCH request
   */
  const patch = async (url, data = {}, options = {}) => {
    const fullUrl = baseUrl + url
    
    loadingStates.updating = true
    state.loading = true
    state.error = null
    
    try {
      const response = await makeRequest({
        method: 'PATCH',
        url: fullUrl,
        data,
        ...options
      })
      
      const result = {
        data: response.data.data || response.data,
        meta: response.data.meta || null,
        status: response.status
      }
      
      // Clear related cache
      clearCache(baseUrl.replace(/\/$/, ''))
      
      if (defaultOptions.showNotifications) {
        console.log('Resource updated successfully')
      }
      
      return result
    } catch (error) {
      return handleError(error, `PATCH ${fullUrl}`)
    } finally {
      loadingStates.updating = false
      state.loading = false
    }
  }

  /**
   * DELETE request
   */
  const del = async (url, options = {}) => {
    const fullUrl = baseUrl + url
    
    loadingStates.deleting = true
    state.loading = true
    state.error = null
    
    try {
      const response = await makeRequest({
        method: 'DELETE',
        url: fullUrl,
        ...options
      })
      
      const result = {
        data: response.data.data || response.data,
        meta: response.data.meta || null,
        status: response.status
      }
      
      // Clear related cache
      clearCache(baseUrl.replace(/\/$/, ''))
      
      if (defaultOptions.showNotifications) {
        console.log('Resource deleted successfully')
      }
      
      return result
    } catch (error) {
      return handleError(error, `DELETE ${fullUrl}`)
    } finally {
      loadingStates.deleting = false
      state.loading = false
    }
  }

  /**
   * Upload file
   */
  const upload = async (url, file, options = {}) => {
    const fullUrl = baseUrl + url
    const formData = new FormData()
    
    if (file instanceof File) {
      formData.append(options.fieldName || 'file', file)
    } else if (typeof file === 'object') {
      Object.keys(file).forEach(key => {
        formData.append(key, file[key])
      })
    }
    
    // Add additional data
    if (options.data) {
      Object.keys(options.data).forEach(key => {
        formData.append(key, options.data[key])
      })
    }
    
    loadingStates.creating = true
    state.loading = true
    state.error = null
    
    try {
      const response = await makeRequest({
        method: 'POST',
        url: fullUrl,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: options.onProgress,
        ...options
      })
      
      const result = {
        data: response.data.data || response.data,
        meta: response.data.meta || null,
        status: response.status
      }
      
      if (defaultOptions.showNotifications) {
        console.log('File uploaded successfully')
      }
      
      return result
    } catch (error) {
      return handleError(error, `UPLOAD ${fullUrl}`)
    } finally {
      loadingStates.creating = false
      state.loading = false
    }
  }

  /**
   * Reset state
   */
  const reset = () => {
    state.loading = false
    state.error = null
    state.data = null
    state.meta = null
    state.lastFetch = null
    state.validationErrors = {}
    
    Object.keys(loadingStates).forEach(key => {
      loadingStates[key] = false
    })
  }

  /**
   * Refresh data
   */
  const refresh = async (url, params = {}) => {
    return get(url, params, { fresh: true })
  }

  return {
    // State
    state: readonly(state),
    loadingStates: readonly(loadingStates),
    
    // Computed
    isLoading,
    hasError,
    hasData,
    
    // Methods
    get,
    post,
    put,
    patch,
    delete: del,
    upload,
    refresh,
    reset,
    clearCache,
    
    // Cache utilities
    getCacheKey,
    setCache,
    getFromCache
  }
}

/**
 * Specialized composable for CRUD operations
 */
export function useCrud(resource, options = {}) {
  const api = useApi(`/api/${resource}`, options)
  
  const items = ref([])
  const currentItem = ref(null)
  const pagination = ref(null)
  const filters = reactive({})
  
  /**
   * Fetch all items
   */
  const fetchAll = async (params = {}) => {
    const result = await api.get('', { ...filters, ...params })
    if (result) {
      items.value = result.data
      pagination.value = result.meta
    }
    return result
  }
  
  /**
   * Fetch single item
   */
  const fetchOne = async (id, params = {}) => {
    const result = await api.get(`/${id}`, params)
    if (result) {
      currentItem.value = result.data
    }
    return result
  }
  
  /**
   * Create item
   */
  const create = async (data) => {
    const result = await api.post('', data)
    if (result) {
      items.value.unshift(result.data)
    }
    return result
  }
  
  /**
   * Update item
   */
  const update = async (id, data) => {
    const result = await api.put(`/${id}`, data)
    if (result) {
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1) {
        items.value[index] = result.data
      }
      if (currentItem.value?.id === id) {
        currentItem.value = result.data
      }
    }
    return result
  }
  
  /**
   * Delete item
   */
  const remove = async (id) => {
    const result = await api.delete(`/${id}`)
    if (result) {
      items.value = items.value.filter(item => item.id !== id)
      if (currentItem.value?.id === id) {
        currentItem.value = null
      }
    }
    return result
  }
  
  /**
   * Set filters
   */
  const setFilters = (newFilters) => {
    Object.assign(filters, newFilters)
  }
  
  /**
   * Clear filters
   */
  const clearFilters = () => {
    Object.keys(filters).forEach(key => {
      delete filters[key]
    })
  }
  
  return {
    ...api,
    
    // Data
    items,
    currentItem,
    pagination,
    filters,
    
    // Methods
    fetchAll,
    fetchOne,
    create,
    update,
    remove,
    setFilters,
    clearFilters
  }
}