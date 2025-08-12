import { useApi } from '@/Composables'

/**
 * Quiz Service - Centralized API management for quiz operations
 * Implements DRY principle and provides consistent error handling
 */
export default class QuizService {
  constructor() {
    const { get, post, put, patch, delete: deleteRequest } = useApi()
    
    // Bind API methods
    this.get = get
    this.post = post
    this.put = put
    this.patch = patch
    this.delete = deleteRequest
    
    // Base endpoint
    this.baseUrl = '/api/quizzes'
  }

  /**
   * Get paginated quizzes with filters
   * @param {Object} params - Query parameters
   * @returns {Promise<Object>} Paginated quiz data
   */
  async getPaginated(params = {}) {
    const queryParams = new URLSearchParams({
      page: params.page || 1,
      per_page: params.per_page || 10,
      search: params.search || '',
      status: params.status || '',
      course_id: params.course_id || '',
      difficulty: params.difficulty || '',
      sort_by: params.sortBy || 'created_at',
      sort_order: params.sortOrder || 'desc'
    }).toString()
    
    return await this.get(`${this.baseUrl}?${queryParams}`)
  }

  /**
   * Get all quizzes (without pagination)
   * @param {Object} filters - Filter parameters
   * @returns {Promise<Array>} Quiz array
   */
  async getAll(filters = {}) {
    const params = new URLSearchParams(filters).toString()
    const response = await this.get(`${this.baseUrl}/all?${params}`)
    return response.data
  }

  /**
   * Get quiz by ID with relationships
   * @param {number} id - Quiz ID
   * @param {Array} includes - Relationships to include
   * @returns {Promise<Object>} Quiz data
   */
  async getById(id, includes = ['questions', 'course', 'attempts']) {
    const params = new URLSearchParams({
      include: includes.join(',')
    }).toString()
    
    return await this.get(`${this.baseUrl}/${id}?${params}`)
  }

  /**
   * Create new quiz
   * @param {Object} data - Quiz data
   * @returns {Promise<Object>} Created quiz
   */
  async create(data) {
    return await this.post(this.baseUrl, this.formatQuizData(data))
  }

  /**
   * Update existing quiz
   * @param {number} id - Quiz ID
   * @param {Object} data - Updated quiz data
   * @returns {Promise<Object>} Updated quiz
   */
  async update(id, data) {
    return await this.put(`${this.baseUrl}/${id}`, this.formatQuizData(data))
  }

  /**
   * Partially update quiz
   * @param {number} id - Quiz ID
   * @param {Object} data - Partial quiz data
   * @returns {Promise<Object>} Updated quiz
   */
  async partialUpdate(id, data) {
    return await this.patch(`${this.baseUrl}/${id}`, data)
  }

  /**
   * Delete quiz
   * @param {number} id - Quiz ID
   * @returns {Promise<void>}
   */
  async remove(id) {
    return await this.delete(`${this.baseUrl}/${id}`)
  }

  /**
   * Duplicate quiz
   * @param {number} id - Quiz ID to duplicate
   * @returns {Promise<Object>} Duplicated quiz
   */
  async duplicate(id) {
    return await this.post(`${this.baseUrl}/${id}/duplicate`)
  }

  /**
   * Publish quiz
   * @param {number} id - Quiz ID
   * @returns {Promise<Object>} Updated quiz
   */
  async publish(id) {
    return await this.patch(`${this.baseUrl}/${id}/publish`)
  }

  /**
   * Unpublish quiz
   * @param {number} id - Quiz ID
   * @returns {Promise<Object>} Updated quiz
   */
  async unpublish(id) {
    return await this.patch(`${this.baseUrl}/${id}/unpublish`)
  }

  /**
   * Archive quiz
   * @param {number} id - Quiz ID
   * @returns {Promise<Object>} Updated quiz
   */
  async archive(id) {
    return await this.patch(`${this.baseUrl}/${id}/archive`)
  }

  /**
   * Get quiz analytics
   * @param {number} id - Quiz ID
   * @param {Object} params - Analytics parameters
   * @returns {Promise<Object>} Analytics data
   */
  async getAnalytics(id, params = {}) {
    const queryParams = new URLSearchParams({
      period: params.period || '30d',
      include_questions: params.includeQuestions || true,
      include_attempts: params.includeAttempts || true
    }).toString()
    
    return await this.get(`${this.baseUrl}/${id}/analytics?${queryParams}`)
  }

  /**
   * Get quiz attempts
   * @param {number} id - Quiz ID
   * @param {Object} params - Query parameters
   * @returns {Promise<Object>} Paginated attempts
   */
  async getAttempts(id, params = {}) {
    const queryParams = new URLSearchParams({
      page: params.page || 1,
      per_page: params.per_page || 20,
      status: params.status || '',
      user_id: params.user_id || ''
    }).toString()
    
    return await this.get(`${this.baseUrl}/${id}/attempts?${queryParams}`)
  }

