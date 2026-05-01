<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <!-- Mobile overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-20 bg-black/40 md:hidden" @click="sidebarOpen = false" />

    <!-- Sidebar -->
    <aside
      class="fixed md:relative z-30 flex flex-col w-64 h-full shrink-0 bg-[#0d0c22] text-white transition-transform duration-300"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10">
        <img src="/images/logo.png" alt="TunAcademy" class="w-9 h-9 rounded-xl object-contain shrink-0" />
        <div>
          <span class="font-bold text-base tracking-wide">TunAcademy</span>
          <span class="block text-[10px] text-[#d63015] font-medium uppercase tracking-widest">{{ roleBadge }}</span>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <RouterLink
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isActive(item.path)
            ? 'bg-[#d63015]/90 text-white shadow-sm'
            : 'text-white/70 hover:bg-white/8 hover:text-white'"
          @click="sidebarOpen = false"
        >
          <span class="w-5 h-5 shrink-0" v-html="item.icon"></span>
          <span>{{ item.label }}</span>
          <span v-if="item.badge" class="ml-auto text-[10px] font-semibold bg-[#d63015]/30 text-red-200 px-1.5 py-0.5 rounded-full">
            {{ item.badge }}
          </span>
        </RouterLink>
      </nav>

      <!-- Bottom links -->
      <div class="px-3 pb-2 border-t border-white/10 pt-2 space-y-0.5">
        <RouterLink
          to="/saved"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isActive('/saved')
            ? 'bg-[#d63015]/90 text-white shadow-sm'
            : 'text-[#d63015]/70 hover:bg-white/8 hover:text-white'"
          @click="sidebarOpen = false"
        >
          <span class="w-5 h-5 shrink-0" v-html="icons.bookmark" />
          <span>Đã lưu</span>
        </RouterLink>
        <RouterLink
          to="/profile"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
          :class="isActive('/profile')
            ? 'bg-[#d63015]/90 text-white shadow-sm'
            : 'text-[#d63015]/70 hover:bg-white/8 hover:text-white'"
          @click="sidebarOpen = false"
        >
          <span class="w-5 h-5 shrink-0" v-html="icons.userCircle" />
          <span>Tài khoản</span>
        </RouterLink>
        <RouterLink
          to="/"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#d63015]/70 hover:bg-white/8 hover:text-white transition-all duration-150"
          @click="sidebarOpen = false"
        >
          <span class="w-5 h-5 shrink-0" v-html="icons.home" />
          <span>Trang chủ</span>
        </RouterLink>
      </div>

      <!-- User profile -->
      <div class="px-3 py-3 border-t border-white/10">
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/8 transition-colors group">
          <AvatarFrame
            :src="auth.user?.avatar"
            :name="auth.user?.name"
            :gender="auth.user?.gender"
            :frame="auth.user?.frame"
            :size="32"
            class="shrink-0"
          />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate leading-tight">{{ auth.user?.name }}</p>
            <p class="text-[11px] text-[#d63015] truncate">{{ auth.user?.email }}</p>
          </div>
          <button
            @click="handleLogout"
            class="p-1.5 rounded-lg text-[#d63015]/70 hover:text-white hover:bg-white/15 transition-colors opacity-0 group-hover:opacity-100"
            title="Đăng xuất"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
      <!-- Header -->
      <header class="h-14 bg-white border-b border-gray-100 flex items-center justify-between px-5 shrink-0 shadow-sm">
        <div class="flex items-center gap-3">
          <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <div>
            <h1 class="text-base font-semibold text-gray-800 leading-tight">{{ pageTitle }}</h1>
            <p v-if="pageSubtitle" class="text-xs text-gray-400 leading-tight">{{ pageSubtitle }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Notification bell -->
          <NotificationBell />

          <!-- Profile -->
          <RouterLink
            to="/profile"
            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs text-gray-500 hover:text-[#d63015] hover:bg-red-50 transition-colors font-medium"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tài khoản
          </RouterLink>

          <!-- Logout -->
          <button @click="handleLogout" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="hidden sm:inline">Đăng xuất</span>
          </button>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-5">
        <Transition name="fade" mode="out-in">
          <RouterView :key="route.path" />
        </Transition>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@stores/auth'
import AvatarFrame from '@components/common/AvatarFrame.vue'
import NotificationBell from '@components/common/NotificationBell.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

// SVG icon strings
const icons = {
  dashboard: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`,
  users: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`,
  classroom: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`,
  calendar: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`,
  book: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>`,
  chart: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`,
  clipboard: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`,
  pencil: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>`,
  star: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>`,
  video: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`,
  trending: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>`,
  subject: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>`,
  home: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`,
  userCircle: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
  bookmark: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>`,
  database: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3-3.582 3-8 3-8-1.343-8-3zm0 0v5c0 1.657 3.582 3 8 3s8-1.343 8-3V7m-16 5v5c0 1.657 3.582 3 8 3s8-1.343 8-3v-5"/></svg>`,
}

const adminNav = [
  { path: '/admin/dashboard', label: 'Tổng quan', icon: icons.dashboard },
  { path: '/admin/users', label: 'Người dùng', icon: icons.users },
  { path: '/admin/classrooms', label: 'Lớp học', icon: icons.classroom },
  { path: '/admin/school-years', label: 'Năm học', icon: icons.calendar },
  { path: '/admin/subjects', label: 'Môn học', icon: icons.subject },
  { path: '/admin/lessons', label: 'Bài học', icon: icons.book },
  { path: '/admin/exams', label: 'Đề thi', icon: icons.clipboard },
  { path: '/admin/assignments', label: 'Bài tập', icon: icons.pencil },
  { path: '/admin/live', label: 'Phòng trực tuyến', icon: icons.video },
  { path: '/admin/reports', label: 'Báo cáo', icon: icons.chart },
]

const teacherNav = [
  { path: '/teacher/dashboard', label: 'Tổng quan', icon: icons.dashboard },
  { path: '/teacher/homeroom', label: 'Lớp chủ nhiệm', icon: icons.classroom },
  { path: '/teacher/lessons', label: 'Bài học', icon: icons.book },
  { path: '/teacher/exams', label: 'Bài kiểm tra', icon: icons.clipboard },
  { path: '/teacher/assignments', label: 'Bài tập', icon: icons.pencil },
  { path: '/teacher/live', label: 'Phòng trực tuyến', icon: icons.video },
  { path: '/teacher/grades', label: 'Điểm số', icon: icons.star },
  { path: '/teacher/question-bank', label: 'Ngân hàng câu hỏi', icon: icons.database },
]

const studentNav = [
  { path: '/student/dashboard', label: 'Tổng quan', icon: icons.dashboard },
  { path: '/student/classes', label: 'Lớp học', icon: icons.classroom },
  { path: '/student/lessons', label: 'Bài học', icon: icons.book },
  { path: '/student/exams', label: 'Bài kiểm tra', icon: icons.clipboard },
  { path: '/student/assignments', label: 'Bài tập', icon: icons.pencil },
  { path: '/student/live', label: 'Phòng trực tuyến', icon: icons.video },
  { path: '/student/progress', label: 'Kết quả học tập', icon: icons.trending },
]

const navItems = computed(() => {
  if (auth.isAdmin) return adminNav
  if (auth.isTeacher) return teacherNav
  return studentNav
})

const roleBadge = computed(() => {
  if (auth.isAdmin) return 'Quản trị viên'
  if (auth.isTeacher) return 'Giáo viên'
  return 'Học sinh'
})

const pageMeta = {
  '/admin/dashboard':    { title: 'Tổng quan',           subtitle: 'Thống kê hệ thống' },
  '/admin/users':        { title: 'Quản lý người dùng',  subtitle: 'Thêm, sửa, xóa tài khoản' },
  '/admin/classrooms':   { title: 'Quản lý lớp học',     subtitle: 'Tổ chức và quản lý lớp' },
  '/admin/school-years': { title: 'Năm học',             subtitle: 'Chu kỳ học tập' },
  '/admin/subjects':     { title: 'Môn học',             subtitle: 'Danh sách môn học' },
  '/admin/lessons':      { title: 'Bài học',             subtitle: 'Quản lý tất cả bài học' },
  '/admin/exams':        { title: 'Đề thi',              subtitle: 'Quản lý tất cả đề thi' },
  '/admin/assignments':  { title: 'Bài tập',             subtitle: 'Quản lý tất cả bài tập' },
  '/admin/live':         { title: 'Phòng trực tuyến',    subtitle: 'Quản lý phòng học trực tuyến' },
  '/admin/reports':      { title: 'Báo cáo',             subtitle: 'Thống kê và xuất dữ liệu' },
  '/teacher/dashboard':  { title: 'Tổng quan',           subtitle: 'Hoạt động giảng dạy' },
  '/teacher/homeroom':   { title: 'Lớp chủ nhiệm',       subtitle: 'Thông tin lớp bạn phụ trách' },
  '/teacher/lessons':    { title: 'Bài học',             subtitle: 'Quản lý nội dung bài học' },
  '/teacher/exams':      { title: 'Bài kiểm tra',        subtitle: 'Tạo và quản lý đề thi' },
  '/teacher/assignments':{ title: 'Bài tập',             subtitle: 'Giao và chấm bài tập' },
  '/teacher/live':          { title: 'Phòng trực tuyến',    subtitle: 'Mở phòng học cho lớp của bạn' },
  '/teacher/grades':        { title: 'Điểm số',             subtitle: 'Kết quả học tập' },
  '/teacher/question-bank': { title: 'Ngân hàng câu hỏi',  subtitle: 'Câu hỏi cho bài kiểm tra' },
  '/student/dashboard':  { title: 'Trang chủ',           subtitle: 'Chào mừng bạn trở lại' },
  '/student/classes':    { title: 'Lớp học',             subtitle: 'Các lớp đang tham gia' },
  '/student/lessons':          { title: 'Bài học',          subtitle: 'Bài học từ các lớp của bạn' },
  '/student/lessons/subjects': { title: 'Bài học theo môn', subtitle: 'Khám phá bài học theo từng môn học' },
  '/student/exams':      { title: 'Bài kiểm tra',        subtitle: 'Danh sách bài kiểm tra' },
  '/student/assignments':{ title: 'Bài tập',             subtitle: 'Bài tập được giao' },
  '/student/live':       { title: 'Phòng trực tuyến',    subtitle: 'Các phòng học trực tuyến' },
  '/student/progress':   { title: 'Kết quả học tập',     subtitle: 'Thành tích và tiến độ' },
  '/profile':            { title: 'Tài khoản',           subtitle: 'Thông tin cá nhân & bảo mật' },
  '/saved':              { title: 'Đã lưu',              subtitle: 'Bài học, đề thi, bài tập đã lưu' },
}

const currentMeta = computed(() => {
  const key = Object.keys(pageMeta)
    .sort((a, b) => b.length - a.length)
    .find(k => route.path === k || route.path.startsWith(k + '/'))
  return pageMeta[key] ?? { title: 'TunAcademy', subtitle: '' }
})
const pageTitle = computed(() => currentMeta.value.title)
const pageSubtitle = computed(() => currentMeta.value.subtitle)

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/')
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
