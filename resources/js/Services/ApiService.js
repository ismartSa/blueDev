import { router } from '@inertiajs/vue3'
import axios from 'axios'

/**
 * Base API Service for handling common HTTP operations
 * Implements DRY principle for API calls
 */
class ApiService {
    constructor() {
        this.setupAxiosInterceptors()
    }

    /**
     * Setup axios interceptors for common functionality
     */
    setupAxiosInterceptors() {
        // Request interceptor
        axios.interceptors.request.use(
            (config) => {
                // Add CSRF token
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                if (token) {
                    config.headers['X-CSRF-TOKEN'] = token
                }
                
                // Add common headers
                config.headers['X-Requested-With'] = 'XMLHttpRequest'
                config.headers['Accept'] = 'application/json'
                
                return config
            },
            (error) => Promise.reject(error)
        )

        // Response interceptor
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                this.handleApiError(error)
                return Promise.reject(error)
            }
        )
    }

    /**
     * Handle API errors consistently
     */
    handleApiError(error) {
        if (error.response?.status === 401) {
            router.visit('/login')
        } else if (error.response?.status === 403) {
            this.showNotification('Access denied', 'error')
        } else if (error.response?.status === 422) {
            // Validation errors - handled by components
            return
        } else if (error.response?.status >= 500) {
            this.showNotification('Server error occurred', 'error')
        }
    }

    /**
     * Show notification (can be customized based on your notification system)
     */
    showNotification(message, type = 'info') {
        // Implement your notification system here
        console.log(`${type.toUpperCase()}: ${message}`)
    }

    /**
     * Generic GET request
     */
    async get(url, params = {}) {
        try {
            const response = await axios.get(url, { params })
            return response.data
        } catch (error) {
            throw this.formatError(error)
        }
    }

    /**
     * Generic POST request
     */
    async post(url, data = {}) {
        try {
            const response = await axios.post(url, data)
            return response.data
        } catch (error) {
            throw this.formatError(error)
        }
    }

    /**
     * Generic PUT request
     */
    async put(url, data = {}) {
        try {
            const response = await axios.put(url, data)
            return response.data
        } catch (error) {
            throw this.formatError(error)
        }
    }

    /**
     * Generic PATCH request
     */
    async patch(url, data = {}) {
        try {
            const response = await axios.patch(url, data)
            return response.data
        } catch (error) {
            throw this.formatError(error)
        }
    }

    /**
     * Generic DELETE request
     */
    async delete(url) {
        try {
            const response = await axios.delete(url)
            return response.data
        } catch (error) {
            throw this.formatError(error)
        }
    }

    /**
     * Upload file with progress tracking
     */
    async upload(url, formData, onProgress = null) {
        try {
            const config = {
                headers: { 'Content-Type': 'multipart/form-data' }
            }
            
            if (onProgress) {
                config.onUploadProgress = (progressEvent) => {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    )
                    onProgress(percentCompleted)
                }
            }
            
            const response = await axios.post(url, formData, config)
            return response.data
        } catch (error) {
            throw this.formatError(error)
        }
    }

    /**
     * Format error for consistent handling
     */
    formatError(error) {
        return {
            message: error.response?.data?.message || error.message || 'An error occurred',
            status: error.response?.status,
            errors: error.response?.data?.errors || {},
            data: error.response?.data
        }
    }

    /**
     * Build query string from object
     */
    buildQueryString(params) {
        const searchParams = new URLSearchParams()
        
        Object.keys(params).forEach(key => {
            const value = params[key]
            if (value !== null && value !== undefined && value !== '') {
                if (Array.isArray(value)) {
                    value.forEach(item => searchParams.append(`${key}[]`, item))
                } else {
                    searchParams.append(key, value)
                }
            }
        })
        
        return searchParams.toString()
    }

    /**
     * Debounce function for search inputs
     */
    debounce(func, wait) {
        let timeout
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout)
                func(...args)
            }
            clearTimeout(timeout)
            timeout = setTimeout(later, wait)
        }
    }

    /**
     * Throttle function for scroll events
     */
    throttle(func, limit) {
        let inThrottle
        return function() {
            const args = arguments
            const context = this
            if (!inThrottle) {
                func.apply(context, args)
                inThrottle = true
                setTimeout(() => inThrottle = false, limit)
            }
        }
    }
}

// Export singleton instance
export default new ApiService()

// Export class for testing or custom instances
export { ApiService }