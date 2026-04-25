<template>
  <div class="space-y-6">
    <!-- Loading -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải kết quả...
    </div>

    <template v-else>
      <!-- Summary stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="s in summaryStats" :key="s.label" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm text-center">
          <p class="text-3xl font-bold mb-1" :class="s.color">{{ s.value }}</p>
          <p class="text-sm text-gray-500">{{ s.label }}</p>
        </div>
      </div>

      <!-- Score distribution -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Phân bố điểm số</h3>
        <div class="space-y-3">
          <div v-for="band in scoreBands" :key="band.label" class="flex items-center gap-3">
            <span class="text-xs font-medium text-gray-500 w-16 shrink-0">{{ band.label }}</span>
            <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
              <div class="h-full rounded-full transition-all duration-700" :class="band.color"
                :style="{ width: attempts.length > 0 ? `${(band.count / attempts.length) * 100}%` : '0%' }">
              </div>
            </div>
            <span class="text-xs text-gray-500 w-8 text-right shrink-0">{{ band.count }}</span>
          </div>
        </div>
      </div>

      <!-- Exam history table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">Lịch sử bài kiểm tra</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bài kiểm tra</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Môn học</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Điểm</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Xếp loại</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời gian</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="attempts.length === 0">
                <td colspan="5" class="py-12 text-center text-gray-400">Chưa có kết quả kiểm tra nào</td>
              </tr>
              <tr v-for="a in attempts" :key="a.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                  <p class="font-medium text-gray-800">{{ a.exam?.title }}</p>
                  <p class="text-xs text-gray-400">{{ a.exam?.classroom?.name }}</p>
                </td>
                <td class="px-5 py-3 text-gray-600 text-sm">{{ a.exam?.subject?.name ?? '—' }}</td>
                <td class="px-5 py-3">
                  <div class="flex items-center gap-2">
                    <span class="text-xl font-bold" :class="(a.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
                      {{ a.score != null ? a.score : '—' }}
                    </span>
                    <span class="text-gray-400 text-xs">/10</span>
                  </div>
                </td>
                <td class="px-5 py-3">
                  <span class="px-2 py-1 rounded-full text-xs font-medium" :class="gradeClass(a.score)">
                    {{ gradeLabel(a.score) }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-400 text-xs">{{ formatDate(a.submitted_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@api/axios'

const attempts = ref([])
const loading = ref(true)

const summaryStats = computed(() => {
  const scored = attempts.value.filter(a => a.score != null)
  const total = scored.length
  const avg = total ? (scored.reduce((s, a) => s + a.score, 0) / total).toFixed(1) : '—'
  const best = total ? Math.max(...scored.map(a => a.score)) : '—'
  const passed = scored.filter(a => a.score >= 5).length
  return [
    { label: 'Bài đã làm', value: total, color: 'text-gray-800' },
    { label: 'Điểm trung bình', value: avg, color: 'text-indigo-600' },
    { label: 'Điểm cao nhất', value: best, color: 'text-green-600' },
    { label: 'Tỷ lệ đạt', value: total ? `${Math.round((passed / total) * 100)}%` : '—', color: 'text-emerald-600' },
  ]
})

const scoreBands = computed(() => {
  const bands = [
    { label: '9 – 10', min: 9, max: 10, color: 'bg-purple-500', count: 0 },
    { label: '8 – 9', min: 8, max: 9, color: 'bg-blue-500', count: 0 },
    { label: '6.5 – 8', min: 6.5, max: 8, color: 'bg-green-500', count: 0 },
    { label: '5 – 6.5', min: 5, max: 6.5, color: 'bg-yellow-400', count: 0 },
    { label: '< 5', min: 0, max: 5, color: 'bg-red-500', count: 0 },
  ]
  attempts.value.filter(a => a.score != null).forEach(a => {
    const band = bands.find(b => a.score >= b.min && a.score <= b.max)
    if (band) band.count++
  })
  return bands
})

function gradeLabel(score) {
  if (score == null) return 'Chưa có'
  if (score >= 9) return 'Xuất sắc'
  if (score >= 8) return 'Giỏi'
  if (score >= 6.5) return 'Khá'
  if (score >= 5) return 'Trung bình'
  return 'Yếu'
}

function gradeClass(score) {
  if (score == null) return 'bg-gray-100 text-gray-500'
  if (score >= 9) return 'bg-purple-100 text-purple-700'
  if (score >= 8) return 'bg-blue-100 text-blue-700'
  if (score >= 6.5) return 'bg-green-100 text-green-700'
  if (score >= 5) return 'bg-yellow-100 text-yellow-700'
  return 'bg-red-100 text-red-600'
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '—'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/student/exams')
    const exams = data.data?.data ?? data.data ?? []
    const completed = exams.filter(e => e.my_attempt)
    attempts.value = completed.map(e => ({
      id: e.id,
      score: e.my_attempt?.score,
      submitted_at: e.my_attempt?.submitted_at,
      exam: e,
    }))
  } finally { loading.value = false }
})
</script>
