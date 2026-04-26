<template>
  <div class="space-y-5">
    <div>
      <h2 class="text-lg font-bold text-gray-900">Phòng học trực tuyến</h2>
      <p class="text-sm text-gray-400 mt-0.5">Các phòng học trực tuyến của lớp bạn</p>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-2 flex-wrap">
      <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key; fetchData()"
        class="px-4 py-2 text-sm font-medium rounded-xl transition-colors"
        :class="activeTab === tab.key ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"/>
        <div class="h-3 bg-gray-200 rounded w-1/2 mb-5"/>
        <div class="h-8 bg-gray-200 rounded-xl"/>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!sessions.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
      <div class="text-4xl mb-3">🎥</div>
      <p class="text-gray-500 font-medium">Không có phòng học nào</p>
      <p class="text-sm text-gray-400 mt-1">Phòng học sẽ xuất hiện khi giáo viên tạo buổi học trực tuyến</p>
    </div>

    <!-- Grid -->
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="s in sessions" :key="s.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
        <!-- Live indicator -->
        <div class="h-1.5" :class="s.status === 'live' ? 'bg-green-500' : s.status === 'scheduled' ? 'bg-blue-500' : 'bg-gray-300'"/>
        <div class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-gray-800 truncate">{{ s.title }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ s.classroom?.name }}</p>
            </div>
            <span class="ml-2 shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
              :class="statusClass(s.status)">
              <span v-if="s.status === 'live'" class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"/>
              {{ statusLabel(s.status) }}
            </span>
          </div>

          <p v-if="s.description" class="text-xs text-gray-500 mb-3 line-clamp-2">{{ s.description }}</p>

          <div class="flex items-center gap-3 text-xs text-gray-400 mb-4">
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ s.teacher?.name }}
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ formatDate(s.scheduled_at) }}
            </span>
          </div>

          <!-- Action -->
          <button v-if="s.status === 'live'"
            @click="joinSession(s)"
            class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition-colors flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Vào phòng học
          </button>
          <div v-else-if="s.status === 'scheduled'"
            class="w-full py-2.5 rounded-xl bg-blue-50 text-blue-600 text-sm font-medium text-center">
            Sắp diễn ra
          </div>
          <div v-else class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-400 text-sm font-medium text-center">
            Đã kết thúc
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'

const sessions = ref([])
const loading = ref(true)
const activeTab = ref('all')

const tabs = [
  { key: 'all', label: 'Tất cả' },
  { key: 'live', label: 'Đang diễn ra' },
  { key: 'scheduled', label: 'Sắp diễn ra' },
  { key: 'ended', label: 'Đã kết thúc' },
]

const statusLabel = (s) => ({ live: 'Đang diễn ra', scheduled: 'Sắp diễn ra', ended: 'Đã kết thúc' }[s] ?? s)
const statusClass = (s) => ({ live: 'bg-green-100 text-green-700', scheduled: 'bg-blue-100 text-blue-700', ended: 'bg-gray-100 text-gray-500' }[s] ?? 'bg-gray-100 text-gray-500')

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

async function joinSession(s) {
  try {
    await api.post(`/student/live-sessions/${s.id}/join`)
    // In a real implementation, redirect to live room
    window.alert('Tính năng phòng học trực tuyến đang được phát triển.')
  } catch { /* ignore */ }
}

async function fetchData() {
  loading.value = true
  try {
    const params = activeTab.value !== 'all' ? { status: activeTab.value } : {}
    const { data } = await api.get('/student/live-sessions', { params })
    sessions.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

onMounted(fetchData)
</script>
