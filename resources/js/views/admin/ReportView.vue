<template>
  <div class="space-y-6">
    <!-- Overview stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <span class="text-2xl">{{ stat.icon }}</span>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ overview[stat.key] ?? '...' }}</p>
        <p class="text-sm text-gray-500">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Export & Filters -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Xuất báo cáo</h3>
      <div class="flex flex-wrap gap-3">
        <button @click="exportData('students')" class="flex items-center gap-2 px-4 py-2 rounded-xl border border-indigo-200 text-indigo-600 text-sm hover:bg-indigo-50 transition-colors">
          📥 Xuất danh sách học sinh
        </button>
        <button @click="exportData('teachers')" class="flex items-center gap-2 px-4 py-2 rounded-xl border border-emerald-200 text-emerald-600 text-sm hover:bg-emerald-50 transition-colors">
          📥 Xuất danh sách giáo viên
        </button>
        <button @click="exportData('exam-results')" class="flex items-center gap-2 px-4 py-2 rounded-xl border border-amber-200 text-amber-600 text-sm hover:bg-amber-50 transition-colors">
          📥 Xuất kết quả kiểm tra
        </button>
      </div>
    </div>

    <!-- Exam attempts table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800">Lịch sử kiểm tra</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Học sinh</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Bài kiểm tra</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Môn</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Điểm</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Thời gian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="attempts.length === 0"><td colspan="5" class="py-10 text-center text-gray-400">Chưa có dữ liệu</td></tr>
            <tr v-for="a in attempts" :key="a.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900">{{ a.student?.name }}</p>
                <p class="text-xs text-gray-400">{{ a.student?.email }}</p>
              </td>
              <td class="px-4 py-3 text-gray-700">{{ a.exam?.title }}</td>
              <td class="px-4 py-3 text-gray-500">{{ a.exam?.subject?.name }}</td>
              <td class="px-4 py-3 font-bold" :class="(a.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">{{ a.score ?? '-' }}</td>
              <td class="px-4 py-3 text-gray-400 text-xs">{{ formatDate(a.submitted_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'

const overview = ref({})
const attempts = ref([])

const stats = [
  { key: 'total_students', label: 'Học sinh', icon: '🎒' },
  { key: 'total_teachers', label: 'Giáo viên', icon: '👨‍🏫' },
  { key: 'total_classrooms', label: 'Lớp học', icon: '🏫' },
  { key: 'total_exams', label: 'Bài kiểm tra', icon: '📝' },
]

async function exportData(type) {
  const link = document.createElement('a')
  link.href = `/api/admin/reports/export?type=${type}`
  link.setAttribute('Authorization', `Bearer ${localStorage.getItem('auth_token')}`)
  const { data } = await api.get(`/admin/reports/export?type=${type}`, { responseType: 'blob' })
  const url = URL.createObjectURL(data)
  link.href = url
  link.download = `${type}-${new Date().toISOString().slice(0, 10)}.xlsx`
  link.click()
  URL.revokeObjectURL(url)
}

function formatDate(d) { return d ? new Date(d).toLocaleString('vi-VN') : '' }

onMounted(async () => {
  const [ov, ex] = await Promise.all([api.get('/admin/reports/overview'), api.get('/admin/reports/exams')])
  overview.value = ov.data.data ?? {}
  attempts.value = ex.data.data?.data ?? []
})
</script>