  /**
   * Export quiz data
   * @param {number} id - Quiz ID
   * @param {string} format - Export format (json, csv, pdf)
   * @returns {Promise<Blob>} Export file
   */
  async export(id, format = 'json') {
    return await this.get(`${this.baseUrl}/${id}/export/${format}`, {
      responseType: 'blob'
    })
  }

  /**
   * Import quiz from file
   * @param {File} file - Quiz file
   * @param {Object} options - Import options
   * @returns {Promise<Object>} Import result
   */
  async import(file, options = {}) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('options', JSON.stringify(options))
    
    return await this.post(`${this.baseUrl}/import`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }

  /**
   * Bulk operations
   */
  async bulkPublish(ids) {
    return await this.post(`${this.baseUrl}/bulk/publish`, { ids })
  }

  async bulkUnpublish(ids) {
    return await this.post(`${this.baseUrl}/bulk/unpublish`, { ids })
  }

  async bulkArchive(ids) {
    return await this.post(`${this.baseUrl}/bulk/archive`, { ids })
  }

  async bulkDelete(ids) {
    return await this.post(`${this.baseUrl}/bulk/delete`, { ids })
  }

  async bulkDuplicate(ids) {
    return await this.post(`${this.baseUrl}/bulk/duplicate`, { ids })
  }

  /**
   * Question management
   */
  async addQuestion(quizId, questionData) {
    return await this.post(`${this.baseUrl}/${quizId}/questions`, questionData)
  }

  async updateQuestion(quizId, questionId, questionData) {
    return await this.put(`${this.baseUrl}/${quizId}/questions/${questionId}`, questionData)
  }

  async removeQuestion(quizId, questionId) {
    return await this.delete(`${this.baseUrl}/${quizId}/questions/${questionId}`)
  }

  async reorderQuestions(quizId, questionIds) {
    return await this.patch(`${this.baseUrl}/${quizId}/questions/reorder`, {
      question_ids: questionIds
    })
  }

  /**
   * Quiz attempt management
   */
  async startAttempt(quizId, userId = null) {
    return await this.post(`${this.baseUrl}/${quizId}/attempts`, {
      user_id: userId
    })
  }

  async submitAttempt(quizId, attemptId, answers) {
    return await this.post(`${this.baseUrl}/${quizId}/attempts/${attemptId}/submit`, {
      answers
    })
  }

  async getAttemptResult(quizId, attemptId) {
    return await this.get(`${this.baseUrl}/${quizId}/attempts/${attemptId}/result`)
  }

  /**
   * Search and filtering
   */
  async search(query, filters = {}) {
    const params = new URLSearchParams({
      q: query,
      ...filters
    }).toString()
    
    return await this.get(`${this.baseUrl}/search?${params}`)
  }

  async getByCategory(categoryId, params = {}) {
    const queryParams = new URLSearchParams(params).toString()
    return await this.get(`${this.baseUrl}/category/${categoryId}?${queryParams}`)
  }

  async getByCourse(courseId, params = {}) {
    const queryParams = new URLSearchParams(params).toString()
    return await this.get(`${this.baseUrl}/course/${courseId}?${queryParams}`)
  }

  async getByDifficulty(difficulty, params = {}) {
    const queryParams = new URLSearchParams(params).toString()
    return await this.get(`${this.baseUrl}/difficulty/${difficulty}?${queryParams}`)
  }

  /**
   * Statistics and reporting
   */
  async getStats(params = {}) {
    const queryParams = new URLSearchParams({
      period: params.period || '30d',
      group_by: params.groupBy || 'day'
    }).toString()
    
    return await this.get(`${this.baseUrl}/stats?${queryParams}`)
  }

  async getLeaderboard(quizId, params = {}) {
    const queryParams = new URLSearchParams({
      limit: params.limit || 10,
      period: params.period || 'all'
    }).toString()
    
    return await this.get(`${this.baseUrl}/${quizId}/leaderboard?${queryParams}`)
  }

  /**
   * Utility methods
   */
  
  /**
   * Format quiz data for API submission
   * @param {Object} data - Raw quiz data
   * @returns {Object} Formatted data
   */
  formatQuizData(data) {
    return {
      title: data.title?.trim(),
      description: data.description?.trim() || null,
      course_id: data.course_id,
      difficulty: data.difficulty,
      time_limit: data.time_limit ? parseInt(data.time_limit) : null,
      max_attempts: data.max_attempts ? parseInt(data.max_attempts) : null,
      passing_score: data.passing_score ? parseInt(data.passing_score) : 70,
      status: data.status || 'draft',
      questions: this.formatQuestions(data.questions || []),
      settings: {
        shuffle_questions: data.shuffle_questions || false,
        shuffle_answers: data.shuffle_answers || false,
        show_results: data.show_results || true,
        allow_review: data.allow_review || true,
        require_completion: data.require_completion || false
      }
    }
  }

