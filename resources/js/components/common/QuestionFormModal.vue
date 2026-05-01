<template>
  <AppModal v-model="show" :title="editItem ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi mới'" size="lg">
    <form class="space-y-4" @submit.prevent>
      <!-- Type + Difficulty + Points -->
      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="label">Loại câu hỏi <span class="text-red-500">*</span></label>
          <select v-model="form.type" @change="onTypeChange" class="input" required>
            <optgroup label="Trắc nghiệm">
              <option value="multiple_choice">Một đáp án</option>
              <option value="multiple_select">Nhiều đáp án</option>
              <option value="true_false">Đúng / Sai</option>
            </optgroup>
            <optgroup label="Tự luận">
              <option value="essay">Tự luận</option>
              <option value="short_answer">Trả lời ngắn</option>
              <option value="fill_blank">Điền vào chỗ trống</option>
            </optgroup>
            <optgroup label="Sắp xếp / Ghép đôi">
              <option value="ordering">Sắp xếp thứ tự</option>
              <option value="matching">Ghép đôi</option>
            </optgroup>
            <optgroup label="Nghe / Đọc">
              <option value="listening">Nghe hiểu</option>
              <option value="reading">Đọc hiểu</option>
            </optgroup>
          </select>
        </div>
        <div>
          <label class="label">Độ khó</label>
          <select v-model="form.difficulty" class="input">
            <option value="easy">Dễ</option>
            <option value="medium">Trung bình</option>
            <option value="hard">Khó</option>
          </select>
        </div>
        <div>
          <label class="label">Điểm</label>
          <input v-model.number="form.points" type="number" step="0.25" min="0.25" max="100" class="input" />
        </div>
      </div>

      <!-- ── Audio upload (listening only) ──────────────────────────────── -->
      <div v-if="form.type === 'listening'">
        <label class="label">File âm thanh <span class="text-red-500">*</span></label>

        <!-- Uploaded: show player + remove -->
        <div v-if="audioUrl" class="rounded-2xl border border-gray-200 bg-gray-50 p-4 space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-700 truncate">{{ audioFileName }}</p>
              <p class="text-xs text-gray-400">Âm thanh đã tải lên</p>
            </div>
            <button type="button" @click="removeAudio"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <!-- Native audio player -->
          <audio :src="audioUrl" controls class="w-full h-10 rounded-xl" />
        </div>

        <!-- Not uploaded yet: drop zone -->
        <div v-else
          class="relative rounded-2xl border-2 border-dashed transition-colors cursor-pointer"
          :class="isDragging ? 'border-[#d63015] bg-red-50' : 'border-gray-200 hover:border-[#d63015] hover:bg-red-50/30'"
          @dragover.prevent="isDragging = true"
          @dragleave="isDragging = false"
          @drop.prevent="onDrop"
          @click="fileInputRef?.click()">

          <input ref="fileInputRef" type="file" accept="audio/*,.mp3,.wav,.ogg,.m4a,.aac"
            class="hidden" @change="onFileSelect" />

          <div v-if="audioUploading" class="py-8 text-center">
            <div class="animate-spin w-7 h-7 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2" />
            <p class="text-sm text-gray-500">Đang tải lên...</p>
          </div>
          <div v-else class="py-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Kéo thả hoặc <span class="text-[#d63015]">chọn file âm thanh</span></p>
            <p class="text-xs text-gray-400 mt-1">MP3, WAV, OGG, M4A – tối đa 50 MB</p>
          </div>
        </div>

        <p v-if="audioUploadError" class="text-xs text-red-500 mt-1">{{ audioUploadError }}</p>
      </div>

      <!-- Content -->
      <div>
        <label class="label">
          {{ form.type === 'listening' ? 'Câu hỏi cho đoạn nghe' : 'Nội dung câu hỏi' }}
          <span class="text-red-500">*</span>
        </label>
        <textarea v-model="form.content" class="input" rows="3"
          :placeholder="contentPlaceholder"
          required />
      </div>

      <!-- ── Options: MC / MS / TF / ordering / listening ────────────────── -->
      <div v-if="needsOptions">
        <div class="flex items-center justify-between mb-2">
          <label class="label mb-0">Đáp án lựa chọn</label>
          <button v-if="form.type !== 'true_false'" type="button" @click="addOption"
            class="text-xs text-[#d63015] hover:underline font-medium">+ Thêm đáp án</button>
        </div>
        <div class="space-y-2">
          <div v-for="(opt, i) in form.options" :key="i" class="flex items-center gap-2">
            <!-- Correct answer indicator -->
            <template v-if="form.type === 'multiple_choice' || form.type === 'true_false' || form.type === 'listening'">
              <input type="radio" :value="String(i)" v-model="singleAnswer"
                class="accent-[#d63015] shrink-0" title="Chọn đây là đáp án đúng" />
            </template>
            <template v-else-if="form.type === 'multiple_select'">
              <input type="checkbox" :value="String(i)" v-model="multiAnswer"
                class="accent-[#d63015] rounded shrink-0" />
            </template>
            <template v-else-if="form.type === 'ordering'">
              <span class="text-xs text-gray-400 font-mono w-5 text-center shrink-0">{{ i + 1 }}</span>
            </template>

            <span class="w-6 text-xs font-bold text-gray-400 shrink-0">{{ String.fromCharCode(65 + i) }}.</span>
            <input v-model="form.options[i]" class="input flex-1 py-1.5"
              :placeholder="`Đáp án ${String.fromCharCode(65 + i)}`"
              :readonly="form.type === 'true_false'" />

            <button v-if="form.type !== 'true_false'" type="button" @click="removeOption(i)"
              class="p-1 text-gray-300 hover:text-red-400 shrink-0">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-1.5">
          <template v-if="form.type === 'listening'">Chọn radio để đánh dấu đáp án đúng.</template>
          <template v-else-if="form.type === 'multiple_choice' || form.type === 'true_false'">Chọn radio để đánh dấu đáp án đúng.</template>
          <template v-else-if="form.type === 'multiple_select'">Tick các ô để đánh dấu đáp án đúng (có thể chọn nhiều).</template>
          <template v-else-if="form.type === 'ordering'">Thứ tự hiển thị ở trên là thứ tự <strong>đúng</strong>. Khi thi, hệ thống sẽ trộn ngẫu nhiên.</template>
        </p>
      </div>

      <!-- ── Matching pairs ── -->
      <div v-if="form.type === 'matching'">
        <div class="flex items-center justify-between mb-2">
          <label class="label mb-0">Cặp ghép đôi</label>
          <button type="button" @click="addPair" class="text-xs text-[#d63015] hover:underline font-medium">
            + Thêm cặp
          </button>
        </div>
        <div class="space-y-2">
          <div v-for="(pair, i) in matchingPairs" :key="i" class="flex items-center gap-2">
            <span class="text-xs font-bold text-gray-400 w-5 shrink-0">{{ i + 1 }}</span>
            <input v-model="pair.left" class="input flex-1 py-1.5" :placeholder="`Vế trái ${i + 1}`" />
            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            <input v-model="pair.right" class="input flex-1 py-1.5" :placeholder="`Vế phải ${i + 1}`" />
            <button type="button" @click="removePair(i)" class="p-1 text-gray-300 hover:text-red-400 shrink-0">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-1.5">Vế phải sẽ được trộn ngẫu nhiên khi thi.</p>
      </div>

      <!-- ── Correct answer: fill_blank ── -->
      <div v-if="form.type === 'fill_blank'">
        <div class="flex items-center justify-between mb-2">
          <label class="label mb-0">Đáp án theo thứ tự chỗ trống</label>
          <button type="button" @click="addFillAnswer"
            class="text-xs text-[#d63015] hover:underline font-medium">+ Thêm chỗ trống</button>
        </div>
        <div class="space-y-2">
          <div v-for="(_, i) in fillAnswers" :key="i" class="flex items-center gap-2">
            <span class="shrink-0 w-20 text-xs font-semibold text-gray-500 bg-gray-100 rounded-lg px-2.5 py-1.5 text-center">
              Chỗ [{{ i + 1 }}]
            </span>
            <input v-model="fillAnswers[i]" class="input flex-1 py-1.5"
              :placeholder="`Đáp án chỗ trống ${i + 1}`" />
            <button v-if="fillAnswers.length > 1" type="button" @click="removeFillAnswer(i)"
              class="p-1 text-gray-300 hover:text-red-400 shrink-0">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-1.5">
          Trong nội dung câu hỏi, dùng <code class="bg-gray-100 px-1 rounded">[1]</code>, <code class="bg-gray-100 px-1 rounded">[2]</code>... để đánh dấu vị trí chỗ trống tương ứng.
        </p>
      </div>

      <!-- ── Correct answer: short_answer ── -->
      <div v-if="form.type === 'short_answer'">
        <label class="label">Đáp án mẫu</label>
        <input v-model="textAnswer" class="input" placeholder="Đáp án mẫu..." />
        <p class="text-xs text-gray-400 mt-1">Dùng để tham khảo khi chấm, không tự động chấm điểm.</p>
      </div>

      <!-- Explanation -->
      <div>
        <label class="label">Giải thích đáp án <span class="text-gray-400">(tuỳ chọn)</span></label>
        <textarea v-model="form.explanation" class="input" rows="2"
          placeholder="Giải thích tại sao đây là đáp án đúng..." />
      </div>

      <div v-if="error" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ error }}</div>
    </form>

    <template #footer>
      <button @click="show = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">
        Hủy
      </button>
      <button @click="submit" :disabled="saving || audioUploading"
        class="px-4 py-2 text-sm rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white font-semibold disabled:opacity-60">
        {{ saving ? 'Đang lưu...' : (editItem ? 'Cập nhật' : 'Thêm câu hỏi') }}
      </button>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import AppModal from '@components/common/AppModal.vue'
