<template>
  <!-- Loading -->
  <div v-if="loading" class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full mx-auto mb-3"/>
      <p class="text-gray-500">Đang tải bài tập...</p>
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
      <h2 class="text-lg font-bold text-gray-900 mb-1">Không thể tải bài tập</h2>
      <p class="text-sm text-gray-500 mb-4">{{ loadError }}</p>
      <RouterLink to="/practice" class="px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 inline-block">
        Quay lại
      </RouterLink>
    </div>
  </div>

  <!-- Result screen -->
  <div v-else-if="result" class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full text-center">
      <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-6"
        :class="(result.score ?? 0) >= 5 ? 'bg-green-100' : 'bg-amber-100'">
        <svg class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <h2 class="text-2xl font-black text-gray-900 mb-1">Hoàn thành!</h2>

      <template v-if="result.score != null">
        <p class="text-gray-500 text-sm mb-4">Trả lời đúng {{ result.total_correct }}/{{ result.total }} câu</p>
        <p class="text-5xl font-black mb-8" :class="result.score >= 5 ? 'text-green-600' : 'text-red-500'">
          {{ result.score }}<span class="text-xl text-gray-400 font-normal">/10</span>
        </p>

        <!-- Review -->
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
      </template>
      <template v-else>
        <p class="text-gray-500 text-sm mb-8">Bài tự luận / tự làm đã được ghi nhận.</p>
      </template>

      <div class="flex flex-col gap-3">
        <button @click="restart"
          class="w-full px-4 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600">
          Làm lại
        </button>
        <button @click="closeTab"
          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
          Quay lại trang bài tập
        </button>
      </div>
    </div>
  </div>

  <!-- Assignment interface -->
  <div v-else class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-4">
        <button @click="closeTab" class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 shrink-0" title="Đóng">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <div class="flex-1 min-w-0">
          <h1 class="font-bold text-gray-900 truncate text-sm sm:text-base">{{ assignment?.title }}</h1>
          <p class="text-xs text-gray-400">{{ assignment?.subject?.name }}</p>
        </div>
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 disabled:opacity-60 shrink-0">
          {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
        </button>
      </div>
      <div v-if="questions.length > 0" class="h-1 bg-gray-100">
        <div class="h-full bg-amber-500 transition-all duration-300"
          :style="{ width: questions.length ? (answeredCount / questions.length * 100) + '%' : '0%' }" />
      </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-5">
      <!-- Description -->
      <div v-if="assignment?.description" class="bg-amber-50 rounded-2xl p-4 text-sm text-amber-800">
        <p class="font-semibold text-amber-900 mb-1">Hướng dẫn</p>
        {{ assignment.description }}
      </div>

      <!-- Question-based types -->
      <template v-if="questions.length > 0">
        <div class="text-sm text-gray-500 font-medium">
          Đã trả lời <strong class="text-gray-700">{{ answeredCount }}/{{ questions.length }}</strong> câu
        </div>

        <div v-for="(q, idx) in questions" :key="q.id"
          class="bg-white rounded-2xl border shadow-sm p-5 transition-colors"
          :class="isAnswered(q) ? 'border-amber-100' : 'border-gray-100'">

          <div class="flex items-start gap-3 mb-4">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
              :class="isAnswered(q) ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'">
              {{ idx + 1 }}
            </span>
            <div class="flex-1">
              <div class="flex flex-wrap gap-1.5 mb-2">
                <span class="text-xs font-medium px-2 py-0.5 rounded-md" :class="qTypeClass(q.type)">{{ qTypeLabel(q.type) }}</span>
                <span v-if="q.points" class="text-xs font-medium text-[#d63015]">{{ q.points }} điểm</span>
              </div>
              <p class="text-gray-900 text-sm leading-relaxed whitespace-pre-line">{{ stripHtml(q.content) }}</p>
            </div>
          </div>

          <div v-if="q.audio_path" class="mb-4">
            <audio :src="'/storage/' + q.audio_path" controls class="w-full rounded-xl h-10" />
          </div>

          <div v-if="['multiple_choice','true_false','listening'].includes(q.type)" class="space-y-2">
            <label v-for="(opt, oi) in q.options" :key="oi"
              class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
              :class="answers[q.id] === String(oi) ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-200 hover:bg-gray-50'">
              <input type="radio" :name="`q${q.id}`" :value="String(oi)" v-model="answers[q.id]" class="accent-amber-500 shrink-0" />
              <span class="text-sm font-medium text-gray-500 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
              <span class="text-sm text-gray-800">{{ optionText(opt) }}</span>
            </label>
          </div>

          <div v-else-if="q.type === 'multiple_select'" class="space-y-2">
            <label v-for="(opt, oi) in q.options" :key="oi"
              class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
              :class="(answers[q.id] || []).includes(String(oi)) ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-200 hover:bg-gray-50'">
              <input type="checkbox" :value="String(oi)"
                :checked="(answers[q.id] || []).includes(String(oi))"
                @change="toggleMulti(q.id, String(oi))" class="accent-amber-500 rounded shrink-0" />
              <span class="text-sm font-medium text-gray-500 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
              <span class="text-sm text-gray-800">{{ optionText(opt) }}</span>
            </label>
          </div>

          <div v-else-if="q.type === 'fill_blank'" class="space-y-2">
            <div v-for="(_, bi) in fillBlanks(q)" :key="bi" class="flex items-center gap-2">
              <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1.5 rounded-lg w-20 text-center shrink-0">Chỗ [{{ bi + 1 }}]</span>
              <input :value="(answers[q.id] || [])[bi] || ''" @input="setFillBlank(q.id, bi, $event.target.value)"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400"
                :placeholder="`Điền vào chỗ trống ${bi + 1}`" />
            </div>
          </div>

          <div v-else-if="q.type === 'ordering'" class="space-y-2">
            <p class="text-xs text-gray-400 mb-2">Dùng nút ↑↓ để sắp xếp đúng thứ tự.</p>
            <div v-for="(itemIdx, pos) in (answers[q.id] || q.options.map((_,i) => String(i)))" :key="pos"
              class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 bg-gray-50">
              <span class="w-6 text-xs font-bold text-gray-400 shrink-0">{{ pos + 1 }}.</span>
              <span class="flex-1 text-sm text-gray-800">{{ optionText(q.options[Number(itemIdx)]) }}</span>
              <div class="flex gap-1 shrink-0">
                <button type="button" @click="moveOrder(q, pos, -1)" :disabled="pos === 0" class="p-1 rounded-lg hover:bg-gray-200 disabled:opacity-30 text-gray-500">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" @click="moveOrder(q, pos, 1)" :disabled="pos === q.options.length - 1" class="p-1 rounded-lg hover:bg-gray-200 disabled:opacity-30 text-gray-500">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
              </div>
            </div>
          </div>

          <div v-else-if="q.type === 'matching'" class="space-y-2">
            <div v-for="(left, li) in q.options?.left ?? []" :key="li" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200">
              <span class="flex-1 text-sm text-gray-800">{{ optionText(left) }}</span>
              <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              <select :value="(answers[q.id] || {})[String(li)] ?? ''" @change="setMatch(q.id, li, $event.target.value)"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                <option value="">-- Chọn --</option>
                <option v-for="(right, ri) in q.options?.right ?? []" :key="ri" :value="String(ri)">{{ optionText(right) }}</option>
              </select>
            </div>
          </div>

          <div v-else-if="['essay','short_answer','writing'].includes(q.type)">
            <textarea v-model="answers[q.id]" rows="5"
              class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
              placeholder="Nhập câu trả lời của bạn..." />
          </div>
        </div>
      </template>

      <!-- Essay / writing (assignment-level) -->
      <div v-else-if="['essay','writing'].includes(assignment?.type)" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Bài làm của bạn</label>
        <textarea v-model="textContent" rows="12"
          class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
          placeholder="Nhập bài làm của bạn tại đây..." />
      </div>

      <!-- Default -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Bài làm của bạn</label>
        <textarea v-model="textContent" rows="8"
          class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
          placeholder="Nhập bài làm của bạn..." />
      </div>

      <div class="text-center pt-2 pb-8">
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-8 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-60">
          {{ submitting ? 'Đang nộp bài...' : 'Nộp bài' }}
        </button>
      </div>
    </main>
  </div>

  <!-- Confirm modal -->
  <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full">
      <h3 class="font-bold text-gray-900 text-lg mb-2">Xác nhận nộp bài</h3>
      <template v-if="questions.length > 0">
        <p class="text-sm text-gray-500 mb-1">Đã trả lời <strong>{{ answeredCount }}/{{ questions.length }}</strong> câu hỏi.</p>
        <p v-if="answeredCount < questions.length" class="text-sm text-amber-600 font-medium mb-4">
          Còn {{ questions.length - answeredCount }} câu chưa trả lời.
        </p>
        <p v-else class="text-sm text-green-600 font-medium mb-4">Đã trả lời hết tất cả câu hỏi.</p>
      </template>
      <p v-else class="text-sm text-gray-500 mb-4">Xác nhận nộp bài?</p>
      <div class="flex gap-3">
        <button @click="showConfirm = false"
          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Huỷ</button>
        <button @click="doSubmit"
          class="flex-1 px-4 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600">Nộp bài</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import publicApi from '@api/public'

const route        = useRoute()
const assignmentId = route.params.id

const assignment  = ref(null)
const questions   = ref([])
const answers     = reactive({})
const textContent = ref('')
const loading     = ref(true)
const loadError   = ref('')
const submitting  = ref(false)
const showConfirm = ref(false)
const result      = ref(null)

const answeredCount = computed(() => questions.value.filter(q => isAnswered(q)).length)

// ── Draft persistence ─────────────────────────────────────────────────────
const draftKey = `assignment_draft_${assignmentId}`

function saveDraft() {
  if (result.value || !assignment.value) return
  localStorage.setItem(draftKey, JSON.stringify({
    answers: JSON.parse(JSON.stringify(answers)),
    textContent: textContent.value
  }))
}

function clearDraft() {
  localStorage.removeItem(draftKey)
}

function onVisibilityHide() {
  if (document.hidden) saveDraft()
}

onUnmounted(() => {
  window.removeEventListener('beforeunload', saveDraft)
  document.removeEventListener('visibilitychange', onVisibilityHide)
})

onMounted(async () => {
  try {
    const { data } = await publicApi.assignmentTake(assignmentId)
    assignment.value = data.data.assignment
    questions.value  = data.data.questions

    questions.value.forEach(q => {
      if (q.type === 'ordering' && Array.isArray(q.options)) {
        answers[q.id] = q.options.map((_, i) => String(i))
      }
    })

    const draftRaw = localStorage.getItem(draftKey)
    if (draftRaw) {
      try {
        const draft = JSON.parse(draftRaw)
        if (draft.answers) Object.assign(answers, draft.answers)
        if (draft.textContent) textContent.value = draft.textContent
      } catch {}
    }

    window.addEventListener('beforeunload', saveDraft)
    document.addEventListener('visibilitychange', onVisibilityHide)
  } catch (e) {
    loadError.value = e.response?.data?.message ?? 'Không thể tải bài tập'
  } finally {
    loading.value = false
  }
})

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

function confirmSubmit() { showConfirm.value = true }

async function doSubmit() {
  showConfirm.value = false
  submitting.value  = true
  try {
    const payload = questions.value.length > 0 ? answers : null
    const { data } = await publicApi.assignmentSubmit(assignmentId, payload)
    clearDraft()
    result.value = data.data
    localStorage.setItem(`assignment_result_${assignmentId}`, JSON.stringify({
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
  textContent.value = ''
  questions.value.forEach(q => {
    if (q.type === 'ordering' && Array.isArray(q.options)) {
      answers[q.id] = q.options.map((_, i) => String(i))
    }
  })
}

function formatCorrectAnswer(correct, q) {
  if (correct === null || correct === undefined) return '—'
  if (Array.isArray(correct)) {
    if (q.type === 'fill_blank') return correct.join(', ')
    return correct.map(i => q.options?.[Number(i)] ?? i).join(', ')
  }
  if (typeof correct === 'object') return JSON.stringify(correct)
  return q.options?.[Number(correct)] ?? String(correct)
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
  setTimeout(() => { window.location.href = `/practice/${assignmentId}/preview` }, 300)
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
