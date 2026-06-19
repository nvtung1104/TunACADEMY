<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-lg font-bold text-gray-900">Phòng học trực tuyến</h2>
      <p class="text-sm text-gray-400 mt-0.5">Phòng học của lớp bạn và phòng học công khai</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"/>
        <div class="h-3 bg-gray-200 rounded w-1/2 mb-5"/>
        <div class="h-10 bg-gray-200 rounded-xl"/>
      </div>
    </div>

    <template v-else>
      <!-- Empty state -->
      <div v-if="!rooms.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
        <div class="text-4xl mb-3">🎥</div>
        <p class="text-gray-500 font-medium">Chưa có phòng học nào</p>
        <p class="text-sm text-gray-400 mt-1">Phòng học sẽ hiển thị khi giáo viên bắt đầu buổi học trực tuyến</p>
      </div>

      <!-- Class rooms section -->
      <div v-if="classRooms.length">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-red-500 inline-block"/>
          Phòng học của lớp
        </h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="r in classRooms" :key="r.id"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
            <div class="h-1.5" :class="r.status === 'live' ? 'bg-green-500' : 'bg-gray-200'"/>
            <div class="p-5">
              <div class="flex items-start justify-between mb-3">
                <div class="min-w-0 flex-1">
                  <p class="font-bold text-gray-900">{{ r.classroom?.name ?? r.title }}</p>
                  <p class="text-xs text-gray-400 mt-0.5 font-mono">Mã phòng: {{ r.classroom?.room_code ?? r.room_code }}</p>
                </div>
                <span class="ml-2 shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="r.status === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'">
                  <span v-if="r.status === 'live'" class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"/>
                  {{ r.status === 'live' ? 'Đang mở' : 'Đã lên lịch' }}
                </span>
              </div>
              <div v-if="r.teacher" class="flex items-center gap-2 mb-4 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ r.teacher.name }}
              </div>
              <button v-if="r.status === 'live'" @click="joinRoom(r)"
                class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Vào phòng học
              </button>
              <div v-else class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-400 text-sm font-medium text-center">
                Giáo viên chưa mở phòng
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Public rooms section -->
      <div v-if="publicRooms.length">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-green-500 inline-block"/>
          Phòng học công khai
        </h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="r in publicRooms" :key="r.id"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
            <div class="h-1.5" :class="r.status === 'live' ? 'bg-green-500' : 'bg-gray-200'"/>
            <div class="p-5">
              <div class="flex items-start justify-between mb-3">
                <div class="min-w-0 flex-1">
                  <p class="font-bold text-gray-900">{{ r.title }}</p>
                  <p v-if="r.room_code" class="text-xs text-gray-400 mt-0.5 font-mono">Mã phòng: {{ r.room_code }}</p>
                </div>
                <span class="ml-2 shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="r.status === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'">
                  <span v-if="r.status === 'live'" class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"/>
                  {{ r.status === 'live' ? 'Đang mở' : 'Đã lên lịch' }}
                </span>
              </div>
              <div v-if="r.teacher" class="flex items-center gap-2 mb-4 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ r.teacher.name }}
              </div>
              <button v-if="r.status === 'live'" @click="joinRoom(r)"
                class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Vào phòng học
              </button>
              <div v-else class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-400 text-sm font-medium text-center">
                Giáo viên chưa mở phòng
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@api/axios'

const router = useRouter()
const rooms = ref([])
const loading = ref(true)

const classRooms = computed(() => rooms.value.filter(r => r.classroom_id !== null))
const publicRooms = computed(() => rooms.value.filter(r => r.classroom_id === null))

async function fetchRooms() {
  loading.value = true
  try {
    const { data } = await api.get('/student/my-rooms')
    rooms.value = data.data ?? []
  } finally {
    loading.value = false
  }
}

function joinRoom(r) {
  router.push(`/live/${r.id}/lobby`)
}

onMounted(fetchRooms)
</script>
