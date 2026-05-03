<template>
  <!-- Loading -->
  <div v-if="loading" class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin w-8 h-8 border-2 border-green-500 border-t-transparent rounded-full mx-auto mb-3"/>
      <p class="text-gray-500">Đang tải đề thi...</p>
    </div>
  </div>

  <!-- Error -->
  <div v-else-if="loadError" class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-sm">
      <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <h2 class="text-lg font-bold text-gray-900 mb-1">Không thể tải đề thi</h2>
      <p class="text-sm text-gray-500 mb-4">{{ loadError }}</p>
      <RouterLink to="/exams" class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-medium hover:bg-green-700 inline-block">
        Quay lại
      </RouterLink>
    </div>
  </div>

  <!-- Result screen -->
  <div v-else-if="result" class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full text-center">
      <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-6"
        :class="result.score >= 5 ? 'bg-green-100' : 'bg-red-100'">
        <svg class="w-12 h-12" :class="result.score >= 5 ? 'text-green-500' : 'text-red-400'"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            :d="result.score >= 5 ? 'M5 13l4 4L19 7' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
        </svg>
      </div>
      <h2 class="text-2xl font-black text-gray-900 mb-1">Kết quả</h2>
      <p class="text-gray-500 text-sm mb-4">Trả lời đúng {{ result.total_correct }}/{{ result.total }} câu</p>
      <p class="text-5xl font-black mb-8" :class="result.score >= 5 ? 'text-green-600' : 'text-red-500'">
        {{ result.score }}<span class="text-xl text-gray-400 font-normal">/10</span>
      </p>

      <!-- Question review -->
      <div v-if="Object.keys(result.detail || {}).length > 0" class="text-left space-y-3 mb-8">
        <p class="text-sm font-semibold text-gray-700 mb-2">Xem lại đáp án</p>
        <div v-for="(q, idx) in questions" :key="q.id"
          class="p-3 rounded-xl border text-sm"
          :class="result.detail[q.id]?.is_correct ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
          <div class="flex items-start gap-2">
            <span class="shrink-0 font-bold mt-0.5" :class="result.detail[q.id]?.is_correct ? 'text-green-600' : 'text-red-500'">
              {{ result.detail[q.id]?.is_correct ? '✓' : '✗' }}
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-gray-700 font-medium line-clamp-2">{{ idx + 1 }}. {{ q.content }}</p>
              <p v-if="!result.detail[q.id]?.is_correct" class="text-xs text-green-700 mt-1">
                Đáp án đúng: {{ formatCorrectAnswer(result.detail[q.id]?.correct_answer, q) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-3">
        <button @click="restart"
          class="w-full px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
          Làm lại
        </button>
        <button @click="closeTab"
          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
          Quay lại trang đề thi
        </button>
      </div>
    </div>
  </div>

  <!-- Exam interface -->
  <div v-else class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Sticky header -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-4">
        <button @click="closeTab" class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 shrink-0" title="Đóng">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <div class="flex-1 min-w-0">
          <h1 class="font-bold text-gray-900 truncate text-sm sm:text-base">{{ exam?.title }}</h1>
          <p class="text-xs text-gray-400">{{ answeredCount }}/{{ questions.length }} câu đã trả lời</p>
        </div>

        <!-- Timer -->
        <div v-if="timerActive" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-mono font-bold text-sm shrink-0"
          :class="timerClass">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ timerDisplay }}
        </div>

        <button @click="confirmSubmit" :disabled="submitting"
          class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 disabled:opacity-60 shrink-0">
          {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
        </button>
      </div>

      <!-- Progress bar -->
      <div class="h-1 bg-gray-100">
        <div class="h-full bg-green-500 transition-all duration-300"
          :style="{ width: questions.length ? (answeredCount / questions.length * 100) + '%' : '0%' }" />
      </div>
    </header>

    <!-- Questions -->
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-5">
      <div v-for="(q, idx) in questions" :key="q.id"
        class="bg-white rounded-2xl border shadow-sm p-5 transition-colors"
        :class="isAnswered(q) ? 'border-green-100' : 'border-gray-100'">

        <div class="flex items-start gap-3 mb-4">
          <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
            :class="isAnswered(q) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ idx + 1 }}
          </span>
          <div class="flex-1">
            <div class="flex flex-wrap gap-1.5 mb-2">
              <span class="text-xs font-medium px-2 py-0.5 rounded-md" :class="qTypeClass(q.type)">
                {{ qTypeLabel(q.type) }}
              </span>
              <span v-if="q.points" class="text-xs font-medium text-[#d63015]">{{ q.points }} điểm</span>
            </div>
            <p class="text-gray-900 text-sm leading-relaxed whitespace-pre-line">{{ stripHtml(q.content) }}</p>
          </div>
        </div>

        <!-- Audio -->
        <div v-if="q.audio_path" class="mb-4">
          <audio :src="'/storage/' + q.audio_path" controls class="w-full rounded-xl h-10" />
        </div>

        <!-- Multiple choice / True-False / Listening -->
        <div v-if="['multiple_choice','true_false','listening'].includes(q.type)" class="space-y-2">
          <label v-for="(opt, oi) in q.options" :key="oi"
            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
            :class="answers[q.id] === String(oi) ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200 hover:bg-gray-50'">
            <input type="radio" :name="`q${q.id}`" :value="String(oi)" v-model="answers[q.id]" class="accent-green-600 shrink-0" />
            <span class="text-sm font-medium text-gray-500 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
            <span class="text-sm text-gray-800">{{ optionText(opt) }}</span>
          </label>
        </div>

        <!-- Multiple select -->
        <div v-else-if="q.type === 'multiple_select'" class="space-y-2">
          <label v-for="(opt, oi) in q.options" :key="oi"
            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
            :class="(answers[q.id] || []).includes(String(oi)) ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200 hover:bg-gray-50'">
            <input type="checkbox" :value="String(oi)"
              :checked="(answers[q.id] || []).includes(String(oi))"
              @change="toggleMulti(q.id, String(oi))" class="accent-green-600 rounded shrink-0" />
            <span class="text-sm font-medium text-gray-500 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
            <span class="text-sm text-gray-800">{{ optionText(opt) }}</span>
          </label>
        </div>

        <!-- Fill blank -->
        <div v-else-if="q.type === 'fill_blank'" class="space-y-2">
          <div v-for="(_, bi) in fillBlanks(q)" :key="bi" class="flex items-center gap-2">
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1.5 rounded-lg w-20 text-center shrink-0">
              Chỗ [{{ bi + 1 }}]
            </span>
            <input :value="(answers[q.id] || [])[bi] || ''"
              @input="setFillBlank(q.id, bi, $event.target.value)"
              class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400"
              :placeholder="`Điền vào chỗ trống ${bi + 1}`" />
          </div>
        </div>

        <!-- Ordering -->
        <div v-else-if="q.type === 'ordering'" class="space-y-2">
          <p class="text-xs text-gray-400 mb-2">Dùng nút ↑↓ để sắp xếp đúng thứ tự.</p>
          <div v-for="(itemIdx, pos) in (answers[q.id] || q.options.map((_,i) => String(i)))" :key="pos"
            class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 bg-gray-50">
            <span class="w-6 text-xs font-bold text-gray-400 shrink-0">{{ pos + 1 }}.</span>
            <span class="flex-1 text-sm text-gray-800">{{ optionText(q.options[Number(itemIdx)]) }}</span>
            <div class="flex gap-1 shrink-0">
              <button type="button" @click="moveOrder(q, pos, -1)" :disabled="pos === 0"
                class="p-1 rounded-lg hover:bg-gray-200 disabled:opacity-30 text-gray-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
              </button>
              <button type="button" @click="moveOrder(q, pos, 1)" :disabled="pos === q.options.length - 1"
                class="p-1 rounded-lg hover:bg-gray-200 disabled:opacity-30 text-gray-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Matching -->
        <div v-else-if="q.type === 'matching'" class="space-y-2">
          <div v-for="(left, li) in q.options?.left ?? []" :key="li"
            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200">
            <span class="flex-1 text-sm text-gray-800">{{ optionText(left) }}</span>
            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            <select :value="(answers[q.id] || {})[String(li)] ?? ''"
              @change="setMatch(q.id, li, $event.target.value)"
              class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
              <option value="">-- Chọn --</option>
              <option v-for="(right, ri) in q.options?.right ?? []" :key="ri" :value="String(ri)">{{ optionText(right) }}</option>
            </select>
          </div>
        </div>

        <!-- Essay / Short answer / Writing -->
        <div v-else-if="['essay','short_answer','writing'].includes(q.type)">
          <textarea v-model="answers[q.id]" rows="5"
            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"
            placeholder="Nhập câu trả lời của bạn..." />
        </div>
      </div>

      <!-- Bottom submit -->
      <div class="text-center pt-2 pb-8">
        <p class="text-sm text-gray-400 mb-3">
          Đã trả lời <strong class="text-gray-700">{{ answeredCount }}/{{ questions.length }}</strong> câu
        </p>
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-8 py-3 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 disabled:opacity-60">
          {{ submitting ? 'Đang nộp bài...' : 'Nộp bài' }}
        </button>
      </div>
    </main>
  </div>

  <!-- Confirm modal -->
  <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full">
      <h3 class="font-bold text-gray-900 text-lg mb-2">Xác nhận nộp bài</h3>
      <p class="text-sm text-gray-500 mb-1">
        Bạn đã trả lời <strong>{{ answeredCount }}/{{ questions.length }}</strong> câu hỏi.
      </p>
      <p v-if="answeredCount < questions.length" class="text-sm text-amber-600 font-medium mb-4">
        Còn {{ questions.length - answeredCount }} câu chưa trả lời.
      </p>
      <p v-else class="text-sm text-green-600 font-medium mb-4">Đã trả lời hết tất cả câu hỏi.</p>
      <div class="flex gap-3">
        <button @click="showConfirm = false"
          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
          Làm tiếp
        </button>
        <button @click="doSubmit"
          class="flex-1 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
          Nộp bài
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import publicApi from '@api/public'

const route   = useRoute()
const examId  = route.params.id

const exam      = ref(null)
const questions = ref([])
const answers   = reactive({})
const loading   = ref(true)
const loadError = ref('')
const submitting = ref(false)
const showConfirm = ref(false)
const result    = ref(null)

// ── Draft persistence ─────────────────────────────────────────────────────
const draftKey = `exam_draft_${examId}`
let startedAt = null

function saveDraft() {
  if (result.value || !exam.value) return
  localStorage.setItem(draftKey, JSON.stringify({
    answers: JSON.parse(JSON.stringify(answers)),
    startedAt,
    durationMinutes: exam.value.duration_minutes
  }))
}

function clearDraft() {
  localStorage.removeItem(draftKey)
}

function onVisibilityHide() {
  if (document.hidden) saveDraft()
}

// ── Timer ─────────────────────────────────────────────────────────────────
const remaining  = ref(0)
const timerActive = ref(false)
let timerInterval = null

const timerDisplay = computed(() => {
  const s = remaining.value
  const m = Math.floor(s / 60)
  return `${String(m).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`
})
const timerClass = computed(() => {
  if (remaining.value <= 60)  return 'bg-red-100 text-red-600 animate-pulse'
  if (remaining.value <= 300) return 'bg-amber-100 text-amber-700'
  return 'bg-green-50 text-green-700'
})

function startTimer(seconds) {
  remaining.value = seconds
  timerActive.value = true
  timerInterval = setInterval(() => {
    if (remaining.value <= 0) { clearInterval(timerInterval); doSubmit(); return }
    remaining.value--
  }, 1000)
}

onUnmounted(() => {
  clearInterval(timerInterval)
  window.removeEventListener('beforeunload', saveDraft)
  document.removeEventListener('visibilitychange', onVisibilityHide)
})

// ── Load ──────────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const { data } = await publicApi.examTake(examId)
    exam.value = data.data.exam
    questions.value = data.data.questions

    questions.value.forEach(q => {
      if (q.type === 'ordering' && Array.isArray(q.options)) {
        answers[q.id] = q.options.map((_, i) => String(i))
      }
    })

    if (exam.value.duration_minutes) {
      let resumeSecs = null
      const draftRaw = localStorage.getItem(draftKey)
      if (draftRaw) {
        try {
          const draft = JSON.parse(draftRaw)
          const elapsed = Math.floor((Date.now() - draft.startedAt) / 1000)
          const left = draft.durationMinutes * 60 - elapsed
          if (left > 0) {
            Object.assign(answers, draft.answers || {})
            startedAt = draft.startedAt
            resumeSecs = left
          }
        } catch {}
      }
      if (resumeSecs === null) {
        startedAt = Date.now()
        resumeSecs = exam.value.duration_minutes * 60
      }
      startTimer(resumeSecs)
    }

    window.addEventListener('beforeunload', saveDraft)
    document.addEventListener('visibilitychange', onVisibilityHide)
  } catch (e) {
    loadError.value = e.response?.data?.message ?? 'Không thể tải đề thi'
  } finally {
    loading.value = false
  }
})

