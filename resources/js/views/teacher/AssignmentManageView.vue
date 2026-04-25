<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2">
        <select v-model="filters.classroom_id" @change="fetch" class="input w-44">
          <option value="">Tất cả lớp</option>
          <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Giao bài tập
      </button>
    </div>

    <!-- Cards grid -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-if="assignments.length === 0" class="col-span-full py-16 text-center text-gray-400">
        <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
        </svg>
        Chưa có bài tập nào
      </div>
      <div v-for="a in assignments" :key="a.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all group">
        <!-- Due date badge -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1 min-w-0 pr-2">
            <h3 class="font-semibold text-gray-900 truncate">{{ a.title }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ a.classroom?.name }} · {{ a.subject?.name }}</p>
          </div>
          <span class="text-xs px-2 py-1 rounded-full shrink-0" :class="dueBadgeClass(a.due_date)">
            {{ dueBadgeLabel(a.due_date) }}
          </span>
        </div>

        <p v-if="a.description" class="text-xs text-gray-500 mb-3 line-clamp-2">{{ a.description }}</p>

        <!-- Stats -->
        <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ a.due_date ? formatDate(a.due_date) : 'Không hạn' }}
          </span>
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            {{ a.submissions_count ?? 0 }} nộp
          </span>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
          <button @click="openSubmissions(a)" class="flex-1 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 text-xs font-medium hover:bg-indigo-100 transition-colors">
            Xem nộp bài
          </button>
          <button @click="openEdit(a)" class="py-1.5 px-3 rounded-xl border border-gray-200 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
            Sửa
          </button>
          <button @click="deleteAssignment(a)" class="py-1.5 px-3 rounded-xl border border-red-200 text-xs text-red-500 hover:bg-red-50 transition-colors">
            Xóa
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa bài tập' : 'Giao bài tập mới'" size="md">
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" required placeholder="Tên bài tập" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả / Đề bài</label>
          <textarea v-model="form.description" class="input resize-none" rows="3" placeholder="Nội dung bài tập, hướng dẫn..."></textarea>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Môn học</label>
            <select v-model="form.subject_id" class="input">
              <option value="">Chọn môn</option>
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Hạn nộp</label>
          <input v-model="form.due_date" type="datetime-local" class="input" />
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

    <!-- Submissions Modal -->
    <AppModal v-model="subModal" title="Danh sách nộp bài" size="lg">
      <div v-if="loadingSubs" class="py-8 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>Đang tải...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Học sinh</th>
            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Thời gian nộp</th>
            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Điểm</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="submissions.length === 0"><td colspan="3" class="py-8 text-center text-gray-400">Chưa có bài nộp</td></tr>
          <tr v-for="s in submissions" :key="s.id" class="hover:bg-gray-50">
            <td class="px-4 py-2.5">
              <p class="font-medium text-gray-800">{{ s.student?.name }}</p>
            </td>
            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ formatDate(s.submitted_at) }}</td>
            <td class="px-4 py-2.5">
              <span v-if="s.score != null" class="font-bold" :class="s.score >= 5 ? 'text-green-600' : 'text-red-500'">{{ s.score }}</span>
              <span v-else class="text-gray-400 text-xs">Chưa chấm</span>
            </td>
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

const assignments = ref([])
const classrooms = ref([])
const subjects = ref([])
const submissions = ref([])
const loading = ref(true)
const modal = ref(false)
const subModal = ref(false)
const loadingSubs = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const filters = reactive({ classroom_id: '' })
const form = reactive({ title: '', description: '', classroom_id: '', subject_id: '', due_date: '' })

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/assignments', { params: filters })
    assignments.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { title: '', description: '', classroom_id: '', subject_id: '', due_date: '' })
  formError.value = ''
  modal.value = true
}

function openEdit(a) {
  editItem.value = a
  Object.assign(form, { title: a.title, description: a.description ?? '', classroom_id: a.classroom?.id ?? '', subject_id: a.subject?.id ?? '', due_date: a.due_date ? a.due_date.slice(0, 16) : '' })
  formError.value = ''
  modal.value = true
}

async function openSubmissions(a) {
  subModal.value = true
  loadingSubs.value = true
  submissions.value = []
  try {
    const { data } = await api.get(`/teacher/assignments/${a.id}/submissions`)
    submissions.value = data.data ?? []
  } finally { loadingSubs.value = false }
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.due_date) delete payload.due_date
    if (!payload.subject_id) delete payload.subject_id
    if (editItem.value) await api.put(`/teacher/assignments/${editItem.value.id}`, payload)
    else await api.post('/teacher/assignments', payload)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function deleteAssignment(a) {
  if (!confirm(`Xóa bài tập "${a.title}"?`)) return
  try { await api.delete(`/teacher/assignments/${a.id}`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể xóa') }
}

function formatDate(iso) { return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '' }

function dueBadgeClass(due) {
  if (!due) return 'bg-gray-100 text-gray-500'
  const diff = new Date(due) - new Date()
  if (diff < 0) return 'bg-red-100 text-red-600'
  if (diff < 86400000) return 'bg-amber-100 text-amber-600'
  return 'bg-green-100 text-green-600'
}

function dueBadgeLabel(due) {
  if (!due) return 'Không hạn'
  const diff = new Date(due) - new Date()
  if (diff < 0) return 'Đã hết hạn'
  if (diff < 86400000) return 'Sắp hết hạn'
  return 'Còn hạn'
}

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
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm; }
</style>
