<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Quản lý đề thi</h2>
        <p class="text-sm text-gray-400 mt-0.5">Tất cả đề thi trong hệ thống</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ meta.total ?? 0 }} đề thi</span>
        <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10]">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Thêm đề thi
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên đề thi..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
      </div>
      <select v-model="filterType" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white">
        <option value="">Tất cả loại</option>
        <option value="quiz_15">Kiểm tra 15p</option>
        <option value="quiz_30">Kiểm tra 30p</option>
        <option value="quiz_45">Kiểm tra 45p</option>
        <option value="practice_exam">Đề ôn tập</option>
      </select>
      <select v-model="filterStatus" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white">
        <option value="">Tất cả trạng thái</option>
        <option value="published">Đã xuất bản</option>
        <option value="draft">Bản nháp</option>
        <option value="closed">Đã đóng</option>
      </select>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-16 text-center">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
        <p class="text-sm text-gray-400">Đang tải...</p>
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Đề thi</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Loại</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Giáo viên</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Lớp</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden xl:table-cell">Bắt đầu</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden xl:table-cell">Kết thúc</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
            <th class="px-5 py-3 w-24"/>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="!exams.length"><td colspan="8" class="py-12 text-center text-gray-400">Không có đề thi nào</td></tr>
          <tr v-for="e in exams" :key="e.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                  :style="{ backgroundColor: (e.subject?.color || '#10b981') + '20', color: e.subject?.color || '#10b981' }">
                  {{ e.subject?.name?.[0] }}
                </div>
                <div>
                  <button @click="$router.push(`/admin/exams/${e.id}`)"
                    class="font-medium text-gray-800 hover:text-[#d63015] transition-colors text-left line-clamp-1">
                    {{ e.title }}
                  </button>
                  <p class="text-xs text-gray-400">{{ e.subject?.name }} · {{ e.duration_minutes }} phút</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 hidden sm:table-cell">
              <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-semibold" :class="typeClass(e.type)">
                {{ typeLabel(e.type) }}
              </span>
            </td>
            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ e.teacher?.name ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-600 hidden lg:table-cell">{{ e.classroom?.name ?? '—' }}</td>
            <td class="px-5 py-3 hidden xl:table-cell">
              <template v-if="e.type === 'practice_exam'">
                <span class="text-xs text-green-600 font-medium">Luôn mở</span>
              </template>
              <template v-else>
                <span class="text-xs text-gray-700">{{ e.opened_at ? formatDateTime(e.opened_at) : '—' }}</span>
              </template>
            </td>
            <td class="px-5 py-3 hidden xl:table-cell">
              <template v-if="e.type === 'practice_exam'">
                <span class="text-xs text-gray-300">—</span>
              </template>
              <template v-else>
                <span class="text-xs text-gray-700">{{ e.closed_at ? formatDateTime(e.closed_at) : '—' }}</span>
              </template>
            </td>
            <td class="px-5 py-3">
              <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(e.status)">
                {{ statusLabel(e.status) }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button @click="openEdit(e)" class="p-1.5 rounded-lg text-gray-400 hover:text-[#d63015] hover:bg-red-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button @click="confirmDelete(e)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :meta="meta" @page="fetchData"/>

    <!-- Create / Edit Modal -->
    <AppModal v-model="formModal" :title="editItem ? 'Chỉnh sửa đề thi' : 'Thêm đề thi mới'">
      <form class="space-y-4" @submit.prevent="save">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Môn học <span class="text-red-500">*</span></label>
            <select v-model="form.subject_id" class="input" required>
              <option value="">Chọn môn</option>
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Lớp học</label>
            <select v-model="form.classroom_id" class="input">
              <option value="">Không gán lớp</option>
              <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" placeholder="Nhập tên đề thi" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Loại đề <span class="text-red-500">*</span></label>
            <select v-model="form.type" class="input" required>
              <option value="quiz_15">Kiểm tra 15 phút</option>
              <option value="quiz_30">Kiểm tra 30 phút</option>
              <option value="quiz_45">Kiểm tra 45 phút</option>
              <option value="practice_exam">Đề ôn tập</option>
            </select>
          </div>
          <div v-if="form.type !== 'practice_exam'">
            <label class="label">Thời gian (phút)</label>
            <input v-model.number="form.duration_minutes" type="number" min="5" max="300" class="input" />
          </div>
        </div>
        <div v-if="form.type !== 'practice_exam'" class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Thời gian mở</label>
            <input v-model="form.opened_at" type="datetime-local" class="input" />
          </div>
          <div>
            <label class="label">Thời gian đóng</label>
            <input v-model="form.closed_at" type="datetime-local" class="input" />
          </div>
        </div>
        <div>
          <label class="label">Mô tả</label>
          <textarea v-model="form.description" class="input" rows="2" placeholder="Mô tả đề thi"/>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Trạng thái</label>
            <select v-model="form.status" class="input">
              <option value="draft">Bản nháp</option>
              <option value="published">Xuất bản</option>
              <option value="closed">Đóng</option>
            </select>
          </div>
          <div>
            <label class="label">Hiển thị</label>
            <select v-model="form.visibility" class="input">
              <option value="class">Theo lớp</option>
              <option value="public">Công khai</option>
              <option value="private">Riêng tư</option>
            </select>
          </div>
        </div>
        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="formModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 text-sm rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white font-semibold disabled:opacity-60">
          {{ saving ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </template>
    </AppModal>

    <AppModal v-model="deleteModal" title="Xác nhận xóa" size="sm">
      <p class="text-sm text-gray-600">Bạn có chắc muốn xóa đề thi <strong>{{ deleteTarget?.title }}</strong>?</p>
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
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import Pagination from '@components/common/Pagination.vue'

const exams = ref([])
const loading = ref(true)
const search = ref('')
const filterStatus = ref('')
const filterType = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })

const formModal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const form = reactive({
  subject_id: '', classroom_id: '', type: 'quiz_15', title: '', description: '',
  duration_minutes: 15, opened_at: '', closed_at: '',
  status: 'draft', visibility: 'class',
})

const deleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

const classrooms = ref([])
const subjects = ref([])

let debounceTimer = null
function debounceFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 400) }

