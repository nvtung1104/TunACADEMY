<template>
  <div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label"
        class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="stat.iconBg">
            <span class="w-5 h-5" :class="stat.iconColor" v-html="stat.icon"></span>
          </div>
          <span class="text-xs font-semibold px-2 py-1 rounded-full" :class="stat.badgeClass">{{ stat.badge }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ loading ? '—' : stat.value }}</p>
        <p class="text-sm text-gray-500 mt-0.5">{{ stat.label }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent exam results -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-800">Kết quả kiểm tra gần đây</h3>
          <RouterLink to="/teacher/exams" class="text-xs text-indigo-600 hover:underline font-medium">Xem tất cả</RouterLink>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="recentResults.length === 0" class="py-10 text-center text-gray-400 text-sm">
            Chưa có kết quả kiểm tra
          </div>
          <div v-for="r in recentResults.slice(0, 6)" :key="r.id" class="px-6 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600 shrink-0 uppercase">
              {{ r.student?.name?.charAt(0) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ r.student?.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ r.exam?.title }}</p>
            </div>
            <div class="text-right shrink-0">
              <span class="text-sm font-bold" :class="(r.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
                {{ r.score != null ? r.score : '—' }}
              </span>
              <p class="text-[10px] text-gray-400">{{ formatDate(r.submitted_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">Thao tác nhanh</h3>
        </div>
        <div class="p-4 space-y-2">
          <RouterLink v-for="action in quickActions" :key="action.to" :to="action.to"
            class="flex items-center gap-3 p-3 rounded-xl hover:bg-indigo-50 transition-colors group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="action.bg">
              <span class="w-4 h-4" :class="action.color" v-html="action.icon"></span>
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">{{ action.label }}</span>
            <svg class="w-4 h-4 text-gray-300 ml-auto group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'

const loading = ref(true)
const recentResults = ref([])

const iconBook = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>`
const iconClip = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`
const iconUsers = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`
const iconPencil = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>`
const iconClass = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`
const iconVideo = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`

const stats = ref([
  { label: 'Lớp học', icon: iconClass, value: 0, badge: 'Của tôi', badgeClass: 'bg-blue-100 text-blue-600', iconBg: 'bg-blue-50', iconColor: 'text-blue-500' },
  { label: 'Học sinh', icon: iconUsers, value: 0, badge: 'Tổng', badgeClass: 'bg-emerald-100 text-emerald-600', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-500' },
  { label: 'Bài học', icon: iconBook, value: 0, badge: 'Đã tạo', badgeClass: 'bg-amber-100 text-amber-600', iconBg: 'bg-amber-50', iconColor: 'text-amber-500' },
  { label: 'Đề kiểm tra', icon: iconClip, value: 0, badge: 'Đã tạo', badgeClass: 'bg-violet-100 text-violet-600', iconBg: 'bg-violet-50', iconColor: 'text-violet-500' },
])

const quickActions = [
  { to: '/teacher/lessons', label: 'Tạo bài học mới', icon: iconBook, bg: 'bg-amber-50', color: 'text-amber-500' },
  { to: '/teacher/exams', label: 'Tạo đề kiểm tra', icon: iconClip, bg: 'bg-violet-50', color: 'text-violet-500' },
  { to: '/teacher/assignments', label: 'Giao bài tập', icon: iconPencil, bg: 'bg-indigo-50', color: 'text-indigo-500' },
  { to: '/teacher/live', label: 'Tạo phòng trực tuyến', icon: iconVideo, bg: 'bg-red-50', color: 'text-red-500' },
]

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })
}

onMounted(async () => {
  try {
    const [classRes, lessonRes, examRes] = await Promise.all([
      api.get('/teacher/classrooms'),
      api.get('/teacher/lessons', { params: { per_page: 1 } }),
      api.get('/teacher/exams'),
    ])

    const classrooms = classRes.data.data?.data ?? classRes.data.data ?? []
    stats.value[0].value = classrooms.length
    stats.value[1].value = classrooms.reduce((s, c) => s + (c.students_count ?? 0), 0)
    stats.value[2].value = lessonRes.data.meta?.total ?? lessonRes.data.data?.total ?? 0
    const exams = examRes.data.data?.data ?? examRes.data.data ?? []
    stats.value[3].value = Array.isArray(exams) ? exams.length : 0

    // Fetch recent results from first exam if available
    if (exams.length > 0) {
      try {
        const results = await api.get(`/teacher/exams/${exams[0].id}/attempts`)
        recentResults.value = results.data.data?.data ?? results.data.data ?? []
      } catch { /* ignore */ }
    }
  } finally {
    loading.value = false
  }
})
</script>
