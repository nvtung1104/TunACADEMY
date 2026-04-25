<template>
  <div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label"
        class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="text-2xl">{{ stat.icon }}</span>
          <span class="text-xs font-medium px-2 py-1 rounded-full" :class="stat.badgeClass">{{ stat.badge }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ loading ? '...' : stat.value }}</p>
        <p class="text-sm text-gray-500 mt-0.5">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Two-column section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent exams -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-800">Bài kiểm tra gần đây</h3>
          <RouterLink to="/admin/reports" class="text-xs text-indigo-600 hover:underline">Xem tất cả</RouterLink>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="recentAttempts.length === 0" class="px-6 py-8 text-center text-gray-400 text-sm">Chưa có dữ liệu</div>
          <div v-for="a in recentAttempts.slice(0, 5)" :key="a.id" class="px-6 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600">
              {{ a.student?.name?.charAt(0) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ a.student?.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ a.exam?.title }}</p>
            </div>
            <span class="text-sm font-bold" :class="(a.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
              {{ a.score ?? '-' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">Thao tác nhanh</h3>
        </div>
        <div class="p-6 grid grid-cols-2 gap-3">
          <RouterLink v-for="action in quickActions" :key="action.to" :to="action.to"
            class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-dashed border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all text-center group">
            <span class="text-2xl">{{ action.icon }}</span>
            <span class="text-xs font-medium text-gray-600 group-hover:text-indigo-700">{{ action.label }}</span>
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
const overview = ref({})
const recentAttempts = ref([])

const stats = ref([
  { label: 'Học sinh', icon: '🎒', value: 0, badge: 'Tổng', badgeClass: 'bg-blue-100 text-blue-600' },
  { label: 'Giáo viên', icon: '👨‍🏫', value: 0, badge: 'Tổng', badgeClass: 'bg-emerald-100 text-emerald-600' },
  { label: 'Lớp học', icon: '🏫', value: 0, badge: 'Đang hoạt động', badgeClass: 'bg-amber-100 text-amber-600' },
  { label: 'Bài kiểm tra', icon: '📝', value: 0, badge: 'Tháng này', badgeClass: 'bg-violet-100 text-violet-600' },
])

const quickActions = [
  { to: '/admin/users', icon: '👤', label: 'Thêm người dùng' },
  { to: '/admin/classrooms', icon: '🏫', label: 'Tạo lớp học' },
  { to: '/admin/school-years', icon: '📅', label: 'Năm học' },
  { to: '/admin/reports', icon: '📊', label: 'Xem báo cáo' },
]

onMounted(async () => {
  try {
    const [ov, ex] = await Promise.all([
      api.get('/admin/reports/overview'),
      api.get('/admin/reports/exams'),
    ])
    overview.value = ov.data.data
    const o = overview.value
    stats.value[0].value = o.total_students ?? 0
    stats.value[1].value = o.total_teachers ?? 0
    stats.value[2].value = o.total_classrooms ?? 0
    stats.value[3].value = o.exams_this_month ?? 0
    recentAttempts.value = ex.data.data?.data ?? []
  } finally {
    loading.value = false
  }
})
</script>
