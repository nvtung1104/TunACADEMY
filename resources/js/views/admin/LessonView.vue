<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Quản lý bài học</h2>
        <p class="text-sm text-gray-400 mt-0.5">Tất cả bài học trong hệ thống</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ meta.total ?? 0 }} bài học</span>
        <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10]">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Thêm bài học
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên bài học..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]"/>
      </div>
      <select v-model="filterStatus" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white">
        <option value="">Tất cả trạng thái</option>
        <option value="published">Đã xuất bản</option>
        <option value="draft">Bản nháp</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-16 text-center">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
        <p class="text-sm text-gray-400">Đang tải...</p>
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bài học</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Giáo viên</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Phạm vi</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
            <th class="px-5 py-3 w-24"/>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="!lessons.length"><td colspan="5" class="py-12 text-center text-gray-400">Không có bài học nào</td></tr>
          <tr v-for="l in lessons" :key="l.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center text-xs font-bold"
                  :style="{ backgroundColor: (l.subject?.color || '#6366f1') + '20', color: l.subject?.color || '#6366f1' }">
                  {{ l.subject?.name?.[0] }}
                </div>
                <div>
                  <p class="font-medium text-gray-800 line-clamp-1">{{ l.title }}</p>
                  <p class="text-xs text-gray-400 flex items-center gap-1.5">
                    {{ l.subject?.name }}
                    <span v-if="l.materials?.length" class="inline-flex items-center gap-0.5 text-blue-500">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                      {{ l.materials.length }} tài liệu
                    </span>
                  </p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ l.teacher?.name ?? '—' }}</td>
            <td class="px-5 py-3 hidden lg:table-cell">
              <span v-if="!l.classroom_id" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                Công khai
              </span>
              <span v-else class="text-xs text-gray-600">{{ l.classroom?.name }}</span>
            </td>
            <td class="px-5 py-3">
              <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                :class="l.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                {{ l.status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button @click="openEdit(l)" class="p-1.5 rounded-lg text-gray-400 hover:text-[#d63015] hover:bg-red-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button @click="confirmDelete(l)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :meta="meta" @page="fetchData" />

    <!-- Create / Edit Modal -->
    <AppModal v-model="formModal" :title="editItem ? 'Chỉnh sửa bài học' : 'Thêm bài học mới'" size="lg">
      <form class="space-y-5" @submit.prevent="save">

        <!-- Phạm vi + Môn -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Lớp học</label>
            <select v-model="form.classroom_id" class="input">
              <option value="">Tất cả lớp (Công khai)</option>
              <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">
              {{ form.classroom_id ? 'Chỉ học sinh lớp này mới xem được' : 'Hiển thị công khai trên trang chủ' }}
            </p>
          </div>
          <div>
            <label class="label">Môn học <span class="text-red-500">*</span></label>
            <select v-model="form.subject_id" class="input" required>
              <option value="">Chọn môn</option>
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>

        <!-- Tiêu đề + Mô tả -->
        <div>
          <label class="label">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" placeholder="Nhập tiêu đề bài học" required />
        </div>
        <div>
          <label class="label">Mô tả</label>
          <textarea v-model="form.description" class="input" rows="2" placeholder="Mô tả ngắn về bài học"/>
        </div>

        <!-- Video + Trạng thái -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Đường dẫn video</label>
            <input v-model="form.video_path" class="input" placeholder="https://..." />
          </div>
          <div>
            <label class="label">Trạng thái</label>
            <select v-model="form.status" class="input">
              <option value="draft">Bản nháp</option>
              <option value="published">Xuất bản</option>
            </select>
          </div>
        </div>

        <!-- Tài liệu đính kèm -->
        <div>
          <label class="label">Tài liệu đính kèm</label>

          <!-- Existing materials (edit mode) -->
          <div v-if="existingMaterials.length" class="mb-2 space-y-1.5">
            <div v-for="m in existingMaterials" :key="m.id"
              class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 border border-gray-100 text-sm">
              <span class="shrink-0 text-base">{{ fileIcon(m.file_type) }}</span>
              <span class="flex-1 truncate text-gray-700">{{ m.file_name }}</span>
              <span class="text-xs text-gray-400">{{ fileSize(m.file_size) }}</span>
              <button type="button" @click="removeExisting(m)"
                class="shrink-0 p-1 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <!-- New files pending upload -->
          <div v-if="newFiles.length" class="mb-2 space-y-1.5">
            <div v-for="(f, i) in newFiles" :key="i"
              class="flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50 border border-blue-100 text-sm">
              <span class="shrink-0 text-base">{{ fileIcon(fileTypeFromName(f.name)) }}</span>
              <span class="flex-1 truncate text-gray-700">{{ f.name }}</span>
              <span class="text-xs text-gray-400">{{ fileSize(f.size) }}</span>
              <button type="button" @click="newFiles.splice(i, 1)"
                class="shrink-0 p-1 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <!-- Drop zone -->
          <label class="flex flex-col items-center justify-center gap-2 px-4 py-5 rounded-xl border-2 border-dashed border-gray-200 hover:border-[#d63015] hover:bg-red-50/30 transition-colors cursor-pointer text-center"
            @dragover.prevent @drop.prevent="onDrop">
            <input type="file" class="hidden" multiple accept=".pdf,.doc,.docx,.ppt,.pptx" @change="onFilePick" />
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <div>
              <p class="text-sm font-medium text-gray-600">Kéo thả hoặc <span class="text-[#d63015]">chọn file</span></p>
              <p class="text-xs text-gray-400 mt-0.5">PDF, Word (.doc/.docx), PowerPoint (.ppt/.pptx) · Tối đa 50 MB/file</p>
            </div>
          </label>
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

    <!-- Delete confirm -->
    <AppModal v-model="deleteModal" title="Xác nhận xóa" size="sm">
      <p class="text-sm text-gray-600">Bạn có chắc muốn xóa bài học <strong>{{ deleteTarget?.title }}</strong>?</p>
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

const lessons = ref([])
const loading = ref(true)
const search = ref('')
const filterStatus = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })

