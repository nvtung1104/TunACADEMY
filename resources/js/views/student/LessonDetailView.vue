<template>
  <div>
    <div v-if="loading" class="py-16 text-center">
      <div class="animate-spin w-7 h-7 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-3"/>
      <p class="text-sm text-gray-400">Đang tải bài học...</p>
    </div>

    <div v-else-if="!lesson" class="py-16 text-center">
      <div class="text-4xl mb-3">😕</div>
      <p class="text-gray-500">Không tìm thấy bài học này.</p>
      <RouterLink to="/student/lessons" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">← Quay lại</RouterLink>
    </div>

    <template v-else>
      <RouterLink to="/student/lessons" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Quay lại danh sách bài học
      </RouterLink>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-5">
          <!-- Header -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="h-2" :style="{ backgroundColor: lesson.subject?.color || '#6366f1' }"/>
            <div class="p-6">
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-sm font-semibold px-3 py-1 rounded-xl"
                  :style="{ backgroundColor: (lesson.subject?.color || '#6366f1') + '18', color: lesson.subject?.color || '#6366f1' }">
                  {{ lesson.subject?.name }}
                </span>
                <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-xl">{{ lesson.classroom?.name }}</span>
              </div>
              <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ lesson.title }}</h1>
              <p v-if="lesson.description" class="text-gray-500 leading-relaxed">{{ lesson.description }}</p>
              <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-50 text-sm text-gray-400">
                <span>GV: {{ lesson.teacher?.name }}</span>
                <span>{{ lesson.view_count ?? 0 }} lượt xem</span>
              </div>
            </div>
          </div>

          <!-- Video -->
          <div v-if="lesson.video_path" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Video bài học</h3></div>
            <div class="p-5">
              <video :src="lesson.video_path" controls class="w-full rounded-xl bg-black aspect-video"/>
            </div>
          </div>

          <!-- Content -->
          <div v-if="lesson.content" class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Nội dung bài học</h3></div>
            <div class="p-6 prose prose-sm max-w-none text-gray-700 leading-relaxed" v-html="lesson.content"/>
          </div>

          <!-- Materials -->
          <div v-if="lesson.materials?.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Tài liệu đính kèm</h3></div>
            <div class="p-4 space-y-2">
              <a v-for="m in lesson.materials" :key="m.id" :href="m.file_path" target="_blank"
                class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition-colors group">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-700">{{ m.title ?? m.file_name }}</p>
                  <p class="text-xs text-gray-400">{{ m.file_type ?? 'Tài liệu' }}</p>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Progress card -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Tiến độ học tập</h3>
            <div class="relative w-28 h-28 mx-auto mb-4">
              <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                <circle cx="50" cy="50" r="40" fill="none"
                  :stroke="lesson.subject?.color || '#6366f1'"
                  stroke-width="10" stroke-linecap="round"
                  :stroke-dasharray="`${2.513 * progress} 251.3`"
                  class="transition-all duration-700"/>
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-2xl font-bold text-gray-800">{{ progress }}%</span>
              </div>
            </div>
            <button @click="markComplete" :disabled="progress >= 100 || markingProgress"
              class="w-full py-2.5 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :class="progress >= 100 ? 'bg-green-100 text-green-700' : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
              {{ progress >= 100 ? '✓ Đã hoàn thành' : (markingProgress ? 'Đang lưu...' : 'Đánh dấu hoàn thành') }}
            </button>
          </div>

          <!-- Related exams -->
          <div v-if="relatedExams.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Bài kiểm tra</h3>
            </div>
            <div class="divide-y divide-gray-50">
              <RouterLink v-for="e in relatedExams" :key="e.id" to="/student/exams"
                class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                  <svg class="w-3.5 h-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ e.title }}</p>
                  <p class="text-xs text-gray-400">{{ e.duration_minutes }} phút</p>
                </div>
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@api/axios'

const route = useRoute()
const lesson = ref(null)
const loading = ref(true)
const progress = ref(0)
const markingProgress = ref(false)
const relatedExams = ref([])

async function markComplete() {
  markingProgress.value = true
  try {
    await api.post(`/student/lessons/${route.params.id}/progress`, { progress: 100 })
    progress.value = 100
  } finally { markingProgress.value = false }
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/student/lessons/${route.params.id}`)
    lesson.value = data.data ?? data
    progress.value = lesson.value.progress?.progress_percent ?? 0
    // Related exams from same subject
    try {
      const ex = await api.get('/student/exams', { params: { per_page: 5 } })
      const all = ex.data.data?.data ?? ex.data.data ?? []
      relatedExams.value = all.filter(e => e.subject_id === lesson.value.subject_id).slice(0, 3)
    } catch { /* ignore */ }
  } finally { loading.value = false }
})
</script>
