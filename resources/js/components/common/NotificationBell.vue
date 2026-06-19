<template>
  <div class="relative" ref="bellRef">
    <button
      @click="toggle"
      class="relative p-2 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
      title="Thông báo"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <span v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 scale-95 translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-1"
    >
      <div v-if="open" class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden origin-top-right">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800 text-sm">Thông báo</h3>
          <button v-if="unreadCount > 0" @click="markAll" class="text-xs text-indigo-600 hover:underline font-medium">
            Đánh dấu đã đọc
          </button>
        </div>

        <!-- List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
          <div v-if="loading" class="py-8 text-center text-gray-400 text-sm">
            <div class="animate-spin w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full mx-auto mb-2"></div>
            Đang tải...
          </div>
          <div v-else-if="notifications.length === 0" class="py-10 text-center">
            <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm text-gray-400">Không có thông báo</p>
          </div>
          <div
            v-for="n in notifications"
            :key="n.id"
            class="flex gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
            :class="!n.read_at ? 'bg-indigo-50/50' : ''"
            @click="markRead(n)"
          >
            <div class="w-2 h-2 rounded-full mt-1.5 shrink-0" :class="!n.read_at ? 'bg-indigo-500' : 'bg-gray-200'"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ n.data?.title ?? 'Thông báo' }}</p>
              <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ n.data?.body ?? n.data?.message }}</p>
              <p class="text-[10px] text-gray-400 mt-1">{{ formatTime(n.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="notifications.length > 0" class="px-4 py-2.5 border-t border-gray-100 text-center">
          <span class="text-xs text-gray-400">{{ notifications.length }} thông báo gần đây</span>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@api/axios'

const open = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const bellRef = ref(null)

function toggle() {
  open.value = !open.value
  if (open.value && notifications.value.length === 0) fetchNotifications()
}

async function fetchNotifications() {
  loading.value = true
  try {
    const { data } = await api.get('/notifications')
    notifications.value = data.data ?? []
    unreadCount.value = notifications.value.filter(n => !n.read_at).length
  } catch {
    // ignore
  } finally {
    loading.value = false
  }
}

async function markRead(n) {
  if (n.read_at) return
  try {
    await api.post(`/notifications/${n.id}/read`)
    n.read_at = new Date().toISOString()
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  } catch {
    // ignore
  }
}

async function markAll() {
  try {
    await api.post('/notifications/read-all')
    notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
    unreadCount.value = 0
  } catch {
    // ignore
  }
}

function formatTime(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const diffMs = now - d
  const diffMin = Math.floor(diffMs / 60000)
  if (diffMin < 1) return 'Vừa xong'
  if (diffMin < 60) return `${diffMin} phút trước`
  const diffH = Math.floor(diffMin / 60)
  if (diffH < 24) return `${diffH} giờ trước`
  return d.toLocaleDateString('vi-VN')
}

function handleClickOutside(e) {
  if (bellRef.value && !bellRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchNotifications()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
