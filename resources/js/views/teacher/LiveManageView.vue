<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex justify-between items-center">
      <p class="text-sm text-gray-500">Quản lý phòng học trực tuyến</p>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo phòng
      </button>
    </div>

    <!-- Sessions -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-if="sessions.length === 0" class="col-span-full py-16 text-center text-gray-400">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        Chưa có phòng học nào
      </div>
      <div v-for="s in sessions" :key="s.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all">
        <!-- Status indicator -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1 min-w-0 pr-2">
            <h3 class="font-semibold text-gray-900 truncate">{{ s.title }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ s.classroom?.name }}</p>
          </div>
          <span class="px-2 py-1 rounded-full text-xs font-medium shrink-0" :class="sessionStatusClass(s.status)">
            {{ sessionStatusLabel(s.status) }}
          </span>
        </div>

        <p v-if="s.description" class="text-xs text-gray-500 mb-3 line-clamp-2">{{ s.description }}</p>

        <!-- Info -->
        <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            {{ s.participants_count ?? 0 }} tham gia
          </span>
          <span v-if="s.started_at" class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ formatDate(s.started_at) }}
          </span>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <button v-if="s.status === 'scheduled'" @click="startSession(s)"
            class="flex-1 py-1.5 rounded-xl bg-green-500 text-white text-xs font-medium hover:bg-green-600 transition-colors">
            Bắt đầu
          </button>
          <button v-if="s.status === 'live'" @click="endSession(s)"
            class="flex-1 py-1.5 rounded-xl bg-red-500 text-white text-xs font-medium hover:bg-red-600 transition-colors">
            Kết thúc
          </button>
          <button v-if="s.status === 'live'" @click="joinSession(s)"
            class="flex-1 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition-colors">
            Vào phòng
          </button>
          <button @click="openEdit(s)" class="py-1.5 px-3 rounded-xl border border-gray-200 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
            Sửa
          </button>
          <button @click="deleteSession(s)" class="py-1.5 px-3 rounded-xl border border-red-200 text-xs text-red-500 hover:bg-red-50 transition-colors">
            Xóa
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa phòng học' : 'Tạo phòng học mới'" size="md">
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" required placeholder="VD: Buổi học Toán - 10A1" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
          <textarea v-model="form.description" class="input resize-none" rows="2"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Lớp học <span class="text-red-500">*</span></label>
          <select v-model="form.classroom_id" class="input" required>
            <option value="">Chọn lớp</option>
            <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian dự kiến</label>
          <input v-model="form.scheduled_at" type="datetime-local" class="input" />
        </div>
        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-60 font-medium">
          {{ saving ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const router = useRouter()
const sessions = ref([])
const classrooms = ref([])
const loading = ref(true)
const modal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const form = reactive({ title: '', description: '', classroom_id: '', scheduled_at: '' })

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/live-sessions')
    sessions.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { title: '', description: '', classroom_id: '', scheduled_at: '' })
  formError.value = ''
  modal.value = true
}

function openEdit(s) {
  editItem.value = s
  Object.assign(form, { title: s.title, description: s.description ?? '', classroom_id: s.classroom?.id ?? '', scheduled_at: s.scheduled_at ? s.scheduled_at.slice(0, 16) : '' })
  formError.value = ''
  modal.value = true
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.scheduled_at) delete payload.scheduled_at
    if (editItem.value) await api.put(`/teacher/live-sessions/${editItem.value.id}`, payload)
    else await api.post('/teacher/live-sessions', payload)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function startSession(s) {
  try { await api.post(`/teacher/live-sessions/${s.id}/start`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể bắt đầu') }
}

async function endSession(s) {
  if (!confirm('Kết thúc phiên học trực tuyến này?')) return
  try { await api.post(`/teacher/live-sessions/${s.id}/end`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể kết thúc') }
}

function joinSession(s) {
  router.push(`/live/rooms/${s.id}`)
}

async function deleteSession(s) {
  if (!confirm(`Xóa phòng học "${s.title}"?`)) return
  try { await api.delete(`/teacher/live-sessions/${s.id}`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể xóa') }
}

function sessionStatusLabel(s) { return { scheduled: 'Chờ bắt đầu', live: 'Đang học', ended: 'Đã kết thúc' }[s] ?? s }
function sessionStatusClass(s) { return { scheduled: 'bg-blue-100 text-blue-700', live: 'bg-green-100 text-green-700 animate-pulse', ended: 'bg-gray-100 text-gray-500' }[s] ?? '' }
function formatDate(iso) { return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '' }

onMounted(async () => {
  const { data } = await api.get('/teacher/classrooms')
  classrooms.value = data.data?.data ?? data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm; }
</style>
