import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // ─── Public layout ───────────────────────────────────────────────────────
    {
      path: '/',
      component: () => import('@/layouts/PublicLayout.vue'),
      children: [
        { path: '', component: () => import('@/views/public/HomeView.vue') },
        { path: 'lessons', component: () => import('@/views/public/LessonsView.vue') },
        { path: 'lessons/:id', component: () => import('@/views/public/LessonDetailView.vue') },
        { path: 'practice', component: () => import('@/views/public/PracticeView.vue') },
        { path: 'practice/:id', component: () => import('@/views/public/AssignmentDetailView.vue') },
        { path: 'exams', component: () => import('@/views/public/ExamsView.vue') },
        { path: 'exams/:id', component: () => import('@/views/public/ExamDetailView.vue') },
        { path: 'classrooms', component: () => import('@/views/public/ClassroomsView.vue') },
      ],
    },

    // ─── Auth ────────────────────────────────────────────────────────────────
    { path: '/login', component: () => import('@/views/auth/LoginView.vue'), meta: { guest: true } },
    {
      path: '/profile',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true },
      children: [
        { path: '', component: () => import('@/views/profile/ProfileView.vue') },
      ],
    },
    {
      path: '/saved',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true },
      children: [
        { path: '', component: () => import('@/views/saved/SavedView.vue') },
      ],
    },

    // ─── Admin ───────────────────────────────────────────────────────────────
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
        { path: 'lessons', component: () => import('@/views/admin/LessonView.vue') },
        { path: 'exams', component: () => import('@/views/admin/ExamView.vue') },
        { path: 'assignments', component: () => import('@/views/admin/AssignmentView.vue') },
        { path: 'live', component: () => import('@/views/admin/LiveView.vue') },
        { path: 'reports', component: () => import('@/views/admin/ReportView.vue') },
      ],
    },

    // ─── Teacher ─────────────────────────────────────────────────────────────
    {
      path: '/teacher',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true, role: 'teacher' },
      children: [
        { path: '', redirect: '/teacher/dashboard' },
        { path: 'dashboard', component: () => import('@/views/teacher/DashboardView.vue') },
        { path: 'homeroom', component: () => import('@/views/teacher/HomeroomView.vue') },
        { path: 'lessons', component: () => import('@/views/teacher/LessonManageView.vue') },
        { path: 'exams', component: () => import('@/views/teacher/ExamManageView.vue') },
        { path: 'assignments', component: () => import('@/views/teacher/AssignmentManageView.vue') },
        { path: 'grades', component: () => import('@/views/teacher/GradeView.vue') },
        { path: 'question-bank', component: () => import('@/views/teacher/QuestionBankView.vue') },
        { path: 'live', component: () => import('@/views/teacher/LiveManageView.vue') },
      ],
    },

    // ─── Student ─────────────────────────────────────────────────────────────
    {
      path: '/student',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { auth: true, role: 'student' },
      children: [
        { path: '', redirect: '/student/dashboard' },
        { path: 'dashboard', component: () => import('@/views/student/DashboardView.vue') },
        { path: 'classes', component: () => import('@/views/student/ClassView.vue') },
        { path: 'lessons', component: () => import('@/views/student/LessonsView.vue') },
        { path: 'lessons/subjects', component: () => import('@/views/student/LessonsBySubjectView.vue') },
        { path: 'lessons/:id', component: () => import('@/views/student/LessonDetailView.vue') },
        { path: 'lessons/:id/learn', component: () => import('@/views/student/LessonLearnView.vue') },
        { path: 'exams', component: () => import('@/views/student/ExamView.vue') },
        { path: 'exams/:id/result', component: () => import('@/views/student/ExamResultView.vue') },
        { path: 'assignments', component: () => import('@/views/student/AssignmentView.vue') },
        { path: 'live', component: () => import('@/views/student/LiveView.vue') },
        { path: 'progress', component: () => import('@/views/student/ProgressView.vue') },
      ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Guest-only page: redirect logged-in users to dashboard
  if (to.meta.guest && auth.token) {
    if (!auth.user) await auth.fetchUser().catch(() => {})
    if (auth.user) return auth.homeRoute()
  }

  // Auth-required: redirect to login, then back after login
  if (to.meta.auth && !auth.token) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  // Role guard (only when user already loaded — avoids blocking on reload)
  if (to.meta.role && auth.user && auth.role !== to.meta.role) {
    return auth.homeRoute()
  }

  return true
})

export default router
