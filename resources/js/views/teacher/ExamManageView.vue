<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="filters.type" @change="fetch" class="input w-44">
          <option value="">Tất cả loại</option>
          <option value="quiz_15">Kiểm tra 15p</option>
          <option value="quiz_30">Kiểm tra 30p</option>
          <option value="quiz_45">Kiểm tra 45p</option>
          <option value="practice_exam">Đề ôn tập</option>
        </select>
        <select v-model="filters.status" @change="fetch" class="input w-40">
          <option value="">Tất cả trạng thái</option>
          <option value="draft">Bản nháp</option>
          <option value="published">Đang mở</option>
          <option value="closed">Đã đóng</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo đề mới
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
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Đề thi</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Loại</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Thời gian mở</th>
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
              Chưa có đề thi nào
            </td>
          </tr>
          <tr v-for="e in exams" :key="e.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <p class="font-medium text-gray-900">{{ e.title }}</p>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xs text-gray-400">{{ e.subject?.name }}</span>
                <span class="text-xs text-gray-300">·</span>
                <span class="text-xs text-gray-400">{{ e.question_count ?? 0 }} câu</span>
                <span v-if="e.classroom" class="text-xs text-gray-300">·</span>
                <span v-if="e.classroom" class="text-xs text-gray-400">{{ e.classroom.name }}</span>
              </div>
            </td>
            <td class="px-5 py-3 hidden md:table-cell">
              <span class="px-2 py-1 rounded-lg text-xs font-semibold" :class="typeClass(e.type)">
                {{ typeLabel(e.type) }}
              </span>
              <div class="mt-1">
                <span class="text-[10px] px-1.5 py-0.5 rounded-md font-medium" :class="visibilityClass(e.visibility)">
                  {{ visibilityLabel(e.visibility) }}
                </span>
              </div>
            </td>
            <td class="px-5 py-3 hidden lg:table-cell">
              <template v-if="e.type === 'practice_exam'">
                <span class="text-xs text-green-600 font-medium">Luôn mở</span>
              </template>
              <template v-else>
                <p class="text-xs text-gray-600">{{ e.opened_at ? formatDate(e.opened_at) : '—' }}</p>
                <p v-if="e.closed_at" class="text-xs text-gray-400">→ {{ formatDate(e.closed_at) }}</p>
              </template>
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
                <button @click="openShare(e)" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-500 transition-colors" title="Chia sẻ">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                  </svg>
                </button>
                <button @click="openResults(e)" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Kết quả">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </button>
                <button @click="openEdit(e)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="Chỉnh sửa">
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
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa đề thi' : 'Tạo đề thi mới'" size="lg">
      <form class="space-y-5">
        <!-- Type selector (only for new) -->
        <div v-if="!editItem">
          <label class="block text-sm font-medium text-gray-700 mb-2">Loại đề <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button type="button" @click="form.type = 'quiz_15'; setDuration('quiz_15')"
              class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 text-center transition-all"
              :class="form.type === 'quiz_15' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-gray-200 hover:border-amber-200 text-gray-600'">
              <span class="text-xl">⏱</span>
              <span class="text-xs font-semibold">Kiểm tra 15p</span>
            </button>
            <button type="button" @click="form.type = 'quiz_30'; setDuration('quiz_30')"
              class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 text-center transition-all"
              :class="form.type === 'quiz_30' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-blue-200 text-gray-600'">
              <span class="text-xl">📋</span>
              <span class="text-xs font-semibold">Kiểm tra 30p</span>
            </button>
            <button type="button" @click="form.type = 'quiz_45'; setDuration('quiz_45')"
              class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 text-center transition-all"
              :class="form.type === 'quiz_45' ? 'border-purple-400 bg-purple-50 text-purple-700' : 'border-gray-200 hover:border-purple-200 text-gray-600'">
              <span class="text-xl">📝</span>
              <span class="text-xs font-semibold">Kiểm tra 45p</span>
            </button>
            <button type="button" @click="form.type = 'practice_exam'; setDuration('practice_exam')"
              class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 text-center transition-all"
              :class="form.type === 'practice_exam' ? 'border-green-400 bg-green-50 text-green-700' : 'border-gray-200 hover:border-green-200 text-gray-600'">
              <span class="text-xl">🎯</span>
              <span class="text-xs font-semibold">Đề ôn tập</span>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" required placeholder="Tên đề thi / bài kiểm tra" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
          <textarea v-model="form.description" class="input resize-none" rows="2" placeholder="Mô tả ngắn"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Môn học <span class="text-red-500">*</span></label>
            <select v-model="form.subject_id" class="input" required>
              <option value="">Chọn môn</option>
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Lớp học
              <span v-if="form.type !== 'practice_exam'" class="text-red-500">*</span>
              <span v-else class="text-xs text-gray-400 font-normal">(tuỳ chọn)</span>
            </label>
            <select v-model="form.classroom_id" class="input">
              <option value="">Chọn lớp</option>
              <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian (phút)</label>
            <input v-model.number="form.duration_minutes" type="number" min="5" max="180" class="input" />
          </div>
          <template v-if="form.type !== 'practice_exam'">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mở từ <span class="text-red-500">*</span></label>
              <input v-model="form.opened_at" type="datetime-local" class="input" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Đóng lúc <span class="text-red-500">*</span></label>
              <input v-model="form.closed_at" type="datetime-local" class="input" />
            </div>
          </template>
          <template v-else>
            <div class="col-span-2 flex items-end pb-1">
              <p class="text-xs text-green-600 bg-green-50 px-3 py-2 rounded-xl w-full">Đề ôn tập luôn mở — học sinh có thể vào luyện tập bất cứ lúc nào</p>
            </div>
          </template>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.shuffle_questions" class="rounded text-[#d63015]" />
            <span class="text-sm text-gray-700">Xáo trộn câu hỏi</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.shuffle_options" class="rounded text-[#d63015]" />
            <span class="text-sm text-gray-700">Xáo trộn đáp án</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer" v-if="form.type !== 'practice_exam'">
            <input type="checkbox" v-model="form.proctoring_enabled" class="rounded text-[#d63015]" />
            <span class="text-sm text-gray-700">Bật giám sát</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.allow_retake" class="rounded text-[#d63015]" />
            <span class="text-sm text-gray-700">Cho phép thi lại</span>
          </label>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Hiển thị</label>
          <select v-model="form.visibility" class="input">
            <option value="private">Riêng tư</option>
            <option value="class">Cho lớp</option>
            <option value="public">Công khai</option>
          </select>
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
    <AppModal v-model="shareModal" title="Chia sẻ đề thi" size="sm">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Chế độ chia sẻ</label>
          <div class="space-y-2">
            <label v-for="opt in visibilityOptions" :key="opt.value" class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
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
          <input v-model="shareForm.student_codes" class="input" placeholder="VD: HS001, HS002, HS003" />
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

    <!-- Results Modal -->
    <AppModal v-model="resultsModal" title="Kết quả kiểm tra" size="lg">
      <div v-if="loadingResults" class="py-8 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>Đang tải...
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
const shareModal = ref(false)
const resultsModal = ref(false)
const loadingResults = ref(false)
const examResults = ref([])
const editItem = ref(null)
const shareTarget = ref(null)
const saving = ref(false)
const sharing = ref(false)
const formError = ref('')
const shareError = ref('')
const filters = reactive({ type: '', status: '' })
const form = reactive({
  type: 'quiz_15', title: '', description: '', classroom_id: '', subject_id: '',
  duration_minutes: 15, opened_at: '', closed_at: '', visibility: 'private',
  shuffle_questions: false, shuffle_options: false,
  proctoring_enabled: false, allow_retake: false,
})
const shareForm = reactive({ visibility: 'class', classroom_id: '', student_codes: '' })

const visibilityOptions = [
  { value: 'private',  label: 'Riêng tư',   desc: 'Chỉ bạn mới thấy' },
  { value: 'class',    label: 'Cho lớp',     desc: 'Chia sẻ cho một lớp học cụ thể' },
  { value: 'public',   label: 'Công khai',   desc: 'Tất cả học sinh đều có thể tìm thấy' },
  { value: 'selected', label: 'Chọn học sinh', desc: 'Chia sẻ cho từng học sinh theo mã' },
]

function setDuration(type) {
  const map = { quiz_15: 15, quiz_30: 30, quiz_45: 45, practice_exam: 60 }
  form.duration_minutes = map[type] ?? 45
}

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/exams', { params: filters })
    exams.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

function openCreate() {
  editItem.value = null
  Object.assign(form, {
    type: 'quiz_15', title: '', description: '', classroom_id: '', subject_id: '',
    duration_minutes: 15, opened_at: '', closed_at: '', visibility: 'private',
    shuffle_questions: false, shuffle_options: false, proctoring_enabled: false, allow_retake: false,
  })
  formError.value = ''
  modal.value = true
}

function openEdit(e) {
  editItem.value = e
  Object.assign(form, {
    type: e.type ?? 'quiz_45', title: e.title, description: e.description ?? '',
    classroom_id: e.classroom?.id ?? '', subject_id: e.subject?.id ?? '',
    duration_minutes: e.duration_minutes, visibility: e.visibility ?? 'private',
    opened_at: e.opened_at ? e.opened_at.slice(0, 16) : '',
    closed_at: e.closed_at ? e.closed_at.slice(0, 16) : '',
    shuffle_questions: e.shuffle_questions ?? false, shuffle_options: e.shuffle_options ?? false,
    proctoring_enabled: e.proctoring_enabled ?? false, allow_retake: e.allow_retake ?? false,
  })
  formError.value = ''
  modal.value = true
}

function openShare(e) {
  shareTarget.value = e
  Object.assign(shareForm, { visibility: e.visibility ?? 'class', classroom_id: e.classroom?.id ?? '', student_codes: '' })
  shareError.value = ''
  shareModal.value = true
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
    if (!payload.classroom_id) delete payload.classroom_id
    if (editItem.value) await api.put(`/teacher/exams/${editItem.value.id}`, payload)
    else await api.post('/teacher/exams', payload)
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
    await api.post(`/teacher/exams/${shareTarget.value.id}/share`, payload)
    shareModal.value = false; fetch()
  } catch (e) { shareError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { sharing.value = false }
}

async function publishExam(e) {
  try { await api.post(`/teacher/exams/${e.id}/publish`); fetch() }
  catch (err) { alert(err.response?.data?.message ?? 'Không thể mở đề thi') }
}

async function deleteExam(e) {
  if (!confirm(`Xóa đề thi "${e.title}"?`)) return
  try { await api.delete(`/teacher/exams/${e.id}`); fetch() }
  catch (err) { alert(err.response?.data?.message ?? 'Không thể xóa') }
}

function typeLabel(t) {
  return { quiz_15: 'KT 15p', quiz_30: 'KT 30p', quiz_45: 'KT 45p', practice_exam: 'Ôn tập' }[t] ?? t
}
function typeClass(t) {
  return { quiz_15: 'bg-amber-100 text-amber-700', quiz_30: 'bg-blue-100 text-blue-700', quiz_45: 'bg-purple-100 text-purple-700', practice_exam: 'bg-green-100 text-green-700' }[t] ?? 'bg-gray-100 text-gray-500'
}
function visibilityLabel(v) {
  return { public: 'Công khai', private: 'Riêng tư', class: 'Lớp học', selected: 'Được chọn' }[v] ?? v
}
function visibilityClass(v) {
  return { public: 'bg-green-50 text-green-600', private: 'bg-gray-100 text-gray-500', class: 'bg-blue-50 text-blue-600', selected: 'bg-violet-50 text-violet-600' }[v] ?? ''
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
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
