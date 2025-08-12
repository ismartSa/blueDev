/**
 * Services Index - Centralized exports for all service classes
 * 
 * This file provides easy access to all service classes that handle
 * API communication and business logic for different entities.
 * 
 * Usage:
 * import { QuizService, UserService, CourseService } from '@/Services'
 * 
 * Or import specific services:
 * import QuizService from '@/Services/QuizService'
 */

// Main service classes
export { default as QuizService, QuizServiceFactory, QuizCacheManager } from './QuizService.js'

// Service instances (singletons)
export { quizService, quizCache } from './QuizService.js'

/**
 * Base Service Class - Abstract class for common service functionality
 * All specific services should extend this class for consistency
 */
export class BaseService {
  constructor(baseUrl, apiComposable = null) {
    // Import useApi if not provided
    if (!apiComposable) {
      const { useApi } = require('@/Composables')
      const { get, post, put, patch, delete: deleteRequest } = useApi()
      
      this.get = get
      this.post = post
      this.put = put
      this.patch = patch
      this.delete = deleteRequest
    } else {
      Object.assign(this, apiComposable)
    }
    
    this.baseUrl = baseUrl
    this.cache = new Map()
    this.cacheTTL = 300000 // 5 minutes
  }

  /**
   * Get paginated data with filters
   * @param {Object} params - Query parameters
   * @returns {Promise<Object>} Paginated data
   */
  async getPaginated(params = {}) {
    const queryParams = new URLSearchParams({
      page: params.page || 1,
      per_page: params.per_page || 10,
      search: params.search || '',
      sort_by: params.sortBy || 'created_at',
      sort_order: params.sortOrder || 'desc',
      ...params.filters
    }).toString()
    
    return await this.get(`${this.baseUrl}?${queryParams}`)
  }

  /**
   * Get all items without pagination
   * @param {Object} filters - Filter parameters
   * @returns {Promise<Array>} Items array
   */
  async getAll(filters = {}) {
    const cacheKey = `all_${JSON.stringify(filters)}`
    const cached = this.getFromCache(cacheKey)
    
    if (cached) return cached
    
    const params = new URLSearchParams(filters).toString()
    const response = await this.get(`${this.baseUrl}/all?${params}`)
    
    this.setCache(cacheKey, response.data)
    return response.data
  }

  /**
   * Get item by ID
   * @param {number} id - Item ID
   * @param {Array} includes - Relationships to include
   * @returns {Promise<Object>} Item data
   */
  async getById(id, includes = []) {
    const cacheKey = `item_${id}_${includes.join(',')}`
    const cached = this.getFromCache(cacheKey)
    
    if (cached) return cached
    
    const params = includes.length > 0 
      ? `?include=${includes.join(',')}` 
      : ''
    
    const response = await this.get(`${this.baseUrl}/${id}${params}`)
    
    this.setCache(cacheKey, response.data)
    return response.data
  }

  /**
   * Create new item
   * @param {Object} data - Item data
   * @returns {Promise<Object>} Created item
   */
  async create(data) {
    const response = await this.post(this.baseUrl, this.formatData(data))
    this.invalidateCache()
    return response.data
  }

  /**
   * Update existing item
   * @param {number} id - Item ID
   * @param {Object} data - Updated item data
   * @returns {Promise<Object>} Updated item
   */
  async update(id, data) {
    const response = await this.put(`${this.baseUrl}/${id}`, this.formatData(data))
    this.invalidateCache()
    return response.data
  }

  /**
   * Partially update item
   * @param {number} id - Item ID
   * @param {Object} data - Partial item data
   * @returns {Promise<Object>} Updated item
   */
  async partialUpdate(id, data) {
    const response = await this.patch(`${this.baseUrl}/${id}`, data)
    this.invalidateCache()
    return response.data
  }

  /**
   * Delete item
   * @param {number} id - Item ID
   * @returns {Promise<void>}
   */
  async remove(id) {
    await this.delete(`${this.baseUrl}/${id}`)
    this.invalidateCache()
  }

  /**
   * Bulk operations
   */
  async bulkCreate(items) {
    const response = await this.post(`${this.baseUrl}/bulk`, {
      items: items.map(item => this.formatData(item))
    })
    this.invalidateCache()
    return response.data
  }

  async bulkUpdate(updates) {
    const response = await this.patch(`${this.baseUrl}/bulk`, { updates })
    this.invalidateCache()
    return response.data
  }

  async bulkDelete(ids) {
    await this.post(`${this.baseUrl}/bulk/delete`, { ids })
    this.invalidateCache()
  }

