<template>
  <!-- Loading -->
  <div v-if="loading" class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-3"/>
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
      <button @click="$router.back()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
        Quay lại
      </button>
    </div>
  </div>

  <!-- Already submitted -->
  <div v-else-if="submission" class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-sm">
      <div class="w-20 h-20 rounded-3xl bg-green-100 flex items-center justify-center mx-auto mb-5">
        <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <h2 class="text-2xl font-black text-gray-900 mb-1">Đã nộp bài!</h2>
      <p class="text-sm text-gray-500 mb-2">
        Nộp lúc {{ formatDate(submission.submitted_at) }}
        <span v-if="submission.is_late" class="ml-1 text-amber-600 font-medium">(Nộp muộn)</span>
      </p>
      <p v-if="submission.score != null" class="text-4xl font-black mb-2"
        :class="submission.score >= 5 ? 'text-green-600' : 'text-red-500'">
        {{ submission.score }}<span class="text-lg text-gray-400 font-normal">/10</span>
      </p>
      <p v-else class="text-sm text-gray-400 mb-6">Đang chờ giáo viên chấm điểm</p>
      <div v-if="submission.feedback" class="text-left mt-4 mb-6 p-4 bg-blue-50 rounded-xl">
        <p class="text-xs font-semibold text-blue-700 mb-1">Nhận xét của giáo viên</p>
        <p class="text-sm text-blue-800">{{ submission.feedback }}</p>
      </div>
      <button @click="$router.push('/student/assignments')"
        class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
        Về danh sách bài tập
      </button>
    </div>
  </div>

  <!-- Assignment interface -->
  <div v-else class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-4">
        <button @click="$router.back()" class="p-2 rounded-xl hover:bg-gray-100 text-gray-500 shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <div class="flex-1 min-w-0">
          <h1 class="font-bold text-gray-900 truncate text-sm sm:text-base">{{ assignment?.title }}</h1>
          <p class="text-xs text-gray-400">
            {{ assignment?.subject?.name }}
            <span v-if="assignment?.deadline"> · Hạn: {{ formatDate(assignment.deadline) }}</span>
          </p>
        </div>
        <span v-if="isOverdue" class="text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-600 shrink-0">
          Quá hạn
        </span>
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60 shrink-0">
          {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
        </button>
      </div>

      <!-- Progress bar (only for question types) -->
      <div v-if="questions.length > 0" class="h-1 bg-gray-100">
        <div class="h-full bg-indigo-500 transition-all duration-300"
          :style="{ width: (answeredCount / questions.length * 100) + '%' }" />
      </div>
    </header>

    <!-- Main content -->
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-5">

      <!-- Description -->
      <div v-if="assignment?.description" class="bg-blue-50 rounded-2xl p-4 text-sm text-blue-800">
        <p class="font-semibold text-blue-900 mb-1">Hướng dẫn</p>
        {{ assignment.description }}
      </div>

      <!-- ── Question-based types ── -->
      <template v-if="questions.length > 0">
        <div v-if="questions.length > 0" class="text-sm text-gray-500 font-medium">
          Đã trả lời <strong class="text-gray-700">{{ answeredCount }}/{{ questions.length }}</strong> câu
        </div>

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
                <span v-if="q.points" class="text-xs font-medium text-[#d63015]">{{ q.points }} điểm</span>
              </div>
              <p class="text-gray-900 text-sm leading-relaxed whitespace-pre-line">{{ stripHtml(q.content) }}</p>
            </div>
          </div>

          <!-- Audio player (listening) -->
          <div v-if="q.audio_path" class="mb-4">
            <audio :src="'/storage/' + q.audio_path" controls class="w-full rounded-xl h-10" />
          </div>

          <!-- Multiple choice / True-False / Listening -->
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

          <!-- Multiple select -->
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

          <!-- Fill blank -->
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

          <!-- Ordering -->
          <div v-else-if="q.type === 'ordering'" class="space-y-2">
            <p class="text-xs text-gray-400 mb-2">Dùng nút ↑↓ để sắp xếp theo thứ tự đúng.</p>
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
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                <option value="">-- Chọn --</option>
                <option v-for="(right, ri) in q.options?.right ?? []" :key="ri" :value="String(ri)">
                  {{ optionText(right) }}
                </option>
              </select>
            </div>
          </div>

          <!-- Essay / Short answer / Writing -->
          <div v-else-if="['essay','short_answer','writing'].includes(q.type)">
            <textarea v-model="answers[q.id]" rows="5"
              class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
              placeholder="Nhập câu trả lời của bạn..." />
          </div>
        </div>
      </template>

      <!-- ── Essay / Writing (assignment-level, no questions) ── -->
      <div v-else-if="['essay','writing'].includes(assignment?.type)" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Bài làm của bạn</label>
        <textarea v-model="textContent" rows="12"
          class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
          placeholder="Nhập bài làm của bạn tại đây..." />
      </div>

      <!-- ── Upload ── -->
      <div v-else-if="assignment?.type === 'upload'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <label class="block text-sm font-semibold text-gray-700">Nộp file bài làm</label>

        <!-- Drop zone -->
        <div @click="fileInput.click()" @dragover.prevent @drop.prevent="onDrop"
          class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors">
          <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <p class="text-sm text-gray-500">Kéo thả file hoặc <span class="text-indigo-600 font-medium">chọn file</span></p>
          <p class="text-xs text-gray-400 mt-1">PDF, DOC, DOCX, PNG, JPG, ZIP (tối đa 10MB mỗi file)</p>
        </div>
        <input ref="fileInput" type="file" multiple class="hidden"
          accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip"
          @change="onFileChange" />

        <!-- Selected files -->
        <div v-if="uploadFiles.length > 0" class="space-y-2">
          <div v-for="(f, i) in uploadFiles" :key="i"
            class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="flex-1 text-sm text-gray-700 truncate">{{ f.name }}</span>
            <span class="text-xs text-gray-400 shrink-0">{{ (f.size / 1024).toFixed(0) }} KB</span>
            <button @click="uploadFiles.splice(i, 1)" class="p-1 rounded-lg hover:bg-red-100 text-gray-400 hover:text-red-500 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Ghi chú (tuỳ chọn)</label>
          <textarea v-model="textContent" rows="3"
            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
            placeholder="Ghi chú thêm cho giáo viên..." />
        </div>
      </div>

      <!-- ── Default (other types with no questions) ── -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Bài làm của bạn</label>
        <textarea v-model="textContent" rows="8"
          class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
          placeholder="Nhập bài làm của bạn..." />
      </div>

      <!-- Bottom submit -->
      <div class="text-center pt-2 pb-8">
        <button @click="confirmSubmit" :disabled="submitting"
          class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 disabled:opacity-60">
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
        <p class="text-sm text-gray-500 mb-1">
          Bạn đã trả lời <strong>{{ answeredCount }}/{{ questions.length }}</strong> câu hỏi.
        </p>
        <p v-if="answeredCount < questions.length" class="text-sm text-amber-600 font-medium mb-4">
          Còn {{ questions.length - answeredCount }} câu chưa trả lời. Bạn có chắc muốn nộp?
        </p>
        <p v-else class="text-sm text-green-600 font-medium mb-4">Đã trả lời hết tất cả câu hỏi.</p>
      </template>
      <p v-else class="text-sm text-gray-500 mb-4">Sau khi nộp bạn sẽ không thể chỉnh sửa. Xác nhận nộp bài?</p>
      <div class="flex gap-3">
        <button @click="showConfirm = false"
          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
          Huỷ
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@api/axios'

const route        = useRoute()
const router       = useRouter()
const assignmentId = route.params.id

const assignment  = ref(null)
const questions   = ref([])
const submission  = ref(null)
const answers     = reactive({})
const textContent = ref('')
const uploadFiles = ref([])
const fileInput   = ref(null)

const loading     = ref(true)
const loadError   = ref('')
const submitting  = ref(false)
const showConfirm = ref(false)

const isOverdue = computed(() =>
  assignment.value?.deadline && new Date(assignment.value.deadline) < new Date()
)

const answeredCount = computed(() =>
  questions.value.filter(q => isAnswered(q)).length
)

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : ''
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/student/assignments/${assignmentId}`)
    assignment.value = data.data?.assignment ?? data.data
    submission.value = data.data?.submission ?? null
    questions.value  = assignment.value?.questions ?? []

    // Init ordering answers
    questions.value.forEach(q => {
      if (q.type === 'ordering' && Array.isArray(q.options)) {
        answers[q.id] = q.options.map((_, i) => String(i))
      }
    })
  } catch (e) {
    loadError.value = e.response?.data?.message ?? 'Không thể tải bài tập'
  } finally {
    loading.value = false
  }
})

// ── Answer helpers ────────────────────────────────────────────────────────
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

// ── File helpers ──────────────────────────────────────────────────────────
function onFileChange(e) {
  for (const f of e.target.files) {
    if (!uploadFiles.value.find(x => x.name === f.name)) uploadFiles.value.push(f)
  }
}

function onDrop(e) {
  for (const f of e.dataTransfer.files) {
    if (!uploadFiles.value.find(x => x.name === f.name)) uploadFiles.value.push(f)
  }
}

// ── Submit ────────────────────────────────────────────────────────────────
function confirmSubmit() { showConfirm.value = true }

async function doSubmit() {
  showConfirm.value = false
  submitting.value  = true
  try {
    const formData = new FormData()

    if (questions.value.length > 0) {
      formData.append('content', JSON.stringify(answers))
    } else {
      formData.append('content', textContent.value)
    }

    for (const f of uploadFiles.value) {
      formData.append('files[]', f)
    }

    const { data } = await api.post(
      `/student/assignments/${assignmentId}/submit`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )

    // Show submitted state by refreshing
    const reload = await api.get(`/student/assignments/${assignmentId}`)
    submission.value = reload.data.data?.submission ?? data.data
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
