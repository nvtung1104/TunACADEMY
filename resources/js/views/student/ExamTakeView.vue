<template>
  <!-- Loading -->
  <div v-if="loading" class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-3"/>
      <p class="text-gray-500">Đang tải đề thi...</p>
    </div>
  </div>

  <!-- Error -->
  <div v-else-if="startError" class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-sm">
      <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <h2 class="text-lg font-bold text-gray-900 mb-1">Không thể vào thi</h2>
      <p class="text-sm text-gray-500 mb-4">{{ startError }}</p>
      <button @click="$router.back()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
        Quay lại
      </button>
    </div>
  </div>

  <!-- Submitted -->
  <div v-else-if="submitted" class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-sm">
      <div class="w-20 h-20 rounded-3xl bg-green-100 flex items-center justify-center mx-auto mb-5">
        <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <h2 class="text-2xl font-black text-gray-900 mb-1">Nộp bài thành công!</h2>
      <p class="text-gray-500 text-sm mb-2">Trả lời đúng {{ submitResult.total_correct }} câu</p>
      <p v-if="submitResult.score != null" class="text-4xl font-black mb-6"
        :class="submitResult.score >= 5 ? 'text-green-600' : 'text-red-500'">
        {{ submitResult.score }}<span class="text-lg text-gray-400 font-normal">/10</span>
      </p>
      <button @click="$router.push(`/student/exams/${examId}/result`)"
        class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
        Xem kết quả chi tiết
      </button>
    </div>
  </div>

  <!-- Exam interface -->
  <div v-else class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Sticky header -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-4">
        <!-- Title -->
        <div class="flex-1 min-w-0">
          <h1 class="font-bold text-gray-900 truncate text-sm sm:text-base">{{ exam?.title }}</h1>
          <p class="text-xs text-gray-400">{{ answeredCount }}/{{ questions.length }} câu đã trả lời</p>
        </div>

        <!-- Timer -->
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-mono font-bold text-sm shrink-0"
          :class="timerClass">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ timerDisplay }}
        </div>

        <!-- Submit -->
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60 shrink-0">
          {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
        </button>
      </div>

      <!-- Progress bar -->
      <div class="h-1 bg-gray-100">
        <div class="h-full bg-indigo-500 transition-all duration-300"
          :style="{ width: (answeredCount / questions.length * 100) + '%' }" />
      </div>
    </header>

    <!-- Questions -->
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-5">
      <div v-for="(q, idx) in questions" :key="q.id" :id="`q-${q.id}`"
        class="bg-white rounded-2xl border shadow-sm p-5 transition-colors"
        :class="isAnswered(q) ? 'border-indigo-100' : 'border-gray-100'">

        <!-- Question header -->
        <div class="flex items-start gap-3 mb-4">
          <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
            :class="isAnswered(q) ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'">
            {{ idx + 1 }}
          </span>
          <div class="flex-1">
            <div class="flex flex-wrap gap-1.5 mb-2">
              <span class="text-xs font-medium px-2 py-0.5 rounded-md" :class="qTypeClass(q.type)">
                {{ qTypeLabel(q.type) }}
              </span>
              <span class="text-xs font-medium text-[#d63015]">{{ q.points }} điểm</span>
            </div>
            <p class="text-gray-900 text-sm leading-relaxed whitespace-pre-line">{{ stripHtml(q.content) }}</p>
          </div>
        </div>

        <!-- Audio player (listening) -->
        <div v-if="q.audio_path" class="mb-4">
          <audio :src="'/storage/' + q.audio_path" controls class="w-full rounded-xl h-10" />
        </div>

        <!-- ── Multiple choice / True-False / Listening ── -->
        <div v-if="['multiple_choice','true_false','listening'].includes(q.type)" class="space-y-2">
          <label v-for="(opt, oi) in q.options" :key="oi"
            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
            :class="answers[q.id] === String(oi)
              ? 'border-indigo-400 bg-indigo-50'
              : 'border-gray-200 hover:border-indigo-200 hover:bg-gray-50'">
            <input type="radio" :name="`q${q.id}`" :value="String(oi)" v-model="answers[q.id]"
              class="accent-indigo-600 shrink-0" />
            <span class="text-sm font-medium text-gray-500 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
            <span class="text-sm text-gray-800">{{ optionText(opt) }}</span>
          </label>
        </div>

        <!-- ── Multiple select ── -->
        <div v-else-if="q.type === 'multiple_select'" class="space-y-2">
          <label v-for="(opt, oi) in q.options" :key="oi"
            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
            :class="(answers[q.id] || []).includes(String(oi))
              ? 'border-indigo-400 bg-indigo-50'
              : 'border-gray-200 hover:border-indigo-200 hover:bg-gray-50'">
            <input type="checkbox" :value="String(oi)"
              :checked="(answers[q.id] || []).includes(String(oi))"
              @change="toggleMulti(q.id, String(oi))"
              class="accent-indigo-600 rounded shrink-0" />
            <span class="text-sm font-medium text-gray-500 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
            <span class="text-sm text-gray-800">{{ optionText(opt) }}</span>
          </label>
        </div>

        <!-- ── Fill blank ── -->
        <div v-else-if="q.type === 'fill_blank'" class="space-y-2">
          <div v-for="(_, bi) in fillBlanks(q)" :key="bi" class="flex items-center gap-2">
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1.5 rounded-lg w-20 text-center shrink-0">
              Chỗ [{{ bi + 1 }}]
            </span>
            <input :value="(answers[q.id] || [])[bi] || ''"
              @input="setFillBlank(q.id, bi, $event.target.value)"
              class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400"
              :placeholder="`Điền vào chỗ trống ${bi + 1}`" />
          </div>
        </div>

        <!-- ── Ordering ── -->
        <div v-else-if="q.type === 'ordering'" class="space-y-2">
          <p class="text-xs text-gray-400 mb-2">Kéo hoặc dùng nút ↑↓ để sắp xếp theo thứ tự đúng.</p>
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
              <button type="button" @click="moveOrder(q, pos, 1)"
                :disabled="pos === q.options.length - 1"
                class="p-1 rounded-lg hover:bg-gray-200 disabled:opacity-30 text-gray-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- ── Matching ── -->
        <div v-else-if="q.type === 'matching'" class="space-y-2">
          <div v-for="(left, li) in q.options?.left ?? []" :key="li"
            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200">
            <span class="flex-1 text-sm text-gray-800">{{ optionText(left) }}</span>
            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            <select :value="(answers[q.id] || {})[String(li)] ?? ''"
              @change="setMatch(q.id, li, $event.target.value)"
              class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
              <option value="">-- Chọn --</option>
              <option v-for="(right, ri) in q.options?.right ?? []" :key="ri" :value="String(ri)">
                {{ optionText(right) }}
              </option>
            </select>
          </div>
        </div>

        <!-- ── Essay / Short answer / Writing ── -->
        <div v-else-if="['essay','short_answer','writing'].includes(q.type)">
          <textarea v-model="answers[q.id]" rows="5"
            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
            placeholder="Nhập câu trả lời của bạn..." />
        </div>
      </div>

      <!-- Bottom submit -->
      <div class="text-center pt-2 pb-8">
        <p class="text-sm text-gray-400 mb-3">
          Đã trả lời <strong class="text-gray-700">{{ answeredCount }}/{{ questions.length }}</strong> câu
        </p>
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 disabled:opacity-60">
          {{ submitting ? 'Đang nộp bài...' : 'Nộp bài' }}
        </button>
      </div>
    </main>
  </div>

  <!-- Confirm submit modal -->
  <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full">
      <h3 class="font-bold text-gray-900 text-lg mb-2">Xác nhận nộp bài</h3>
      <p class="text-sm text-gray-500 mb-1">
        Bạn đã trả lời <strong>{{ answeredCount }}/{{ questions.length }}</strong> câu hỏi.
      </p>
      <p v-if="answeredCount < questions.length" class="text-sm text-amber-600 font-medium mb-4">
        Còn {{ questions.length - answeredCount }} câu chưa trả lời. Bạn có chắc muốn nộp?
      </p>
      <p v-else class="text-sm text-green-600 font-medium mb-4">Đã trả lời hết tất cả câu hỏi.</p>
      <div class="flex gap-3">
        <button @click="showConfirm = false"
          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
          Làm tiếp
        </button>
        <button @click="doSubmit"
          class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
          Nộp bài
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@api/axios'

const route  = useRoute()
const router = useRouter()
const examId = route.params.id

const exam        = ref(null)
const questions   = ref([])
const attempt     = ref(null)
const answers     = reactive({})   // { [questionId]: value }
const loading     = ref(true)
const startError  = ref('')
const submitting  = ref(false)
const submitted   = ref(false)
const submitResult = ref({})
const showConfirm = ref(false)

// ── Timer ─────────────────────────────────────────────────────────────────
const remaining = ref(0)
let timerInterval = null

const timerDisplay = computed(() => {
  const s = remaining.value
  if (s <= 0) return '00:00'
  const m = Math.floor(s / 60)
  const sec = s % 60
  return `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
})

const timerClass = computed(() => {
  if (remaining.value <= 60)  return 'bg-red-100 text-red-600 animate-pulse'
  if (remaining.value <= 300) return 'bg-amber-100 text-amber-700'
  return 'bg-indigo-50 text-indigo-700'
})

function startTimer(expiresAt) {
  const update = () => {
    remaining.value = Math.max(0, Math.floor((new Date(expiresAt) - Date.now()) / 1000))
    if (remaining.value === 0) {
      clearInterval(timerInterval)
      doSubmit()
    }
  }
  update()
  timerInterval = setInterval(update, 1000)
}

onUnmounted(() => clearInterval(timerInterval))

// ── Load exam ─────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    // First get exam info
    const infoRes = await api.get(`/student/exams/${examId}`)
    exam.value = infoRes.data.data?.exam ?? infoRes.data.data

    // Start / resume attempt
    const startRes = await api.post(`/student/exams/${examId}/start`)
    const payload  = startRes.data.data
    attempt.value  = payload.attempt
    questions.value = payload.questions ?? []

    // Init ordering answers to default order
    questions.value.forEach(q => {
      if (q.type === 'ordering' && Array.isArray(q.options)) {
        answers[q.id] = q.options.map((_, i) => String(i))
      }
    })

    if (attempt.value?.expires_at) startTimer(attempt.value.expires_at)
  } catch (e) {
    startError.value = e.response?.data?.message ?? 'Không thể bắt đầu bài thi'
  } finally {
    loading.value = false
  }
})

// ── Answer helpers ────────────────────────────────────────────────────────
const answeredCount = computed(() =>
  questions.value.filter(q => isAnswered(q)).length
)

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
  // Determine number of blanks from content [1],[2]... markers or from correct_answer count
  const matches = (q.content || '').match(/\[\d+\]/g)
  const count = matches ? matches.length : 1
  return Array(count).fill(null)
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
    const { data } = await api.post(`/student/exams/${examId}/submit`, { answers })
    submitResult.value = data.data
    submitted.value = true
  } catch (e) {
    alert(e.response?.data?.message ?? 'Nộp bài thất bại. Thử lại.')
    submitting.value = false
  }
}

// ── Display helpers ───────────────────────────────────────────────────────
const stripHtml = (html) => html ? html.replace(/<[^>]*>/g, '').trim() : ''
const optionText = (opt) => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt === 'object') return stripHtml(String(opt.text ?? opt.label ?? opt.content ?? ''))
  return stripHtml(String(opt))
}

// ── Type helpers ──────────────────────────────────────────────────────────
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
