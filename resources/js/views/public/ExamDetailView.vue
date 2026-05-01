<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div v-if="loading" class="py-20 text-center">
      <div class="animate-spin w-8 h-8 border-2 border-green-500 border-t-transparent rounded-full mx-auto mb-3" />
      <p class="text-sm text-gray-400">Đang tải đề thi...</p>
    </div>

    <div v-else-if="!exam" class="py-20 text-center">
      <div class="text-5xl mb-4">😕</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy đề thi</h3>
      <RouterLink to="/exams" class="mt-4 inline-block text-sm text-green-600 hover:underline">← Quay lại danh sách</RouterLink>
    </div>

    <template v-else>
      <RouterLink to="/exams" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Quay lại danh sách đề thi
      </RouterLink>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="h-2" :style="{ backgroundColor: exam.subject?.color || '#10b981' }" />
            <div class="p-6">
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-sm font-semibold px-3 py-1 rounded-xl"
                  :style="{ backgroundColor: (exam.subject?.color || '#10b981') + '18', color: exam.subject?.color || '#10b981' }">
                  {{ exam.subject?.name }}
                </span>
                <span v-if="exam.classroom?.grade?.name" class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-xl font-medium">
                  {{ exam.classroom.grade.name }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-xl font-semibold" :class="statusClass">{{ statusLabel }}</span>
              </div>
              <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ exam.title }}</h1>
              <p v-if="exam.description" class="text-gray-500 leading-relaxed">{{ exam.description }}</p>
              <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-50 text-sm text-gray-400">
                <span>GV: {{ exam.teacher?.name }}</span>
                <span>Lớp: {{ exam.classroom?.name }}</span>
              </div>
            </div>
          </div>

          <!-- Exam info -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Thông tin đề thi</h3>
            <div class="grid sm:grid-cols-2 gap-4">
              <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Thời gian làm bài</p>
                  <p class="font-semibold text-gray-800">{{ exam.duration_minutes }} phút</p>
                </div>
              </div>
              <div v-if="exam.opened_at" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Ngày mở thi</p>
                  <p class="font-semibold text-gray-800">{{ formatDate(exam.opened_at) }}</p>
                </div>
              </div>
              <div v-if="exam.closed_at" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Ngày đóng thi</p>
                  <p class="font-semibold text-gray-800">{{ formatDate(exam.closed_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <!-- Draft in progress -->
            <div v-if="draft" class="p-4 rounded-xl bg-amber-50 border border-amber-300 flex items-center gap-3 mb-1">
              <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p class="text-xs font-semibold text-amber-800">Đang làm dở</p>
                <p class="text-xs text-amber-700">Còn <strong class="font-mono">{{ draftRemainingDisplay }}</strong></p>
              </div>
            </div>
            <!-- Previous result -->
            <div v-else-if="previousResult" class="p-4 rounded-xl bg-green-50 border border-green-200 text-center mb-1">
              <p class="text-xs text-gray-500 mb-1">Kết quả lần làm gần nhất</p>
              <p class="text-3xl font-black" :class="previousResult.score >= 5 ? 'text-green-600' : 'text-red-500'">
                {{ previousResult.score }}<span class="text-base text-gray-400 font-normal">/10</span>
              </p>
              <p class="text-xs text-gray-500 mt-1">Đúng {{ previousResult.total_correct }}/{{ previousResult.total }} câu</p>
            </div>
            <button @click="openTakePage"
              class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
              :class="draft ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700'">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ draft ? `Làm tiếp (còn ${draftRemainingDisplay})` : previousResult ? 'Làm lại' : 'Xem trước & Làm bài' }}
            </button>

            <button v-if="auth.isLoggedIn" @click="toggleSave" :disabled="savePending"
              class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold border transition-colors"
              :class="saved ? 'bg-amber-50 border-amber-300 text-amber-700 hover:bg-amber-100' : 'bg-white border-gray-200 text-gray-600 hover:border-amber-300 hover:text-amber-600'">
              <svg class="w-4 h-4" :fill="saved ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              {{ saved ? 'Đã lưu' : 'Lưu đề thi' }}
            </button>
            <RouterLink v-else to="/login"
              class="flex items-center justify-center gap-2 w-full py-2 rounded-xl text-sm text-gray-500 border border-dashed border-gray-200 hover:border-amber-300 hover:text-amber-600 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              Đăng nhập để lưu
            </RouterLink>
          </div>

          <div v-if="exam.subject" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Môn học</h3>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                :style="{ backgroundColor: (exam.subject.color || '#10b981') + '18' }">
                <span class="text-lg">{{ exam.subject.icon ?? '📝' }}</span>
              </div>
              <div>
                <p class="font-medium text-gray-800 text-sm">{{ exam.subject.name }}</p>
                <p v-if="exam.classroom?.grade?.name" class="text-xs text-gray-400">{{ exam.classroom.grade.name }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import publicApi from '@api/public'
import bookmarkApi from '@api/bookmarks'
import { useAuthStore } from '@stores/auth'

const route  = useRoute()
const router = useRouter()
const auth = useAuthStore()
const exam = ref(null)
const loading = ref(true)
const saved = ref(false)
const savePending = ref(false)
const previousResult = ref(null)
const draft = ref(null)
const draftRemaining = ref(0)
let draftTimer = null

function checkDraft() {
  clearInterval(draftTimer)
  try {
    const raw = localStorage.getItem(`exam_draft_${route.params.id}`)
    if (!raw) { draft.value = null; return }
    const d = JSON.parse(raw)
    const elapsed = Math.floor((Date.now() - d.startedAt) / 1000)
    const left = d.durationMinutes * 60 - elapsed
    if (left > 0) {
      draft.value = d
      draftRemaining.value = left
      draftTimer = setInterval(() => {
        draftRemaining.value--
        if (draftRemaining.value <= 0) { clearInterval(draftTimer); draft.value = null }
      }, 1000)
    } else { draft.value = null; draftRemaining.value = 0 }
  } catch { draft.value = null }
}

const draftRemainingDisplay = computed(() => {
  const m = Math.floor(draftRemaining.value / 60)
  const s = draftRemaining.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

function checkPreviousResult() {
  try {
    const stored = localStorage.getItem(`exam_result_${route.params.id}`)
    if (stored) previousResult.value = JSON.parse(stored)
  } catch {}
}

function openTakePage() {
  router.push(`/exams/${route.params.id}/preview`)
}

function onVisibilityChange() {
  if (!document.hidden) { checkDraft(); checkPreviousResult() }
}

async function toggleSave() {
  savePending.value = true
  try {
    const { data } = await bookmarkApi.toggle('exam', route.params.id)
    saved.value = data.saved
  } finally { savePending.value = false }
}

const statusLabel = computed(() => {
  if (!exam.value) return ''
  const now = new Date()
  const opened = exam.value.opened_at ? new Date(exam.value.opened_at) : null
  const closed = exam.value.closed_at ? new Date(exam.value.closed_at) : null
  if (opened && closed) {
    if (now < opened) return 'Sắp mở'
    if (now > closed) return 'Đã kết thúc'
    return 'Đang mở'
  }
  return 'Đã xuất bản'
})

const statusClass = computed(() => {
  const map = { 'Đang mở': 'bg-green-50 text-green-700', 'Sắp mở': 'bg-blue-50 text-blue-700', 'Đã kết thúc': 'bg-gray-100 text-gray-500' }
  return map[statusLabel.value] ?? 'bg-indigo-50 text-indigo-700'
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await publicApi.exam(route.params.id)
    exam.value = data.data ?? data
  } catch {
    exam.value = null
  } finally {
    loading.value = false
  }
  if (auth.isLoggedIn) {
    try { const { data } = await bookmarkApi.check('exam', route.params.id); saved.value = data.saved } catch {}
  }
  checkDraft()
  checkPreviousResult()
  document.addEventListener('visibilitychange', onVisibilityChange)
})

onUnmounted(() => {
  clearInterval(draftTimer)
  document.removeEventListener('visibilitychange', onVisibilityChange)
})
</script>
