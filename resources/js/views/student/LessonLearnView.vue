<template>
  <!-- -m-5 pulls content flush to the main container edges, overriding the p-5 padding -->
  <div class="-m-5 flex flex-col" style="min-height: calc(100vh - 56px)">

    <!-- ── Loading ── -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <div class="w-10 h-10 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"/>
        <p class="text-sm text-gray-400">Đang tải bài học...</p>
      </div>
    </div>

    <!-- ── Error / Not found ── -->
    <div v-else-if="!lesson" class="flex-1 flex items-center justify-center">
      <div class="text-center max-w-sm px-4">
        <div class="text-5xl mb-4">{{ errorCode === 403 ? '🔒' : '😕' }}</div>
        <p class="text-gray-800 font-semibold text-lg mb-1">
          {{ errorCode === 403 ? 'Không có quyền truy cập' : 'Không tìm thấy bài học' }}
        </p>
        <p class="text-gray-400 text-sm mb-5">
          {{ errorCode === 403
            ? 'Bài học này chỉ dành cho học sinh trong lớp. Hãy kiểm tra lại lớp học của bạn.'
            : 'Bài học không tồn tại hoặc đã bị ẩn.' }}
        </p>
        <RouterLink to="/student/lessons"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors">
          ← Quay lại danh sách bài học
        </RouterLink>
      </div>
    </div>

    <template v-else>
      <!-- ── Sticky top bar ── -->
      <div class="sticky top-0 z-20 bg-white border-b border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 px-5 py-3">
          <!-- Back -->
          <RouterLink to="/student/lessons"
            class="flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="hidden sm:inline">Bài học</span>
          </RouterLink>

          <!-- Subject badge -->
          <span class="hidden sm:inline-flex shrink-0 text-xs font-semibold px-2.5 py-1 rounded-lg"
            :style="{ backgroundColor: (lesson.subject?.color || '#6366f1') + '18', color: lesson.subject?.color || '#6366f1' }">
            {{ lesson.subject?.name }}
          </span>

          <!-- Title -->
          <h2 class="flex-1 text-sm font-semibold text-gray-700 truncate">{{ lesson.title }}</h2>

          <!-- Progress ring -->
          <div class="shrink-0 flex items-center gap-2">
            <svg class="w-6 h-6 -rotate-90" viewBox="0 0 36 36">
              <circle cx="18" cy="18" r="14" fill="none" stroke="#f3f4f6" stroke-width="3.5"/>
              <circle cx="18" cy="18" r="14" fill="none"
                :stroke="isCompleted ? '#22c55e' : (lesson.subject?.color || '#6366f1')"
                stroke-width="3.5" stroke-linecap="round"
                :stroke-dasharray="`${isCompleted ? 88 : 0} 88`"
                class="transition-all duration-700"/>
            </svg>
            <span class="text-xs font-semibold text-gray-500 hidden sm:inline">
              {{ isCompleted ? '100%' : '0%' }}
            </span>
          </div>

          <!-- Mark complete button -->
          <button @click="markComplete" :disabled="isCompleted || marking"
            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors disabled:cursor-not-allowed"
            :class="isCompleted
              ? 'bg-green-100 text-green-700'
              : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
            <svg v-if="isCompleted" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="hidden sm:inline">{{ isCompleted ? 'Đã xong' : (marking ? 'Đang lưu...' : 'Hoàn thành') }}</span>
          </button>
        </div>

        <!-- Progress bar strip -->
        <div class="h-0.5 bg-gray-100">
          <div class="h-full transition-all duration-700"
            :style="{ width: isCompleted ? '100%' : '0%', backgroundColor: lesson.subject?.color || '#6366f1' }"/>
        </div>
      </div>

      <!-- ── Main content ── -->
      <div class="flex-1 w-full max-w-3xl mx-auto px-5 py-8 space-y-6">

        <!-- Lesson meta -->
        <div>
          <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="text-sm font-semibold px-3 py-1 rounded-xl"
              :style="{ backgroundColor: (lesson.subject?.color || '#6366f1') + '18', color: lesson.subject?.color || '#6366f1' }">
              {{ lesson.subject?.name }}
            </span>
            <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-xl">{{ lesson.classroom?.name }}</span>
            <span class="text-xs text-gray-400 bg-gray-50 px-2.5 py-1 rounded-xl">GV: {{ lesson.teacher?.name }}</span>
            <span class="text-xs text-gray-400 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              {{ lesson.view_count ?? 0 }} lượt xem
            </span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ lesson.title }}</h1>
          <p v-if="lesson.description" class="text-gray-500 leading-relaxed">{{ lesson.description }}</p>
        </div>

        <!-- Video player -->
        <div v-if="lesson.video_path" class="rounded-2xl overflow-hidden bg-black shadow-lg">
          <video
            ref="videoEl"
            :src="lesson.video_path"
            controls
            class="w-full aspect-video"
            @ended="onVideoEnd"
          />
        </div>

        <!-- Content -->
        <div v-if="lesson.content"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
            <div class="w-1 h-5 rounded-full" :style="{ backgroundColor: lesson.subject?.color || '#6366f1' }"/>
            <h3 class="font-semibold text-gray-800">Nội dung bài học</h3>
          </div>
          <div class="px-6 py-5 prose prose-sm max-w-none text-gray-700 leading-relaxed" v-html="lesson.content"/>
        </div>

        <!-- Materials -->
        <div v-if="lesson.materials?.length"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
            <div class="w-1 h-5 rounded-full bg-amber-500"/>
            <h3 class="font-semibold text-gray-800">Tài liệu đính kèm</h3>
          </div>
          <div class="p-4 space-y-2">
            <a v-for="m in lesson.materials" :key="m.id" :href="m.file_path" target="_blank"
              class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition-colors group">
              <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-700">
                  {{ m.title ?? m.file_name }}
                </p>
                <p class="text-xs text-gray-400">{{ m.file_type ?? 'Tài liệu' }}</p>
              </div>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Related exams -->
        <div v-if="relatedExams.length"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
            <div class="w-1 h-5 rounded-full bg-green-500"/>
            <h3 class="font-semibold text-gray-800">Bài kiểm tra cùng môn</h3>
          </div>
          <div class="p-4 grid sm:grid-cols-2 gap-3">
            <RouterLink v-for="e in relatedExams" :key="e.id" :to="`/student/exams`"
              class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-100 hover:border-green-200 hover:bg-green-50 transition-colors group">
              <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate group-hover:text-green-700">{{ e.title }}</p>
                <p class="text-xs text-gray-400">{{ e.duration_minutes }} phút</p>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Spacer for bottom nav -->
        <div class="h-4"/>
      </div>

      <!-- ── Bottom navigation ── -->
      <div class="bg-white border-t border-gray-100 shadow-[0_-1px_8px_rgba(0,0,0,0.06)]">
        <div class="max-w-3xl mx-auto px-5 py-3 flex items-center justify-between gap-3">
          <!-- Prev -->
          <button v-if="prevLesson" @click="router.push(`/student/lessons/${prevLesson.id}/learn`)"
            class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors text-sm text-gray-600 hover:text-indigo-700 group max-w-xs min-w-0">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="truncate text-left">
              <span class="block text-[10px] text-gray-400 leading-tight">Bài trước</span>
              <span class="font-medium leading-tight">{{ prevLesson.title }}</span>
            </span>
          </button>
          <div v-else/>

          <!-- Center: mark complete -->
          <button @click="markComplete" :disabled="isCompleted || marking"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors disabled:cursor-not-allowed shrink-0"
            :class="isCompleted
              ? 'bg-green-100 text-green-700 border border-green-200'
              : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isCompleted ? 'Đã hoàn thành' : (marking ? 'Đang lưu...' : 'Đánh dấu hoàn thành') }}
          </button>

          <!-- Next -->
          <button v-if="nextLesson" @click="router.push(`/student/lessons/${nextLesson.id}/learn`)"
            class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors text-sm text-gray-600 hover:text-indigo-700 group max-w-xs min-w-0">
            <span class="truncate text-right">
              <span class="block text-[10px] text-gray-400 leading-tight">Bài tiếp</span>
              <span class="font-medium leading-tight">{{ nextLesson.title }}</span>
            </span>
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <div v-else/>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@api/axios'

