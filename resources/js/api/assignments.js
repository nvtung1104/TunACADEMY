import api from './axios'

export default {
  // ── Teacher ───────────────────────────────────────────────────────────────
  list:         (params)                     => api.get('/teacher/assignments', { params }),
  create:       (data)                       => api.post('/teacher/assignments', data),
  get:          (id)                         => api.get(`/teacher/assignments/${id}`),
  update:       (id, data)                   => api.put(`/teacher/assignments/${id}`, data),
  remove:       (id)                         => api.delete(`/teacher/assignments/${id}`),
  share:        (id, data)                   => api.post(`/teacher/assignments/${id}/share`, data),
  uploadThumb:  (id, formData)               => api.post(`/teacher/assignments/${id}/thumbnail`, formData),
  submissions:  (id, params)                 => api.get(`/teacher/assignments/${id}/submissions`, { params }),
  grade:        (id, submissionId, data)     => api.post(`/teacher/assignments/${id}/submissions/${submissionId}/grade`, data),

  // Questions
  storeQuestion:     (assignId, data)          => api.post(`/teacher/assignments/${assignId}/questions`, data),
  updateQuestion:    (assignId, qId, data)     => api.put(`/teacher/assignments/${assignId}/questions/${qId}`, data),
  deleteQuestion:    (assignId, qId)           => api.delete(`/teacher/assignments/${assignId}/questions/${qId}`),
  reorderQuestions:  (assignId, questionIds)   => api.post(`/teacher/assignments/${assignId}/questions/reorder`, { question_ids: questionIds }),
  duplicateQuestion: (assignId, qId)           => api.post(`/teacher/assignments/${assignId}/questions/${qId}/duplicate`),
  saveToBank:        (assignId, qId, isPublic) => api.post(`/teacher/assignments/${assignId}/questions/${qId}/save-to-bank`, { is_public: isPublic }),
  importQuestions:   (assignId, questionIds)   => api.post(`/teacher/assignments/${assignId}/import-questions`, { question_ids: questionIds }),

  // ── Student ───────────────────────────────────────────────────────────────
  studentList: (params)     => api.get('/student/assignments', { params }),
  studentGet:  (id)         => api.get(`/student/assignments/${id}`),
  submit:      (id, formData) => api.post(`/student/assignments/${id}/submit`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
}
