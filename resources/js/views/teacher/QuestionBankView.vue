<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="filters.search" @input="debounceFetch" type="text" placeholder="Tìm câu hỏi..."
            class="pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] w-48"/>
        </div>
        <select v-model="filters.type" @change="fetch" class="input w-40">
          <option value="">Tất cả loại</option>
          <optgroup label="Trắc nghiệm">
            <option value="multiple_choice">Chọn 1 đáp án</option>
            <option value="multiple_select">Chọn nhiều đáp án</option>
            <option value="true_false">Đúng / Sai</option>
          </optgroup>
          <optgroup label="Tự luận">
            <option value="fill_blank">Điền vào chỗ trống</option>
            <option value="short_answer">Trả lời ngắn</option>
            <option value="essay">Tự luận</option>
            <option value="calculation">Tính toán</option>
          </optgroup>
          <optgroup label="Tương tác">
            <option value="ordering">Sắp xếp thứ tự</option>
            <option value="matching">Nối cặp</option>
            <option value="drag_drop">Kéo thả</option>
          </optgroup>
          <optgroup label="Kỹ năng">
            <option value="reading">Đọc hiểu</option>
            <option value="listening">Nghe</option>
            <option value="multi_step">Nhiều bước</option>
          </optgroup>
          <optgroup label="Lập trình">
            <option value="code_fill">Code điền vào</option>
            <option value="code_debug">Sửa lỗi code</option>
            <option value="code_output">Đọc output</option>
            <option value="code_runner">Chạy code</option>
          </optgroup>
        </select>
        <select v-model="filters.difficulty" @change="fetch" class="input w-36">
          <option value="">Tất cả độ khó</option>
          <option value="easy">Dễ</option>
          <option value="medium">Trung bình</option>
          <option value="hard">Khó</option>
        </select>
        <select v-model="filters.subject_id" @change="fetch" class="input w-40">
          <option value="">Tất cả môn</option>
          <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div class="flex gap-2">
        <button @click="showPublicBank = !showPublicBank" class="px-4 py-2 rounded-xl border text-sm font-medium transition-colors"
          :class="showPublicBank ? 'border-[#d63015] text-[#d63015] bg-red-50' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
          {{ showPublicBank ? 'Ngân hàng của tôi' : 'Ngân hàng công khai' }}
        </button>
        <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Thêm câu hỏi
        </button>
      </div>
    </div>

    <!-- Question list -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <div v-else-if="questions.length === 0" class="py-16 text-center text-gray-400">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>Chưa có câu hỏi nào</p>
      </div>
      <div v-else class="divide-y divide-gray-50">
        <div v-for="q in questions" :key="q.id" class="px-5 py-4 hover:bg-gray-50 transition-colors group">
          <div class="flex items-start gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold" :class="typeClass(q.type)">
                  {{ typeLabel(q.type) }}
                </span>
                <span class="px-2 py-0.5 rounded-lg text-xs font-medium" :class="difficultyClass(q.difficulty)">
                  {{ difficultyLabel(q.difficulty) }}
                </span>
                <span v-if="q.chapter_tag" class="px-2 py-0.5 rounded-lg text-xs bg-gray-100 text-gray-600">
                  {{ q.chapter_tag }}
                </span>
                <span v-if="q.subject" class="px-2 py-0.5 rounded-lg text-xs bg-blue-50 text-blue-600">
                  {{ q.subject.name }}
                </span>
                <span v-if="q.is_public" class="px-2 py-0.5 rounded-lg text-xs bg-green-50 text-green-600">Công khai</span>
              </div>
              <div class="text-sm text-gray-800 line-clamp-2" v-html="q.content"></div>
              <p v-if="q.teacher" class="text-xs text-gray-400 mt-1">Tác giả: {{ q.teacher.name }}</p>
            </div>
            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
              <span class="text-xs font-semibold text-gray-500 mr-2">{{ q.default_points ?? 1 }}đ</span>
              <button v-if="!showPublicBank" @click="openEdit(q)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="Chỉnh sửa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button v-if="!showPublicBank" @click="deleteQuestion(q)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Xóa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ meta.total }} câu hỏi</p>
        <div class="flex gap-1">
          <button v-for="p in meta.last_page" :key="p" @click="goPage(p)"
            class="w-8 h-8 rounded-lg text-xs font-medium transition-colors"
            :class="p === meta.current_page ? 'bg-[#d63015] text-white' : 'hover:bg-gray-100 text-gray-600'">
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi'" size="lg">
      <form class="space-y-4">
        <!-- Type -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Loại câu hỏi <span class="text-red-500">*</span></label>
            <select v-model="form.type" class="input" required>
              <optgroup label="Trắc nghiệm">
                <option value="multiple_choice">Chọn 1 đáp án</option>
                <option value="multiple_select">Chọn nhiều đáp án</option>
                <option value="true_false">Đúng / Sai</option>
              </optgroup>
              <optgroup label="Tự luận / Tính toán">
                <option value="fill_blank">Điền vào chỗ trống</option>
                <option value="short_answer">Trả lời ngắn</option>
                <option value="essay">Tự luận</option>
                <option value="calculation">Tính toán (có sai số)</option>
              </optgroup>
              <optgroup label="Tương tác">
                <option value="ordering">Sắp xếp thứ tự</option>
                <option value="matching">Nối cặp</option>
                <option value="drag_drop">Kéo thả</option>
              </optgroup>
              <optgroup label="Kỹ năng ngôn ngữ">
                <option value="reading">Đọc hiểu</option>
                <option value="listening">Nghe hiểu</option>
                <option value="speaking">Nói</option>
                <option value="writing">Viết</option>
              </optgroup>
              <optgroup label="Phân tích">
                <option value="multi_step">Nhiều bước</option>
                <option value="data_analysis">Phân tích dữ liệu</option>
                <option value="chart_analysis">Phân tích biểu đồ</option>
                <option value="experiment">Thí nghiệm</option>
                <option value="case_study">Tình huống</option>
              </optgroup>
              <optgroup label="Lập trình">
                <option value="code_fill">Code điền vào</option>
                <option value="code_debug">Sửa lỗi code</option>
                <option value="code_output">Đọc output</option>
                <option value="code_runner">Chạy code</option>
              </optgroup>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Độ khó</label>
            <select v-model="form.difficulty" class="input">
              <option value="easy">Dễ</option>
              <option value="medium">Trung bình</option>
              <option value="hard">Khó</option>
            </select>
          </div>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Chương / Tag</label>
            <input v-model="form.chapter_tag" class="input" placeholder="VD: Đại số, Hình học..." />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung câu hỏi <span class="text-red-500">*</span></label>
          <textarea v-model="form.content" class="input resize-none" rows="3" required placeholder="Nhập câu hỏi..."></textarea>
        </div>

        <!-- Options for multiple_choice / multiple_select / true_false / ordering -->
        <div v-if="needsOptions">
          <label class="block text-sm font-medium text-gray-700 mb-2">Đáp án lựa chọn <span class="text-red-500">*</span></label>
          <div v-if="form.type === 'true_false'" class="space-y-2">
            <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50">
              <span class="w-7 h-7 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center justify-center">T</span>
              <span class="text-sm text-gray-700">Đúng</span>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50">
              <span class="w-7 h-7 rounded-full bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center">F</span>
              <span class="text-sm text-gray-700">Sai</span>
            </div>
          </div>
          <div v-else class="space-y-2">
            <div v-for="(opt, idx) in formOptions" :key="idx" class="flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center shrink-0">{{ opt.id }}</span>
              <input v-model="opt.text" class="input flex-1" :placeholder="`Đáp án ${opt.id}`" />
              <button v-if="formOptions.length > 2" type="button" @click="formOptions.splice(idx, 1)"
                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <button v-if="formOptions.length < 8" type="button" @click="addOption"
              class="text-xs text-[#d63015] hover:underline font-medium">+ Thêm đáp án</button>
          </div>
        </div>

        <!-- Correct answer -->
        <div v-if="!isTeacherGraded">
          <label class="block text-sm font-medium text-gray-700 mb-1">Đáp án đúng <span class="text-red-500">*</span></label>
          <div v-if="form.type === 'multiple_choice' || form.type === 'true_false' || form.type === 'ordering'">
            <select v-if="form.type === 'multiple_choice'" v-model="form.correct_answer" class="input">
              <option value="">Chọn đáp án đúng</option>
              <option v-for="opt in formOptions" :key="opt.id" :value="opt.id">{{ opt.id }}: {{ opt.text }}</option>
            </select>
            <select v-else-if="form.type === 'true_false'" v-model="form.correct_answer" class="input">
              <option value="T">Đúng (T)</option>
              <option value="F">Sai (F)</option>
            </select>
            <input v-else v-model="form.correct_answer" class="input" placeholder="VD: 3,1,4,2 (thứ tự các id đúng)" />
          </div>
          <div v-else-if="form.type === 'multiple_select'" class="flex flex-wrap gap-2">
            <label v-for="opt in formOptions" :key="opt.id" class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" :value="opt.id" v-model="multiSelectAnswer" class="rounded text-[#d63015]" />
              <span class="text-sm text-gray-700">{{ opt.id }}</span>
            </label>
          </div>
          <div v-else-if="form.type === 'calculation'" class="grid grid-cols-3 gap-2">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Giá trị</label>
              <input v-model.number="calcAnswer.value" type="number" step="any" class="input" placeholder="78.5" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Sai số ±</label>
              <input v-model.number="calcAnswer.tolerance" type="number" step="any" min="0" class="input" placeholder="0.5" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Đơn vị</label>
              <input v-model="calcAnswer.unit" class="input" placeholder="cm²" />
            </div>
          </div>
          <input v-else v-model="form.correct_answer" class="input" placeholder="Nhập đáp án đúng" />
        </div>
        <div v-else>
          <p class="text-xs text-gray-400 bg-amber-50 p-3 rounded-xl">Loại câu hỏi này do giáo viên chấm điểm thủ công — không cần nhập đáp án</p>
        </div>

        <!-- Explanation -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Giải thích đáp án</label>
          <textarea v-model="form.explanation" class="input resize-none" rows="2" placeholder="Giải thích tại sao đây là đáp án đúng..."></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Điểm mặc định</label>
            <input v-model.number="form.default_points" type="number" min="0.25" max="100" step="0.25" class="input" />
          </div>
          <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_public" class="rounded text-[#d63015]" />
              <span class="text-sm text-gray-700">Chia sẻ công khai</span>
            </label>
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const questions = ref([])
const subjects = ref([])
const loading = ref(true)
const modal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const showPublicBank = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', type: '', difficulty: '', subject_id: '', page: 1 })

const form = reactive({
  type: 'multiple_choice', difficulty: 'medium', subject_id: '', chapter_tag: '',
  content: '', correct_answer: '', explanation: '', default_points: 1, is_public: false,
})
const formOptions = ref([
  { id: 'A', text: '' }, { id: 'B', text: '' }, { id: 'C', text: '' }, { id: 'D', text: '' },
])
const multiSelectAnswer = ref([])
const calcAnswer = reactive({ value: null, tolerance: 0, unit: '' })

const TEACHER_GRADED = ['essay', 'speaking', 'writing']
const NEEDS_OPTIONS_TYPES = ['multiple_choice', 'multiple_select', 'true_false', 'ordering', 'drag_drop']

const needsOptions = computed(() => NEEDS_OPTIONS_TYPES.includes(form.type))
const isTeacherGraded = computed(() => TEACHER_GRADED.includes(form.type))

watch(() => form.type, (newType) => {
  form.correct_answer = ''
  multiSelectAnswer.value = []
  if (newType === 'true_false') {
    formOptions.value = [{ id: 'T', text: 'Đúng' }, { id: 'F', text: 'Sai' }]
  } else if (NEEDS_OPTIONS_TYPES.includes(newType) && newType !== 'true_false') {
    formOptions.value = [
      { id: 'A', text: '' }, { id: 'B', text: '' },
      { id: 'C', text: '' }, { id: 'D', text: '' },
    ]
  }
})

function addOption() {
  const ids = 'ABCDEFGH'
  const nextId = ids[formOptions.value.length] ?? String(formOptions.value.length + 1)
  formOptions.value.push({ id: nextId, text: '' })
}

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetch(), 400)
}

