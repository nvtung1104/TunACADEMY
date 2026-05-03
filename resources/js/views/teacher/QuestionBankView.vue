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
              <button @click="openAddModal(q)" class="p-1.5 rounded-lg hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition-colors" title="Thêm vào đề/bài tập">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
              <RouterLink v-if="!showPublicBank" :to="`/teacher/question-bank/${q.id}/edit`"
                class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="Chỉnh sửa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </RouterLink>
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

  </div>

  <!-- Modal: Thêm vào đề thi / bài tập -->
  <Teleport to="body">
    <div v-if="addModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" @click.self="addModal.show = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-400 mb-0.5">Thêm câu hỏi vào</p>
            <h3 class="text-sm font-semibold text-gray-800 line-clamp-1" v-html="addModal.question?.content"></h3>
          </div>
          <button @click="addModal.show = false" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-100">
          <button @click="addModal.tab = 'exam'" class="flex-1 py-2.5 text-sm font-medium transition-colors"
            :class="addModal.tab === 'exam' ? 'text-[#d63015] border-b-2 border-[#d63015]' : 'text-gray-500 hover:text-gray-700'">
            Đề thi
          </button>
          <button @click="addModal.tab = 'assignment'" class="flex-1 py-2.5 text-sm font-medium transition-colors"
            :class="addModal.tab === 'assignment' ? 'text-[#d63015] border-b-2 border-[#d63015]' : 'text-gray-500 hover:text-gray-700'">
            Bài tập
          </button>
        </div>

        <!-- List -->
        <div class="max-h-72 overflow-y-auto">
          <div v-if="addModal.loadingList" class="py-10 text-center text-gray-400 text-sm">Đang tải...</div>
          <template v-else>
            <div v-if="addModal.tab === 'exam'">
              <div v-if="modalExams.length === 0" class="py-10 text-center text-gray-400 text-sm">Chưa có đề thi nào</div>
              <button v-for="e in modalExams" :key="e.id" @click="addToTarget(e.id)"
                :disabled="addModal.adding === e.id"
                class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors text-left border-b border-gray-50 last:border-0">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ e.title }}</p>
                  <p class="text-xs text-gray-400">{{ e.subject?.name }} · {{ e.questions_count ?? 0 }} câu</p>
                </div>
                <div v-if="addModal.adding === e.id" class="animate-spin w-4 h-4 border-2 border-[#d63015] border-t-transparent rounded-full shrink-0"></div>
                <div v-else-if="addModal.added.includes(`exam-${e.id}`)" class="text-green-500 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <svg v-else class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            </div>
            <div v-else>
              <div v-if="modalAssignments.length === 0" class="py-10 text-center text-gray-400 text-sm">Chưa có bài tập nào</div>
              <button v-for="a in modalAssignments" :key="a.id" @click="addToTarget(a.id)"
                :disabled="addModal.adding === a.id"
                class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors text-left border-b border-gray-50 last:border-0">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ a.title }}</p>
                  <p class="text-xs text-gray-400">{{ a.subject?.name }} · {{ a.questions_count ?? 0 }} câu</p>
                </div>
                <div v-if="addModal.adding === a.id" class="animate-spin w-4 h-4 border-2 border-[#d63015] border-t-transparent rounded-full shrink-0"></div>
                <div v-else-if="addModal.added.includes(`assignment-${a.id}`)" class="text-green-500 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <svg v-else class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'

const questions = ref([])
const subjects = ref([])
const loading = ref(true)
const showPublicBank = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', type: '', difficulty: '', subject_id: '', page: 1 })

// ── Modal thêm vào đề/bài tập ────────────────────────────────────────────────
const addModal = reactive({ show: false, question: null, tab: 'exam', loadingList: false, adding: null, added: [] })
const modalExams = ref([])
const modalAssignments = ref([])

async function openAddModal(q) {
  addModal.question = q
  addModal.tab = 'exam'
  addModal.adding = null
  addModal.added = []
  addModal.show = true
  addModal.loadingList = true
  try {
    const [eRes, aRes] = await Promise.all([
      api.get('/teacher/exams', { params: { per_page: 100 } }),
      api.get('/teacher/assignments', { params: { per_page: 100 } }),
    ])
    const unpack = (res) => {
      const d = res.data?.data
      return Array.isArray(d) ? d : (d?.data ?? [])
    }
    modalExams.value = unpack(eRes)
    modalAssignments.value = unpack(aRes)
  } finally {
    addModal.loadingList = false
  }
}

async function addToTarget(targetId) {
  const key = `${addModal.tab}-${targetId}`
  if (addModal.added.includes(key)) return
  addModal.adding = targetId
  try {
    const url = addModal.tab === 'exam'
      ? `/teacher/exams/${targetId}/import-questions`
      : `/teacher/assignments/${targetId}/import-questions`
    await api.post(url, { question_ids: [addModal.question.id] })
    addModal.added.push(key)
  } catch (e) {
    alert(e.response?.data?.message ?? 'Không thể thêm câu hỏi')
  } finally {
    addModal.adding = null
  }
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
  const { data } = await api.get('/public/subjects').catch(() => ({ data: { data: [] } }))
  subjects.value = data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
