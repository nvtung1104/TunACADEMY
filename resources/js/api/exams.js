import api from './axios'

export default {
  // ── Teacher ──────────────────────────────────────────────────────────────
  list:        (params) => api.get('/teacher/exams', { params }),
  create:      (data)   => api.post('/teacher/exams', data),
  get:         (id)     => api.get(`/teacher/exams/${id}`),
  update:      (id, data) => api.put(`/teacher/exams/${id}`, data),
  remove:      (id)     => api.delete(`/teacher/exams/${id}`),
  publish:     (id)     => api.post(`/teacher/exams/${id}/publish`),
  share:       (id, data) => api.post(`/teacher/exams/${id}/share`, data),
  uploadThumb: (id, formData) => api.post(`/teacher/exams/${id}/thumbnail`, formData),

  // Questions
  storeQuestion:     (examId, data)          => api.post(`/teacher/exams/${examId}/questions`, data),
  updateQuestion:    (examId, qId, data)     => api.put(`/teacher/exams/${examId}/questions/${qId}`, data),
  deleteQuestion:    (examId, qId)           => api.delete(`/teacher/exams/${examId}/questions/${qId}`),
  reorderQuestions:  (examId, questionIds)   => api.post(`/teacher/exams/${examId}/questions/reorder`, { question_ids: questionIds }),
  duplicateQuestion: (examId, qId)           => api.post(`/teacher/exams/${examId}/questions/${qId}/duplicate`),
  saveToBank:        (examId, qId, isPublic) => api.post(`/teacher/exams/${examId}/questions/${qId}/save-to-bank`, { is_public: isPublic }),
  importQuestions:   (examId, questionIds)   => api.post(`/teacher/exams/${examId}/import-questions`, { question_ids: questionIds }),

  // Results
  results:       (examId, params)    => api.get(`/teacher/exams/${examId}/results`, { params }),
  studentResult: (examId, studentId) => api.get(`/teacher/exams/${examId}/results/${studentId}`),
  attempts:      (examId, params)    => api.get(`/teacher/exams/${examId}/attempts`, { params }),
  attemptLogs:   (examId, attemptId) => api.get(`/teacher/exams/${examId}/attempts/${attemptId}/logs`),

  // ── Student ───────────────────────────────────────────────────────────────
  studentList:  (params)             => api.get('/student/exams', { params }),
  studentShow:  (id)                 => api.get(`/student/exams/${id}`),
  start:        (id)                 => api.post(`/student/exams/${id}/start`),
  submit:       (id, answers)        => api.post(`/student/exams/${id}/submit`, { answers }),
  result:       (id)                 => api.get(`/student/exams/${id}/result`),
  logViolation: (examId, attemptId, data) => api.post(`/student/exams/${examId}/attempts/${attemptId}/proctoring`, data),
}
