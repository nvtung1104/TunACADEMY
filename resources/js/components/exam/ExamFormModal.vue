<template>
  <AppModal v-model="show" :title="editItem ? 'Chỉnh sửa đề thi' : 'Tạo đề thi mới'" size="xl">
    <form class="space-y-6">
      <!-- Type selector (create only) -->
      <div v-if="!editItem">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2.5">Loại đề thi <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <button v-for="t in examTypes" :key="t.value" type="button"
            @click="form.type = t.value; setDuration(t.value)"
            class="flex flex-col items-center justify-center gap-2.5 p-4 rounded-2xl border-2 text-center transition-all shadow-sm"
            :class="form.type === t.value ? t.activeClass : 'border-gray-200 bg-white hover:border-gray-300 text-gray-600'">
            <span class="text-2xl">{{ t.icon }}</span>
            <span class="text-xs font-extrabold uppercase tracking-wider">{{ t.label }}</span>
          </button>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tiêu đề đề thi <span class="text-red-500">*</span></label>
        <input v-model="form.title" class="input" required placeholder="Tên đề thi / bài kiểm tra" />
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Mô tả / Hướng dẫn đề thi</label>
        <textarea v-model="form.description" class="input resize-none" rows="2" placeholder="Nhập mô tả ngắn hoặc hướng dẫn làm bài cho học sinh..."></textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Môn học <span class="text-red-500">*</span></label>
          <select v-model="form.subject_id" class="input" required>
            <option value="">Chọn môn học</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
            Lớp học
            <span v-if="form.type !== 'practice_exam'" class="text-red-500">*</span>
            <span v-else class="text-xs text-gray-400 font-normal lowercase">(tuỳ chọn)</span>
          </label>
          <select v-model="form.classroom_id" class="input">
            <option value="">Chọn lớp học</option>
            <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Thời gian làm bài (phút)</label>
          <input v-model.number="form.duration_minutes" type="number" min="5" max="180" class="input" placeholder="Ví dụ: 15, 45, 90" />
        </div>
        <template v-if="form.type !== 'practice_exam'">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Mở từ ngày <span class="text-red-500">*</span></label>
            <input v-model="form.opened_at" type="datetime-local" class="input" />
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Đóng lúc <span class="text-red-500">*</span></label>
            <input v-model="form.closed_at" type="datetime-local" class="input" />
          </div>
        </template>
        <template v-else>
          <div class="md:col-span-2 flex items-end pb-0.5">
            <p class="text-xs text-green-650 bg-green-50/50 border border-green-150 px-4 py-3 rounded-2xl w-full font-semibold flex items-center gap-1.5 shadow-sm">
              <span>🎯</span>
              <span>Đề luyện tập ôn tập luôn ở trạng thái mở — học sinh tự do luyện tập bất kỳ lúc nào.</span>
            </p>
          </div>
        </template>
      </div>

      <!-- Checkboxes as beautiful checkable cards -->
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Tùy chọn cấu hình đề thi</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50/50 p-5 rounded-3xl border border-gray-100">
          <!-- Shuffle questions -->
          <label class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white border border-gray-150/70 hover:border-gray-300 cursor-pointer transition-all select-none shadow-sm group">
            <input type="checkbox" v-model="form.shuffle_questions" class="w-5 h-5 rounded-lg border-gray-300 text-[#d63015] focus:ring-[#d63015]/20 cursor-pointer mt-0.5 shrink-0" />
            <div>
              <p class="text-sm font-bold text-gray-800 group-hover:text-black transition-colors">Xáo trộn câu hỏi</p>
              <p class="text-[11px] text-gray-400 mt-0.5 leading-normal">Đảo vị trí câu hỏi ngẫu nhiên khi học sinh làm bài</p>
            </div>
          </label>

          <!-- Shuffle options -->
          <label class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white border border-gray-150/70 hover:border-gray-300 cursor-pointer transition-all select-none shadow-sm group">
            <input type="checkbox" v-model="form.shuffle_options" class="w-5 h-5 rounded-lg border-gray-300 text-[#d63015] focus:ring-[#d63015]/20 cursor-pointer mt-0.5 shrink-0" />
            <div>
              <p class="text-sm font-bold text-gray-800 group-hover:text-black transition-colors">Xáo trộn đáp án</p>
              <p class="text-[11px] text-gray-400 mt-0.5 leading-normal">Đảo vị trí các phương án A, B, C, D ngẫu nhiên</p>
            </div>
          </label>

          <!-- Proctoring -->
          <label v-if="form.type !== 'practice_exam'" class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white border border-gray-150/70 hover:border-gray-300 cursor-pointer transition-all select-none shadow-sm group">
            <input type="checkbox" v-model="form.proctoring_enabled" class="w-5 h-5 rounded-lg border-gray-300 text-[#d63015] focus:ring-[#d63015]/20 cursor-pointer mt-0.5 shrink-0" />
            <div>
              <p class="text-sm font-bold text-gray-800 group-hover:text-black transition-colors">Bật giám sát (Proctoring)</p>
              <p class="text-[11px] text-gray-400 mt-0.5 leading-normal">Theo dõi chuyển tab, thoát ứng dụng khi đang thi</p>
            </div>
          </label>

          <!-- Allow retake -->
          <label class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white border border-gray-150/70 hover:border-gray-300 cursor-pointer transition-all select-none shadow-sm group">
            <input type="checkbox" v-model="form.allow_retake" class="w-5 h-5 rounded-lg border-gray-300 text-[#d63015] focus:ring-[#d63015]/20 cursor-pointer mt-0.5 shrink-0" />
            <div>
              <p class="text-sm font-bold text-gray-800 group-hover:text-black transition-colors">Cho phép thi lại</p>
              <p class="text-[11px] text-gray-400 mt-0.5 leading-normal">Học sinh có thể làm lại đề thi để cải thiện điểm số</p>
            </div>
          </label>
        </div>
      </div>

      <!-- Visibility selection: radio cards -->
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2.5">Phạm vi gửi đề thi</label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label v-for="opt in visibilityOptions" :key="opt.value"
            class="flex items-start gap-3.5 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 select-none shadow-sm relative overflow-hidden"
            :class="form.visibility === opt.value
              ? 'border-[#d63015] bg-[#d63015]/5 text-[#d63015]'
               : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:shadow-md'">
            <input type="radio" :value="opt.value" v-model="form.visibility" class="sr-only" />
            <span v-html="opt.icon" class="w-5 h-5 shrink-0 mt-0.5" :class="form.visibility === opt.value ? 'text-[#d63015]' : 'text-gray-400'"></span>
            <div>
              <p class="font-bold text-sm leading-tight text-gray-900" :class="form.visibility === opt.value ? 'text-[#d63015]' : 'text-gray-900'">{{ opt.label }}</p>
              <p class="text-[11px] text-gray-400 leading-normal mt-1">{{ opt.desc }}</p>
            </div>
            <!-- Selected dot -->
            <span v-if="form.visibility === opt.value" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-[#d63015]"></span>
          </label>
        </div>
      </div>

      <!-- Grade/class picker when visibility = 'class' -->
      <div v-if="form.visibility === 'class'" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/50 p-5 rounded-3xl border border-gray-100">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Khối lớp <span class="text-red-500">*</span></label>
          <select v-model="form.grade_id" @change="form.classroom_id = ''" class="input">
            <option value="">Chọn khối lớp</option>
            <option v-for="g in grades" :key="g.id" :value="g.id">Khối {{ g.level }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Lớp nhận bài thi <span class="text-red-500">*</span></label>
          <select v-model="form.classroom_id" class="input" :disabled="!form.grade_id">
            <option value="">Chọn lớp học</option>
            <option v-for="c in filteredClassrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
      </div>

      <!-- Question bank picker (create only) -->
      <div v-if="!editItem" class="bg-gray-50/50 p-5 rounded-3xl border border-gray-100">
        <div class="flex items-center justify-between mb-3">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Chọn câu hỏi từ ngân hàng</label>
            <p class="text-[11px] text-gray-400 mt-0.5">Thêm câu hỏi có sẵn cùng môn học vào đề thi này</p>
          </div>
          <button type="button" @click="openPicker"
            class="text-xs px-3.5 py-2 rounded-xl bg-indigo-50 border border-indigo-150 text-indigo-700 hover:bg-indigo-100 hover:border-indigo-200 font-bold transition-all shadow-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Chọn câu hỏi
          </button>
        </div>
        <div v-if="pickedQuestions.length === 0"
          class="text-xs text-gray-400 bg-white rounded-2xl p-4 text-center border border-dashed border-gray-200">
          Chưa có câu hỏi nào được chọn từ ngân hàng đề.
        </div>
        <ul v-else class="space-y-2 max-h-48 overflow-y-auto">
          <li v-for="(q, idx) in pickedQuestions" :key="q.id"
            class="flex items-center justify-between gap-3 bg-white rounded-xl px-4 py-2.5 text-xs border border-gray-150 shadow-sm hover:border-gray-250 transition-colors">
            <span class="text-gray-400 shrink-0 font-bold">{{ idx + 1 }}.</span>
            <span class="truncate text-gray-700 flex-1">{{ q.content }}</span>
            <button type="button" @click="pickedQuestions = pickedQuestions.filter(x => x.id !== q.id)"
              class="shrink-0 p-1 hover:bg-red-50 rounded-lg text-gray-400 hover:text-red-650 transition-colors">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </li>
        </ul>
      </div>

      <div v-if="formError" class="text-sm text-red-650 bg-red-50 p-4 rounded-2xl border border-red-100 font-medium">{{ formError }}</div>
    </form>

    <template #footer>
      <button @click="show = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm hover:bg-gray-50 text-gray-600 font-bold transition-colors">Hủy</button>
      <button @click="save" :disabled="saving"
        class="px-6 py-2.5 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-bold shadow-md shadow-red-500/10 hover:shadow-lg transition-all">
        {{ saving ? 'Đang lưu...' : 'Lưu đề thi' }}
      </button>
    </template>
  </AppModal>

  <!-- Question Picker (teleported outside modal stack) -->
  <Teleport to="body">
    <div v-if="pickerModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="pickerModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">Chọn câu hỏi từ ngân hàng</h3>
          <button @click="pickerModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="px-6 py-3 border-b border-gray-100 flex gap-2">
          <input v-model="pickerSearch" @input="debouncePicker" type="text" placeholder="Tìm câu hỏi..."
            class="input flex-1 text-sm" />
          <select v-model="pickerSubject" @change="fetchPicker" class="input text-sm w-36">
            <option value="">Tất cả môn</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-3">
          <div v-if="pickerLoading" class="text-center py-8 text-gray-400 text-sm">Đang tải...</div>
          <div v-else-if="pickerList.length === 0" class="text-center py-8 text-gray-400 text-sm">Không có câu hỏi</div>
          <ul v-else class="space-y-2">
            <li v-for="q in pickerList" :key="q.id"
              class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
              :class="pickerSelected.has(q.id) ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
              @click="togglePick(q.id)">
              <input type="checkbox" :checked="pickerSelected.has(q.id)" @click.stop @change="togglePick(q.id)"
                class="mt-0.5 text-indigo-600 rounded" />
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800 line-clamp-2">{{ q.content }}</p>
                <div class="flex gap-2 mt-1">
                  <span class="text-[11px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ qTypeLabel(q.type) }}</span>
                  <span class="text-[11px] px-1.5 py-0.5 rounded font-medium" :class="diffClass(q.difficulty)">{{ diffLabel(q.difficulty) }}</span>
                  <span v-if="q.subject" class="text-[11px] text-gray-400">{{ q.subject.name }}</span>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
          <span class="text-sm text-gray-500">Đã chọn: <strong>{{ pickerSelected.size }}</strong> câu</span>
          <div class="flex gap-2">
            <button @click="pickerModal = false"
              class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
            <button @click="confirmPicker" :disabled="pickerSelected.size === 0"
              class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
              Thêm {{ pickerSelected.size > 0 ? pickerSelected.size + ' câu' : '' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  editItem:   { type: Object,  default: null },
  subjects:   { type: Array,   default: () => [] },
  classrooms: { type: Array,   default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'saved'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const saving    = ref(false)
const formError = ref('')

const form = reactive({
  type: 'quiz_15', title: '', description: '', classroom_id: '', grade_id: '', subject_id: '',
  duration_minutes: 15, opened_at: '', closed_at: '', visibility: 'private',
  shuffle_questions: false, shuffle_options: false, proctoring_enabled: false, allow_retake: false,
})

const pickedQuestions = ref([])
const pickerModal     = ref(false)
const pickerList      = ref([])
const pickerLoading   = ref(false)
const pickerSelected  = ref(new Set())
const pickerSearch    = ref('')
const pickerSubject   = ref('')
let   pickerDebounce  = null

const examTypes = [
  { value: 'quiz_15',      label: 'Kiểm tra 15p', icon: '⏱', activeClass: 'border-amber-400 bg-amber-50 text-amber-700' },
  { value: 'quiz_30',      label: 'Kiểm tra 30p', icon: '📋', activeClass: 'border-blue-400 bg-blue-50 text-blue-700' },
  { value: 'quiz_45',      label: 'Kiểm tra 45p', icon: '📝', activeClass: 'border-purple-400 bg-purple-50 text-purple-700' },
  { value: 'practice_exam',label: 'Đề ôn tập',    icon: '🎯', activeClass: 'border-green-400 bg-green-50 text-green-700' },
]

const visibilityOptions = [
  { value: 'private', label: 'Riêng tư',    desc: 'Chỉ bạn xem được',         icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>' },
  { value: 'class',   label: 'Gửi cho lớp', desc: 'Học sinh lớp được chọn',   icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
  { value: 'public',  label: 'Công khai',   desc: 'Tất cả học sinh đều thấy', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
]

const grades = computed(() => {
  const map = new Map()
  props.classrooms.forEach(c => { if (c.grade && !map.has(c.grade.id)) map.set(c.grade.id, c.grade) })
  return [...map.values()].sort((a, b) => a.level - b.level)
})

const filteredClassrooms = computed(() =>
  form.grade_id ? props.classrooms.filter(c => c.grade_id === form.grade_id) : []
)

watch(() => form.visibility, (v) => {
  if (v !== 'class') { form.classroom_id = ''; form.grade_id = '' }
})

watch(() => props.modelValue, (open) => {
  if (!open) return
  formError.value = ''
  if (props.editItem) {
    const e = props.editItem
    const existingClass = props.classrooms.find(c => c.id === e.classroom?.id)
    Object.assign(form, {
      type: e.type ?? 'quiz_45', title: e.title, description: e.description ?? '',
      classroom_id: e.classroom?.id ?? '', grade_id: existingClass?.grade_id ?? '',
      subject_id: e.subject?.id ?? '', duration_minutes: e.duration_minutes,
      visibility: e.visibility ?? 'private',
      opened_at: e.opened_at ? e.opened_at.slice(0, 16) : '',
      closed_at: e.closed_at ? e.closed_at.slice(0, 16) : '',
      shuffle_questions: e.shuffle_questions ?? false, shuffle_options: e.shuffle_options ?? false,
      proctoring_enabled: e.proctoring_enabled ?? false, allow_retake: e.allow_retake ?? false,
    })
  } else {
    Object.assign(form, {
      type: 'quiz_15', title: '', description: '', classroom_id: '', grade_id: '', subject_id: '',
      duration_minutes: 15, opened_at: '', closed_at: '', visibility: 'private',
      shuffle_questions: false, shuffle_options: false, proctoring_enabled: false, allow_retake: false,
    })
    pickedQuestions.value = []
  }
})

function setDuration(type) {
  form.duration_minutes = { quiz_15: 15, quiz_30: 30, quiz_45: 45, practice_exam: 60 }[type] ?? 45
}

async function save() {
  saving.value    = true
  formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.opened_at)    delete payload.opened_at
    if (!payload.closed_at)    delete payload.closed_at
    if (!payload.classroom_id) delete payload.classroom_id
    if (props.editItem) {
      await api.put(`/teacher/exams/${props.editItem.id}`, payload)
    } else {
      const { data } = await api.post('/teacher/exams', payload)
      const examId = data.data?.id
      if (examId && pickedQuestions.value.length > 0) {
        await api.post(`/teacher/exams/${examId}/import-questions`, {
          question_ids: pickedQuestions.value.map(q => q.id),
        }).catch(() => {})
      }
    }
    show.value = false
    emit('saved')
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally {
    saving.value = false
  }
}

function debouncePicker() {
  clearTimeout(pickerDebounce)
  pickerDebounce = setTimeout(fetchPicker, 350)
}

async function fetchPicker() {
  pickerLoading.value = true
  try {
    const { data } = await api.get('/teacher/question-bank', {
      params: { search: pickerSearch.value || undefined, subject_id: pickerSubject.value || undefined },
    })
    pickerList.value = data.data?.data ?? data.data ?? []
  } finally {
    pickerLoading.value = false
  }
}

function openPicker() {
  pickerSelected.value = new Set(pickedQuestions.value.map(q => q.id))
  pickerSearch.value   = ''
  pickerSubject.value  = ''
  fetchPicker()
  pickerModal.value = true
}

function togglePick(id) {
  pickerSelected.has(id) ? pickerSelected.delete(id) : pickerSelected.add(id)
}

function confirmPicker() {
  const selectedIds = [...pickerSelected.value]
  const existing    = pickerList.value.filter(q => selectedIds.includes(q.id))
  const alreadyIds  = new Set(pickedQuestions.value.map(q => q.id))
  existing.forEach(q => { if (!alreadyIds.has(q.id)) pickedQuestions.value.push(q) })
  pickerModal.value = false
}

function qTypeLabel(t) {
  return {
    multiple_choice: 'Trắc nghiệm', multiple_select: 'Nhiều đáp án', true_false: 'Đúng/Sai',
    fill_blank: 'Điền chỗ', short_answer: 'Trả lời ngắn', essay: 'Tự luận',
    ordering: 'Sắp xếp', matching: 'Ghép đôi', listening: 'Nghe hiểu',
  }[t] ?? t
}
function diffLabel(d) { return { easy: 'Dễ', medium: 'TB', hard: 'Khó' }[d] ?? d }
function diffClass(d) {
  return { easy: 'bg-green-100 text-green-700', medium: 'bg-yellow-100 text-yellow-700', hard: 'bg-red-100 text-red-700' }[d] ?? 'bg-gray-100 text-gray-500'
}
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-[#d63015] focus:outline-none focus:ring-4 focus:ring-[#d63015]/10 text-sm transition-all shadow-sm bg-white text-gray-800 font-medium; }
</style>