function goPage(p) { filters.page = p; fetch() }

async function fetch() {
  loading.value = true
  try {
    const endpoint = showPublicBank.value ? '/teacher/question-bank-public' : '/teacher/question-bank'
    const { data } = await api.get(endpoint, { params: filters })
    const list = data.data
    if (list?.data) {
      questions.value = list.data
      meta.value = { current_page: list.current_page, last_page: list.last_page, total: list.total }
    } else {
      questions.value = Array.isArray(list) ? list : []
    }
  } finally { loading.value = false }
}

watch(showPublicBank, () => { filters.page = 1; fetch() })

function openCreate() {
  editItem.value = null
  Object.assign(form, { type: 'multiple_choice', difficulty: 'medium', subject_id: '', chapter_tag: '', content: '', correct_answer: '', explanation: '', default_points: 1, is_public: false })
  formOptions.value = [
    { id: 'A', text: '' }, { id: 'B', text: '' },
    { id: 'C', text: '' }, { id: 'D', text: '' },
  ]
  multiSelectAnswer.value = []
  Object.assign(calcAnswer, { value: null, tolerance: 0, unit: '' })
  formError.value = ''
  modal.value = true
}

function openEdit(q) {
  editItem.value = q
  Object.assign(form, {
    type: q.type, difficulty: q.difficulty ?? 'medium', subject_id: q.subject?.id ?? '',
    chapter_tag: q.chapter_tag ?? '', content: q.content, explanation: q.explanation ?? '',
    default_points: q.default_points ?? 1, is_public: q.is_public ?? false,
  })
  if (q.options) {
    if (Array.isArray(q.options)) {
      formOptions.value = q.options.map(o => ({ id: o.id, text: o.text }))
    }
  }
  if (q.type === 'multiple_select' && Array.isArray(q.correct_answer)) {
    multiSelectAnswer.value = q.correct_answer
    form.correct_answer = ''
  } else if (q.type === 'calculation' && q.correct_answer?.value !== undefined) {
    Object.assign(calcAnswer, q.correct_answer)
    form.correct_answer = ''
  } else {
    form.correct_answer = Array.isArray(q.correct_answer) ? q.correct_answer.join(',') : (q.correct_answer ?? '')
  }
  formError.value = ''
  modal.value = true
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }

    if (needsOptions.value) {
      payload.options = formOptions.value
    }

    if (form.type === 'multiple_select') {
      payload.correct_answer = multiSelectAnswer.value
    } else if (form.type === 'calculation') {
      payload.correct_answer = { ...calcAnswer }
    } else if (form.type === 'ordering') {
      payload.correct_answer = form.correct_answer ? form.correct_answer.split(',').map(s => s.trim()) : []
    }

    if (!payload.chapter_tag) delete payload.chapter_tag

    if (editItem.value) await api.put(`/teacher/question-bank/${editItem.value.id}`, payload)
    else await api.post('/teacher/question-bank', payload)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function deleteQuestion(q) {
  if (!confirm(`Xóa câu hỏi này?`)) return
  try { await api.delete(`/teacher/question-bank/${q.id}`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể xóa') }
}

function typeLabel(t) {
  const map = {
    multiple_choice: 'Chọn 1', multiple_select: 'Chọn nhiều', true_false: 'Đúng/Sai',
    fill_blank: 'Điền trống', short_answer: 'Trả lời ngắn', essay: 'Tự luận',
    calculation: 'Tính toán', ordering: 'Sắp xếp', matching: 'Nối cặp',
    drag_drop: 'Kéo thả', reading: 'Đọc hiểu', listening: 'Nghe', speaking: 'Nói',
    writing: 'Viết', multi_step: 'Nhiều bước', code_fill: 'Code fill',
    code_debug: 'Code debug', code_output: 'Code output', code_runner: 'Code runner',
    data_analysis: 'Phân tích DL', chart_analysis: 'Biểu đồ', experiment: 'Thí nghiệm',
    case_study: 'Tình huống', map_analysis: 'Bản đồ',
  }
  return map[t] ?? t
}
function typeClass(t) {
  const group = {
    multiple_choice: 'bg-blue-100 text-blue-700', multiple_select: 'bg-blue-100 text-blue-700',
    true_false: 'bg-green-100 text-green-700', fill_blank: 'bg-amber-100 text-amber-700',
    short_answer: 'bg-amber-100 text-amber-700', essay: 'bg-orange-100 text-orange-700',
    calculation: 'bg-purple-100 text-purple-700', ordering: 'bg-pink-100 text-pink-700',
    matching: 'bg-pink-100 text-pink-700', drag_drop: 'bg-pink-100 text-pink-700',
    code_fill: 'bg-gray-200 text-gray-700', code_debug: 'bg-gray-200 text-gray-700',
    code_output: 'bg-gray-200 text-gray-700', code_runner: 'bg-gray-200 text-gray-700',
  }
  return group[t] ?? 'bg-gray-100 text-gray-600'
}
function difficultyLabel(d) { return { easy: 'Dễ', medium: 'TB', hard: 'Khó' }[d] ?? d }
function difficultyClass(d) { return { easy: 'bg-green-50 text-green-700', medium: 'bg-amber-50 text-amber-700', hard: 'bg-red-50 text-red-700' }[d] ?? '' }

onMounted(async () => {
  const { data } = await api.get('/admin/subjects', { params: { status: 'active' } }).catch(() => ({ data: { data: [] } }))
  subjects.value = data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
