<template>
  <AppModal v-model="show" :title="editItem ? 'Chỉnh sửa bài tập' : 'Giao bài tập mới'" size="xl">
    <form class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tiêu đề bài tập <span class="text-red-500">*</span></label>
          <input v-model="form.title" class="input" required placeholder="Tên bài tập (ví dụ: Bài tập về nhà hàm số)" />
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Môn học <span class="text-red-500">*</span></label>
          <select v-model="form.subject_id" class="input" required>
            <option value="">Chọn môn học</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Mô tả / Đề bài / Hướng dẫn</label>
        <textarea v-model="form.description" class="input resize-none" rows="3" placeholder="Nội dung bài tập, yêu cầu nộp bài, hướng dẫn chi tiết..."></textarea>
      </div>

      <!-- Visibility selection: radio cards -->
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2.5">Phạm vi giao bài tập</label>
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
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Lớp nhận bài tập <span class="text-red-500">*</span></label>
          <select v-model="form.classroom_id" class="input" :disabled="!form.grade_id">
            <option value="">Chọn lớp học</option>
            <option v-for="c in filteredClassrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Hạn nộp bài tập</label>
          <input v-model="form.due_date" type="datetime-local" class="input" />
        </div>
      </div>

      <!-- Question bank picker (create only) -->
      <div v-if="!editItem" class="bg-gray-50/50 p-5 rounded-3xl border border-gray-100">
        <div class="flex items-center justify-between mb-3">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Chọn câu hỏi từ ngân hàng</label>
            <p class="text-[11px] text-gray-400 mt-0.5">Thêm câu hỏi có sẵn cùng môn học vào bài tập này</p>
          </div>
          <button type="button" @click="openPicker"
            class="text-xs px-3.5 py-2 rounded-xl bg-indigo-50 border border-indigo-150 text-indigo-700 hover:bg-indigo-100 hover:border-indigo-200 font-bold transition-all shadow-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Chọn câu hỏi
          </button>
        </div>
        <div v-if="pickedQuestions.length === 0"
          class="text-xs text-gray-400 bg-white rounded-2xl p-4 text-center border border-dashed border-gray-200">
          Chưa chọn câu hỏi nào. Nhấn "Chọn câu hỏi" để lấy câu hỏi từ ngân hàng.
        </div>
        <div v-else class="space-y-2 max-h-48 overflow-y-auto">
          <div v-for="(q, idx) in pickedQuestions" :key="q.id"
            class="flex items-center justify-between gap-3 bg-white rounded-xl px-4 py-2.5 text-xs border border-gray-150 shadow-sm hover:border-gray-250 transition-colors">
            <span class="text-gray-400 shrink-0 font-bold">{{ idx + 1 }}.</span>
            <div class="flex-1 min-w-0 text-xs text-gray-700 truncate" v-html="renderMath(q.content)"></div>
            <button type="button" @click="pickedQuestions.splice(idx, 1)"
              class="shrink-0 p-1 hover:bg-red-50 rounded-lg text-gray-400 hover:text-red-655 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="formError" class="text-sm text-red-650 bg-red-50 p-4 rounded-2xl border border-red-100 font-medium">{{ formError }}</div>
    </form>
    <template #footer>
      <button @click="show = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm hover:bg-gray-50 text-gray-650 font-bold transition-colors">Hủy</button>
      <button @click="save" :disabled="saving" class="px-6 py-2.5 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-bold shadow-md shadow-red-500/10 hover:shadow-lg transition-all">
        {{ saving ? 'Đang lưu...' : 'Giao bài tập' }}
      </button>
    </template>
  </AppModal>

  <!-- Question Picker — teleported so it stacks above the form modal -->
  <Teleport to="body">
    <Transition name="overlay">
      <div v-if="pickerModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/40" @click.self="pickerModal = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[80vh]">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
            <h3 class="font-semibold text-gray-900">Chọn câu hỏi từ ngân hàng</h3>
            <button @click="pickerModal = false" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-4 space-y-3 flex-1 overflow-y-auto">
            <div class="flex gap-2">
              <input v-model="pickerSearch" @input="debouncePicker" type="text" placeholder="Tìm câu hỏi..." class="input flex-1" />
              <select v-model="pickerSubject" @change="fetchPicker" class="input w-36">
                <option value="">Tất cả môn</option>
                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div v-if="pickerLoading" class="py-8 text-center text-gray-400 text-sm">Đang tải...</div>
            <div v-else-if="pickerList.length === 0" class="py-8 text-center text-gray-400 text-sm">Không tìm thấy câu hỏi nào</div>
            <div v-else class="space-y-1.5">
              <label v-for="q in pickerList" :key="q.id"
                class="flex items-start gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-colors"
                :class="pickerSelected.includes(q.id) ? 'border-[#d63015] bg-red-50' : 'border-gray-100 hover:bg-gray-50'">
                <input type="checkbox" :value="q.id" v-model="pickerSelected" class="mt-0.5 text-[#d63015] rounded shrink-0" />
                <div class="min-w-0 flex-1">
                  <div class="text-sm text-gray-800 line-clamp-2" v-html="renderMath(q.content)"></div>
                  <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-700">{{ typeLabel(q.type) }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-md" :class="diffClass(q.difficulty)">{{ diffLabel(q.difficulty) }}</span>
                    <span v-if="q.subject" class="text-[10px] text-gray-400">{{ q.subject.name }}</span>
                  </div>
                </div>
              </label>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-100 flex items-center gap-3 shrink-0">
            <span class="text-sm text-gray-500 mr-auto">{{ pickerSelected.length }} đã chọn</span>
            <button @click="pickerModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
            <button @click="confirmPicker" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] font-medium">
              Thêm {{ pickerSelected.length }} câu
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import { renderMath } from '@/utils/math'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editItem:   { type: Object,  default: null },
  subjects:   { type: Array,   default: () => [] },
  allClassrooms: { type: Array, default: () => [] },
})
const emit = defineEmits(['update:modelValue', 'saved'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const saving    = ref(false)
const formError = ref('')
const form = reactive({ title: '', description: '', classroom_id: '', grade_id: '', subject_id: '', due_date: '', visibility: 'private' })

const pickedQuestions = ref([])
const pickerModal     = ref(false)
const pickerList      = ref([])
const pickerLoading   = ref(false)
const pickerSelected  = ref([])
const pickerSearch    = ref('')
const pickerSubject   = ref('')
let   pickerDebounce  = null

const grades = computed(() => {
  const map = new Map()
  props.allClassrooms.forEach(c => { if (c.grade && !map.has(c.grade.id)) map.set(c.grade.id, c.grade) })
  return [...map.values()].sort((a, b) => a.level - b.level)
})

const filteredClassrooms = computed(() =>
  form.grade_id ? props.allClassrooms.filter(c => c.grade_id === form.grade_id) : []
)

const visibilityOptions = [
  { value: 'private', label: 'Riêng tư',    desc: 'Chỉ bạn xem được',        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>' },
  { value: 'class',   label: 'Gửi cho lớp', desc: 'Học sinh lớp được chọn',  icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
  { value: 'public',  label: 'Công khai',   desc: 'Tất cả học sinh đều thấy', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
]

watch(() => props.modelValue, (open) => {
  if (!open) return
  formError.value = ''
  pickedQuestions.value = []
  if (props.editItem) {
    const a = props.editItem
    const existingClass = props.allClassrooms.find(c => c.id === a.classroom?.id)
    Object.assign(form, {
      title:        a.title,
      description:  a.description ?? '',
      classroom_id: a.classroom?.id ?? '',
      grade_id:     existingClass?.grade_id ?? '',
      subject_id:   a.subject?.id ?? '',
      due_date:     a.due_date ? a.due_date.slice(0, 16) : '',
      visibility:   a.visibility ?? 'private',
    })
  } else {
    Object.assign(form, { title: '', description: '', classroom_id: '', grade_id: '', subject_id: '', due_date: '', visibility: 'private' })
  }
})

watch(() => form.visibility, (v) => {
  if (v !== 'class') { form.classroom_id = ''; form.grade_id = '' }
})

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.due_date) delete payload.due_date
    if (!payload.subject_id) delete payload.subject_id
    if (!payload.classroom_id) delete payload.classroom_id
    if (props.editItem) {
      await api.put(`/teacher/assignments/${props.editItem.id}`, payload)
    } else {
      const { data } = await api.post('/teacher/assignments', payload)
      const assignmentId = data.data?.id
      if (assignmentId && pickedQuestions.value.length > 0) {
        await api.post(`/teacher/assignments/${assignmentId}/import-questions`, {
          question_ids: pickedQuestions.value.map(q => q.id),
        }).catch(() => {})
      }
    }
    show.value = false
    emit('saved')
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

function openPicker() {
  pickerSelected.value = pickedQuestions.value.map(q => q.id)
  pickerSearch.value   = ''
  pickerSubject.value  = ''
  pickerModal.value    = true
  fetchPicker()
}

function confirmPicker() {
  const ids = pickerSelected.value
  const existing = pickedQuestions.value.map(q => q.id)
  const toAdd = pickerList.value.filter(q => ids.includes(q.id) && !existing.includes(q.id))
  pickedQuestions.value = [
    ...pickedQuestions.value.filter(q => ids.includes(q.id)),
    ...toAdd,
  ]
  pickerModal.value = false
}

function debouncePicker() {
  clearTimeout(pickerDebounce)
  pickerDebounce = setTimeout(() => fetchPicker(), 400)
}

async function fetchPicker() {
  pickerLoading.value = true
  try {
    const { data } = await api.get('/teacher/question-bank', {
      params: { search: pickerSearch.value, subject_id: pickerSubject.value, per_page: 50 },
    })
    pickerList.value = data.data?.data ?? data.data ?? []
  } finally { pickerLoading.value = false }
}

function typeLabel(t) {
  return { multiple_choice: 'Chọn 1', multiple_select: 'Chọn nhiều', true_false: 'Đúng/Sai',
    fill_blank: 'Điền trống', short_answer: 'Ngắn', essay: 'Tự luận', calculation: 'Tính toán',
    ordering: 'Sắp xếp', matching: 'Nối cặp', drag_drop: 'Kéo thả',
    reading: 'Đọc hiểu', listening: 'Nghe', speaking: 'Nói', writing: 'Viết' }[t] ?? t
}
function diffLabel(d) { return { easy: 'Dễ', medium: 'TB', hard: 'Khó' }[d] ?? d }
function diffClass(d) { return { easy: 'bg-green-100 text-green-700', medium: 'bg-amber-100 text-amber-700', hard: 'bg-red-100 text-red-700' }[d] ?? '' }
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-[#d63015] focus:outline-none focus:ring-4 focus:ring-[#d63015]/10 text-sm transition-all shadow-sm bg-white text-gray-800 font-medium; }
.overlay-enter-active { transition: opacity 0.15s ease; }
.overlay-leave-active { transition: opacity 0.15s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }
</style>