const formatDateTime = (iso) => iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '—'
const typeLabel = (t) => ({ quiz_15: 'KT 15p', quiz_30: 'KT 30p', quiz_45: 'KT 45p', practice_exam: 'Ôn tập' }[t] ?? t ?? '—')
const typeClass = (t) => ({ quiz_15: 'bg-amber-100 text-amber-700', quiz_30: 'bg-blue-100 text-blue-700', quiz_45: 'bg-purple-100 text-purple-700', practice_exam: 'bg-green-100 text-green-700' }[t] ?? 'bg-gray-100 text-gray-500')
const statusLabel = (s) => ({ published: 'Đã xuất bản', draft: 'Bản nháp', closed: 'Đã đóng' }[s] ?? s)
const statusClass = (s) => ({ published: 'bg-green-100 text-green-700', draft: 'bg-gray-100 text-gray-500', closed: 'bg-red-100 text-red-600' }[s] ?? 'bg-gray-100 text-gray-500')

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/admin/content/exams', { params: { search: search.value, status: filterStatus.value, type: filterType.value, page } })
    exams.value = data.data
    meta.value = data.meta ?? { total: data.total, current_page: data.current_page, last_page: data.last_page }
  } finally { loading.value = false }
}

async function loadOptions() {
  const [cls, sub] = await Promise.all([
    api.get('/admin/classrooms', { params: { per_page: 200 } }),
    api.get('/admin/subjects'),
  ])
  classrooms.value = cls.data.data ?? cls.data
  subjects.value = sub.data.data ?? sub.data
}

function openCreate() {
  editItem.value = null
  Object.assign(form, {
    subject_id: '', classroom_id: '', type: 'quiz_15', title: '', description: '',
    duration_minutes: 15, opened_at: '', closed_at: '', status: 'draft', visibility: 'class',
  })
  formError.value = ''
  formModal.value = true
}

function openEdit(e) {
  editItem.value = e
  Object.assign(form, {
    subject_id: e.subject?.id ?? e.subject_id ?? '',
    classroom_id: e.classroom?.id ?? e.classroom_id ?? '',
    type: e.type,
    title: e.title,
    description: e.description ?? '',
    duration_minutes: e.duration_minutes ?? 15,
    opened_at: e.opened_at ? e.opened_at.slice(0, 16) : '',
    closed_at: e.closed_at ? e.closed_at.slice(0, 16) : '',
    status: e.status ?? 'draft',
    visibility: e.visibility ?? 'class',
  })
  formError.value = ''
  formModal.value = true
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    const payload = { ...form }
    if (payload.type === 'practice_exam') { delete payload.opened_at; delete payload.closed_at }
    if (editItem.value) await api.put(`/admin/content/exams/${editItem.value.id}`, payload)
    else await api.post('/admin/content/exams', payload)
    formModal.value = false
    fetchData(meta.value.current_page)
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) formError.value = Object.values(errors).flat().join(' ')
    else formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally { saving.value = false }
}

function confirmDelete(item) { deleteTarget.value = item; deleteModal.value = true }

async function doDelete() {
  deleting.value = true
  try {
    await api.delete(`/admin/content/exams/${deleteTarget.value.id}`)
    deleteModal.value = false
    fetchData(meta.value.current_page)
  } finally { deleting.value = false }
}

onMounted(() => { fetchData(); loadOptions() })
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm bg-white; }
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
</style>