// ── Answer helpers ────────────────────────────────────────────────────────
const answeredCount = computed(() => questions.value.filter(q => isAnswered(q)).length)

function isAnswered(q) {
  const a = answers[q.id]
  if (a === undefined || a === null || a === '') return false
  if (Array.isArray(a)) return a.some(v => v !== '' && v !== undefined && v !== null)
  if (typeof a === 'object') return Object.keys(a).length > 0
  return true
}

function toggleMulti(qId, val) {
  if (!answers[qId]) answers[qId] = []
  const idx = answers[qId].indexOf(val)
  if (idx === -1) answers[qId].push(val)
  else answers[qId].splice(idx, 1)
}

function fillBlanks(q) {
  const matches = (q.content || '').match(/\[\d+\]/g)
  return Array(matches ? matches.length : 1).fill(null)
}

function setFillBlank(qId, idx, val) {
  if (!Array.isArray(answers[qId])) answers[qId] = []
  answers[qId][idx] = val
}

function setMatch(qId, leftIdx, rightVal) {
  if (typeof answers[qId] !== 'object' || Array.isArray(answers[qId])) answers[qId] = {}
  answers[qId][String(leftIdx)] = rightVal
}

function moveOrder(q, pos, dir) {
  if (!answers[q.id]) answers[q.id] = q.options.map((_, i) => String(i))
  const arr = [...answers[q.id]]
  const newPos = pos + dir
  if (newPos < 0 || newPos >= arr.length) return
  ;[arr[pos], arr[newPos]] = [arr[newPos], arr[pos]]
  answers[q.id] = arr
}

