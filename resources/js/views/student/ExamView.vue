<template>
  <div class="space-y-4">
    <!-- Filter tabs -->
    <div class="flex gap-2">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
        :class="activeTab === tab.value ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300'">
        {{ tab.label }}
      </button>
    </div>

    <!-- List -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <div v-else class="space-y-3">
      <div v-if="filteredExams.length === 0" class="py-16 text-center bg-white rounded-2xl border border-gray-100 shadow-sm text-gray-400">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Không có bài kiểm tra nào
      </div>

      <div v-for="e in filteredExams" :key="e.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all">
        <div class="flex items-start gap-4">
          <!-- Icon -->
          <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
            :style="e.subject?.color ? `background: ${e.subject.color}20` : 'background: #eef2ff'">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <h3 class="font-semibold text-gray-900">{{ e.title }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ e.subject?.name }} · {{ e.classroom?.name }}</p>
              </div>
              <span class="px-2 py-1 rounded-full text-xs font-medium shrink-0" :class="examStatusClass(e)">
                {{ examStatusLabel(e) }}
              </span>
            </div>

            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ e.duration_minutes }} phút
              </span>
              <span v-if="e.opened_at" class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Từ {{ formatDate(e.opened_at) }}
              </span>
              <span v-if="e.closed_at" class="text-gray-400">→ {{ formatDate(e.closed_at) }}</span>
            </div>

            <!-- Score if completed -->
            <div v-if="e.my_attempt?.score != null" class="mt-2 flex items-center gap-2">
              <span class="text-sm font-bold" :class="e.my_attempt.score >= 5 ? 'text-green-600' : 'text-red-500'">
                Điểm: {{ e.my_attempt.score }}/10
              </span>
            </div>
          </div>

          <!-- Action -->
          <div class="shrink-0">
            <button v-if="canTake(e)" @click="takeExam(e)"
              class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors">
              Làm bài
            </button>
            <RouterLink v-else-if="e.my_attempt?.score != null" :to="`/student/exams/${e.id}/result`"
              class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-colors inline-block">
              Xem kết quả
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@api/axios'

const router = useRouter()
const exams = ref([])
const loading = ref(true)
const activeTab = ref('all')

const tabs = [
  { value: 'all', label: 'Tất cả' },
  { value: 'available', label: 'Có thể làm' },
  { value: 'completed', label: 'Đã hoàn thành' },
]

const filteredExams = computed(() => {
  if (activeTab.value === 'available') return exams.value.filter(e => e.status === 'published' && !e.my_attempt)
  if (activeTab.value === 'completed') return exams.value.filter(e => e.my_attempt)
  return exams.value
})

function canTake(e) {
  return e.status === 'published' && (!e.my_attempt || e.allow_retake)
}

function examStatusLabel(e) {
  if (e.my_attempt?.score != null) return 'Đã hoàn thành'
  if (e.my_attempt) return 'Đang làm'
  if (e.status === 'published') return 'Đang mở'
  if (e.status === 'closed') return 'Đã đóng'
  return 'Chưa mở'
}

function examStatusClass(e) {
  if (e.my_attempt?.score != null) return 'bg-green-100 text-green-700'
  if (e.my_attempt) return 'bg-blue-100 text-blue-700'
  if (e.status === 'published') return 'bg-indigo-100 text-indigo-700'
  if (e.status === 'closed') return 'bg-gray-100 text-gray-500'
  return 'bg-amber-100 text-amber-700'
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' }) : ''
}

async function takeExam(e) {
  try {
    await api.post(`/student/exams/${e.id}/start`)
    router.push(`/student/exams/${e.id}/result`)
  } catch (err) {
    alert(err.response?.data?.message ?? 'Không thể bắt đầu bài thi')
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/student/exams')
    exams.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
})
</script>
