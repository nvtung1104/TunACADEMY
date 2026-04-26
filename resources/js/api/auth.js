import api from './axios'

export default {
  me: () => api.get('/auth/me'),
  login: (data) => api.post('/auth/login', data),
  logout: () => api.post('/auth/logout'),
  updateProfile: (data) => api.put('/auth/profile', data),
  updateAvatar: (file) => {
    const form = new FormData()
    form.append('avatar', file)
    return api.post('/auth/avatar', form, { headers: { 'Content-Type': 'multipart/form-data' } })
  },
  changePassword: (data) => api.post('/auth/change-password', data),
}