  /**
   * Search functionality
   * @param {string} query - Search query
   * @param {Object} filters - Additional filters
   * @returns {Promise<Array>} Search results
   */
  async search(query, filters = {}) {
    const params = new URLSearchParams({
      q: query,
      ...filters
    }).toString()
    
    const response = await this.get(`${this.baseUrl}/search?${params}`)
    return response.data
  }

  /**
   * Export data
   * @param {string} format - Export format (json, csv, xlsx)
   * @param {Object} filters - Export filters
   * @returns {Promise<Blob>} Export file
   */
  async export(format = 'json', filters = {}) {
    const params = new URLSearchParams(filters).toString()
    return await this.get(`${this.baseUrl}/export/${format}?${params}`, {
      responseType: 'blob'
    })
  }

  /**
   * Cache management
   */
  setCache(key, data) {
    this.cache.set(key, {
      data,
      timestamp: Date.now()
    })
  }

  getFromCache(key) {
    const cached = this.cache.get(key)
    
    if (!cached) return null
    
    if (Date.now() - cached.timestamp > this.cacheTTL) {
      this.cache.delete(key)
      return null
    }
    
    return cached.data
  }

  invalidateCache(pattern = null) {
    if (pattern) {
      for (const key of this.cache.keys()) {
        if (key.includes(pattern)) {
          this.cache.delete(key)
        }
      }
    } else {
      this.cache.clear()
    }
  }

  /**
   * Format data before sending to API
   * Override in specific services for custom formatting
   * @param {Object} data - Raw data
   * @returns {Object} Formatted data
   */
  formatData(data) {
    return data
  }

  /**
   * Validate data before submission
   * Override in specific services for custom validation
   * @param {Object} data - Data to validate
   * @returns {Object} Validation result
   */
  validateData(data) {
    return {
      isValid: true,
      errors: []
    }
  }
}

/**
 * User Service - Handles user-related API operations
 */
export class UserService extends BaseService {
  constructor() {
    super('/api/users')
  }

  async getProfile(userId = null) {
    const endpoint = userId ? `${this.baseUrl}/${userId}/profile` : '/api/profile'
    return await this.get(endpoint)
  }

  async updateProfile(data, userId = null) {
    const endpoint = userId ? `${this.baseUrl}/${userId}/profile` : '/api/profile'
    return await this.put(endpoint, data)
  }

  async changePassword(data, userId = null) {
    const endpoint = userId ? `${this.baseUrl}/${userId}/password` : '/api/password'
    return await this.patch(endpoint, data)
  }

  async activate(id) {
    return await this.patch(`${this.baseUrl}/${id}/activate`)
  }

  async deactivate(id) {
    return await this.patch(`${this.baseUrl}/${id}/deactivate`)
  }

  async bulkActivate(ids) {
    return await this.post(`${this.baseUrl}/bulk/activate`, { ids })
  }

  async bulkDeactivate(ids) {
    return await this.post(`${this.baseUrl}/bulk/deactivate`, { ids })
  }

  formatData(data) {
    return {
      name: data.name?.trim(),
      email: data.email?.trim().toLowerCase(),
      role: data.role,
      status: data.status || 'active',
      profile: {
        phone: data.phone?.trim(),
        address: data.address?.trim(),
        bio: data.bio?.trim()
      }
    }
  }

  validateData(data) {
    const errors = []
    
    if (!data.name?.trim()) {
      errors.push('Name is required')
    }
    
    if (!data.email?.trim()) {
      errors.push('Email is required')
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
      errors.push('Invalid email format')
    }
    
    if (!data.role) {
      errors.push('Role is required')
    }
    
    return {
      isValid: errors.length === 0,
      errors
    }
  }
}

/**
 * Course Service - Handles course-related API operations
 */
export class CourseService extends BaseService {
  constructor() {
    super('/api/courses')
  }

  async getStudents(courseId, params = {}) {
    const queryParams = new URLSearchParams(params).toString()
    return await this.get(`${this.baseUrl}/${courseId}/students?${queryParams}`)
  }

  async enrollStudent(courseId, studentId) {
    return await this.post(`${this.baseUrl}/${courseId}/enroll`, {
      student_id: studentId
    })
  }

  async unenrollStudent(courseId, studentId) {
    return await this.delete(`${this.baseUrl}/${courseId}/students/${studentId}`)
  }

  async getQuizzes(courseId, params = {}) {
    const queryParams = new URLSearchParams(params).toString()
    return await this.get(`${this.baseUrl}/${courseId}/quizzes?${queryParams}`)
  }

  async publish(id) {
    return await this.patch(`${this.baseUrl}/${id}/publish`)
  }