  /**
   * Format questions data
   * @param {Array} questions - Raw questions array
   * @returns {Array} Formatted questions
   */
  formatQuestions(questions) {
    return questions.map((question, index) => ({
      order: index + 1,
      text: question.text?.trim(),
      type: question.type || 'multiple_choice',
      options: question.options?.map(opt => opt?.trim()).filter(Boolean) || [],
      correct_answer: question.correct_answer,
      explanation: question.explanation?.trim() || null,
      points: question.points ? parseInt(question.points) : 1,
      required: question.required !== false
    }))
  }

  /**
   * Validate quiz data before submission
   * @param {Object} data - Quiz data to validate
   * @returns {Object} Validation result
   */
  validateQuizData(data) {
    const errors = []
    
    // Required fields
    if (!data.title?.trim()) {
      errors.push('Title is required')
    }
    
    if (!data.course_id) {
      errors.push('Course is required')
    }
    
    if (!data.difficulty) {
      errors.push('Difficulty is required')
    }
    
    // Questions validation
    if (!data.questions || data.questions.length === 0) {
      errors.push('At least one question is required')
    } else {
      data.questions.forEach((question, index) => {
        if (!question.text?.trim()) {
          errors.push(`Question ${index + 1}: Text is required`)
        }
        
        if (question.type === 'multiple_choice') {
          if (!question.options || question.options.length < 2) {
            errors.push(`Question ${index + 1}: At least 2 options are required`)
          }
          
          if (question.correct_answer === undefined || question.correct_answer === null) {
            errors.push(`Question ${index + 1}: Correct answer must be selected`)
          }
        }
      })
    }
    
    return {
      isValid: errors.length === 0,
      errors
    }
  }

  /**
   * Calculate quiz statistics
   * @param {Object} quiz - Quiz data with attempts
   * @returns {Object} Calculated statistics
   */
  calculateStats(quiz) {
    const attempts = quiz.attempts || []
    const completedAttempts = attempts.filter(a => a.status === 'completed')
    
    if (completedAttempts.length === 0) {
      return {
        totalAttempts: attempts.length,
        completedAttempts: 0,
        averageScore: 0,
        passRate: 0,
        completionRate: 0,
        averageTime: 0
      }
    }
    
    const scores = completedAttempts.map(a => a.score)
    const times = completedAttempts.map(a => a.time_taken).filter(Boolean)
    const passedAttempts = completedAttempts.filter(a => a.score >= (quiz.passing_score || 70))
    
    return {
      totalAttempts: attempts.length,
      completedAttempts: completedAttempts.length,
      averageScore: Math.round(scores.reduce((sum, score) => sum + score, 0) / scores.length),
      passRate: Math.round((passedAttempts.length / completedAttempts.length) * 100),
      completionRate: Math.round((completedAttempts.length / attempts.length) * 100),
      averageTime: times.length > 0 ? Math.round(times.reduce((sum, time) => sum + time, 0) / times.length) : 0
    }
  }
}

/**
 * Quiz Service Factory - Creates configured service instances
 */
export class QuizServiceFactory {
  static create(config = {}) {
    const service = new QuizService()
    
    // Apply configuration
    if (config.baseUrl) {
      service.baseUrl = config.baseUrl
    }
    
    return service
  }
  
  static createWithAuth(token) {
    const service = new QuizService()
    
    // Configure authentication
    service.defaultHeaders = {
      'Authorization': `Bearer ${token}`
    }
    
    return service
  }
}

/**
 * Quiz Cache Manager - Handles quiz data caching
 */
export class QuizCacheManager {
  constructor(ttl = 300000) { // 5 minutes default TTL
    this.cache = new Map()
    this.ttl = ttl
  }
  
  set(key, data) {
    this.cache.set(key, {
      data,
      timestamp: Date.now()
    })
  }
  
  get(key) {
    const cached = this.cache.get(key)
    
    if (!cached) return null
    
    if (Date.now() - cached.timestamp > this.ttl) {
      this.cache.delete(key)
      return null
    }
    
    return cached.data
  }
  
  clear() {
    this.cache.clear()
  }
  
  invalidate(pattern) {
    for (const key of this.cache.keys()) {
      if (key.includes(pattern)) {
        this.cache.delete(key)
      }
    }
  }
}

// Export singleton instances
export const quizService = new QuizService()
export const quizCache = new QuizCacheManager()