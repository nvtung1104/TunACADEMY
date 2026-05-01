<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div v-if="loading" class="py-20 text-center">
      <div class="animate-spin w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full mx-auto mb-3" />
      <p class="text-sm text-gray-400">Đang tải bài tập...</p>
    </div>

    <div v-else-if="!assignment" class="py-20 text-center">
      <div class="text-5xl mb-4">😕</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy bài tập</h3>
      <RouterLink to="/practice" class="mt-4 inline-block text-sm text-amber-600 hover:underline">← Quay lại danh sách</RouterLink>
    </div>

    <template v-else>
      <RouterLink to="/practice" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-amber-600 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Quay lại danh sách bài tập
      </RouterLink>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="h-2" :style="{ backgroundColor: assignment.subject?.color || '#f59e0b' }" />
            <div class="p-6">
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-sm font-semibold px-3 py-1 rounded-xl"
                  :style="{ backgroundColor: (assignment.subject?.color || '#f59e0b') + '18', color: assignment.subject?.color || '#f59e0b' }">
                  {{ assignment.subject?.name }}
                </span>
                <span v-if="assignment.classroom?.grade?.name" class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-xl font-medium">
                  {{ assignment.classroom.grade.name }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-xl font-semibold" :class="typeClass">{{ typeLabel }}</span>
              </div>
              <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ assignment.title }}</h1>
              <p v-if="assignment.description" class="text-gray-500 leading-relaxed">{{ assignment.description }}</p>
              <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-50 text-sm text-gray-400">
                <span>GV: {{ assignment.teacher?.name }}</span>
                <span>Lớp: {{ assignment.classroom?.name }}</span>
              </div>
            </div>
          </div>

          <!-- Assignment info -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Thông tin bài tập</h3>
            <div class="grid sm:grid-cols-2 gap-4">
              <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Điểm tối đa</p>
                  <p class="font-semibold text-gray-800">{{ assignment.max_score }} điểm</p>
                </div>
              </div>
              <div v-if="assignment.deadline" class="flex items-center gap-3 p-4 rounded-xl bg-gray-50">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Hạn nộp</p>
                  <p class="font-semibold text-gray-800">{{ formatDate(assignment.deadline) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <div v-if="previousResult" class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-center mb-1">
              <p class="text-xs text-gray-500 mb-1">Kết quả lần làm gần nhất</p>
              <p v-if="previousResult.score != null" class="text-3xl font-black" :class="previousResult.score >= 5 ? 'text-green-600' : 'text-red-500'">
                {{ previousResult.score }}<span class="text-base text-gray-400 font-normal">/10</span>
              </p>
              <p v-else class="text-sm font-medium text-amber-700">Đã hoàn thành</p>
              <p v-if="previousResult.total" class="text-xs text-gray-500 mt-1">Đúng {{ previousResult.total_correct }}/{{ previousResult.total }} câu</p>
            </div>
            <button @click="openDoPage"
              class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ previousResult ? 'Làm lại' : 'Xem trước & Làm bài' }}
            </button>

            <button v-if="auth.isLoggedIn" @click="toggleSave" :disabled="savePending"
              class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold border transition-colors"
              :class="saved ? 'bg-amber-50 border-amber-300 text-amber-700 hover:bg-amber-100' : 'bg-white border-gray-200 text-gray-600 hover:border-amber-300 hover:text-amber-600'">
              <svg class="w-4 h-4" :fill="saved ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              {{ saved ? 'Đã lưu' : 'Lưu bài tập' }}
            </button>
            <RouterLink v-else to="/login"
              class="flex items-center justify-center gap-2 w-full py-2 rounded-xl text-sm text-gray-500 border border-dashed border-gray-200 hover:border-amber-300 hover:text-amber-600 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              Đăng nhập để lưu
            </RouterLink>
          </div>

          <div v-if="assignment.subject" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Môn học</h3>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                :style="{ backgroundColor: (assignment.subject.color || '#f59e0b') + '18' }">
                <span class="text-lg">{{ assignment.subject.icon ?? '✏️' }}</span>
              </div>
              <div>
                <p class="font-medium text-gray-800 text-sm">{{ assignment.subject.name }}</p>
                <p v-if="assignment.classroom?.grade?.name" class="text-xs text-gray-400">{{ assignment.classroom.grade.name }}</p>
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
import { useAuthStore } from '@stores/auth'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const assignment = ref(null)
const loading = ref(true)
const previousResult = ref(null)

function checkPreviousResult() {
  try {
    const stored = localStorage.getItem(`assignment_result_${route.params.id}`)
    if (stored) previousResult.value = JSON.parse(stored)
  } catch {}
}

function openDoPage() {
  router.push(`/practice/${route.params.id}/preview`)
}

function onVisibilityChange() {
  if (!document.hidden) checkPreviousResult()
}

const typeLabel = computed(() => {
  const map = { homework: 'Bài tập', project: 'Dự án', essay: 'Bài luận', quiz: 'Kiểm tra nhanh' }
  return map[assignment.value?.type] ?? 'Bài tập'
})

const typeClass = computed(() => {
  const map = { homework: 'bg-blue-50 text-blue-700', project: 'bg-purple-50 text-purple-700', essay: 'bg-green-50 text-green-700', quiz: 'bg-orange-50 text-orange-700' }
  return map[assignment.value?.type] ?? 'bg-gray-100 text-gray-600'
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await publicApi.assignment(route.params.id)
    assignment.value = data.data ?? data
  } catch {
    assignment.value = null
  } finally {
    loading.value = false
  }
  checkPreviousResult()
  document.addEventListener('visibilitychange', onVisibilityChange)
})

onUnmounted(() => document.removeEventListener('visibilitychange', onVisibilityChange))
</script>
