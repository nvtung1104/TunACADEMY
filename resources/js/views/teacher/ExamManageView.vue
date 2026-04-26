<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="filters.status" @change="fetch" class="input w-40">
          <option value="">Tất cả trạng thái</option>
          <option value="draft">Bản nháp</option>
          <option value="published">Đang mở</option>
          <option value="closed">Đã đóng</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo đề kiểm tra
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Đề kiểm tra</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lớp / Môn</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời gian</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="exams.length === 0">
            <td colspan="5" class="py-14 text-center text-gray-400">
              <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
              Chưa có đề kiểm tra nào
            </td>
          </tr>
          <tr v-for="e in exams" :key="e.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <p class="font-medium text-gray-900">{{ e.title }}</p>
              <div class="flex items-center gap-3 mt-1">
                <span class="text-xs text-gray-400">{{ e.question_count ?? 0 }} câu hỏi</span>
                <span class="text-xs text-gray-400">{{ e.duration_minutes }} phút</span>
              </div>
            </td>
            <td class="px-5 py-3">
              <p class="text-sm text-gray-700">{{ e.classroom?.name ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ e.subject?.name ?? '—' }}</p>
            </td>
            <td class="px-5 py-3">
              <p class="text-xs text-gray-600">{{ e.opened_at ? formatDate(e.opened_at) : 'Chưa mở' }}</p>
              <p v-if="e.closed_at" class="text-xs text-gray-400">→ {{ formatDate(e.closed_at) }}</p>
            </td>
            <td class="px-5 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(e.status)">
                {{ statusLabel(e.status) }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button v-if="e.status === 'draft'" @click="publishExam(e)"
                  class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors" title="Mở đề thi">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
                <button @click="openResults(e)" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Xem kết quả">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </button>
                <button @click="openEdit(e)" class="p-1.5 rounded-lg hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition-colors" title="Chỉnh sửa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="deleteExam(e)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Xóa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa đề kiểm tra' : 'Tạo đề kiểm tra mới'" size="lg">
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" required placeholder="Tên đề kiểm tra" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
          <textarea v-model="form.description" class="input resize-none" rows="2" placeholder="Mô tả đề thi"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lớp học <span class="text-red-500">*</span></label>
            <select v-model="form.classroom_id" class="input" required>
              <option value="">Chọn lớp</option>
              <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Môn học <span class="text-red-500">*</span></label>
            <select v-model="form.subject_id" class="input" required>
              <option value="">Chọn môn</option>
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian (phút) <span class="text-red-500">*</span></label>
            <input v-model.number="form.duration_minutes" type="number" min="5" max="180" class="input" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mở từ</label>
            <input v-model="form.opened_at" type="datetime-local" class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Đóng lúc</label>
            <input v-model="form.closed_at" type="datetime-local" class="input" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.shuffle_questions" class="rounded text-indigo-600" />
            <span class="text-sm text-gray-700">Xáo trộn câu hỏi</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.shuffle_options" class="rounded text-indigo-600" />
            <span class="text-sm text-gray-700">Xáo trộn đáp án</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.proctoring_enabled" class="rounded text-indigo-600" />
            <span class="text-sm text-gray-700">Bật giám sát</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.allow_retake" class="rounded text-indigo-600" />
            <span class="text-sm text-gray-700">Cho phép thi lại</span>
          </label>
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

    <!-- Results Modal -->
    <AppModal v-model="resultsModal" title="Kết quả kiểm tra" size="lg">
      <div v-if="loadingResults" class="py-8 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>Đang tải...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Học sinh</th>
            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Điểm</th>
            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Thời gian nộp</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="examResults.length === 0"><td colspan="3" class="py-8 text-center text-gray-400">Chưa có kết quả</td></tr>
          <tr v-for="r in examResults" :key="r.id" class="hover:bg-gray-50">
            <td class="px-4 py-2.5">
              <p class="font-medium text-gray-800">{{ r.student?.name }}</p>
              <p class="text-xs text-gray-400">{{ r.student?.email }}</p>
            </td>
            <td class="px-4 py-2.5">
              <span class="font-bold text-lg" :class="(r.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
                {{ r.score != null ? r.score : '—' }}
              </span>
              <span class="text-gray-400 text-xs">/10</span>
            </td>
            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ r.submitted_at ? formatDate(r.submitted_at) : '—' }}</td>
          </tr>
        </tbody>
      </table>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const exams = ref([])
const classrooms = ref([])
const subjects = ref([])
const loading = ref(true)
const modal = ref(false)
const resultsModal = ref(false)
const loadingResults = ref(false)
const examResults = ref([])
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const filters = reactive({ status: '' })
const form = reactive({
  title: '', description: '', classroom_id: '', subject_id: '',
  duration_minutes: 45, opened_at: '', closed_at: '',
  shuffle_questions: false, shuffle_options: false,
  proctoring_enabled: false, allow_retake: false,
})

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/exams', { params: filters })
    exams.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { title: '', description: '', classroom_id: '', subject_id: '', duration_minutes: 45, opened_at: '', closed_at: '', shuffle_questions: false, shuffle_options: false, proctoring_enabled: false, allow_retake: false })
  formError.value = ''
  modal.value = true
}

function openEdit(e) {
  editItem.value = e
  Object.assign(form, { title: e.title, description: e.description ?? '', classroom_id: e.classroom?.id ?? '', subject_id: e.subject?.id ?? '', duration_minutes: e.duration_minutes, opened_at: e.opened_at ? e.opened_at.slice(0, 16) : '', closed_at: e.closed_at ? e.closed_at.slice(0, 16) : '', shuffle_questions: e.shuffle_questions ?? false, shuffle_options: e.shuffle_options ?? false, proctoring_enabled: e.proctoring_enabled ?? false, allow_retake: e.allow_retake ?? false })
  formError.value = ''
  modal.value = true
}

async function openResults(e) {
  resultsModal.value = true
  loadingResults.value = true
  examResults.value = []
  try {
    const { data } = await api.get(`/teacher/exams/${e.id}/attempts`)
    examResults.value = data.data?.data ?? data.data ?? []
  } finally { loadingResults.value = false }
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.opened_at) delete payload.opened_at
    if (!payload.closed_at) delete payload.closed_at
    if (editItem.value) await api.put(`/teacher/exams/${editItem.value.id}`, payload)
    else await api.post('/teacher/exams', payload)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function publishExam(e) {
  try { await api.post(`/teacher/exams/${e.id}/publish`); fetch() }
  catch (err) { alert(err.response?.data?.message ?? 'Không thể mở đề thi') }
}

async function deleteExam(e) {
  if (!confirm(`Xóa đề kiểm tra "${e.title}"?`)) return
  try { await api.delete(`/teacher/exams/${e.id}`); fetch() }
  catch (err) { alert(err.response?.data?.message ?? 'Không thể xóa') }
}

function statusLabel(s) { return { draft: 'Bản nháp', published: 'Đang mở', closed: 'Đã đóng' }[s] ?? s }
function statusClass(s) { return { draft: 'bg-amber-100 text-amber-700', published: 'bg-green-100 text-green-700', closed: 'bg-gray-100 text-gray-500' }[s] ?? '' }
function formatDate(iso) { return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '' }

onMounted(async () => {
  const [cr, sr] = await Promise.all([
    api.get('/teacher/classrooms'),
    api.get('/admin/subjects', { params: { status: 'active' } }).catch(() => ({ data: { data: [] } })),
  ])
  classrooms.value = cr.data.data?.data ?? cr.data.data ?? []
  subjects.value = sr.data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm; }
</style>
