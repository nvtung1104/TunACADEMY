<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Bài học của tôi</h2>
        <p class="text-sm text-gray-400 mt-0.5">Bài học từ các lớp bạn đang học</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm bài học..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
      </div>
      <select v-model="filterSubject" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
        <option value="">Tất cả môn học</option>
        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
        <div class="h-1.5 bg-gray-200 rounded mb-4 w-1/3"/>
        <div class="h-5 bg-gray-200 rounded mb-2 w-3/4"/>
        <div class="h-4 bg-gray-200 rounded mb-4 w-1/2"/>
        <div class="h-3 bg-gray-200 rounded w-2/3"/>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!lessons.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
      <div class="text-4xl mb-3">📚</div>
      <p class="text-gray-500 font-medium">Chưa có bài học nào</p>
      <p class="text-sm text-gray-400 mt-1">Bài học sẽ xuất hiện khi giáo viên xuất bản nội dung</p>
    </div>

    <!-- Grid -->
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <RouterLink v-for="l in lessons" :key="l.id" :to="`/student/lessons/${l.id}`"
        class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all overflow-hidden">
        <div class="h-1.5" :style="{ backgroundColor: l.subject?.color || '#6366f1' }"/>
        <div class="p-5">
          <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg"
              :style="{ backgroundColor: (l.subject?.color || '#6366f1') + '18', color: l.subject?.color || '#6366f1' }">
              {{ l.subject?.name }}
            </span>
            <span v-if="progressMap[l.id]" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-green-100 text-green-700">
              {{ progressMap[l.id] }}% hoàn thành
            </span>
          </div>
          <h3 class="font-semibold text-gray-800 line-clamp-2 mb-1 group-hover:text-indigo-700 transition-colors">{{ l.title }}</h3>
          <p v-if="l.description" class="text-xs text-gray-400 line-clamp-2 mb-3">{{ l.description }}</p>
          <div class="flex items-center justify-between text-xs text-gray-400 pt-3 border-t border-gray-50">
            <span>{{ l.classroom?.name }}</span>
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              {{ l.view_count ?? 0 }}
            </span>
          </div>
        </div>
      </RouterLink>
    </div>

    <Pagination :meta="meta" @page="fetchData"/>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'
import Pagination from '@components/common/Pagination.vue'

const lessons = ref([])
const subjects = ref([])
const progressMap = ref({})
const loading = ref(true)
const search = ref('')
const filterSubject = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })

let debounceTimer = null
function debounceFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 400) }

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/student/lessons', { params: { search: search.value, subject_id: filterSubject.value, page } })
    lessons.value = data.data?.data ?? data.data ?? []
    meta.value = data.meta ?? data.data ?? { total: 0, current_page: 1, last_page: 1 }
  } finally { loading.value = false }
}

onMounted(async () => {
  await fetchData()
  // Load subjects from lessons for filter
  const subjectSet = new Map()
  lessons.value.forEach(l => { if (l.subject) subjectSet.set(l.subject.id, l.subject) })
  subjects.value = [...subjectSet.values()]
})
</script>