const formModal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const form = reactive({ classroom_id: '', subject_id: '', title: '', description: '', video_path: '', status: 'draft' })

const existingMaterials = ref([])
const newFiles = ref([])

const deleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

const classrooms = ref([])
const subjects = ref([])

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchData(), 400)
}

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/admin/content/lessons', {
      params: { search: search.value, status: filterStatus.value, page }
    })
    lessons.value = data.data
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
  Object.assign(form, { classroom_id: '', subject_id: '', title: '', description: '', video_path: '', status: 'draft' })
  existingMaterials.value = []
  newFiles.value = []
  formError.value = ''
  formModal.value = true
}

function openEdit(l) {
  editItem.value = l
  Object.assign(form, {
    classroom_id: l.classroom_id ?? '',
    subject_id: l.subject?.id ?? l.subject_id ?? '',
    title: l.title,
    description: l.description ?? '',
    video_path: l.video_path ?? '',
    status: l.status ?? 'draft',
  })
  existingMaterials.value = (l.materials ?? []).map(m => ({ ...m }))
  newFiles.value = []
  formError.value = ''
  formModal.value = true
}

async function removeExisting(material) {
  try {
    await api.delete(`/admin/content/lessons/${editItem.value.id}/materials/${material.id}`)
    existingMaterials.value = existingMaterials.value.filter(m => m.id !== material.id)
  } catch {
    formError.value = 'Không thể xóa tài liệu này'
  }
}

function onFilePick(e) {
  addFiles(Array.from(e.target.files))
  e.target.value = ''
}

function onDrop(e) {
  addFiles(Array.from(e.dataTransfer.files))
}

function addFiles(files) {
  const allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx']
  files.forEach(f => {
    const ext = f.name.split('.').pop().toLowerCase()
    if (allowed.includes(ext)) newFiles.value.push(f)
  })
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    const payload = { ...form }
    if (payload.classroom_id === '') payload.classroom_id = null

    let lessonId
    if (editItem.value) {
      await api.put(`/admin/content/lessons/${editItem.value.id}`, payload)
      lessonId = editItem.value.id
    } else {
      const { data } = await api.post('/admin/content/lessons', payload)
      lessonId = data.data.id
    }

    for (const file of newFiles.value) {
      const fd = new FormData()
      fd.append('file', file)
      await api.post(`/admin/content/lessons/${lessonId}/materials`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }

    formModal.value = false
    fetchData(meta.value.current_page)
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) formError.value = Object.values(errors).flat().join(' ')
    else formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally { saving.value = false }
}

function confirmDelete(item) {
  deleteTarget.value = item
  deleteModal.value = true
}

async function doDelete() {
  deleting.value = true
  try {
    await api.delete(`/admin/content/lessons/${deleteTarget.value.id}`)
    deleteModal.value = false
    fetchData(meta.value.current_page)
  } finally { deleting.value = false }
}

function fileTypeFromName(name) {
  const ext = name.split('.').pop().toLowerCase()
  if (ext === 'pdf') return 'pdf'
  if (['doc', 'docx'].includes(ext)) return 'word'
  if (['ppt', 'pptx'].includes(ext)) return 'ppt'
  return 'other'
}
function fileIcon(type) { return { pdf: '📄', word: '📝', ppt: '📊', other: '📎' }[type] ?? '📎' }
function fileSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1024 / 1024).toFixed(1) + ' MB'
}

onMounted(() => { fetchData(); loadOptions() })
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm bg-white; }
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
</style>
