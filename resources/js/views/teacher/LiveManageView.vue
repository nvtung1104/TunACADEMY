<template>
  <div class="space-y-5">
    <div>
      <h2 class="text-lg font-bold text-gray-900">Phòng học trực tuyến</h2>
      <p class="text-sm text-gray-400 mt-0.5">Mỗi lớp có sẵn 1 phòng học, bấm Bắt đầu để mở phòng cho học sinh vào</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"/>
        <div class="h-3 bg-gray-200 rounded w-1/2 mb-5"/>
        <div class="h-10 bg-gray-200 rounded-xl"/>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!classrooms.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
      <div class="text-4xl mb-3">🎥</div>
      <p class="text-gray-500 font-medium">Bạn chưa được giao lớp nào</p>
      <p class="text-sm text-gray-400 mt-1">Liên hệ quản trị viên để được thêm vào lớp</p>
    </div>

    <!-- Rooms grid -->
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="c in classrooms" :key="c.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
        <!-- Live status bar -->
        <div class="h-1.5" :class="c.is_live ? 'bg-green-500' : 'bg-gray-200'"/>

        <div class="p-5">
          <!-- Header -->
          <div class="flex items-start justify-between mb-4">
            <div>
              <p class="font-bold text-gray-900 text-base">{{ c.name }}</p>
              <p class="text-xs text-gray-400 mt-0.5 font-mono">Mã phòng: {{ c.room_code }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold shrink-0"
              :class="c.is_live ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
              <span v-if="c.is_live" class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"/>
              {{ c.is_live ? 'Đang mở' : 'Đóng' }}
            </span>
          </div>

          <!-- Participant count when live -->
          <div v-if="c.is_live && c.session" class="flex items-center gap-2 mb-4 px-3 py-2 bg-green-50 rounded-xl">
            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-sm text-green-700 font-medium">{{ c.session.participants?.length ?? 0 }} học sinh đang trong phòng</span>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button v-if="!c.is_live"
              @click="startRoom(c)"
              :disabled="c.starting"
              class="flex-1 py-2.5 rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white text-sm font-semibold transition-colors flex items-center justify-center gap-2 disabled:opacity-60">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ c.starting ? 'Đang mở...' : 'Bắt đầu' }}
            </button>
            <template v-else>
              <button
                @click="openRoom(c)"
                class="flex-1 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Vào phòng
              </button>
              <button
                @click="endRoom(c)"
                :disabled="c.ending"
                class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition-colors disabled:opacity-60">
                {{ c.ending ? '...' : 'Kết thúc' }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'

const classrooms = ref([])
const loading = ref(true)

async function fetchRooms() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/classrooms')
    const list = data.data ?? []

    // Fetch room status for each classroom in parallel
    const withStatus = await Promise.all(
      list.map(async (c) => {
        try {
          const res = await api.get(`/teacher/classrooms/${c.id}/room`)
          const room = res.data.data
          return { ...c, is_live: room.is_live, session: room.session, starting: false, ending: false }
        } catch {
          return { ...c, is_live: false, session: null, starting: false, ending: false }
        }
      })
    )
    classrooms.value = withStatus
  } finally {
    loading.value = false
  }
}

async function startRoom(c) {
  c.starting = true
  try {
    const { data } = await api.post(`/teacher/classrooms/${c.id}/room/start`)
    c.session = data.data
    c.is_live = true
  } catch (e) {
    alert(e.response?.data?.message ?? 'Không thể mở phòng')
  } finally {
    c.starting = false
  }
}

async function endRoom(c) {
  if (!confirm(`Kết thúc phòng học "${c.name}"?`)) return
  c.ending = true
  try {
    await api.post(`/teacher/classrooms/${c.id}/room/end`)
    c.is_live = false
    c.session = null
  } catch (e) {
    alert(e.response?.data?.message ?? 'Không thể kết thúc phòng')
  } finally {
    c.ending = false
  }
}

function openRoom(c) {
  alert(`Phòng học ${c.name}\nMã phòng: ${c.room_code}\n\nTính năng WebRTC đang được tích hợp.`)
}

onMounted(fetchRooms)
</script>
