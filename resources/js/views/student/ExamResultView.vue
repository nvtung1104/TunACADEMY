<template>
  <div v-if="loading" class="py-16 text-center text-gray-400">
    <div class="animate-spin w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full mx-auto mb-2"></div>
    Đang tải kết quả...
  </div>

  <div v-else-if="error" class="py-16 text-center text-red-500">{{ error }}</div>

  <div v-else class="max-w-4xl mx-auto p-4 space-y-6 select-none">
    <!-- Header Summary Card -->
    <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-gray-900 mb-1">{{ examTitle }}</h2>
        <p class="text-xs text-gray-500 font-medium">Nộp lúc {{ formatDate(result?.submitted_at) }}</p>
      </div>
      <div class="flex items-center gap-6 shrink-0 bg-gray-50 px-5 py-3 rounded-2xl border border-gray-100/50 self-start md:self-auto">
        <div class="text-center">
          <p class="text-2xl font-black" :class="(result?.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
            {{ result?.score ?? '—' }}<span class="text-xs text-gray-400 font-normal">/10</span>
          </p>
          <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Điểm số</p>
        </div>
        <div class="w-px h-8 bg-gray-200"></div>
        <div class="text-center">
          <p class="text-2xl font-black text-gray-800">{{ result?.total_correct ?? 0 }}</p>
          <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Câu đúng</p>
        </div>
      </div>
    </div>

    <!-- Nhận xét bài làm Card -->
    <div v-if="result?.ai_evaluation" class="bg-red-50/60 border border-red-100 rounded-3xl p-6 space-y-4 shadow-sm">
      <div class="flex items-center gap-2 mb-1">
        <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
          <span class="text-base">✨</span>
        </div>
        <h3 class="font-bold text-gray-900 text-sm">Nhận xét bài làm</h3>
      </div>

      <div v-if="result.ai_evaluation.competency_comment" class="bg-white/60 p-4 rounded-2xl border border-red-100/30">
        <p class="text-xs font-bold text-[#c02a10] uppercase tracking-wide mb-1">Nhận xét năng lực</p>
        <p class="text-sm text-gray-800 leading-relaxed">{{ result.ai_evaluation.competency_comment }}</p>
      </div>

      <div v-if="mistakeExplanations.length > 0" class="bg-white/60 p-4 rounded-2xl border border-red-100/30">
        <p class="text-xs font-bold text-[#c02a10] uppercase tracking-wide mb-1.5">Các điểm cần lưu ý</p>
        <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1">
          <li v-for="(item, idx) in mistakeExplanations" :key="idx" class="leading-relaxed">{{ item }}</li>
        </ul>
      </div>

      <div v-if="result.ai_evaluation.next_study_method" class="bg-white/60 p-4 rounded-2xl border border-red-100/30">
        <p class="text-xs font-bold text-[#c02a10] uppercase tracking-wide mb-1">Phương pháp học tiếp theo</p>
        <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ result.ai_evaluation.next_study_method }}</p>
      </div>

      <!-- Chi tiết từng câu sai (Nhận xét) -->
      <div v-if="wrongAnswers && wrongAnswers.length > 0" class="bg-white/60 p-4 rounded-2xl border border-red-100/30 space-y-3">
        <p class="text-xs font-bold text-[#c02a10] uppercase tracking-wide mb-1">Nhận xét chi tiết các câu sai</p>
        <div class="space-y-3">
          <div v-for="wa in wrongAnswers" :key="wa.question_id" class="p-3.5 rounded-xl bg-red-50/20 border border-red-100/30 text-xs space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="font-extrabold text-[#c02a10]">Câu {{ getQNumber(wa.question_id) }}</span>
              <span class="px-2 py-0.5 rounded text-[10px] font-black bg-red-100 text-[#c02a10]">Sai</span>
            </div>
            <p class="text-gray-900 leading-relaxed font-semibold"><span class="font-bold text-gray-500">Nội dung:</span> <span v-html="renderMath(getQFullContent(wa.question_id))"></span></p>
            <p class="text-gray-700"><span class="font-bold text-[#c02a10]">Nhận xét & Lỗi sai:</span> {{ wa.reason ?? wa }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Solutions Section -->
    <div v-if="result?.answers && result.answers.length > 0" class="space-y-4">
      <div class="flex items-center justify-between px-1">
        <h3 class="text-base font-black text-gray-900">Chi tiết bài làm & Lời giải</h3>
        <span class="text-xs font-semibold text-gray-400">Tổng số: {{ result.answers.length }} câu trắc nghiệm</span>
      </div>
      
      <div v-for="(ans, idx) in result.answers" :key="ans.question_id"
        class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm space-y-4 relative overflow-hidden transition-colors"
        :class="ans.is_correct ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-red-500'">
        
        <!-- Question Header -->
        <div class="flex items-start gap-3 justify-between">
          <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shrink-0"
              :class="ans.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
              {{ idx + 1 }}
            </span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 uppercase tracking-wide">
              Trắc nghiệm
            </span>
            <span v-if="ans.question?.chapter_tag" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-500 uppercase tracking-wide">
              {{ ans.question.chapter_tag }}
            </span>
          </div>
          <span class="text-xs font-semibold text-gray-400">
            Điểm: <span :class="ans.is_correct ? 'text-green-600 font-bold' : 'text-red-500 font-bold'">+{{ ans.points_earned }}đ</span>
          </span>
        </div>

        <!-- Question Content -->
        <div v-if="ans.question" class="text-gray-900 text-sm leading-relaxed whitespace-pre-line q-content font-medium pt-1"
          v-html="renderMath(ans.question.content)">
        </div>

        <!-- Options -->
        <div v-if="ans.question?.options" class="grid grid-cols-1 gap-2.5 pt-2">
          <div v-for="opt in ans.question.options" :key="opt.id"
            class="flex items-start gap-3 p-3.5 rounded-2xl border text-sm transition-all"
            :class="getOptionClass(ans, opt.id)">
            
            <span class="font-black text-gray-400 w-5 shrink-0">{{ opt.id }}.</span>
            <span class="flex-1 leading-relaxed text-gray-700 font-medium" v-html="renderMath(opt.text)"></span>
            
            <!-- Result Icons -->
            <svg v-if="opt.id === ans.question.correct_answer" class="w-5 h-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <svg v-else-if="opt.id === ans.answer && !ans.is_correct" class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
        </div>

        <!-- Display Choice Info -->
        <div class="text-xs font-semibold py-1 px-3 rounded-xl bg-gray-50 flex items-center justify-between gap-2 border border-gray-100">
          <span class="text-gray-500">
            Bạn đã chọn: <strong :class="ans.is_correct ? 'text-green-600' : 'text-red-500'">{{ ans.answer ?? 'Chưa trả lời' }}</strong>
          </span>
          <span class="text-gray-500">
            Đáp án đúng: <strong class="text-green-600">{{ ans.question?.correct_answer }}</strong>
          </span>
        </div>

        <!-- Explanation Block (Lời giải chi tiết) -->
        <div v-if="ans.question?.explanation" class="mt-4 p-4 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50/50 to-indigo-50/30">
          <div class="flex items-center gap-1.5 mb-2.5 text-indigo-950 font-black text-[10px] uppercase tracking-wider">
            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/>
            </svg>
            Lời giải chi tiết
          </div>
          <div class="text-sm text-gray-800 leading-relaxed explanation-content" v-html="renderMath(ans.question.explanation)">
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@api/axios'
import { renderMath } from '@/utils/math'

const route = useRoute()
const examId = route.params.id

const loading = ref(true)
const error = ref('')
const result = ref(null)

const wrongAnswers = computed(() => result.value?.ai_evaluation?.wrong_answers ?? [])
const mistakeExplanations = computed(() => result.value?.ai_evaluation?.mistake_explanations ?? [])
const examTitle = computed(() => route.query.title || `Kết quả bài thi #${examId}`)

const getQNumber = (qId) => {
  if (result.value && result.value.answers) {
    const idx = result.value.answers.findIndex(ans => String(ans.question_id) === String(qId))
    return idx !== -1 ? idx + 1 : '—'
  }
  return '—'
}

const getQFullContent = (qId) => {
  if (result.value && result.value.answers) {
    const ans = result.value.answers.find(ans => String(ans.question_id) === String(qId))
    return ans ? ans.question?.content : ''
  }
  return ''
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '—'
}

function getOptionClass(ans, optId) {
  const isCorrectAnswer = optId === ans.question?.correct_answer
  const isStudentAnswer = optId === ans.answer

  if (isCorrectAnswer) {
    return 'border-green-300 bg-green-50/50 hover:border-green-400'
  }
  if (isStudentAnswer && !ans.is_correct) {
    return 'border-red-300 bg-red-50/50 hover:border-red-400'
  }
  return 'border-gray-200 bg-white hover:bg-gray-50'
}

import { onUnmounted } from 'vue'

function preventCopy(e) {
  e.preventDefault()
  return false
}

function preventShortcuts(e) {
  if (
    (e.ctrlKey && (e.key === 'c' || e.key === 'C' || e.key === 'a' || e.key === 'A' || e.key === 'u' || e.key === 'U' || e.key === 'x' || e.key === 'X')) ||
    e.key === 'F12'
  ) {
    e.preventDefault()
    return false
  }
}

onUnmounted(() => {
  document.removeEventListener('contextmenu', preventCopy)
  document.removeEventListener('copy', preventCopy)
  document.removeEventListener('cut', preventCopy)
  document.removeEventListener('keydown', preventShortcuts)
})

onMounted(async () => {
  document.addEventListener('contextmenu', preventCopy)
  document.addEventListener('copy', preventCopy)
  document.addEventListener('cut', preventCopy)
  document.addEventListener('keydown', preventShortcuts)

  try {
    const { data } = await api.get(`/student/exams/${examId}/result`)
    result.value = data.data
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Không thể tải kết quả'
  } finally {
    loading.value = false
  }
})
</script>

<style>
/* Styling math structures rendered by KaTeX */
.q-content .katex, .explanation-content .katex {
  font-size: 1.05em;
  line-height: inherit;
}
.katex-display {
  margin: 0.5em 0 !important;
}
.select-none {
  user-select: none !important;
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
}
</style>
