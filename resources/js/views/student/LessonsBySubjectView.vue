<template>
  <div class="space-y-6">
    <!-- Header + Tabs -->
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Bài học theo môn học</h2>
        <p class="text-sm text-gray-400 mt-0.5">Khám phá bài học được phân theo từng môn</p>
      </div>
      <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
        <RouterLink to="/student/lessons"
          class="px-4 py-1.5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
          Tất cả
        </RouterLink>
        <span class="px-4 py-1.5 rounded-lg text-sm font-medium bg-white text-indigo-600 shadow-sm">
          Theo môn học
        </span>
      </div>
    </div>

    <!-- Search bar -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm bài học trong tất cả các môn..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
      </div>
    </div>

    <!-- Skeleton loading -->
    <template v-if="loading">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden animate-pulse">
        <div class="h-14 bg-gray-100 mx-5 mt-5 rounded-xl"/>
        <div class="p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="j in 3" :key="j" class="h-32 bg-gray-100 rounded-xl"/>
        </div>
      </div>
    </template>

    <!-- Empty state -->
    <div v-else-if="!subjectsWithLessons.length"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
      <div class="text-4xl mb-3">📚</div>
      <p class="text-gray-500 font-medium">Chưa có bài học nào</p>
      <p class="text-sm text-gray-400 mt-1">Bài học sẽ xuất hiện khi giáo viên xuất bản nội dung</p>
    </div>

    <!-- Subject sections -->
    <template v-else>
      <div v-for="subject in subjectsWithLessons" :key="subject.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <!-- Subject header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
              :style="{ backgroundColor: subject.color || '#6366f1' }">
              {{ subject.name?.charAt(0)?.toUpperCase() }}
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">{{ subject.name }}</h3>
              <p class="text-xs text-gray-400">{{ subject.lessons.length }} bài học</p>
            </div>
          </div>
          <RouterLink :to="`/student/lessons?subject_id=${subject.id}`"
            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors">
            Xem tất cả
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </RouterLink>
        </div>

        <!-- Lesson cards grid -->
        <div class="p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <RouterLink
            v-for="lesson in (subject.expanded ? subject.lessons : subject.lessons.slice(0, 6))"
            :key="lesson.id"
            :to="`/student/lessons/${lesson.id}`"
            class="group relative rounded-xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all overflow-hidden bg-gray-50 hover:bg-white">
            <!-- Color accent top bar -->
            <div class="h-1" :style="{ backgroundColor: subject.color || '#6366f1' }"/>
            <div class="p-4">
              <!-- Progress + completed badges -->
              <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                <span v-if="progressMap[lesson.id] >= 100"
                  class="text-xs font-semibold px-2 py-0.5 rounded-lg bg-green-100 text-green-700">
                  ✓ Hoàn thành
                </span>
                <span v-else-if="progressMap[lesson.id] > 0"
                  class="text-xs font-semibold px-2 py-0.5 rounded-lg bg-amber-100 text-amber-700">
                  {{ progressMap[lesson.id] }}%
                </span>
              </div>
              <!-- Title -->
              <h4 class="text-sm font-semibold text-gray-800 line-clamp-2 group-hover:text-indigo-700 transition-colors mb-1">
                {{ lesson.title }}
              </h4>
              <p v-if="lesson.description" class="text-xs text-gray-400 line-clamp-1 mb-3">
                {{ lesson.description }}
              </p>
              <!-- Meta row -->
              <div class="flex items-center justify-between text-xs text-gray-400 pt-2 border-t border-gray-100">
                <span class="truncate">{{ lesson.classroom?.name }}</span>
                <span class="flex items-center gap-1 shrink-0">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  {{ lesson.view_count ?? 0 }}
                </span>
              </div>
            </div>
          </RouterLink>
        </div>

        <!-- Show more / less -->
        <div v-if="subject.lessons.length > 6" class="px-5 pb-4 text-center">
          <button @click="subject.expanded = !subject.expanded"
            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
            {{ subject.expanded ? '← Thu gọn' : `Xem thêm ${subject.lessons.length - 6} bài →` }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'

const allLessons = ref([])
const progressMap = ref({})
const loading = ref(true)
const search = ref('')

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchData(), 400)
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await api.get('/student/lessons', {
      params: { per_page: 200, search: search.value || undefined },
    })
    allLessons.value = data.data?.data ?? data.data ?? []
  } finally {
    loading.value = false
  }
}

const subjectsWithLessons = computed(() => {
  const map = new Map()
  for (const lesson of allLessons.value) {
    if (!lesson.subject) continue
    const sid = lesson.subject.id
    if (!map.has(sid)) {
      map.set(sid, { ...lesson.subject, lessons: [], expanded: false })
    }
    map.get(sid).lessons.push(lesson)
  }
  return [...map.values()]
})

onMounted(async () => {
  await fetchData()
  // Load progress in background
  try {
    const { data } = await api.get('/student/lessons', { params: { per_page: 200 } })
    const lessons = data.data?.data ?? data.data ?? []
    lessons.forEach(l => {
      if (l.progress?.progress_percent != null) {
        progressMap.value[l.id] = l.progress.progress_percent
      }
    })
  } catch { /* progress is optional */ }
})
</script>
