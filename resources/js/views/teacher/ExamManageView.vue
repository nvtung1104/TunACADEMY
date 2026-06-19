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
      <button @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo đề mới
      </button>
    </div>

    <!-- Cards grid -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
      <div v-if="exams.length === 0" class="col-span-full py-20 text-center text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <p class="font-semibold text-gray-500">Chưa có đề thi nào</p>
        <p class="text-xs text-gray-400 mt-1">Nhấp vào "Tạo đề mới" để tạo bài kiểm tra đầu tiên của bạn.</p>
      </div>
      <div v-for="e in exams" :key="e.id" class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200/80 transition-all duration-300 flex flex-col justify-between overflow-hidden min-h-[300px] group">
        <!-- Top area: Content & info -->
        <div class="p-6 pb-5 flex-1 flex flex-col justify-between">
          <div>
            <div class="flex items-start justify-between gap-3 mb-2.5">
              <div class="flex-1 min-w-0">
                <RouterLink :to="'/teacher/exams/' + e.id" class="font-bold text-gray-900 hover:text-[#d63015] hover:underline transition-colors block text-base leading-snug line-clamp-2" :title="e.title">
                  {{ e.title }}
                </RouterLink>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1 font-medium">
                  <span>{{ e.subject?.name ?? '—' }}</span>
                  <span class="text-gray-300">·</span>
                  <span>{{ e.classroom?.name ?? 'Công khai' }}</span>
                </p>
              </div>
            </div>

            <!-- Badges row -->
            <div class="flex flex-wrap items-center gap-1.5 mb-4">
              <span class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-extrabold border" :class="typeClass(e.type)">
                {{ typeLabel(e.type) }}
              </span>
              <span class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-extrabold border" :class="statusClass(e.status)">
                {{ statusLabel(e.status) }}
              </span>
              <span class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-extrabold border" :class="visibilityClass(e.visibility)">
                {{ visibilityLabel(e.visibility) }}
              </span>
              <span v-if="e.duration_minutes" class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-bold bg-gray-50 border border-gray-150 text-gray-500 flex items-center gap-0.5">
                ⏱️ {{ e.duration_minutes }}p
              </span>
            </div>
          </div>

          <!-- Details list -->
          <div class="border-t border-dashed border-gray-100 pt-4 mt-auto space-y-2.5 text-xs text-gray-500">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span>Số câu hỏi:</span>
              <strong class="text-gray-800 ml-auto">{{ e.questions_count ?? e.question_count ?? 0 }} câu</strong>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span>Bắt đầu:</span>
              <strong v-if="e.type === 'practice_exam'" class="text-green-600 ml-auto">Luôn mở</strong>
              <strong v-else class="text-gray-800 ml-auto">{{ e.opened_at ? formatDate(e.opened_at) : '—' }}</strong>
            </div>
            <div v-if="e.type !== 'practice_exam'" class="flex items-center gap-2">
              <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>Kết thúc:</span>
              <strong class="text-gray-800 ml-auto">{{ e.closed_at ? formatDate(e.closed_at) : '—' }}</strong>
            </div>
          </div>
        </div>

        <!-- Footer Actions: Permanently visible -->
        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-4 flex items-center justify-between gap-1.5">
          <RouterLink :to="'/teacher/exams/' + e.id" class="flex-1 py-2 px-3 rounded-xl bg-gray-900 text-white hover:bg-black text-xs font-bold text-center transition-all shadow-sm">
            Chi tiết
          </RouterLink>
          <button v-if="e.status === 'draft'" @click="publishExam(e)" class="flex-1 py-2 px-3 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold text-center transition-all shadow-sm" title="Mở đề thi">
            Mở đề
          </button>
          <button @click="openShare(e)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-blue-600 hover:border-blue-200 transition-all shrink-0" title="Chia sẻ">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
          </button>
          <button @click="openResults(e)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-amber-600 hover:border-amber-200 transition-all shrink-0" title="Kết quả">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </button>
          <button @click="openEdit(e)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-indigo-650 hover:border-indigo-200 transition-all shrink-0" title="Chỉnh sửa">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <button @click="deleteExam(e)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-red-50 text-gray-400 hover:text-red-650 hover:border-red-200 transition-all shrink-0" title="Xóa đề">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ExamFormModal
      v-model="modal"
      :edit-item="editItem"
      :subjects="subjects"
      :classrooms="allClassrooms"
      @saved="fetch"
    />
    <ExamShareModal
      v-model="shareModal"
      :exam="shareTarget"
      :classrooms="classrooms"
      @shared="fetch"
    />
    <ExamResultsModal
      v-model="resultsModal"
      :exam="resultsTarget"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'
