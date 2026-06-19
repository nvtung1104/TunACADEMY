<template>
  <div>
    <div class="space-y-4">
    <!-- Modern Premium Toolbar -->
    <div class="space-y-4">
      <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <!-- Search bar -->
        <div class="relative flex-1 max-w-lg">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="filters.search" @input="debounceFetch" type="text" placeholder="Tìm câu hỏi theo nội dung..."
            class="w-full pl-11 pr-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] focus:border-transparent transition-all placeholder:text-gray-400 bg-gray-50/50 hover:bg-gray-50 focus:bg-white"/>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-3 shrink-0">
          <!-- Toggle active/public bank -->
          <div class="bg-gray-100 p-1 rounded-xl flex items-center shrink-0">
            <button @click="showPublicBank = false" class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200"
              :class="!showPublicBank ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800'">
              Ngân hàng của tôi
            </button>
            <button @click="showPublicBank = true" class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200"
              :class="showPublicBank ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800'">
              Ngân hàng công khai
            </button>
          </div>

          <!-- Advanced Filters Button -->
          <button @click="showFilters = !showFilters"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-semibold transition-all duration-200 shrink-0"
            :class="showFilters || activeFiltersCount > 0 ? 'border-[#d63015] text-[#d63015] bg-red-50' : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Bộ lọc
            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-[#d63015] text-white text-[10px] font-bold flex items-center justify-center animate-pulse">
              {{ activeFiltersCount }}
            </span>
          </button>

          <!-- Create Question Button (visible if my bank) -->
          <RouterLink v-if="!showPublicBank" to="/teacher/question-bank/create"
            class="flex items-center gap-2 px-4 py-2 bg-[#d63015] hover:bg-[#c02a10] text-white rounded-xl text-sm font-bold shadow-sm transition-all duration-200 shrink-0 hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tạo câu hỏi
          </RouterLink>
        </div>
      </div>

      <!-- Expandable Filters Panel -->
      <Transition name="expand">
        <div v-if="showFilters" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
          <div class="flex items-center justify-between border-b border-gray-50 pb-3">
            <h4 class="text-sm font-bold text-gray-700">Bộ lọc nâng cao</h4>
            <button v-if="activeFiltersCount > 0" @click="clearFilters" class="text-xs text-[#d63015] hover:text-[#c02a10] font-semibold hover:underline flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Xóa tất cả bộ lọc
            </button>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- 1. Môn học -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-gray-500">Môn học</label>
              <select v-model="filters.subject_id" @change="fetch" class="input">
                <option value="">Tất cả môn</option>
                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>

            <!-- 2. Loại câu hỏi -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-gray-500">Loại câu hỏi</label>
              <select v-model="filters.type" @change="fetch" class="input">
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
              </select>
            </div>

            <!-- 3. Độ khó -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-gray-500">Độ khó</label>
              <select v-model="filters.difficulty" @change="fetch" class="input">
                <option value="">Tất cả độ khó</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
              </select>
            </div>

            <!-- 4. Trạng thái giao -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-gray-500">Trạng thái giao</label>
              <select v-model="filters.assigned_status" @change="fetch" class="input">
                <option value="">Tất cả trạng thái</option>
                <option value="assigned">Đã giao</option>
                <option value="unassigned">Chưa giao</option>
              </select>
            </div>

            <!-- 5. Đề thi -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-gray-500">Lọc theo Đề thi</label>
              <select v-model="filters.exam_id" @change="onExamFilterChange" class="input">
                <option value="">Chọn đề thi...</option>
                <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title }}</option>
              </select>
            </div>

            <!-- 6. Bài tập -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-gray-500">Lọc theo Bài tập</label>
              <select v-model="filters.assignment_id" @change="onAssignmentFilterChange" class="input">
                <option value="">Chọn bài tập...</option>
                <option v-for="a in assignments" :key="a.id" :value="a.id">{{ a.title }}</option>
              </select>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Question list -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <!-- Visual indicator when filtering by exam/assignment -->
      <div v-if="filters.exam_id || filters.assignment_id" class="px-5 py-3 bg-[#d63015]/5 border-b border-gray-100 flex items-center justify-between text-xs font-semibold">
        <span class="text-gray-700 flex items-center gap-1.5">
          <span>🔍</span>
          Đang xem danh sách câu hỏi của: 
          <strong class="text-[#d63015]">
            {{ filters.exam_id ? exams.find(e => e.id === filters.exam_id)?.title : assignments.find(a => a.id === filters.assignment_id)?.title }}
          </strong>
        </span>
        <button @click="clearExamAssignmentFilters" class="text-[#d63015] hover:underline font-bold">
          Xem tất cả câu hỏi
        </button>
      </div>

      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <div v-else-if="questions.length === 0" class="py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-sm">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="font-bold text-gray-700 text-base">Chưa có câu hỏi nào</h3>
        <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto leading-relaxed">
          Không tìm thấy câu hỏi nào phù hợp với bộ lọc hiện tại. Thử xóa bớt bộ lọc hoặc thêm một câu hỏi mới.
        </p>
        <button v-if="activeFiltersCount > 0" @click="clearFilters" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold transition-all">
          Xóa bộ lọc
        </button>
      </div>
      <div v-else class="divide-y divide-gray-50">
        <div v-for="q in questions" :key="q.id" 
          class="px-5 py-4 hover:bg-gray-50/50 transition-all group flex items-start gap-4 border-l-4"
          :class="q.exams?.length || q.assignments?.length ? 'border-l-[#d63015]/60 bg-[#d63015]/[0.01]' : 'border-l-transparent'">
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
              <!-- Assignment tags -->
              <span v-for="e in q.exams" :key="'exam-'+e.id" @click.stop="filterByExam(e.id)" class="cursor-pointer hover:bg-red-100 transition-colors px-2 py-0.5 rounded-lg text-xs bg-red-50 text-[#d63015] border border-red-100 flex items-center gap-1">
                📝 Đề: {{ e.title }}
              </span>
              <span v-for="a in q.assignments" :key="'assign-'+a.id" @click.stop="filterByAssignment(a.id)" class="cursor-pointer hover:bg-amber-100 transition-colors px-2 py-0.5 rounded-lg text-xs bg-amber-50 text-amber-600 border border-amber-100 flex items-center gap-1">
                ✏️ Bài tập: {{ a.title }}
              </span>
            </div>
            <div class="text-sm text-gray-800 line-clamp-2" v-html="renderMath(q.content)"></div>
            <p v-if="q.teacher" class="text-xs text-gray-400 mt-1">Tác giả: {{ q.teacher.name }}</p>
          </div>
          <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="text-xs font-semibold text-gray-500 mr-2">{{ q.default_points ?? 1 }}đ</span>
            <button @click="openAddModal(q)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-[#d63015] transition-colors" title="Thêm vào đề/bài tập">
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
            <h3 class="text-sm font-semibold text-gray-800 line-clamp-1" v-html="renderMath(addModal.question?.content)"></h3>
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
              <button v-for="e in modalExams" :key="e.id" @click="toggleTarget(e.id)"
                :disabled="addModal.adding === e.id"
                class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors text-left border-b border-gray-50 last:border-0">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ e.title }}</p>
                  <p class="text-xs text-gray-400">{{ e.subject?.name }} · {{ e.questions_count ?? 0 }} câu</p>
                </div>
                <div v-if="addModal.adding === e.id" class="animate-spin w-4 h-4 border-2 border-[#d63015] border-t-transparent rounded-full shrink-0"></div>
                <div v-else-if="addModal.added.includes(`exam-${e.id}`)" class="text-[#d63015] hover:text-red-700 shrink-0 p-1 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" title="Loại bỏ">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                </div>
                <div v-else class="text-gray-400 hover:text-[#d63015] shrink-0 p-1 hover:bg-red-50 rounded-lg transition-colors" title="Thêm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </div>
              </button>
            </div>
            <div v-else>
              <div v-if="modalAssignments.length === 0" class="py-10 text-center text-gray-400 text-sm">Chưa có bài tập nào</div>
              <button v-for="a in modalAssignments" :key="a.id" @click="toggleTarget(a.id)"
                :disabled="addModal.adding === a.id"
                class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors text-left border-b border-gray-50 last:border-0">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ a.title }}</p>
                  <p class="text-xs text-gray-400">{{ a.subject?.name }} · {{ a.questions_count ?? 0 }} câu</p>
                </div>
                <div v-if="addModal.adding === a.id" class="animate-spin w-4 h-4 border-2 border-[#d63015] border-t-transparent rounded-full shrink-0"></div>
                <div v-else-if="addModal.added.includes(`assignment-${a.id}`)" class="text-[#d63015] hover:text-red-700 shrink-0 p-1 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" title="Loại bỏ">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                </div>
                <div v-else class="text-gray-400 hover:text-[#d63015] shrink-0 p-1 hover:bg-red-50 rounded-lg transition-colors" title="Thêm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </div>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'
