<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Loading -->
    <div v-if="loading" class="py-20 text-center">
      <div class="animate-spin w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full mx-auto mb-3" />
      <p class="text-sm text-gray-400">Đang tải...</p>
    </div>

    <div v-else-if="!assignment" class="py-20 text-center">
      <div class="text-5xl mb-4">😕</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy bài tập</h3>
      <RouterLink to="/practice" class="text-sm text-amber-600 hover:underline">← Quay lại</RouterLink>
    </div>

    <template v-else>
      <div class="animate__animated animate__fadeIn">
        <!-- Back -->
        <RouterLink :to="`/practice/${assignment.id}`"
          class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-amber-600 mb-6 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Quay lại
        </RouterLink>

        <!-- Main card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
          <div class="h-2" :style="{ backgroundColor: assignment.subject?.color || '#f59e0b' }" />
          <div class="p-7">
            <!-- Badges -->
            <div class="flex flex-wrap gap-2 mb-4">
              <span class="text-sm font-semibold px-3 py-1 rounded-xl"
                :style="{ backgroundColor: (assignment.subject?.color || '#f59e0b') + '18', color: assignment.subject?.color || '#f59e0b' }">
                {{ assignment.subject?.name }}
              </span>
              <span v-if="assignment.classroom?.grade?.name" class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-xl font-medium">
                {{ assignment.classroom.grade.name }}
              </span>
              <span class="text-xs px-2.5 py-1 rounded-xl font-semibold" :class="typeClass">{{ typeLabel }}</span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ assignment.title }}</h1>
            <p v-if="assignment.description" class="text-gray-500 leading-relaxed mb-5">{{ assignment.description }}</p>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
              <div v-if="assignment.max_score" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Điểm tối đa</p>
                  <p class="font-bold text-gray-800 text-sm">{{ assignment.max_score }} điểm</p>
                </div>
              </div>

              <div v-if="assignment.questions_count" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Số câu hỏi</p>
                  <p class="font-bold text-gray-800 text-sm">{{ assignment.questions_count }} câu</p>
                </div>
              </div>

              <div v-if="assignment.deadline" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                  :class="isOverdue ? 'bg-red-100' : 'bg-green-100'">
                  <svg class="w-5 h-5" :class="isOverdue ? 'text-red-500' : 'text-green-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Hạn nộp</p>
                  <p class="font-bold text-sm" :class="isOverdue ? 'text-red-500' : 'text-gray-800'">
                    {{ formatDate(assignment.deadline) }}
                  </p>
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
                  <p class="font-bold text-gray-800 text-sm truncate">{{ assignment.teacher?.name }}</p>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
              <p class="text-xs font-semibold text-blue-800 mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Hướng dẫn
              </p>
              <ul class="text-xs text-blue-700 space-y-1.5">
                <li class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Bài tập sẽ mở trong tab mới. Kết quả được lưu lại sau khi nộp.</li>
                <li v-if="assignment.questions_count" class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Trả lời tất cả <strong>{{ assignment.questions_count }} câu hỏi</strong> rồi nhấn Nộp bài.</li>
                <li v-if="isOverdue" class="flex items-start gap-1.5 text-red-600"><span class="shrink-0 mt-0.5">⚠</span> Bài tập đã quá hạn nộp.</li>
                <li class="flex items-start gap-1.5"><span class="shrink-0 mt-0.5">•</span> Bạn có thể làm lại sau khi đã nộp.</li>
              </ul>
            </div>

            <!-- Previous result -->
            <div v-if="previousResult" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-center">
              <p class="text-xs text-gray-500 mb-1">Kết quả lần làm gần nhất</p>
              <template v-if="previousResult.score != null">
                <p class="text-2xl font-black" :class="previousResult.score >= 5 ? 'text-green-600' : 'text-red-500'">
                  {{ previousResult.score }}<span class="text-sm text-gray-400 font-normal">/10</span>
                </p>
                <p v-if="previousResult.total" class="text-xs text-gray-500">Đúng {{ previousResult.total_correct }}/{{ previousResult.total }} câu</p>
              </template>
              <p v-else class="text-sm font-medium text-amber-700">Đã hoàn thành</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatDate(previousResult.completedAt) }}</p>
            </div>

            <!-- CTA -->
            <button @click="startAssignment"
              class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-base font-bold bg-amber-500 hover:bg-amber-600 text-white transition-colors shadow-sm shadow-amber-200">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ previousResult ? 'Làm lại' : 'Bắt đầu làm bài' }}
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
const assignment = ref(null)
const loading = ref(true)
const previousResult = ref(null)

function checkPreviousResult() {
  try {
    const s = localStorage.getItem(`assignment_result_${route.params.id}`)
    if (s) previousResult.value = JSON.parse(s)
  } catch {}
}

function onVisibilityChange() { if (!document.hidden) checkPreviousResult() }

onMounted(async () => {
  try {
    const { data } = await publicApi.assignment(route.params.id)
    assignment.value = data.data ?? data
  } catch { assignment.value = null }
  finally { loading.value = false }
  checkPreviousResult()
  document.addEventListener('visibilitychange', onVisibilityChange)
})

onUnmounted(() => document.removeEventListener('visibilitychange', onVisibilityChange))

function startAssignment() {
  window.open(`/practice/${route.params.id}/do`, '_blank')
}

const isOverdue = computed(() =>
  assignment.value?.deadline && new Date(assignment.value.deadline) < new Date()
)

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) : ''
}

const typeLabel = computed(() => {
  const map = { quiz: 'Trắc nghiệm', essay: 'Tự luận', fill_blank: 'Điền chỗ trống',
    matching: 'Ghép đôi', upload: 'Nộp file', listening: 'Nghe hiểu', writing: 'Viết' }
  return map[assignment.value?.type] ?? 'Bài tập'
})

const typeClass = computed(() => {
  const map = { quiz: 'bg-blue-50 text-blue-700', essay: 'bg-purple-50 text-purple-700',
    fill_blank: 'bg-orange-50 text-orange-700', matching: 'bg-pink-50 text-pink-700',
    upload: 'bg-gray-100 text-gray-600', listening: 'bg-green-50 text-green-700',
    writing: 'bg-cyan-50 text-cyan-700' }
  return map[assignment.value?.type] ?? 'bg-gray-100 text-gray-600'
})
</script>
