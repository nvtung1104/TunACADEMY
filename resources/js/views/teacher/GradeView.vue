<template>
  <div class="space-y-4">
    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="selectedExam" @change="loadResults" class="input w-64">
          <option value="">Chọn bài kiểm tra</option>
          <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title }}</option>
        </select>
      </div>
      <p class="text-sm text-gray-500">{{ results.length }} kết quả</p>
    </div>

    <!-- Empty state: no exam selected -->
    <div v-if="!selectedExam" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
      <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      <p class="text-gray-400 font-medium">Chọn một bài kiểm tra để xem điểm số</p>
    </div>

    <!-- Stats row -->
    <div v-else-if="!loading && results.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Học sinh</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-emerald-600">{{ stats.avg }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Điểm trung bình</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ stats.passed }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Đạt (≥5)</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-red-500">{{ stats.failed }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Không đạt</p>
      </div>
    </div>

    <!-- Results table -->
    <div v-if="selectedExam" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải kết quả...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Học sinh</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Điểm</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Xếp loại</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời gian nộp</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="results.length === 0">
            <td colspan="5" class="py-10 text-center text-gray-400">Chưa có kết quả</td>
          </tr>
          <tr v-for="(r, i) in results" :key="r.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3 text-gray-400 text-xs font-mono">{{ i + 1 }}</td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600 uppercase shrink-0">
                  {{ r.student?.name?.charAt(0) }}
                </div>
                <div>
                  <p class="font-medium text-gray-800">{{ r.student?.name }}</p>
                  <p class="text-xs text-gray-400">{{ r.student?.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3">
              <span class="text-xl font-bold" :class="(r.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
                {{ r.score != null ? r.score : '—' }}
              </span>
              <span class="text-gray-400 text-xs">/10</span>
            </td>
            <td class="px-5 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="gradeClass(r.score)">
                {{ gradeLabel(r.score) }}
              </span>
            </td>
            <td class="px-5 py-3 text-gray-400 text-xs">{{ formatDate(r.submitted_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@api/axios'

const exams = ref([])
const results = ref([])
const loading = ref(false)
const selectedExam = ref('')

const stats = computed(() => {
  const scored = results.value.filter(r => r.score != null)
  const total = scored.length
  const avg = total ? (scored.reduce((s, r) => s + r.score, 0) / total).toFixed(1) : '—'
  const passed = scored.filter(r => r.score >= 5).length
  return { total, avg, passed, failed: total - passed }
})

async function loadResults() {
  if (!selectedExam.value) return
  loading.value = true
  results.value = []
  try {
    const { data } = await api.get(`/teacher/exams/${selectedExam.value}/attempts`)
    results.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

function gradeLabel(score) {
  if (score == null) return 'Chưa có'
  if (score >= 9) return 'Xuất sắc'
  if (score >= 8) return 'Giỏi'
  if (score >= 6.5) return 'Khá'
  if (score >= 5) return 'TB'
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

function formatDate(iso) { return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '—' }

onMounted(async () => {
  const { data } = await api.get('/teacher/exams')
  exams.value = data.data?.data ?? data.data ?? []
})
</script>

<style scoped>
.input { @apply px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm; }
</style>
