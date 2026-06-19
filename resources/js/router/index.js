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
        { path: 'practice/:id/preview', component: () => import('@/views/public/AssignmentPreviewView.vue') },
        { path: 'exams', component: () => import('@/views/public/ExamsView.vue') },
        { path: 'exams/:id', component: () => import('@/views/public/ExamDetailView.vue') },
        { path: 'exams/:id/preview', component: () => import('@/views/public/ExamPreviewView.vue') },
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
        { path: 'exams/:id', component: () => import('@/views/admin/ExamDetailView.vue') },
        { path: 'assignments', component: () => import('@/views/admin/AssignmentView.vue') },
        { path: 'assignments/:id', component: () => import('@/views/admin/AssignmentDetailView.vue') },
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
        { path: 'timetable', component: () => import('@/views/teacher/TimetableView.vue') },
        { path: 'homeroom', component: () => import('@/views/teacher/HomeroomView.vue') },
        { path: 'lessons', component: () => import('@/views/teacher/LessonManageView.vue') },
        { path: 'exams', component: () => import('@/views/teacher/ExamManageView.vue') },
        { path: 'exams/:id', component: () => import('@/views/teacher/ExamDetailView.vue') },
        { path: 'assignments', component: () => import('@/views/teacher/AssignmentManageView.vue') },
        { path: 'assignments/:id', component: () => import('@/views/teacher/AssignmentDetailView.vue') },
        { path: 'grades', component: () => import('@/views/teacher/GradeView.vue') },
        { path: 'question-bank', component: () => import('@/views/teacher/QuestionBankView.vue') },
        { path: 'question-bank/create', component: () => import('@/views/teacher/QuestionCreateView.vue') },
        { path: 'question-bank/:id/edit', component: () => import('@/views/teacher/QuestionCreateView.vue') },
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
        { path: 'timetable', component: () => import('@/views/student/TimetableView.vue') },
        { path: 'classes', component: () => import('@/views/student/ClassView.vue') },
        { path: 'lessons', component: () => import('@/views/student/LessonsView.vue') },
        { path: 'lessons/subjects', component: () => import('@/views/student/LessonsBySubjectView.vue') },
        { path: 'lessons/:id', component: () => import('@/views/student/LessonDetailView.vue') },
        { path: 'lessons/:id/learn', component: () => import('@/views/student/LessonLearnView.vue') },
        { path: 'exams', component: () => import('@/views/student/ExamView.vue') },
        { path: 'exams/:id/take', component: () => import('@/views/student/ExamTakeView.vue') },
        { path: 'exams/:id/result', component: () => import('@/views/student/ExamResultView.vue') },
        { path: 'assignments', component: () => import('@/views/student/AssignmentView.vue') },
        { path: 'assignments/:id/do', component: () => import('@/views/student/AssignmentDoView.vue') },
        { path: 'live', component: () => import('@/views/student/LiveView.vue') },
        { path: 'progress', component: () => import('@/views/student/ProgressView.vue') },
      ],
    },

    // ─── Standalone fullscreen pages (no layout, no navbar) ──────────────────
    { path: '/exams/:id/take', component: () => import('@/views/public/PublicExamTakeView.vue') },
    { path: '/practice/:id/do', component: () => import('@/views/public/PublicAssignmentDoView.vue') },
    { path: '/live/:id/lobby', component: () => import('@/views/live/LiveLobbyView.vue'), meta: { auth: true } },
    { path: '/live/:id/room', component: () => import('@/views/live/LiveRoomView.vue'), meta: { auth: true } },

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

// Recover from ChunkLoadErrors / failed dynamic imports after new builds
router.onError((error, to) => {
  const msg = error?.message || ''
  const isChunkError = 
    msg.includes('Failed to fetch dynamically imported module') ||
    msg.includes('Importing a module script failed') ||
    msg.includes('error loading dynamically imported module') ||
    msg.includes('Failed to fetch') ||
    /loading chunk/i.test(msg)
  
  if (isChunkError) {
    const lastReload = sessionStorage.getItem('last_chunk_reload')
    const now = Date.now()
    
    // Prevent infinite reload loop if user is offline or file is completely missing
    if (!lastReload || now - parseInt(lastReload) > 10000) {
      sessionStorage.setItem('last_chunk_reload', now.toString())
      window.location.href = to.fullPath
    }
  }
})

export default router
