<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Phòng học trực tuyến</h2>
        <p class="text-sm text-gray-400 mt-0.5">Tất cả phòng học trực tuyến trong hệ thống</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="openBulkCreate"
          class="flex items-center gap-1.5 px-3 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-red-300 hover:text-[#d63015] transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
          </svg>
          Tạo cho tất cả lớp
        </button>
        <button @click="openCreate"
          class="flex items-center gap-1.5 px-3 py-2 text-sm rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white font-semibold transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tạo phòng học
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên phòng học..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
      </div>
      <select v-model="filterStatus" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white">
        <option value="">Tất cả trạng thái</option>
        <option value="scheduled">Đã lên lịch</option>
        <option value="live">Đang diễn ra</option>
        <option value="ended">Đã kết thúc</option>
      </select>
      <span class="self-center text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ meta.total ?? 0 }} phòng</span>
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
              <p class="text-xs text-gray-400 mt-0.5">
                {{ s.classroom?.name }}
                <template v-if="s.classroom?.homeroom_teacher">· GVCN: {{ s.classroom.homeroom_teacher.name }}</template>
                <template v-else-if="s.teacher">· GV: {{ s.teacher.name }}</template>
              </p>
            </div>
            <span class="ml-2 shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-semibold" :class="liveStatusClass(s.status)">
              {{ liveStatusLabel(s.status) }}
            </span>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              {{ s.scheduled_at ? formatDate(s.scheduled_at) : (s.is_permanent ? 'Phòng thường trực' : '—') }}
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ s.participants_count ?? 0 }}/{{ s.max_participants ?? '∞' }}
            </span>
            <span v-if="s.is_permanent" class="px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-700 font-medium">Thường trực</span>
          </div>
          <div v-if="s.room_code" class="flex items-center gap-1.5 mb-4">
            <span class="text-xs text-gray-400">Mã:</span>
            <code class="text-xs font-mono font-bold text-[#c02a10] bg-red-50 px-2 py-0.5 rounded-lg">{{ s.room_code }}</code>
          </div>
          <div class="flex gap-2">
            <button @click="router.push(`/live/${s.id}/lobby`)"
              class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold rounded-xl transition-colors"
              :class="s.status === 'live' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-600'">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              Vào phòng
            </button>
            <button @click="confirmDelete(s)" class="flex items-center justify-center gap-1 px-3 py-2 text-xs text-red-500 hover:bg-red-50 rounded-xl transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Xóa
            </button>
          </div>
        </div>
      </template>
    </div>

    <Pagination :meta="meta" @page="fetchData"/>

    <!-- Create modal -->
    <AppModal v-model="createModal" title="Tạo phòng học mới" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Lớp học <span class="text-gray-400 font-normal">(để trống = phòng công khai)</span></label>
          <select v-model="form.classroom_id" @change="onClassroomChange"
            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white">
            <option value="">-- Phòng học công khai (không thuộc lớp nào) --</option>
            <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }} ({{ c.grade?.name }})</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Tên phòng học <span class="text-red-500">*</span></label>
          <input v-model="form.title" type="text" placeholder="VD: Buổi học trực tuyến Toán 10A1"
            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Mô tả</label>
          <textarea v-model="form.description" rows="2" placeholder="Mô tả buổi học..."
            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] resize-none"/>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Thời gian bắt đầu</label>
            <input v-model="form.scheduled_at" type="datetime-local"
              class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Thời lượng (phút)</label>
            <input v-model.number="form.duration_minutes" type="number" min="15" max="480"
              class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Số học sinh tối đa</label>
          <input v-model.number="form.max_participants" type="number" min="2" max="500"
            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
        </div>
        <p v-if="createError" class="text-xs text-red-500">{{ createError }}</p>
      </div>
      <template #footer>
        <button @click="createModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="doCreate" :disabled="creating"
          class="px-4 py-2 text-sm rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white font-semibold disabled:opacity-60">
          {{ creating ? 'Đang tạo...' : 'Tạo phòng học' }}
        </button>
      </template>
    </AppModal>

    <!-- Bulk create confirm modal -->
    <AppModal v-model="bulkModal" title="Tạo phòng học cho tất cả lớp" size="sm">
      <!-- Checking -->
      <div v-if="bulkChecking" class="flex items-center gap-2 text-sm text-gray-500 py-2">
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        Đang kiểm tra danh sách lớp...
      </div>
      <!-- All covered -->
      <div v-else-if="bulkMissing.length === 0" class="flex items-start gap-3 py-1">
        <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-gray-700">Tất cả lớp học đã có phòng học rồi, không cần tạo thêm.</p>
      </div>
      <!-- Has missing -->
      <div v-else class="space-y-3">
        <p class="text-sm text-gray-600">Còn <strong class="text-[#d63015]">{{ bulkMissing.length }} lớp</strong> chưa có phòng học thường trực:</p>
        <ul class="text-sm text-gray-700 space-y-1 max-h-48 overflow-y-auto pr-1">
          <li v-for="c in bulkMissing" :key="c.id" class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"/>
            <span>{{ c.name }}<span v-if="c.teacher" class="text-gray-400"> — GV. {{ c.teacher }}</span></span>
          </li>
        </ul>
      </div>
      <template #footer>
        <button @click="bulkModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">
          {{ bulkMissing.length === 0 && !bulkChecking ? 'Đóng' : 'Hủy' }}
        </button>
        <button v-if="bulkMissing.length > 0" @click="doBulkCreate" :disabled="bulkCreating"
          class="px-4 py-2 text-sm rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white font-semibold disabled:opacity-60">
          {{ bulkCreating ? 'Đang tạo...' : `Tạo ${bulkMissing.length} phòng` }}
        </button>
      </template>
    </AppModal>

    <!-- Delete confirm modal -->
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
import { useRouter } from 'vue-router'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import Pagination from '@components/common/Pagination.vue'
import { useToastStore } from '@stores/toast'

