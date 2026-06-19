<template>
  <div class="space-y-6 max-w-7xl mx-auto px-1 pb-12">
    <!-- Header with tab switches aligned to match ClassView styling -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
          <span>🗂️ Bài học theo môn</span>
        </h2>
        <p class="text-sm text-slate-500 mt-1">Khám phá các bài học được phân loại theo từng môn học của bạn</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-100 p-1 shadow-sm flex items-center gap-1 shrink-0 self-start md:self-auto">
        <RouterLink to="/student/lessons"
          class="px-4 py-2 rounded-xl text-xs font-extrabold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-all uppercase tracking-wider">
          Tất cả bài học
        </RouterLink>
        <span class="px-4 py-2 rounded-xl text-xs font-extrabold bg-[#d63015] text-white shadow-sm select-none cursor-default uppercase tracking-wider">
          Theo môn học
        </span>
      </div>
    </div>

    <!-- Search bar card matching search styling of ClassView -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4">
      <div class="relative">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input 
          v-model="search" 
          @input="debounceFetch" 
          type="text" 
          placeholder="Tìm bài học trong các môn học..."
          class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/20 focus:border-[#d63015] bg-white transition-all shadow-sm text-slate-700"
        />
      </div>
    </div>

    <!-- Skeleton loader -->
    <template v-if="loading">
      <div v-for="i in 3" :key="i" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden animate-pulse mb-6">
        <div class="h-14 bg-slate-100 mx-6 mt-6 rounded-2xl"/>
        <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="j in 3" :key="j" class="h-36 bg-slate-50 rounded-2xl"/>
        </div>
      </div>
    </template>

    <!-- Empty view state -->
    <div v-else-if="!subjectsWithLessons.length"
      class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-3xl border border-slate-100 shadow-sm px-6 max-w-2xl mx-auto">
      <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-3xl mb-4">📚</div>
      <h3 class="font-bold text-slate-800 text-lg">Chưa có bài học nào</h3>
      <p class="text-sm text-slate-500 mt-1 max-w-sm leading-relaxed">Không tìm thấy bài học nào phù hợp với môn học của bạn.</p>
    </div>

    <!-- Grouped Subject Cards -->
    <template v-else>
      <div v-for="subject in subjectsWithLessons" :key="subject.id"
        class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between mb-6">

        <!-- Subject details header -->
        <div class="flex items-center justify-between px-6 py-4.5 border-b border-slate-50 bg-slate-55/40">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white text-sm font-extrabold shrink-0 shadow-sm"
              :style="{ backgroundColor: subject.color || '#d63015' }">
              {{ subject.name?.charAt(0)?.toUpperCase() }}
            </div>
            <div>
              <h3 class="font-extrabold text-slate-800 text-sm sm:text-base leading-tight">{{ subject.name }}</h3>
              <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ subject.lessons.length }} bài học</p>
            </div>
          </div>
          <RouterLink :to="`/student/lessons?subject_id=${subject.id}`"
            class="text-xs sm:text-sm text-[#d63015] hover:text-[#c02a10] font-bold flex items-center gap-1 transition-colors group">
            Xem tất cả
            <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </RouterLink>
        </div>

        <!-- Lessons items grid with consistent card design -->
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="lesson in (subject.expanded ? subject.lessons : subject.lessons.slice(0, 6))"
            :key="lesson.id"
            class="group bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl hover:border-red-500/10 hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between"
          >
            <div class="p-5">
              <!-- Status tags and view stats -->
              <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                <span v-if="lesson.progress?.is_completed"
                  class="text-[9px] font-extrabold px-2.5 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">
                  ✓ Đã học
                </span>
                <span v-else
                  class="text-[9px] font-extrabold px-2.5 py-0.5 rounded bg-slate-50 text-slate-400 border border-slate-150">
                  Chưa học
                </span>
                <span class="text-[10px] text-slate-450 font-medium">Lượt xem: {{ lesson.view_count ?? 0 }}</span>
              </div>
              
              <!-- Lesson title -->
              <h4 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-[#c02a10] transition-colors mb-1.5">
                {{ lesson.title }}
              </h4>
              <p v-if="lesson.description" class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                {{ lesson.description }}
              </p>
            </div>

            <!-- Meta info footers & action button -->
            <div class="flex items-center justify-between px-5 pb-5 pt-3 border-t border-slate-50">
              <span class="text-[10px] text-slate-450 font-semibold">{{ lesson.classroom?.name }}</span>
              <RouterLink :to="`/student/lessons/${lesson.id}`" class="px-3.5 py-2 bg-[#d63015]/10 hover:bg-[#d63015] text-[#d63015] hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                <span>Học ngay</span>
                <span>📖</span>
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Toggle collapse button -->
        <div v-if="subject.lessons.length > 6" class="px-6 pb-5 text-center">
          <button @click="subject.expanded = !subject.expanded"
            class="text-xs font-bold text-[#d63015] hover:text-[#c02a10] transition-colors inline-flex items-center gap-1.5 px-4 py-2 border border-slate-150 hover:bg-slate-50 rounded-xl cursor-pointer"
          >
            {{ subject.expanded ? 'Thu gọn bài giảng' : `Xem thêm ${subject.lessons.length - 6} bài giảng` }}
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
})
</script>