import { useToastStore }   from '@stores/toast'
import { useConfirmStore } from '@stores/confirm'
import { renderMath } from '@/utils/math'

const toast        = useToastStore()
const confirmStore = useConfirmStore()

const questions = ref([])
const subjects = ref([])
const exams = ref([])
const assignments = ref([])
const loading = ref(true)
const showPublicBank = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', type: '', difficulty: '', subject_id: '', page: 1, exam_id: '', assignment_id: '', assigned_status: '' })

const showFilters = ref(false)

const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.type) count++
  if (filters.difficulty) count++
  if (filters.subject_id) count++
  if (filters.assigned_status) count++
  if (filters.exam_id) count++
  if (filters.assignment_id) count++
  return count
})

function clearFilters() {
  filters.type = ''
  filters.difficulty = ''
  filters.subject_id = ''
  filters.assigned_status = ''
  filters.exam_id = ''
  filters.assignment_id = ''
  filters.search = ''
  fetch()
}

function onExamFilterChange() {
  if (filters.exam_id) filters.assignment_id = ''
  filters.page = 1
  fetch()
}

function onAssignmentFilterChange() {
  if (filters.assignment_id) filters.exam_id = ''
  filters.page = 1
  fetch()
}

function clearExamAssignmentFilters() {
  filters.exam_id = ''
  filters.assignment_id = ''
  filters.page = 1
  fetch()
}

