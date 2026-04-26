import api from './axios'

export default {
  list: (params) => api.get('/bookmarks', { params }),
  toggle: (type, id) => api.post('/bookmarks/toggle', { type, id }),
  check: (type, id) => api.get('/bookmarks/check', { params: { type, id } }),
}
