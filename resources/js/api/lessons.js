import api from './axios'

export default {
  // ── Teacher ───────────────────────────────────────────────────────────────
  list:           (params)              => api.get('/teacher/lessons', { params }),
  create:         (data)                => api.post('/teacher/lessons', data),
  get:            (id)                  => api.get(`/teacher/lessons/${id}`),
  update:         (id, data)            => api.put(`/teacher/lessons/${id}`, data),
  remove:         (id)                  => api.delete(`/teacher/lessons/${id}`),
  publish:        (id)                  => api.post(`/teacher/lessons/${id}/publish`),
  share:          (id, data)            => api.post(`/teacher/lessons/${id}/share`, data),
  uploadThumb:    (id, formData)        => api.post(`/teacher/lessons/${id}/thumbnail`, formData),
  uploadMaterial: (id, formData)        => api.post(`/teacher/lessons/${id}/materials`, formData),
  deleteMaterial: (id, materialId)      => api.delete(`/teacher/lessons/${id}/materials/${materialId}`),

  // ── Student ───────────────────────────────────────────────────────────────
  studentList:      (params)               => api.get('/student/lessons', { params }),
  studentGet:       (id)                   => api.get(`/student/lessons/${id}`),
  updateProgress:   (id, data)             => api.post(`/student/lessons/${id}/progress`, data),
  downloadMaterial: (lessonId, materialId) => api.get(`/student/lessons/${lessonId}/materials/${materialId}/download`, { responseType: 'blob' }),
}
