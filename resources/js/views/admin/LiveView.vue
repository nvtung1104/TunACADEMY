<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Phòng học trực tuyến</h2>
        <p class="text-sm text-gray-400 mt-0.5">Tất cả phòng học trực tuyến trong hệ thống</p>
      </div>
      <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ meta.total ?? 0 }} phòng</span>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên phòng học..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
      </div>
      <select v-model="filterStatus" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
        <option value="">Tất cả trạng thái</option>
        <option value="scheduled">Đã lên lịch</option>
        <option value="live">Đang diễn ra</option>
        <option value="ended">Đã kết thúc</option>
      </select>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-if="loading" v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"/><div class="h-3 bg-gray-200 rounded w-1/2 mb-5"/>
        <div class="h-3 bg-gray-200 rounded w-full mb-2"/><div class="h-3 bg-gray-200 rounded w-3/4"/>
      </div>
      <template v-else>
        <div v-if="!sessions.length" class="col-span-full py-12 text-center text-gray-400">Không có phòng học nào</div>
        <div v-for="s in sessions" :key="s.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-gray-800 truncate">{{ s.title }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ s.classroom?.name }} · {{ s.teacher?.name }}</p>
            </div>
            <span class="ml-2 shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" :class="liveStatusClass(s.status)">
              {{ liveStatusLabel(s.status) }}
            </span>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              {{ formatDate(s.scheduled_at) }}
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ s.participants_count ?? 0 }}/{{ s.max_participants ?? '∞' }}
            </span>
          </div>
          <button @click="confirmDelete(s)" class="w-full flex items-center justify-center gap-1.5 py-1.5 text-xs text-red-500 hover:bg-red-50 rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Xóa phòng học
          </button>
        </div>
      </template>
    </div>

    <Pagination :meta="meta" @page="fetchData"/>

    <AppModal v-model="deleteModal" title="Xác nhận xóa" size="sm">
      <p class="text-sm text-gray-600">Bạn có chắc muốn xóa phòng học <strong>{{ deleteTarget?.title }}</strong>?</p>
      <template #footer>
        <button @click="deleteModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="doDelete" :disabled="deleting" class="px-4 py-2 text-sm rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-60">
          {{ deleting ? 'Đang xóa...' : 'Xóa' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import Pagination from '@components/common/Pagination.vue'

const sessions = ref([])
const loading = ref(true)
const search = ref('')
const filterStatus = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })
const deleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

let debounceTimer = null
function debounceFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 400) }
function formatDate(d) { return d ? new Date(d).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—' }
const liveStatusLabel = (s) => ({ scheduled: 'Đã lên lịch', live: 'Đang diễn ra', ended: 'Đã kết thúc' }[s] ?? s)
const liveStatusClass = (s) => ({ scheduled: 'bg-blue-100 text-blue-700', live: 'bg-green-100 text-green-700 animate-pulse', ended: 'bg-gray-100 text-gray-500' }[s] ?? 'bg-gray-100 text-gray-500')

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/admin/content/live-sessions', { params: { search: search.value, status: filterStatus.value, page } })
    sessions.value = data.data
    meta.value = data.meta ?? { total: data.total, current_page: data.current_page, last_page: data.last_page }
  } finally { loading.value = false }
}

function confirmDelete(item) { deleteTarget.value = item; deleteModal.value = true }

async function doDelete() {
  deleting.value = true
  try {
    await api.delete(`/admin/content/live-sessions/${deleteTarget.value.id}`)
    deleteModal.value = false; fetchData(meta.value.current_page)
  } finally { deleting.value = false }
}

onMounted(fetchData)
</script>