function filterByExam(id) {
  filters.exam_id = id
  filters.assignment_id = ''
  filters.page = 1
  fetch()
}

function filterByAssignment(id) {
  filters.assignment_id = id
  filters.exam_id = ''
  filters.page = 1
  fetch()
}

// ── Modal thêm vào đề/bài tập ────────────────────────────────────────────────
const addModal = reactive({ show: false, question: null, tab: 'exam', loadingList: false, adding: null, added: [] })
const modalExams = ref([])
const modalAssignments = ref([])

async function openAddModal(q) {
  addModal.question = q
  addModal.tab = 'exam'
  addModal.adding = null
  addModal.added = []

  if (q.exams) {
    q.exams.forEach(e => addModal.added.push(`exam-${e.id}`))
  }
  if (q.assignments) {
    q.assignments.forEach(a => addModal.added.push(`assignment-${a.id}`))
  }

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

async function toggleTarget(targetId) {
  const key = `${addModal.tab}-${targetId}`
  const isAlreadyAdded = addModal.added.includes(key)
  addModal.adding = targetId
  try {
    if (isAlreadyAdded) {
      // REMOVE
      const url = addModal.tab === 'exam'
        ? `/teacher/exams/${targetId}/remove-questions`
        : `/teacher/assignments/${targetId}/remove-questions`
      await api.post(url, { question_ids: [addModal.question.id] })
      addModal.added = addModal.added.filter(k => k !== key)
      toast.success('Đã loại bỏ câu hỏi')
    } else {
      // ADD
      const url = addModal.tab === 'exam'
        ? `/teacher/exams/${targetId}/import-questions`
        : `/teacher/assignments/${targetId}/import-questions`
      await api.post(url, { question_ids: [addModal.question.id] })
      addModal.added.push(key)
      toast.success('Đã thêm câu hỏi')
    }
    // Refresh list to sync tags
    fetch()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Không thể thực hiện yêu cầu')
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
  if (!await confirmStore.ask('Xóa câu hỏi này?')) return
  try { await api.delete(`/teacher/question-bank/${q.id}`); fetch() }
  catch (e) { toast.error(e.response?.data?.message ?? 'Không thể xóa') }
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
  const [subRes, examRes, assignRes] = await Promise.all([
    api.get('/public/subjects').catch(() => ({ data: { data: [] } })),
    api.get('/teacher/exams', { params: { per_page: 999 } }).catch(() => ({ data: { data: [] } })),
    api.get('/teacher/assignments', { params: { per_page: 999 } }).catch(() => ({ data: { data: [] } })),
  ])
  subjects.value = subRes.data.data ?? []

  const unpack = (res) => {
    const d = res?.data?.data
    return Array.isArray(d) ? d : (d?.data ?? [])
  }
  exams.value = unpack(examRes)
  assignments.value = unpack(assignRes)

  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }

.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s ease-out;
  max-height: 300px;
  overflow: hidden;
}
.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  margin-top: 0 !important;
  margin-bottom: 0 !important;
}
</style>