  async unpublish(id) {
    return await this.patch(`${this.baseUrl}/${id}/unpublish`)
  }

  formatData(data) {
    return {
      title: data.title?.trim(),
      description: data.description?.trim(),
      category_id: data.category_id,
      instructor_id: data.instructor_id,
      status: data.status || 'draft',
      settings: {
        max_students: data.max_students ? parseInt(data.max_students) : null,
        duration: data.duration ? parseInt(data.duration) : null,
        difficulty: data.difficulty || 'beginner'
      }
    }
  }
}

/**
 * Notification Service - Handles notification operations
 */
export class NotificationService extends BaseService {
  constructor() {
    super('/api/notifications')
  }

  async markAsRead(id) {
    return await this.patch(`${this.baseUrl}/${id}/read`)
  }

  async markAllAsRead() {
    return await this.post(`${this.baseUrl}/mark-all-read`)
  }

  async getUnreadCount() {
    const response = await this.get(`${this.baseUrl}/unread-count`)
    return response.count
  }

  async subscribe(type, entityId = null) {
    return await this.post(`${this.baseUrl}/subscribe`, {
      type,
      entity_id: entityId
    })
  }

  async unsubscribe(type, entityId = null) {
    return await this.post(`${this.baseUrl}/unsubscribe`, {
      type,
      entity_id: entityId
    })
  }
}

/**
 * Analytics Service - Handles analytics and reporting
 */
export class AnalyticsService extends BaseService {
  constructor() {
    super('/api/analytics')
  }

  async getDashboardStats(period = '30d') {
    return await this.get(`${this.baseUrl}/dashboard?period=${period}`)
  }

  async getUserStats(userId, period = '30d') {
    return await this.get(`${this.baseUrl}/users/${userId}?period=${period}`)
  }

  async getQuizStats(quizId, period = '30d') {
    return await this.get(`${this.baseUrl}/quizzes/${quizId}?period=${period}`)
  }

  async getCourseStats(courseId, period = '30d') {
    return await this.get(`${this.baseUrl}/courses/${courseId}?period=${period}`)
  }

  async generateReport(type, params = {}) {
    return await this.post(`${this.baseUrl}/reports/${type}`, params)
  }
}

/**
 * File Service - Handles file upload and management
 */
export class FileService extends BaseService {
  constructor() {
    super('/api/files')
  }

  async upload(file, options = {}) {
    const formData = new FormData()
    formData.append('file', file)
    
    if (options.folder) {
      formData.append('folder', options.folder)
    }
    
    if (options.public !== undefined) {
      formData.append('public', options.public)
    }
    
    return await this.post(`${this.baseUrl}/upload`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: options.onProgress
    })
  }

  async uploadMultiple(files, options = {}) {
    const formData = new FormData()
    
    files.forEach((file, index) => {
      formData.append(`files[${index}]`, file)
    })
    
    if (options.folder) {
      formData.append('folder', options.folder)
    }
    
    return await this.post(`${this.baseUrl}/upload/multiple`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: options.onProgress
    })
  }

  async getDownloadUrl(fileId) {
    const response = await this.get(`${this.baseUrl}/${fileId}/download-url`)
    return response.url
  }

  async delete(fileId) {
    return await this.delete(`${this.baseUrl}/${fileId}`)
  }
}

// Create service instances
export const userService = new UserService()
export const courseService = new CourseService()
export const notificationService = new NotificationService()
export const analyticsService = new AnalyticsService()
export const fileService = new FileService()

// Grouped exports for easier bulk imports
export const CoreServices = {
  UserService,
  CourseService,
  QuizService,
  NotificationService,
  AnalyticsService,
  FileService
}

export const ServiceInstances = {
  userService,
  courseService,
  quizService,
  notificationService,
  analyticsService,
  fileService
}

// Service factory for creating configured instances
export class ServiceFactory {
  static create(ServiceClass, config = {}) {
    const service = new ServiceClass()
    
    // Apply configuration
    if (config.baseUrl) {
      service.baseUrl = config.baseUrl
    }
    
    if (config.cacheTTL) {
      service.cacheTTL = config.cacheTTL
    }
    
    return service
  }
  
  static createWithAuth(ServiceClass, token) {
    const service = new ServiceClass()
    
    // Configure authentication
    service.defaultHeaders = {
      'Authorization': `Bearer ${token}`
    }
    
    return service
  }
}

// Default export for convenience
export default {
  BaseService,
  UserService,
  CourseService,
  QuizService,
  NotificationService,
  AnalyticsService,
  FileService,
  ServiceFactory
}