import api from '@/api/axios'

const API_BASE = 'teacher'

/**
 * Question Bank Service
 */
export const questionBankService = {
  /**
   * Get question bank list with filters
   */
  getList(filters = {}) {
    return api.get(`${API_BASE}/question-bank`, { params: filters })
  },

  /**
   * Get public question bank
   */
  getPublicBank(filters = {}) {
    return api.get(`${API_BASE}/question-bank-public`, { params: filters })
  },

  /**
   * Get single question
   */
  getOne(id) {
    return api.get(`${API_BASE}/question-bank/${id}`)
  },

  /**
   * Create new question
   */
  create(data) {
    return api.post(`${API_BASE}/question-bank`, data)
  },

  /**
   * Update question
   */
  update(id, data) {
    return api.put(`${API_BASE}/question-bank/${id}`, data)
  },

  /**
   * Delete question
   */
  delete(id) {
    return api.delete(`${API_BASE}/question-bank/${id}`)
  },

  /**
   * Get question usage statistics
   */
  getUsage(id) {
    return api.get(`${API_BASE}/question-bank/${id}/usage`)
  },
}

/**
 * Exam Question Service
 */
export const examQuestionService = {
  /**
   * Import questions from bank to exam
   */
  importFromBank(examId, questionIds) {
    return api.post(`${API_BASE}/exams/${examId}/import-questions`, {
      question_ids: questionIds,
    })
  },

  /**
   * Create question directly in exam
   */
  create(examId, data) {
    return api.post(`${API_BASE}/exams/${examId}/questions`, data)
  },

  /**
   * Update exam question
   */
  update(examId, questionId, data) {
    return api.put(`${API_BASE}/exams/${examId}/questions/${questionId}`, data)
  },

  /**
   * Delete exam question
   */
  delete(examId, questionId) {
    return api.delete(`${API_BASE}/exams/${examId}/questions/${questionId}`)
  },

  /**
   * Reorder questions
   */
  reorder(examId, questionIds) {
    return api.post(`${API_BASE}/exams/${examId}/questions/reorder`, {
      question_ids: questionIds,
    })
  },

  /**
   * Duplicate question
   */
  duplicate(examId, questionId) {
    return api.post(`${API_BASE}/exams/${examId}/questions/${questionId}/duplicate`)
  },

  /**
   * Save question to bank
   */
  saveToBank(examId, questionId, isPublic = false) {
    return api.post(`${API_BASE}/exams/${examId}/questions/${questionId}/save-to-bank`, {
      is_public: isPublic,
    })
  },
}

/**
 * Assignment Question Service
 */
export const assignmentQuestionService = {
  /**
   * Import questions from bank to assignment
   */
  importFromBank(assignmentId, questionIds) {
    return api.post(`${API_BASE}/assignments/${assignmentId}/import-questions`, {
      question_ids: questionIds,
    })
  },

  /**
   * Create question directly in assignment
   */
  create(assignmentId, data) {
    return api.post(`${API_BASE}/assignments/${assignmentId}/questions`, data)
  },

  /**
   * Update assignment question
   */
  update(assignmentId, questionId, data) {
    return api.put(`${API_BASE}/assignments/${assignmentId}/questions/${questionId}`, data)
  },

  /**
   * Delete assignment question
   */
  delete(assignmentId, questionId) {
    return api.delete(`${API_BASE}/assignments/${assignmentId}/questions/${questionId}`)
  },

  /**
   * Reorder questions
   */
  reorder(assignmentId, questionIds) {
    return api.post(`${API_BASE}/assignments/${assignmentId}/questions/reorder`, {
      question_ids: questionIds,
    })
  },

  /**
   * Duplicate question
   */
  duplicate(assignmentId, questionId) {
    return api.post(`${API_BASE}/assignments/${assignmentId}/questions/${questionId}/duplicate`)
  },

  /**
   * Save question to bank
   */
  saveToBank(assignmentId, questionId, isPublic = false) {
    return api.post(`${API_BASE}/assignments/${assignmentId}/questions/${questionId}/save-to-bank`, {
      is_public: isPublic,
    })
  },
}

/**
 * Helper functions
 */
export const questionHelpers = {
  /**
   * Build filter query string
   */
  buildFilters(filters) {
    const params = {}
    
    if (filters.subject_id) params.subject_id = filters.subject_id
    if (filters.grade_id) params.grade_id = filters.grade_id
    if (filters.type) params.type = filters.type
    if (filters.difficulty) params.difficulty = filters.difficulty
    if (filters.chapter_tag) params.chapter_tag = filters.chapter_tag
    if (filters.search) params.search = filters.search
    
    return params
  },

  /**
   * Format question for API
   */
  formatForApi(question) {
    return {
      grade_id: question.grade_id || null,
      lesson_id: question.lesson_id || null,
      type: question.type,
      difficulty: question.difficulty || 'medium',
      chapter_tag: question.chapter_tag || null,
      content: question.content,
      media_type: question.media_type || null,
      media_path: question.media_path || null,
      audio_path: question.audio_path || null,
      options: question.options || null,
      correct_answer: question.correct_answer || null,
      explanation: question.explanation || null,
      sub_questions: question.sub_questions || null,
      metadata: question.metadata || null,
      points: question.points || 1,
      order_index: question.order_index || 0,
    }
  },

  /**
   * Parse question from API
   */
  parseFromApi(question) {
    return {
      ...question,
      options: question.options || [],
      sub_questions: question.sub_questions || [],
      metadata: question.metadata || {},
    }
  },
}

/**
 * Batch operations
 */
export const batchOperations = {
  /**
   * Import multiple questions to multiple exams
   */
  async importToMultipleExams(examIds, questionIds) {
    const promises = examIds.map(examId =>
      examQuestionService.importFromBank(examId, questionIds)
    )
    return Promise.all(promises)
  },

  /**
   * Import multiple questions to multiple assignments
   */
  async importToMultipleAssignments(assignmentIds, questionIds) {
    const promises = assignmentIds.map(assignmentId =>
      assignmentQuestionService.importFromBank(assignmentId, questionIds)
    )
    return Promise.all(promises)
  },

  /**
   * Delete multiple questions from bank
   */
  async deleteMultipleFromBank(questionIds) {
    const promises = questionIds.map(id =>
      questionBankService.delete(id)
    )
    return Promise.all(promises)
  },

  /**
   * Save multiple exam questions to bank
   */
  async saveMultipleToBank(examId, questionIds, isPublic = false) {
    const promises = questionIds.map(questionId =>
      examQuestionService.saveToBank(examId, questionId, isPublic)
    )
    return Promise.all(promises)
  },
}

export default {
  questionBank: questionBankService,
  examQuestion: examQuestionService,
  assignmentQuestion: assignmentQuestionService,
  helpers: questionHelpers,
  batch: batchOperations,
}