// ── Submit ────────────────────────────────────────────────────────────────
function confirmSubmit() { showConfirm.value = true }

async function doSubmit() {
  showConfirm.value = false
  submitting.value  = true
  clearInterval(timerInterval)
  try {
    const { data } = await publicApi.examSubmit(examId, answers)
    clearDraft()
    result.value = data.data
    localStorage.setItem(`exam_result_${examId}`, JSON.stringify({
      score: data.data.score,
      total_correct: data.data.total_correct,
      total: data.data.total,
      completedAt: new Date().toISOString()
    }))
  } catch (e) {
    alert(e.response?.data?.message ?? 'Nộp bài thất bại. Thử lại.')
    submitting.value = false
  }
}

function restart() {
  clearDraft()
  result.value = null
  Object.keys(answers).forEach(k => delete answers[k])
  questions.value.forEach(q => {
    if (q.type === 'ordering' && Array.isArray(q.options)) {
      answers[q.id] = q.options.map((_, i) => String(i))
    }
  })
  startedAt = Date.now()
  if (exam.value.duration_minutes) startTimer(exam.value.duration_minutes * 60)
}

// ── Display helpers ───────────────────────────────────────────────────────
const stripHtml = (html) => html ? html.replace(/<[^>]*>/g, '').trim() : ''
const optionText = (opt) => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt === 'object') return stripHtml(String(opt.text ?? opt.label ?? opt.content ?? ''))
  return stripHtml(String(opt))
}

