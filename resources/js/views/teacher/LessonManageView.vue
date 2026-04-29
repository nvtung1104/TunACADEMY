<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="filters.classroom_id" @change="fetch" class="input w-44">
          <option value="">Tất cả lớp</option>
          <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="filters.status" @change="fetch" class="input w-40">
          <option value="">Tất cả trạng thái</option>
          <option value="draft">Bản nháp</option>
          <option value="published">Đã đăng</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo bài học
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bài học</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Lớp / Môn</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Ngày tạo</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="lessons.length === 0">
            <td colspan="5" class="py-14 text-center text-gray-400">
              <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              Chưa có bài học nào
            </td>
          </tr>
          <tr v-for="l in lessons" :key="l.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <p class="font-medium text-gray-900">{{ l.title }}</p>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span class="text-[10px] px-1.5 py-0.5 rounded-md font-medium" :class="visibilityClass(l.visibility)">
                  {{ visibilityLabel(l.visibility) }}
                </span>
                <p v-if="l.description" class="text-xs text-gray-400 truncate max-w-xs">{{ l.description }}</p>
              </div>
            </td>
            <td class="px-5 py-3 hidden md:table-cell">
              <p class="text-sm text-gray-700">{{ l.classroom?.name ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ l.subject?.name ?? '—' }}</p>
            </td>
            <td class="px-5 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium"
                :class="l.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                {{ l.status === 'published' ? 'Đã đăng' : 'Bản nháp' }}
              </span>
            </td>
            <td class="px-5 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(l.created_at) }}</td>
            <td class="px-5 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button v-if="l.status === 'draft'" @click="publishLesson(l)"
                  class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors" title="Đăng bài">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
                <button @click="openShare(l)" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-500 transition-colors" title="Chia sẻ">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                  </svg>
                </button>
                <button @click="openEdit(l)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="Chỉnh sửa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="deleteLesson(l)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Xóa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="pagination.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ pagination.total }} bài học</p>
        <div class="flex gap-1">
          <button v-for="p in pagination.last_page" :key="p" @click="goPage(p)"
            class="w-8 h-8 rounded-lg text-xs font-medium transition-colors"
            :class="p === pagination.current_page ? 'bg-[#d63015] text-white' : 'hover:bg-gray-100 text-gray-600'">
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa bài học' : 'Tạo bài học mới'" size="md">
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" placeholder="Tên bài học" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
          <textarea v-model="form.description" class="input resize-none" rows="2" placeholder="Mô tả ngắn về bài học"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lớp học</label>
            <select v-model="form.classroom_id" class="input" @change="loadSubjects">
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
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select v-model="form.status" class="input">
              <option value="draft">Bản nháp</option>
              <option value="published">Đăng ngay</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hiển thị</label>
            <select v-model="form.visibility" class="input">
              <option value="private">Riêng tư</option>
              <option value="class">Cho lớp</option>
              <option value="public">Công khai</option>
            </select>
          </div>
        </div>
        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-medium">
          {{ saving ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </template>
    </AppModal>

    <!-- Share Modal -->
    <AppModal v-model="shareModal" title="Chia sẻ bài học" size="sm">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Chế độ chia sẻ</label>
          <div class="space-y-2">
            <label v-for="opt in visibilityOptions" :key="opt.value"
              class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
              :class="shareForm.visibility === opt.value ? 'border-[#d63015] bg-red-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" :value="opt.value" v-model="shareForm.visibility" class="mt-0.5 text-[#d63015]" />
              <div>
                <p class="text-sm font-medium text-gray-800">{{ opt.label }}</p>
                <p class="text-xs text-gray-400">{{ opt.desc }}</p>
              </div>
            </label>
          </div>
        </div>
        <div v-if="shareForm.visibility === 'class'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Chọn lớp</label>
          <select v-model="shareForm.classroom_id" class="input">
            <option value="">Chọn lớp</option>
            <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div v-if="shareForm.visibility === 'selected'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Mã học sinh (cách nhau bởi dấu phẩy)</label>
          <input v-model="shareForm.student_codes" class="input" placeholder="VD: HS001, HS002" />
        </div>
        <div v-if="shareError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ shareError }}</div>
      </div>
      <template #footer>
        <button @click="shareModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="doShare" :disabled="sharing" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-medium">
          {{ sharing ? 'Đang lưu...' : 'Lưu chia sẻ' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const lessons = ref([])
const classrooms = ref([])
const subjects = ref([])
const loading = ref(true)
const modal = ref(false)
const shareModal = ref(false)
const editItem = ref(null)
const shareTarget = ref(null)
const saving = ref(false)
const sharing = ref(false)
const formError = ref('')
const shareError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ classroom_id: '', status: '', page: 1 })
const form = reactive({ title: '', description: '', classroom_id: '', subject_id: '', status: 'draft', visibility: 'private' })
const shareForm = reactive({ visibility: 'class', classroom_id: '', student_codes: '' })

const visibilityOptions = [
  { value: 'private',  label: 'Riêng tư',     desc: 'Chỉ bạn mới thấy' },
  { value: 'class',    label: 'Cho lớp',       desc: 'Chia sẻ cho một lớp học cụ thể' },
  { value: 'public',   label: 'Công khai',     desc: 'Tất cả học sinh đều có thể tìm thấy' },
  { value: 'selected', label: 'Chọn học sinh', desc: 'Chia sẻ cho từng học sinh theo mã' },
]

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/lessons', { params: filters })
    lessons.value = data.data?.data ?? []
    pagination.value = { current_page: data.data?.current_page ?? 1, last_page: data.data?.last_page ?? 1, total: data.data?.total ?? 0 }
  } finally { loading.value = false }
}

