import api from './axios'

export default {
  grades: () => api.get('/public/grades'),
  subjects: () => api.get('/public/subjects'),
  lessons: (params) => api.get('/public/lessons', { params }),
  lesson: (id) => api.get(`/public/lessons/${id}`),
  exams: (params) => api.get('/public/exams', { params }),
  exam: (id) => api.get(`/public/exams/${id}`),
  examTake: (id) => api.get(`/public/exams/${id}/take`),
  examSubmit: (id, answers) => api.post(`/public/exams/${id}/submit`, { answers }),
  assignments: (params) => api.get('/public/assignments', { params }),
  assignment: (id) => api.get(`/public/assignments/${id}`),
  assignmentTake: (id) => api.get(`/public/assignments/${id}/take`),
  assignmentSubmit: (id, answers) => api.post(`/public/assignments/${id}/submit`, { answers }),
  classrooms: (params) => api.get('/public/classrooms', { params }),
  aiReview: (payload) => api.post('/public/ai-review', payload),
}