import api from '@api/axios'

function stripHtml(html) {
  return html ? html.replace(/<[^>]*>/g, '').trim() : ''
}

const props = defineProps({
  modelValue: Boolean,
  editItem: { type: Object, default: null },
  saving: Boolean,
  error: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue', 'save'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

// ── Types that show options list ──────────────────────────────────────────
const NEEDS_OPTIONS_TYPES = ['multiple_choice', 'multiple_select', 'true_false', 'ordering', 'listening']
const needsOptions = computed(() => NEEDS_OPTIONS_TYPES.includes(form.type))

// ── Audio state ───────────────────────────────────────────────────────────
const fileInputRef    = ref(null)
const audioPath       = ref('')   // stored path (goes to audio_path field)
const audioUrl        = ref('')   // preview URL
const audioFileName   = ref('')
const audioUploading  = ref(false)
const audioUploadError = ref('')
const isDragging      = ref(false)

// ── Form state ────────────────────────────────────────────────────────────
const form = reactive({
  type: 'multiple_choice',
  content: '',
  difficulty: 'medium',
  chapter_tag: '',
  points: 1,
  options: ['', '', '', ''],
  explanation: '',
})

const singleAnswer  = ref('0')
const multiAnswer   = ref([])
const textAnswer    = ref('')       // short_answer only
const fillAnswers   = ref([''])     // fill_blank: one entry per blank
const matchingPairs = ref([{ left: '', right: '' }, { left: '', right: '' }])

const contentPlaceholder = computed(() => {
  if (form.type === 'listening')  return 'VD: Người nói đang làm gì trong đoạn hội thoại?'
  if (form.type === 'fill_blank') return 'VD: Hà Nội là thủ đô của [1], có diện tích [2] km²'
  return 'Nhập nội dung câu hỏi...'
})

// ── Populate form when editing ────────────────────────────────────────────
watch(() => props.editItem, (item) => {
  if (!item) { resetForm(); return }

  form.type        = item.type ?? 'multiple_choice'
  form.content     = stripHtml(item.content ?? '')
  form.difficulty  = item.difficulty ?? 'medium'
  form.chapter_tag = item.chapter_tag ?? ''
  form.points      = item.points ?? 1
  form.explanation = item.explanation ?? ''

  // Reset audio state first
  audioPath.value  = ''
  audioUrl.value   = ''
  audioFileName.value = ''

  const type = item.type

  if (NEEDS_OPTIONS_TYPES.includes(type)) {
    form.options = Array.isArray(item.options) ? [...item.options] : ['', '', '', '']
    if (type === 'multiple_choice' || type === 'true_false' || type === 'listening') {
      const ans = item.correct_answer
      singleAnswer.value = Array.isArray(ans) ? (ans[0] ?? '0') : (ans ?? '0')
    } else if (type === 'multiple_select') {
      multiAnswer.value = Array.isArray(item.correct_answer) ? [...item.correct_answer] : []
    }

    // Restore audio when editing a listening question
    if (type === 'listening' && item.audio_path) {
      audioPath.value    = item.audio_path
      audioUrl.value     = '/storage/' + item.audio_path
      audioFileName.value = item.audio_path.split('/').pop()
    }
  } else if (type === 'matching') {
    const left = item.options?.left ?? []
    const right = item.options?.right ?? []
    const ans   = item.correct_answer ?? {}
    matchingPairs.value = left.map((l, i) => ({
      left: l,
      right: right[ans[String(i)] ?? i] ?? right[i] ?? '',
    }))
    if (!matchingPairs.value.length) matchingPairs.value = [{ left: '', right: '' }]
  } else if (type === 'fill_blank') {
    const ans = item.correct_answer
    fillAnswers.value = Array.isArray(ans) && ans.length ? [...ans] : ['']
  } else if (type === 'short_answer') {
    textAnswer.value = Array.isArray(item.correct_answer)
      ? (item.correct_answer[0] ?? '')
      : (item.correct_answer ?? '')
  }
}, { immediate: true })

watch(() => props.modelValue, (v) => {
  if (v && !props.editItem) resetForm()
})

function resetForm() {
  form.type        = 'multiple_choice'
  form.content     = ''
  form.difficulty  = 'medium'
  form.chapter_tag = ''
  form.points      = 1
  form.options     = ['', '', '', '']
  form.explanation = ''
  singleAnswer.value   = '0'
  multiAnswer.value    = []
  textAnswer.value     = ''
  fillAnswers.value    = ['']
  matchingPairs.value  = [{ left: '', right: '' }, { left: '', right: '' }]
  audioPath.value      = ''
  audioUrl.value       = ''
  audioFileName.value  = ''
  audioUploadError.value = ''
}

function onTypeChange() {
  if (form.type === 'true_false') {
    form.options = ['Đúng', 'Sai']
    singleAnswer.value = '0'
  } else if (form.type === 'listening') {
    form.options = ['', '', '', '']
    singleAnswer.value = '0'
  } else if (NEEDS_OPTIONS_TYPES.includes(form.type) && form.options.length < 2) {
    form.options = ['', '', '', '']
  }
  multiAnswer.value    = []
  textAnswer.value     = ''
  fillAnswers.value    = ['']
  matchingPairs.value  = [{ left: '', right: '' }, { left: '', right: '' }]
  if (form.type !== 'listening') {
    audioPath.value = ''
    audioUrl.value  = ''
    audioFileName.value = ''
  }
}

// ── Audio upload helpers ──────────────────────────────────────────────────
async function uploadFile(file) {
  if (!file) return
  audioUploadError.value = ''
  audioUploading.value   = true

  // Local preview immediately
  audioUrl.value      = URL.createObjectURL(file)
  audioFileName.value = file.name

  const fd = new FormData()
  fd.append('file', file)

  try {
    const { data } = await api.post('/admin/content/media/audio', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    audioPath.value = data.data.path
    audioUrl.value  = data.data.url  // replace blob with real URL
  } catch (e) {
    audioUploadError.value = e.response?.data?.message ?? 'Tải lên thất bại. Thử lại.'
    audioUrl.value      = ''
    audioFileName.value = ''
  } finally {
    audioUploading.value = false
  }
}

function onFileSelect(e) {
  const file = e.target.files?.[0]
  if (file) uploadFile(file)
  e.target.value = ''
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  if (file) uploadFile(file)
}

function removeAudio() {
  audioPath.value      = ''
  audioUrl.value       = ''
  audioFileName.value  = ''
  audioUploadError.value = ''
}

// ── Options helpers ───────────────────────────────────────────────────────
function addFillAnswer()    { fillAnswers.value.push('') }
function removeFillAnswer(i) { fillAnswers.value.splice(i, 1) }

function addOption() { form.options.push('') }
function removeOption(i) {
  form.options.splice(i, 1)
  singleAnswer.value = '0'
  multiAnswer.value  = multiAnswer.value
    .filter(v => Number(v) !== i)
    .map(v => Number(v) > i ? String(Number(v) - 1) : v)
}
function addPair()      { matchingPairs.value.push({ left: '', right: '' }) }
function removePair(i)  { matchingPairs.value.splice(i, 1) }

// ── Build payload ─────────────────────────────────────────────────────────
function buildPayload() {
  const base = {
    type:           form.type,
    content:        form.content,
    difficulty:     form.difficulty,
    chapter_tag:    form.chapter_tag || null,
    points:         form.points,
    explanation:    form.explanation || null,
    options:        null,
    correct_answer: null,
    audio_path:     null,
  }

  if (form.type === 'multiple_choice' || form.type === 'true_false') {
    base.options        = [...form.options]
    base.correct_answer = [singleAnswer.value]
  } else if (form.type === 'listening') {
    base.options        = [...form.options]
    base.correct_answer = [singleAnswer.value]
    base.audio_path     = audioPath.value || null
  } else if (form.type === 'multiple_select') {
    base.options        = [...form.options]
    base.correct_answer = [...multiAnswer.value]
  } else if (form.type === 'ordering') {
    base.options        = [...form.options]
    base.correct_answer = form.options.map((_, i) => String(i))
  } else if (form.type === 'matching') {
    const leftArr = matchingPairs.value.map(p => p.left)
    const rightArr = matchingPairs.value.map(p => p.right)
    base.options = { left: leftArr, right: rightArr }
    const ans = {}
    matchingPairs.value.forEach((_, i) => { ans[String(i)] = String(i) })
    base.correct_answer = ans
  } else if (form.type === 'fill_blank') {
    base.correct_answer = [...fillAnswers.value]
  } else if (form.type === 'short_answer') {
    base.correct_answer = [textAnswer.value]
  }

  return base
}

function submit() {
  emit('save', buildPayload())
}
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm bg-white; }
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
</style>
