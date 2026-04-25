import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },

    { path: '/login', component: () => import('@/views/auth/LoginView.vue'), meta: { guest: true } },
    { path: '/change-password', component: () => import('@/views/auth/ChangePasswordView.vue'), meta: { auth: true } },

    {
      path: '/admin',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true, role: 'admin' },
      children: [
        { path: '', redirect: '/admin/dashboard' },
        { path: 'dashboard', component: () => import('@/views/admin/DashboardView.vue') },
        { path: 'users', component: () => import('@/views/admin/UserManageView.vue') },
        { path: 'classrooms', component: () => import('@/views/admin/ClassroomManageView.vue') },
        { path: 'school-years', component: () => import('@/views/admin/SchoolYearView.vue') },
        { path: 'subjects', component: () => import('@/views/admin/SubjectView.vue') },
        { path: 'reports', component: () => import('@/views/admin/ReportView.vue') },
      ],
    },

    {
      path: '/teacher',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true, role: 'teacher' },
      children: [
        { path: '', redirect: '/teacher/dashboard' },
        { path: 'dashboard', component: () => import('@/views/teacher/DashboardView.vue') },
        { path: 'lessons', component: () => import('@/views/teacher/LessonManageView.vue') },
        { path: 'exams', component: () => import('@/views/teacher/ExamManageView.vue') },
        { path: 'assignments', component: () => import('@/views/teacher/AssignmentManageView.vue') },
        { path: 'live', component: () => import('@/views/teacher/LiveManageView.vue') },
        { path: 'grades', component: () => import('@/views/teacher/GradeView.vue') },
      ],
    },

    {
      path: '/student',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true, role: 'student' },
      children: [
        { path: '', redirect: '/student/dashboard' },
        { path: 'dashboard', component: () => import('@/views/student/DashboardView.vue') },
        { path: 'classes', component: () => import('@/views/student/ClassView.vue') },
        { path: 'lessons/:id', component: () => import('@/views/student/LessonDetailView.vue') },
        { path: 'exams', component: () => import('@/views/student/ExamView.vue') },
        { path: 'exams/:id/result', component: () => import('@/views/student/ExamResultView.vue') },
        { path: 'assignments', component: () => import('@/views/student/AssignmentView.vue') },
        { path: 'progress', component: () => import('@/views/student/ProgressView.vue') },
      ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/login' },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.guest && auth.token) {
    if (!auth.user) await auth.fetchUser().catch(() => {})
    if (auth.user) return auth.homeRoute()
  }

  if (to.meta.auth && !auth.token) return '/login'

  if (to.meta.role && auth.user && auth.role !== to.meta.role) return auth.homeRoute()

  return true
})

export default router
