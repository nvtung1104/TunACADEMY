<template>
  <div class="space-y-6">

    <!-- Welcome banner -->
    <div class="welcome-banner rounded-2xl px-6 py-5 text-white shadow-sm flex items-center justify-between">
      <div>
        <p class="text-white/70 text-sm mb-0.5">{{ greeting }},</p>
        <h1 class="text-2xl font-medium">{{ auth.user?.name ?? 'Giáo viên' }}</h1>
        <p class="text-white/70 text-sm mt-1">{{ today }}</p>
      </div>
      <div class="hidden sm:flex items-center gap-4">
        <div v-for="stat in stats" :key="stat.label" class="text-center px-4 border-l border-white/20 first:border-0">
          <p class="text-2xl font-bold">{{ loading ? '—' : stat.value }}</p>
          <p class="text-xs text-white/70 mt-0.5">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Stats row (mobile only) -->
    <div class="grid grid-cols-2 gap-3 sm:hidden">
      <div v-for="stat in stats" :key="stat.label"
        class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" :class="stat.iconBg">
          <span class="w-4 h-4" :class="stat.iconColor" v-html="stat.icon"></span>
        </div>
        <div>
          <p class="text-xl font-bold text-gray-900">{{ loading ? '—' : stat.value }}</p>
          <p class="text-xs text-gray-500">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Recent exam results -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-800">Kết quả kiểm tra gần đây</h3>
          <RouterLink to="/teacher/exams" class="text-xs text-[#d63015] hover:underline font-medium">Xem tất cả</RouterLink>
        </div>

        <div v-if="loading" class="p-4 space-y-3">
          <div v-for="i in 5" :key="i" class="animate-pulse flex items-center gap-3 px-2">
            <div class="w-8 h-8 rounded-full bg-gray-200 shrink-0"/>
            <div class="flex-1 space-y-1.5">
              <div class="h-3.5 bg-gray-200 rounded w-1/3"/>
              <div class="h-3 bg-gray-200 rounded w-1/2"/>
            </div>
            <div class="h-4 bg-gray-200 rounded w-10"/>
          </div>
        </div>

        <div v-else-if="recentResults.length === 0" class="flex-1 flex flex-col items-center justify-center py-16 text-gray-400">
          <svg class="w-10 h-10 mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
          <p class="text-sm">Chưa có kết quả kiểm tra</p>
        </div>

        <div v-else class="divide-y divide-gray-50">
          <div v-for="r in recentResults.slice(0, 6)" :key="r.id"
            class="px-6 py-3 flex items-center gap-3 hover:bg-gray-50 transition-colors">
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-xs font-bold text-[#d63015] shrink-0 uppercase">
              {{ r.student?.name?.charAt(0) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ r.student?.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ r.exam?.title }}</p>
            </div>
            <div class="text-right shrink-0">
              <span class="text-sm font-bold tabular-nums"
                :class="(r.score ?? 0) >= 5 ? 'text-emerald-600' : 'text-red-500'">
                {{ r.score != null ? Number(r.score).toFixed(2) : '—' }}
              </span>
              <p class="text-[10px] text-gray-400">{{ formatDate(r.submitted_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column -->
      <div class="flex flex-col gap-5">

        <!-- Quick actions -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Thao tác nhanh</h3>
          </div>
          <div class="p-3 space-y-1">
            <RouterLink v-for="action in quickActions" :key="action.to" :to="action.to"
              class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-colors group">
              <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" :class="action.bg">
                <span class="w-4 h-4" :class="action.color" v-html="action.icon"></span>
              </div>
              <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 flex-1">{{ action.label }}</span>
              <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </RouterLink>
          </div>
        </div>

        <!-- My subjects -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex-1">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Môn dạy của tôi</h3>
            <span class="text-xs text-gray-400">{{ mySubjects.length }} môn</span>
          </div>

          <div v-if="loading" class="p-4 space-y-3">
            <div v-for="i in 2" :key="i" class="animate-pulse flex items-center gap-3">
              <div class="w-8 h-8 rounded-xl bg-gray-200 shrink-0"/>
              <div class="flex-1 space-y-1.5">
                <div class="h-3.5 bg-gray-200 rounded w-2/3"/>
                <div class="h-3 bg-gray-200 rounded w-1/3"/>
              </div>
            </div>
          </div>

          <div v-else-if="mySubjects.length === 0" class="py-8 text-center">
            <p class="text-sm text-gray-400">Chưa được phân công môn dạy</p>
          </div>

          <div v-else class="p-3 space-y-2">
            <div v-for="item in mySubjects" :key="item.subject.id"
              class="rounded-xl border border-gray-100 overflow-hidden">
              <div class="flex items-center gap-3 px-3 py-2.5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-white text-sm font-bold"
                  :style="{ backgroundColor: item.subject.color || '#d63015' }">
                  {{ item.subject.icon || item.subject.name?.[0] }}
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-gray-800 truncate">{{ item.subject.name }}</p>
                  <p class="text-xs text-gray-400">{{ item.classrooms.length }} lớp</p>
                </div>
              </div>
              <div class="bg-gray-50 px-3 py-2 flex flex-wrap gap-1.5">
                <span v-for="cls in item.classrooms" :key="cls.id"
                  class="inline-flex items-center text-xs px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-600">
                  {{ cls.name }}<span v-if="cls.grade" class="ml-1 text-gray-400">({{ cls.grade }})</span>
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const recentResults = ref([])
const mySubjects = ref([])

const now = new Date()
const hour = now.getHours()
const greeting = hour < 12 ? 'Chào buổi sáng' : hour < 18 ? 'Chào buổi chiều' : 'Chào buổi tối'
const today = now.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })

const iconBook = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>`
const iconClip = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`
const iconUsers = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`
const iconPencil = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>`
const iconClass = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`
const iconDatabase = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3-3.582 3-8 3-8-1.343-8-3zm0 0v5c0 1.657 3.582 3 8 3s8-1.343 8-3V7m-16 5v5c0 1.657 3.582 3 8 3s8-1.343 8-3v-5"/></svg>`

const stats = ref([
  { label: 'Lớp chủ nhiệm', icon: iconClass, value: 0, iconBg: 'bg-blue-50', iconColor: 'text-blue-500' },
  { label: 'Học sinh', icon: iconUsers, value: 0, iconBg: 'bg-emerald-50', iconColor: 'text-emerald-500' },
  { label: 'Bài học', icon: iconBook, value: 0, iconBg: 'bg-amber-50', iconColor: 'text-amber-500' },
  { label: 'Đề kiểm tra', icon: iconClip, value: 0, iconBg: 'bg-red-50', iconColor: 'text-[#d63015]' },
])

const quickActions = [
  { to: '/teacher/lessons', label: 'Tạo bài học mới', icon: iconBook, bg: 'bg-amber-50', color: 'text-amber-500' },
  { to: '/teacher/exams', label: 'Tạo đề kiểm tra', icon: iconClip, bg: 'bg-red-50', color: 'text-[#d63015]' },
  { to: '/teacher/assignments', label: 'Giao bài tập', icon: iconPencil, bg: 'bg-emerald-50', color: 'text-emerald-500' },
  { to: '/teacher/question-bank', label: 'Ngân hàng câu hỏi', icon: iconDatabase, bg: 'bg-red-50', color: 'text-[#d63015]' },
]

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })
}

