<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Loading -->
    <div v-if="loading" class="py-20 text-center">
      <div class="animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-3" />
      <p class="text-sm text-gray-400">Đang tải bài học...</p>
    </div>

    <!-- Not found -->
    <div v-else-if="!lesson" class="py-20 text-center">
      <div class="text-5xl mb-4">😕</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy bài học</h3>
      <RouterLink to="/lessons" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">← Quay lại danh sách</RouterLink>
    </div>

    <template v-else>
      <!-- Back -->
      <RouterLink to="/lessons" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Quay lại danh sách bài học
      </RouterLink>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-5">
          <!-- Header card -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="h-2" :style="{ backgroundColor: lesson.subject?.color || '#6366f1' }" />
            <div class="p-6">
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-sm font-semibold px-3 py-1 rounded-xl"
                  :style="{ backgroundColor: (lesson.subject?.color || '#6366f1') + '18', color: lesson.subject?.color || '#6366f1' }">
                  {{ lesson.subject?.name }}
                </span>
                <span v-if="lesson.classroom?.grade?.name"
                  class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-xl font-medium">
                  {{ lesson.classroom.grade.name }}
                </span>
                <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-xl">{{ lesson.classroom?.name }}</span>
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
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Video bài học</h3>
            </div>
            <div class="p-5">
              <video :src="lesson.video_path" controls class="w-full rounded-xl bg-black aspect-video" />
            </div>
          </div>

          <!-- Content -->
          <div v-if="lesson.content" class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Nội dung bài học</h3>
            </div>
            <div class="p-6 prose prose-sm max-w-none text-gray-700 leading-relaxed" v-html="lesson.content" />
          </div>

          <!-- Materials -->
          <div v-if="lesson.materials?.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Tài liệu đính kèm</h3>
            </div>
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
          <!-- CTA card -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <!-- Student: go to learn page -->
            <template v-if="auth.isStudent">
              <RouterLink :to="`/student/lessons/${route.params.id}/learn`"
                class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Vào học bài này
              </RouterLink>
            </template>
            <!-- Not logged in: go to login -->
            <template v-else-if="!auth.isLoggedIn">
              <RouterLink :to="`/login?redirect=/student/lessons/${route.params.id}/learn`"
                class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                Đăng nhập để học
              </RouterLink>
            </template>

            <!-- Save button (all logged-in users) -->
            <button v-if="auth.isLoggedIn" @click="toggleSave"
              :disabled="savePending"
              class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold border transition-colors"
              :class="saved
                ? 'bg-amber-50 border-amber-300 text-amber-700 hover:bg-amber-100'
                : 'bg-white border-gray-200 text-gray-600 hover:border-amber-300 hover:text-amber-600'">
              <svg class="w-4 h-4" :fill="saved ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              {{ saved ? 'Đã lưu bài' : 'Lưu bài' }}
            </button>
            <!-- Not logged in save hint -->
            <RouterLink v-else to="/login"
              class="flex items-center justify-center gap-2 w-full py-2 rounded-xl text-sm text-gray-500 border border-dashed border-gray-200 hover:border-amber-300 hover:text-amber-600 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              Đăng nhập để lưu bài
            </RouterLink>
          </div>

          <!-- Subject info -->
          <div v-if="lesson.subject" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Môn học</h3>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                :style="{ backgroundColor: (lesson.subject.color || '#6366f1') + '18' }">
                <span class="text-lg">{{ lesson.subject.icon ?? '📚' }}</span>
              </div>
              <div>
                <p class="font-medium text-gray-800 text-sm">{{ lesson.subject.name }}</p>
                <p v-if="lesson.classroom?.grade?.name" class="text-xs text-gray-400">{{ lesson.classroom.grade.name }}</p>
              </div>
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
import publicApi from '@api/public'
import bookmarkApi from '@api/bookmarks'
import { useAuthStore } from '@stores/auth'

const route = useRoute()
const auth = useAuthStore()
const lesson = ref(null)
const loading = ref(true)
const saved = ref(false)
const savePending = ref(false)

async function toggleSave() {
  savePending.value = true
  try {
    const { data } = await bookmarkApi.toggle('lesson', route.params.id)
    saved.value = data.saved
  } finally {
    savePending.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await publicApi.lesson(route.params.id)
    lesson.value = data.data ?? data
  } catch {
    lesson.value = null
  } finally {
    loading.value = false
  }

  if (auth.isLoggedIn) {
    try {
      const { data } = await bookmarkApi.check('lesson', route.params.id)
      saved.value = data.saved
    } catch {}
  }
})
</script>