function goPage(p) { filters.page = p; fetch() }

function openCreate() {
  editItem.value = null
  Object.assign(form, { title: '', description: '', classroom_id: '', subject_id: '', status: 'draft', visibility: 'private' })
  subjects.value = []
  formError.value = ''
  modal.value = true
}

function openEdit(l) {
  editItem.value = l
  Object.assign(form, { title: l.title, description: l.description ?? '', classroom_id: l.classroom?.id ?? '', subject_id: l.subject?.id ?? '', status: l.status, visibility: l.visibility ?? 'private' })
  loadSubjects()
  formError.value = ''
  modal.value = true
}

function openShare(l) {
  shareTarget.value = l
  Object.assign(shareForm, { visibility: l.visibility ?? 'class', classroom_id: l.classroom?.id ?? '', student_codes: '' })
  shareError.value = ''
  shareModal.value = true
}

async function loadSubjects() {
  if (!form.classroom_id) { subjects.value = []; return }
  try {
    const { data } = await api.get('/admin/subjects', { params: { status: 'active' } })
    subjects.value = data.data ?? []
  } catch { subjects.value = [] }
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.classroom_id) delete payload.classroom_id
    if (editItem.value) await api.put(`/teacher/lessons/${editItem.value.id}`, payload)
    else await api.post('/teacher/lessons', payload)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function doShare() {
  sharing.value = true; shareError.value = ''
  try {
    const payload = { visibility: shareForm.visibility }
    if (shareForm.visibility === 'class') payload.classroom_id = shareForm.classroom_id
    if (shareForm.visibility === 'selected') payload.student_codes = shareForm.student_codes.split(',').map(s => s.trim()).filter(Boolean)
    await api.post(`/teacher/lessons/${shareTarget.value.id}/share`, payload)
    shareModal.value = false; fetch()
  } catch (e) { shareError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { sharing.value = false }
}

async function publishLesson(l) {
  try { await api.post(`/teacher/lessons/${l.id}/publish`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể đăng bài') }
}

async function deleteLesson(l) {
  if (!confirm(`Xóa bài học "${l.title}"?`)) return
  try { await api.delete(`/teacher/lessons/${l.id}`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể xóa') }
}

function visibilityLabel(v) {
  return { public: 'Công khai', private: 'Riêng tư', class: 'Lớp học', selected: 'Được chọn' }[v] ?? v
}
function visibilityClass(v) {
  return { public: 'bg-green-50 text-green-600', private: 'bg-gray-100 text-gray-500', class: 'bg-blue-50 text-blue-600', selected: 'bg-violet-50 text-violet-600' }[v] ?? ''
}
function formatDate(iso) { return iso ? new Date(iso).toLocaleDateString('vi-VN') : '' }

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
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