const route = useRoute()
const router = useRouter()

const lesson = ref(null)
const loading = ref(true)
const errorCode = ref(null)
const isCompleted = ref(false)
const marking = ref(false)
const relatedExams = ref([])
const prevLesson = ref(null)
const nextLesson = ref(null)
const videoEl = ref(null)

async function markComplete() {
  if (isCompleted.value || marking.value) return
  marking.value = true
  try {
    await api.post(`/student/lessons/${route.params.id}/progress`, { completed: true })
    isCompleted.value = true
  } finally {
    marking.value = false
  }
}

function onVideoEnd() {
  if (!isCompleted.value) markComplete()
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/student/lessons/${route.params.id}`)
    const payload = data.data ?? data
    lesson.value = payload.lesson ?? payload
    isCompleted.value = !!(payload.progress?.is_completed ?? payload.progress?.completed ?? false)
  } catch (err) {
    errorCode.value = err.response?.status ?? 0
    loading.value = false
    return
  }

  // Non-critical loads after lesson is confirmed accessible
  try {

    // Load siblings for prev/next navigation
    if (lesson.value.classroom_id) {
      try {
        const res = await api.get('/student/lessons', {
          params: { classroom_id: lesson.value.classroom_id, per_page: 200 },
        })
        const siblings = res.data.data?.data ?? res.data.data ?? []
        const idx = siblings.findIndex(l => l.id === lesson.value.id)
        if (idx > 0) prevLesson.value = siblings[idx - 1]
        if (idx >= 0 && idx < siblings.length - 1) nextLesson.value = siblings[idx + 1]
      } catch { /* navigation is optional */ }
    }

    // Related exams from same subject
    try {
      const ex = await api.get('/student/exams', { params: { per_page: 10 } })
      const all = ex.data.data?.data ?? ex.data.data ?? []
      relatedExams.value = all
        .filter(e => e.subject_id === (lesson.value.subject?.id ?? lesson.value.subject_id))
        .slice(0, 4)
    } catch { /* optional */ }
  } finally {
    loading.value = false
  }
})
</script>
