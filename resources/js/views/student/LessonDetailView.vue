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
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-semibold text-gray-800">Tài liệu đính kèm</h3>
              <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ lesson.materials.length }} file</span>
            </div>
            <div class="p-4 space-y-2">
              <div v-for="m in lesson.materials" :key="m.id"
                class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-100 transition-all"
                :class="materialHoverClass(m.file_type)">
                <!-- File type icon -->
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-white text-xs font-bold"
                  :class="materialBgClass(m.file_type)">
                  {{ materialLabel(m.file_type) }}
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ m.file_name }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ materialTypeName(m.file_type) }}
                    <span v-if="m.file_size"> · {{ formatSize(m.file_size) }}</span>
                    <span v-if="m.download_count"> · {{ m.download_count }} lượt tải</span>
                  </p>
                </div>
                <!-- Actions -->
                <div class="flex items-center gap-1.5 shrink-0">
                  <button @click="openPreview(m)"
                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors"
                    :class="materialActionClass(m.file_type)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Xem
                  </button>
                  <a :href="`/api/student/lessons/${lesson.id}/materials/${m.id}/download`"
                    :download="m.file_name"
                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Tải về
                  </a>
                </div>
              </div>
            </div>
          </div>

          <MaterialPreviewModal v-model="previewOpen" :material="previewMaterial" />
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
import MaterialPreviewModal from '@components/common/MaterialPreviewModal.vue'

const route = useRoute()
const lesson = ref(null)
const loading = ref(true)
const progress = ref(0)
const markingProgress = ref(false)
const relatedExams = ref([])
const previewOpen = ref(false)
const previewMaterial = ref(null)

function openPreview(m) {
  previewMaterial.value = m
  previewOpen.value = true
}
function materialActionClass(type) {
  return { pdf: 'bg-red-50 text-red-600 hover:bg-red-100', word: 'bg-blue-50 text-blue-600 hover:bg-blue-100', ppt: 'bg-orange-50 text-orange-600 hover:bg-orange-100' }[type] ?? 'bg-gray-50 text-gray-600 hover:bg-gray-100'
}
function materialBgClass(type) {
  return { pdf: 'bg-red-500', word: 'bg-blue-500', ppt: 'bg-orange-500' }[type] ?? 'bg-gray-400'
}
function materialHoverClass(type) {
  return { pdf: 'hover:border-red-200 hover:bg-red-50', word: 'hover:border-blue-200 hover:bg-blue-50', ppt: 'hover:border-orange-200 hover:bg-orange-50' }[type] ?? 'hover:border-gray-200 hover:bg-gray-50'
}
function materialLabel(type) {
  return { pdf: 'PDF', word: 'DOC', ppt: 'PPT' }[type] ?? 'FILE'
}
function materialTypeName(type) {
  return { pdf: 'PDF Document', word: 'Word Document', ppt: 'PowerPoint' }[type] ?? 'Tài liệu'
}
function materialTextClass(type) {
  return { pdf: 'text-red-500', word: 'text-blue-500', ppt: 'text-orange-500' }[type] ?? 'text-gray-500'
}
function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1024 / 1024).toFixed(1) + ' MB'
}

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
