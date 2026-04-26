import api from './axios'

export default {
  grades: () => api.get('/public/grades'),
  subjects: () => api.get('/public/subjects'),
  lessons: (params) => api.get('/public/lessons', { params }),
  lesson: (id) => api.get(`/public/lessons/${id}`),
  exams: (params) => api.get('/public/exams', { params }),
  exam: (id) => api.get(`/public/exams/${id}`),
  assignments: (params) => api.get('/public/assignments', { params }),
  assignment: (id) => api.get(`/public/assignments/${id}`),
  classrooms: (params) => api.get('/public/classrooms', { params }),
}
