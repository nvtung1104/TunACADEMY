import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const role = computed(() => user.value?.roles?.[0] ?? null)
  const isAdmin = computed(() => role.value === 'admin')
  const isTeacher = computed(() => role.value === 'teacher')
  const isStudent = computed(() => role.value === 'student')

  async function login(email, password) {
    const { data } = await api.post('/auth/login', { email, password })
    token.value = data.data.token
    user.value = data.data.user
    localStorage.setItem('auth_token', token.value)
    return data.data
  }

  async function fetchUser() {
    const { data } = await api.get('/auth/me')
    user.value = data.data
    return data.data
  }

  async function logout() {
    try { await api.post('/auth/logout') } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  function homeRoute() {
    if (isAdmin.value) return '/admin'
    if (isTeacher.value) return '/teacher'
    if (isStudent.value) return '/student'
    return '/login'
  }

  return { user, token, isLoggedIn, role, isAdmin, isTeacher, isStudent, login, fetchUser, logout, homeRoute }
})
