<template>
  <div class="space-y-5">
    <!-- Back + Header -->
    <div class="flex items-start gap-4">
      <button @click="$router.back()"
        class="mt-0.5 p-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-500 hover:text-gray-700 transition-colors shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <div class="flex-1 min-w-0">
        <div v-if="assignment" class="flex flex-wrap items-center gap-2 mb-1">
          <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">
            {{ typeLabel(assignment.type) }}
          </span>
          <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
            :class="assignment.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ assignment.status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
          </span>
          <span v-if="assignment.deadline && isOverdue(assignment.deadline)"
            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">
            Hết hạn
          </span>
        </div>
        <h2 class="text-lg font-bold text-gray-900 truncate">{{ assignment?.title ?? 'Đang tải...' }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">
          {{ assignment?.subject?.name }} · {{ assignment?.classroom?.name }} · {{ assignment?.teacher?.name }}
          <span v-if="assignment?.deadline"> · Hạn: {{ formatDate(assignment.deadline) }}</span>
        </p>
      </div>
      <!-- Stats pills -->
      <div v-if="assignment" class="hidden sm:flex items-center gap-3 shrink-0">
        <div class="text-center px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm">
          <p class="text-xl font-black text-gray-900">{{ questions.length }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Câu hỏi</p>
        </div>
        <div class="text-center px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm">
          <p class="text-xl font-black text-gray-900">{{ subMeta.total ?? 0 }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Đã nộp</p>
        </div>
        <div class="text-center px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm">
          <p class="text-xl font-black text-[#d63015]">{{ avgScore ?? '—' }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Điểm TB</p>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-gray-100">
      <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
        class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors"
        :class="activeTab === tab.key
          ? 'text-[#d63015] border-b-2 border-[#d63015] bg-red-50/40'
          : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
        {{ tab.label }}
        <span v-if="tab.key === 'questions' && questions.length"
          class="ml-1.5 text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full">
          {{ questions.length }}
        </span>
      </button>
    </div>

    <!-- ── Tab: Câu hỏi ───────────────────────────────────────────────────── -->
    <div v-if="activeTab === 'questions'">
      <div class="flex items-center justify-between mb-3">
        <p class="text-sm text-gray-500">
          <span class="font-semibold text-gray-800">{{ questions.length }}</span> câu hỏi
          · Tổng <span class="font-semibold text-gray-800">{{ totalPoints }}</span> điểm
        </p>
        <button @click="openCreate"
          class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10]">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Thêm câu hỏi
        </button>
      </div>

      <div v-if="qLoading" class="py-16 text-center">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
        <p class="text-sm text-gray-400">Đang tải...</p>
      </div>

      <div v-else-if="!questions.length"
        class="py-16 text-center bg-white rounded-2xl border border-gray-100">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-gray-400 text-sm">Chưa có câu hỏi nào. Bấm "Thêm câu hỏi" để bắt đầu.</p>
      </div>

      <div v-else class="space-y-3">
        <div v-for="(q, idx) in questions" :key="q.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <div class="flex items-start gap-3">
            <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 shrink-0 mt-0.5">
              {{ idx + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold"
                  :class="qTypeClass(q.type)">{{ qTypeLabel(q.type) }}</span>
                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium"
                  :class="diffClass(q.difficulty)">{{ diffLabel(q.difficulty) }}</span>
                <span v-if="q.chapter_tag" class="text-xs text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md">
                  {{ q.chapter_tag }}
                </span>
                <span class="ml-auto text-xs font-bold text-[#d63015]">{{ q.points }} điểm</span>
              </div>
              <p class="text-sm text-gray-800 line-clamp-2">{{ stripHtml(q.content) }}</p>
              <div v-if="Array.isArray(q.options) && q.options.length" class="mt-2 flex flex-wrap gap-2">
                <span v-for="(opt, oi) in q.options.slice(0, 4)" :key="oi"
                  class="text-xs px-2 py-0.5 rounded-lg"
                  :class="q.type === 'ordering'
                    ? 'bg-gray-100 text-gray-500'
                    : isCorrectOption(q, oi) ? 'bg-green-100 text-green-700 font-semibold' : 'bg-gray-100 text-gray-500'">
                  <template v-if="q.type === 'ordering'">{{ oi + 1 }}. {{ opt }}</template>
                  <template v-else>{{ String.fromCharCode(65 + oi) }}. {{ opt }}</template>
                </span>
              </div>
            </div>
            <div class="flex gap-1 shrink-0">
              <button @click="openEdit(q)"
                class="p-1.5 rounded-lg text-gray-400 hover:text-[#d63015] hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button @click="confirmDelete(q)"
                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Tab: Bài nộp ───────────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'submissions'">
      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 mb-5">
        <div class="relative flex-1 min-w-40">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên học sinh..."
            class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]" />
        </div>
        <select v-model="filterStatus" @change="fetchSubmissions()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white">
          <option value="">Tất cả</option>
          <option value="submitted">Đã nộp</option>
          <option value="graded">Đã chấm</option>
          <option value="late">Nộp muộn</option>
        </select>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="sLoading" class="py-16 text-center">
          <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
          <p class="text-sm text-gray-400">Đang tải...</p>
        </div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Học sinh</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Thời gian nộp</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Người chấm</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Điểm</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Trạng thái</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!submissions.length">
              <td colspan="6" class="py-14 text-center text-gray-400">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Chưa có học sinh nào nộp bài
              </td>
            </tr>
            <tr v-for="(s, idx) in submissions" :key="s.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 text-gray-400 text-xs font-medium">
                {{ (subMeta.current_page - 1) * 50 + idx + 1 }}
              </td>
              <td class="px-5 py-3">
                <p class="font-medium text-gray-800">{{ s.student?.name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ s.student?.email }}</p>
              </td>
              <td class="px-5 py-3 hidden sm:table-cell">
                <p class="text-sm text-gray-700">{{ s.submitted_at ? formatDateTime(s.submitted_at) : '—' }}</p>
                <p v-if="s.is_late" class="text-xs text-red-500 font-medium mt-0.5">Nộp muộn</p>
              </td>
              <td class="px-5 py-3 hidden md:table-cell">
                <template v-if="s.graded_by">
                  <p class="text-sm text-gray-700">{{ s.graded_by?.name }}</p>
                  <p class="text-xs text-gray-400">{{ formatDateTime(s.graded_at) }}</p>
                </template>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-5 py-3">
                <template v-if="s.score != null">
                  <span class="text-lg font-black"
                    :class="(s.score / (assignment?.max_score || 10)) >= 0.5 ? 'text-green-600' : 'text-red-500'">
                    {{ s.score }}
                  </span>
                  <span class="text-xs text-gray-400">/{{ assignment?.max_score ?? 10 }}</span>
                </template>
                <span v-else class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Chưa chấm</span>
              </td>
              <td class="px-5 py-3 hidden lg:table-cell">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-green-100 text-green-700': s.status === 'graded',
                    'bg-blue-100 text-blue-700': s.status === 'submitted',
                    'bg-red-100 text-red-600': s.status === 'late',
                    'bg-gray-100 text-gray-500': !['graded','submitted','late'].includes(s.status),
                  }">
                  {{ { graded: 'Đã chấm', submitted: 'Đã nộp', late: 'Nộp muộn', draft: 'Bản nháp' }[s.status] ?? s.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :meta="subMeta" @page="fetchSubmissions" class="mt-4" />
    </div>

    <!-- Question form modal -->
    <QuestionFormModal
      v-model="formModal"
      :edit-item="editQuestion"
      :saving="saving"
      :error="formError"
      @save="saveQuestion"
    />

    <!-- Delete confirm -->
    <AppModal v-model="deleteModal" title="Xác nhận xóa" size="sm">
      <p class="text-sm text-gray-600">Bạn có chắc muốn xóa câu hỏi này?</p>
      <template #footer>
        <button @click="deleteModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="doDelete" :disabled="deleting"
          class="px-4 py-2 text-sm rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-60">
          {{ deleting ? 'Đang xóa...' : 'Xóa' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@api/axios'
import Pagination from '@components/common/Pagination.vue'
import AppModal from '@components/common/AppModal.vue'
import QuestionFormModal from '@components/common/QuestionFormModal.vue'

const route = useRoute()
const assignmentId = route.params.id

const assignment = ref(null)
const activeTab = ref('questions')
const tabs = [
  { key: 'questions',   label: 'Câu hỏi' },
  { key: 'submissions', label: 'Bài nộp' },
]

// ── Questions state ───────────────────────────────────────────────────────
const questions  = ref([])
const qLoading   = ref(true)
const formModal  = ref(false)
const editQuestion = ref(null)
const saving     = ref(false)
const formError  = ref('')
const deleteModal   = ref(false)
const deleteTarget  = ref(null)
const deleting   = ref(false)

const totalPoints = computed(() =>
  questions.value.reduce((s, q) => s + Number(q.points ?? 0), 0).toFixed(2).replace(/\.00$/, '')
)

// ── Submissions state ─────────────────────────────────────────────────────
const submissions = ref([])
const sLoading    = ref(false)
const search      = ref('')
const filterStatus = ref('')
const subMeta     = ref({ total: 0, current_page: 1, last_page: 1 })

const gradedCount = computed(() => submissions.value.filter(s => s.status === 'graded').length)
const avgScore = computed(() => {
  const scored = submissions.value.filter(s => s.score != null)
  if (!scored.length) return null
  return (scored.reduce((sum, s) => sum + Number(s.score), 0) / scored.length).toFixed(1)
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchSubmissions(), 400)
}

// ── API calls ─────────────────────────────────────────────────────────────
async function fetchAssignment() {
  const { data } = await api.get(`/admin/content/assignments/${assignmentId}`)
  assignment.value = data.data
}

async function fetchQuestions() {
  qLoading.value = true
  try {
    const { data } = await api.get(`/admin/content/assignments/${assignmentId}/questions`)
    questions.value = data.data ?? []
  } finally { qLoading.value = false }
}

async function fetchSubmissions(page = 1) {
  sLoading.value = true
  try {
    const { data } = await api.get(`/admin/content/assignments/${assignmentId}/submissions`, {
      params: { page, status: filterStatus.value, search: search.value },
    })
    const paged = data.data
    submissions.value = paged?.data ?? []
    subMeta.value = { total: paged?.total ?? 0, current_page: paged?.current_page ?? 1, last_page: paged?.last_page ?? 1 }
  } finally { sLoading.value = false }
}

function openCreate() {
  editQuestion.value = null
  formError.value = ''
  formModal.value = true
}
function openEdit(q) {
  editQuestion.value = q
  formError.value = ''
  formModal.value = true
}

async function saveQuestion(payload) {
  saving.value = true
  formError.value = ''
  try {
    if (editQuestion.value) {
      await api.put(`/admin/content/assignments/${assignmentId}/questions/${editQuestion.value.id}`, payload)
    } else {
      await api.post(`/admin/content/assignments/${assignmentId}/questions`, payload)
    }
    formModal.value = false
    fetchQuestions()
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) formError.value = Object.values(errors).flat().join(' ')
    else formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally { saving.value = false }
}

function confirmDelete(q) { deleteTarget.value = q; deleteModal.value = true }

async function doDelete() {
  deleting.value = true
  try {
    await api.delete(`/admin/content/assignments/${assignmentId}/questions/${deleteTarget.value.id}`)
    deleteModal.value = false
    fetchQuestions()
  } finally { deleting.value = false }
}

// ── Helpers ───────────────────────────────────────────────────────────────
function formatDateTime(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '—'
}
function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('vi-VN', { dateStyle: 'short' }) : '—'
}
function isOverdue(d) { return d && new Date(d) < new Date() }
function stripHtml(html) { return html ? html.replace(/<[^>]*>/g, '').trim() : '' }
function isCorrectOption(q, idx) {
  if (!q.correct_answer) return false
  const ans = Array.isArray(q.correct_answer) ? q.correct_answer : [q.correct_answer]
  return ans.includes(String(idx))
}

const typeLabel  = (t) => ({ essay: 'Tự luận', quiz: 'Trắc nghiệm', upload: 'Nộp file', fill_blank: 'Điền chỗ trống', matching: 'Ghép đôi', listening: 'Nghe hiểu', writing: 'Viết' }[t] ?? t)
const qTypeLabel = (t) => ({
  multiple_choice: 'Trắc nghiệm', multiple_select: 'Nhiều đáp án', true_false: 'Đúng/Sai',
  fill_blank: 'Điền chỗ trống', short_answer: 'Trả lời ngắn', essay: 'Tự luận',
  ordering: 'Sắp xếp', matching: 'Ghép đôi', listening: 'Nghe hiểu', reading: 'Đọc hiểu',
}[t] ?? t)
const qTypeClass = (t) => ({
  multiple_choice: 'bg-blue-100 text-blue-700', multiple_select: 'bg-indigo-100 text-indigo-700',
  true_false: 'bg-teal-100 text-teal-700', fill_blank: 'bg-orange-100 text-orange-700',
  short_answer: 'bg-yellow-100 text-yellow-700', essay: 'bg-gray-100 text-gray-600',
  ordering: 'bg-pink-100 text-pink-700', matching: 'bg-purple-100 text-purple-700',
  listening: 'bg-green-100 text-green-700', reading: 'bg-cyan-100 text-cyan-700',
}[t] ?? 'bg-gray-100 text-gray-500')
const diffLabel = (d) => ({ easy: 'Dễ', medium: 'TB', hard: 'Khó' }[d] ?? d)
const diffClass = (d) => ({ easy: 'bg-green-50 text-green-600', medium: 'bg-amber-50 text-amber-600', hard: 'bg-red-50 text-red-600' }[d] ?? 'bg-gray-50 text-gray-500')

onMounted(() => {
  fetchAssignment()
  fetchQuestions()
  fetchSubmissions()
})
</script>
