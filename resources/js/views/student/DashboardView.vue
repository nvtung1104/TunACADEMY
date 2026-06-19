<template>
  <div class="space-y-6">
    <!-- Welcome banner -->
    <div class="welcome-banner rounded-2xl p-6 text-white shadow-sm">
      <h2 class="text-xl font-medium mb-1">Chào mừng trở lại, {{ auth.user?.name?.split(' ').at(-1) }}! 👋</h2>
      <p class="text-white/70 text-sm">Hãy tiếp tục hành trình học tập của bạn hôm nay</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" :class="stat.iconBg">
          <span class="w-5 h-5" :class="stat.iconColor" v-html="stat.icon"></span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ loading ? '—' : stat.value }}</p>
        <p class="text-sm text-gray-500 mt-0.5">{{ stat.label }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Upcoming exams -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-800">Bài kiểm tra sắp tới</h3>
          <RouterLink to="/student/exams" class="text-xs text-[#d63015] hover:text-[#c02a10] hover:underline font-medium transition-colors">Xem tất cả</RouterLink>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="upcomingExams.length === 0" class="py-10 text-center text-gray-400 text-sm">
            Không có bài kiểm tra sắp tới
          </div>
          <div v-for="e in upcomingExams" :key="e.id" class="px-6 py-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
              :style="e.subject?.color ? `background: ${e.subject.color}20` : 'background: #fef2f2'">
              <span class="text-lg">📝</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ e.title }}</p>
              <p class="text-xs text-gray-400">{{ e.subject?.name }} · {{ e.duration_minutes }} phút</p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-xs font-medium text-[#d63015]">{{ e.opened_at ? formatDate(e.opened_at) : 'Chưa mở' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending assignments -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-800">Bài tập chưa nộp</h3>
          <RouterLink to="/student/assignments" class="text-xs text-[#d63015] hover:text-[#c02a10] hover:underline font-medium transition-colors">Xem tất cả</RouterLink>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="pendingAssignments.length === 0" class="py-10 text-center text-gray-400 text-sm">
            Không có bài tập chờ nộp
          </div>
          <div v-for="a in pendingAssignments" :key="a.id" class="px-6 py-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
              <span class="text-lg">✏️</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ a.title }}</p>
              <p class="text-xs text-gray-400">{{ a.classroom?.name ?? '—' }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-xs font-medium" :class="isOverdue(a.due_date) ? 'text-red-500' : 'text-amber-600'">
                {{ a.due_date ? formatDate(a.due_date) : 'Không hạn' }}
              </p>
              <p v-if="isOverdue(a.due_date)" class="text-[10px] text-red-400">Đã quá hạn</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const upcomingExams = ref([])
const pendingAssignments = ref([])

const iconClass = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`
const iconExam = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 0-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`
const iconAssign = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>`
const iconTrend = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>`

const stats = ref([
  { label: 'Lớp học', icon: iconClass, value: 0, iconBg: 'bg-blue-50', iconColor: 'text-blue-500' },
  { label: 'Đề thi có thể làm', icon: iconExam, value: 0, iconBg: 'bg-red-50', iconColor: 'text-[#d63015]' },
  { label: 'Bài tập chờ nộp', icon: iconAssign, value: 0, iconBg: 'bg-amber-50', iconColor: 'text-amber-500' },
  { label: 'Bài đã hoàn thành', icon: iconTrend, value: 0, iconBg: 'bg-green-50', iconColor: 'text-green-500' },
])

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: '2-digit' }) : ''
}

function isOverdue(due) {
  return due && new Date(due) < new Date()
}

onMounted(async () => {
  try {
    const [classRes, examRes, assignRes] = await Promise.all([
      api.get('/student/classrooms'),
      api.get('/student/exams'),
      api.get('/student/assignments'),
    ])

    const classrooms = classRes.data.data ?? []
    stats.value[0].value = classrooms.length

    const allExams = examRes.data.data?.data ?? examRes.data.data ?? []
    const available = allExams.filter(e => e.status === 'published')
    stats.value[1].value = available.length
    upcomingExams.value = available.slice(0, 5)

    const allAssignments = assignRes.data.data?.data ?? assignRes.data.data ?? []
    const pending = allAssignments.filter(a => !a.submitted)
    stats.value[2].value = pending.length
    stats.value[3].value = allAssignments.length - pending.length
    pendingAssignments.value = pending.slice(0, 5)
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