const router = useRouter()
const toast = useToastStore()
const sessions = ref([])
const loading = ref(true)
const search = ref('')
const filterStatus = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })

const deleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

const createModal = ref(false)
const creating = ref(false)
const createError = ref('')
const classrooms = ref([])

const form = ref({ classroom_id: '', title: '', description: '', scheduled_at: '', duration_minutes: 45, max_participants: 50 })

const bulkModal = ref(false)
const bulkChecking = ref(false)
const bulkCreating = ref(false)
const bulkMissing = ref([])

let debounceTimer = null
function debounceFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 400) }
function formatDate(d) { return d ? new Date(d).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—' }
const liveStatusLabel = (s) => ({ scheduled: 'Đã lên lịch', live: 'Đang diễn ra', ended: 'Đã kết thúc', cancelled: 'Đã hủy' }[s] ?? s)
const liveStatusClass = (s) => ({ scheduled: 'bg-blue-100 text-blue-700', live: 'bg-green-100 text-green-700 animate-pulse', ended: 'bg-gray-100 text-gray-500', cancelled: 'bg-red-100 text-red-500' }[s] ?? 'bg-gray-100 text-gray-500')

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/admin/content/live-sessions', { params: { search: search.value, status: filterStatus.value, page } })
    sessions.value = data.data
    meta.value = data.meta ?? { total: data.total, current_page: data.current_page, last_page: data.last_page }
  } finally { loading.value = false }
}

async function loadClassrooms() {
  if (classrooms.value.length) return
  try {
    const { data } = await api.get('/admin/classrooms', { params: { per_page: 999 } })
    classrooms.value = data.data ?? data
  } catch {}
}

function onClassroomChange() {
  const cls = classrooms.value.find(c => c.id === form.value.classroom_id)
  if (cls && !form.value.title) form.value.title = `Buổi học trực tuyến ${cls.name}`
}

async function openCreate() {
  form.value = { classroom_id: '', title: '', description: '', scheduled_at: '', duration_minutes: 45, max_participants: 50 }
  createError.value = ''
  await loadClassrooms()
  createModal.value = true
}

async function doCreate() {
  if (!form.value.title.trim()) { createError.value = 'Vui lòng nhập tên phòng học'; return }
  creating.value = true; createError.value = ''
  try {
    await api.post('/admin/content/live-sessions', form.value)
    createModal.value = false
    fetchData(1)
  } catch (e) {
    createError.value = e.response?.data?.message ?? 'Tạo thất bại'
  } finally { creating.value = false }
}

async function openBulkCreate() {
  bulkMissing.value = []
  bulkChecking.value = true
  bulkModal.value = true
  try {
    const { data } = await api.get('/admin/content/live-sessions/coverage')
    bulkMissing.value = data.data?.missing ?? []
  } catch {
    bulkMissing.value = []
  } finally {
    bulkChecking.value = false
  }
}

async function doBulkCreate() {
  bulkCreating.value = true
  try {
    const { data } = await api.post('/admin/content/live-sessions/create-for-all')
    const createdFor = data.data?.created_for ?? []
    bulkModal.value = false
    fetchData(1)
    if (createdFor.length > 0) {
      toast.success(`Đã tạo phòng học cho ${createdFor.length} lớp`)
    }
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Tạo thất bại')
  } finally { bulkCreating.value = false }
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