onMounted(async () => {
  try {
    const [classRes, lessonRes, examRes, subjectRes] = await Promise.all([
      api.get('/teacher/classrooms'),
      api.get('/teacher/lessons', { params: { per_page: 1 } }),
      api.get('/teacher/exams'),
      api.get('/teacher/my-subjects'),
    ])

    const classrooms = classRes.data.data?.data ?? classRes.data.data ?? []
    const homeroom = classrooms.filter(c => c.homeroom_teacher_id === auth.user?.id)
    stats.value[0].value = homeroom.length
    stats.value[1].value = homeroom.reduce((s, c) => s + (c.students_count ?? 0), 0)
    stats.value[2].value = lessonRes.data.meta?.total ?? lessonRes.data.data?.total ?? 0
    const exams = examRes.data.data?.data ?? examRes.data.data ?? []
    stats.value[3].value = Array.isArray(exams) ? exams.length : 0
    mySubjects.value = subjectRes.data.data ?? []

    // Load recent results separately — does not block loading state
    if (exams.length > 0) {
      api.get(`/teacher/exams/${exams[0].id}/attempts`)
        .then(res => { recentResults.value = res.data.data?.data ?? res.data.data ?? [] })
        .catch(() => {})
    }
  } catch {
    /* ignore */
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.welcome-banner {
  background-image: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%) !important;
  position: relative;
  overflow: hidden;
}

.welcome-banner::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 150px;
  height: 150px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.08);
  pointer-events: none;
}

.welcome-banner::after {
  content: '';
  position: absolute;
  bottom: -30px;
  right: 120px;
  width: 80px;
  height: 80px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.05);
  pointer-events: none;
}
</style>