import ExamFormModal    from '@components/exam/ExamFormModal.vue'
import ExamShareModal   from '@components/exam/ExamShareModal.vue'
import ExamResultsModal from '@components/exam/ExamResultsModal.vue'
import { useToastStore }   from '@stores/toast'
import { useConfirmStore } from '@stores/confirm'

const toast        = useToastStore()
const confirmStore = useConfirmStore()

const exams         = ref([])
const classrooms    = ref([])
const allClassrooms = ref([])
const subjects      = ref([])
const loading       = ref(true)

const modal        = ref(false)
const shareModal   = ref(false)
const resultsModal = ref(false)
const editItem     = ref(null)
const shareTarget  = ref(null)
const resultsTarget = ref(null)

const filters = reactive({ type: '', status: '' })

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/exams', { params: filters })
    exams.value = data.data?.data ?? data.data ?? []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editItem.value = null
  modal.value    = true
}

function openEdit(e) {
  editItem.value = e
  modal.value    = true
}

function openShare(e) {
  shareTarget.value = e
  shareModal.value  = true
}

function openResults(e) {
  resultsTarget.value = e
  resultsModal.value  = true
}

async function publishExam(e) {
  try {
    await api.post(`/teacher/exams/${e.id}/publish`)
    fetch()
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Không thể mở đề thi')
  }
}

async function deleteExam(e) {
  if (!await confirmStore.ask(`Xóa đề thi "${e.title}"?`)) return
  try {
    await api.delete(`/teacher/exams/${e.id}`)
    fetch()
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Không thể xóa')
  }
}

function typeLabel(t) {
  return { quiz_15: 'KT 15p', quiz_30: 'KT 30p', quiz_45: 'KT 45p', practice_exam: 'Ôn tập' }[t] ?? t
}
function typeClass(t) {
  return {
    quiz_15: 'bg-amber-50 text-amber-750 border-amber-200/60',
    quiz_30: 'bg-blue-50 text-blue-750 border-blue-200/60',
    quiz_45: 'bg-purple-50 text-purple-750 border-purple-200/60',
    practice_exam: 'bg-green-50 text-green-750 border-green-200/60'
  }[t] ?? 'bg-gray-50 text-gray-500 border-gray-200'
}
function visibilityLabel(v) {
  return { public: 'Công khai', private: 'Riêng tư', class: 'Lớp học', selected: 'Được chọn' }[v] ?? v
}
function visibilityClass(v) {
  return {
    public: 'bg-teal-50 text-teal-700 border-teal-200/60',
    private: 'bg-gray-50 text-gray-550 border-gray-250',
    class: 'bg-indigo-50 text-indigo-700 border-indigo-200/60',
    selected: 'bg-rose-50 text-[#d63015] border-red-200/60'
  }[v] ?? 'bg-gray-50 text-gray-550 border-gray-200'
}
function statusLabel(s) { return { draft: 'Bản nháp', published: 'Đang mở', closed: 'Đã đóng' }[s] ?? s }
function statusClass(s) {
  return {
    draft: 'bg-amber-50 text-amber-700 border-amber-200/60',
    published: 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
    closed: 'bg-gray-50 text-gray-400 border-gray-200'
  }[s] ?? 'bg-gray-50 text-gray-550 border-gray-200'
}
function formatDate(iso) { return new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) }

onMounted(async () => {
  const [cr, allCr, sr] = await Promise.all([
    api.get('/teacher/classrooms').catch(() => ({ data: { data: [] } })),
    api.get('/teacher/all-classrooms').catch(() => ({ data: { data: [] } })),
    api.get('/public/subjects').catch(() => ({ data: { data: [] } })),
  ])
  classrooms.value    = cr.data.data?.data ?? cr.data.data ?? []
  allClassrooms.value = allCr.data.data ?? []
  subjects.value      = sr.data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