function closeTab() {
  window.close()
  setTimeout(() => { window.location.href = `/exams/${examId}/preview` }, 300)
}

// ── Helpers ───────────────────────────────────────────────────────────────
function formatCorrectAnswer(correct, q) {
  if (correct === null || correct === undefined) return '—'
  if (Array.isArray(correct)) {
    if (q.type === 'fill_blank') return correct.join(', ')
    return correct.map(i => q.options?.[Number(i)] ?? i).join(', ')
  }
  if (typeof correct === 'object') return JSON.stringify(correct)
  return q.options?.[Number(correct)] ?? String(correct)
}

const qTypeLabel = (t) => ({
  multiple_choice: 'Trắc nghiệm', multiple_select: 'Nhiều đáp án', true_false: 'Đúng/Sai',
  fill_blank: 'Điền chỗ trống', short_answer: 'Trả lời ngắn', essay: 'Tự luận',
  ordering: 'Sắp xếp', matching: 'Ghép đôi', listening: 'Nghe hiểu', writing: 'Viết',
}[t] ?? t)

const qTypeClass = (t) => ({
  multiple_choice: 'bg-blue-100 text-blue-700', multiple_select: 'bg-indigo-100 text-indigo-700',
  true_false: 'bg-teal-100 text-teal-700', fill_blank: 'bg-orange-100 text-orange-700',
  short_answer: 'bg-yellow-100 text-yellow-700', essay: 'bg-gray-100 text-gray-600',
  ordering: 'bg-pink-100 text-pink-700', matching: 'bg-purple-100 text-purple-700',
  listening: 'bg-green-100 text-green-700', writing: 'bg-cyan-100 text-cyan-700',
}[t] ?? 'bg-gray-100 text-gray-500')
</script>
