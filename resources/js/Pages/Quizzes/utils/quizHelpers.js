/**
 * Calculate the score based on correct answers
 * @param {Array} questions - Array of question objects
 * @returns {Object} Score information including percentage, correct answers count, and total questions
 */
export const calculateScore = (questions) => {
  const totalQuestions = questions.length
  const correctAnswers = questions.filter(q =>
    q.answers.some(a => a.is_correct && a.is_selected)
  ).length

  return {
    score: Math.round((correctAnswers / totalQuestions) * 100),
    correctAnswers,
    totalQuestions
  }
}

/**
 * Format time in seconds to MM:SS format
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string
 */
export const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs < 10 ? '0' : ''}${secs}`
}

/**
 * Validate quiz data
 * @param {Object} quiz - Quiz object to validate
 * @returns {Array} Array of error messages
 */
export const validateQuiz = (quiz) => {
  const errors = []

  if (!quiz.title) errors.push('Title is required')
  if (!quiz.time_limit || quiz.time_limit < 1) errors.push('Valid time limit is required')
  if (!quiz.passing_score || quiz.passing_score < 1 || quiz.passing_score > 100) {
    errors.push('Passing score must be between 1 and 100')
  }

  return errors
}

/**
 * Check if a quiz attempt has passed
 * @param {number} score - Score percentage
 * @param {number} passingScore - Required passing score
 * @returns {boolean} Whether the attempt passed
 */
export const hasPassed = (score, passingScore) => {
  return score >= passingScore
}

/**
 * Calculate remaining time for a quiz
 * @param {number} timeLimit - Time limit in minutes
 * @param {number} elapsedTime - Elapsed time in seconds
 * @returns {number} Remaining time in seconds
 */
export const calculateRemainingTime = (timeLimit, elapsedTime) => {
  const totalSeconds = timeLimit * 60
  return Math.max(0, totalSeconds - elapsedTime)
}

/**
 * Format quiz duration for display
 * @param {number} minutes - Duration in minutes
 * @returns {string} Formatted duration string
 */
export const formatDuration = (minutes) => {
  if (minutes === 1) return '1 minute'
  return `${minutes} minutes`
}
