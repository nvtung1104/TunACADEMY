<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Loading -->
    <div v-if="loading" class="py-20 text-center">
      <div class="animate-spin w-8 h-8 border-2 border-green-500 border-t-transparent rounded-full mx-auto mb-3" />
      <p class="text-sm text-gray-400">Đang tải...</p>
    </div>

    <div v-else-if="!exam" class="py-20 text-center">
      <div class="text-5xl mb-4">😕</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy đề thi</h3>
      <RouterLink to="/exams" class="text-sm text-green-600 hover:underline">← Quay lại</RouterLink>
    </div>

    <template v-else>
      <div class="animate__animated animate__fadeIn">
        <!-- Back -->
        <RouterLink :to="`/exams/${exam.id}`"
          class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 mb-6 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Quay lại
        </RouterLink>

        <!-- Main card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
          <div class="h-2" :style="{ backgroundColor: exam.subject?.color || '#10b981' }" />
          <div class="p-7">
            <!-- Badges -->
            <div class="flex flex-wrap gap-2 mb-4">
              <span class="text-sm font-semibold px-3 py-1 rounded-xl"
                :style="{ backgroundColor: (exam.subject?.color || '#10b981') + '18', color: exam.subject?.color || '#10b981' }">
                {{ exam.subject?.name }}
              </span>
              <span v-if="exam.classroom?.grade?.name" class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-xl font-medium">
                {{ exam.classroom.grade.name }}
              </span>
              <span class="text-xs px-2.5 py-1 rounded-xl font-semibold" :class="statusClass">{{ statusLabel }}</span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ exam.title }}</h1>
            <p v-if="exam.description" class="text-gray-500 leading-relaxed mb-5">{{ exam.description }}</p>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
              <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                  <svg class="w-4.5 h-4.5 w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Thời gian</p>
                  <p class="font-bold text-gray-800 text-sm">{{ exam.duration_minutes }} phút</p>
                </div>
              </div>

              <div v-if="exam.questions_count" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Số câu hỏi</p>
                  <p class="font-bold text-gray-800 text-sm">{{ exam.questions_count }} câu</p>
                </div>
              </div>

              <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Giáo viên</p>
                  <p class="font-bold text-gray-800 text-sm truncate">{{ exam.teacher?.name }}</p>
                </div>
              </div>
            </div>

            <!-- Rules -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
              <p class="text-xs font-semibold text-amber-800 mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                Lưu ý trước khi làm bài
              </p>
              <ul class="text-xs text-amber-700 space-y-1.5 list-none">
                <li class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Bài thi sẽ mở trong tab mới. Kết quả được lưu lại sau khi nộp.</li>
                <li v-if="exam.duration_minutes" class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Thời gian làm bài <strong>{{ exam.duration_minutes }} phút</strong>, đồng hồ bắt đầu chạy ngay khi vào thi.</li>
                <li class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Không đóng tab trong khi làm bài. Khi hết giờ bài sẽ tự động nộp.</li>
                <li class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Bạn có thể làm lại sau khi đã nộp.</li>
              </ul>
            </div>

            <!-- Draft in progress -->
            <div v-if="draft" class="bg-amber-50 border border-amber-300 rounded-xl p-4 mb-5 flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-amber-800">Đang làm dở</p>
                <p class="text-xs text-amber-700">Còn lại <strong class="font-mono">{{ draftRemainingDisplay }}</strong> để tiếp tục</p>
              </div>
            </div>

            <!-- Previous result -->
            <div v-else-if="previousResult" class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 text-center">
              <p class="text-xs text-gray-500 mb-1">Kết quả lần làm gần nhất</p>
              <p class="text-2xl font-black" :class="previousResult.score >= 5 ? 'text-green-600' : 'text-red-500'">
                {{ previousResult.score }}<span class="text-sm text-gray-400 font-normal">/10</span>
              </p>
              <p class="text-xs text-gray-500">Đúng {{ previousResult.total_correct }}/{{ previousResult.total }} câu
                · {{ formatDate(previousResult.completedAt) }}</p>
            </div>

            <!-- CTA -->
            <button @click="startExam"
              class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-base font-bold text-white transition-colors shadow-sm"
              :class="draft ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-200' : 'bg-green-600 hover:bg-green-700 shadow-green-200'">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ draft ? `Làm tiếp (còn ${draftRemainingDisplay})` : previousResult ? 'Làm lại' : 'Bắt đầu làm bài' }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import publicApi from '@api/public'

const route = useRoute()
const exam  = ref(null)
const loading = ref(true)
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
    const s = localStorage.getItem(`exam_result_${route.params.id}`)
    if (s) previousResult.value = JSON.parse(s)
  } catch {}
}

function onVisibilityChange() {
  if (!document.hidden) { checkDraft(); checkPreviousResult() }
}

onMounted(async () => {
  try {
    const { data } = await publicApi.exam(route.params.id)
    exam.value = data.data ?? data
  } catch { exam.value = null }
  finally { loading.value = false }
  checkDraft()
  checkPreviousResult()
  document.addEventListener('visibilitychange', onVisibilityChange)
})

onUnmounted(() => {
  clearInterval(draftTimer)
  document.removeEventListener('visibilitychange', onVisibilityChange)
})

function startExam() {
  window.open(`/exams/${route.params.id}/take`, '_blank')
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) : ''
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

const statusClass = computed(() => ({
  'Đang mở': 'bg-green-50 text-green-700',
  'Sắp mở': 'bg-blue-50 text-blue-700',
  'Đã kết thúc': 'bg-gray-100 text-gray-500',
}[statusLabel.value] ?? 'bg-red-50 text-[#c02a10]'))
</script>
