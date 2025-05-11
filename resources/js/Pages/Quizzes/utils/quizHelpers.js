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

export const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs < 10 ? '0' : ''}${secs}`
}

export const validateQuiz = (quiz) => {
  const errors = []

  if (!quiz.title) errors.push('العنوان مطلوب')
  if (!quiz.time_limit || quiz.time_limit < 1) errors.push('يجب تحديد وقت صحيح')
  if (!quiz.passing_score || quiz.passing_score < 1 || quiz.passing_score > 100) {
    errors.push('يجب أن تكون درجة النجاح بين 1 و 100')
  }

  return errors
}
