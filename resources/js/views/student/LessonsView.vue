<template>
  <div class="space-y-6 max-w-7xl mx-auto px-1 pb-12">
    <!-- Header with tab switches aligned to match ClassView styling -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
          <span>📚 Bài học của tôi</span>
        </h2>
        <p class="text-sm text-slate-500 mt-1">Các bài học lý thuyết và luyện tập dành cho bạn</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-100 p-1 shadow-sm flex items-center gap-1 shrink-0 self-start md:self-auto">
        <span class="px-4 py-2 rounded-xl text-xs font-extrabold bg-[#d63015] text-white shadow-sm select-none cursor-default uppercase tracking-wider">
          Tất cả bài học
        </span>
        <RouterLink to="/student/lessons/subjects"
          class="px-4 py-2 rounded-xl text-xs font-extrabold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-all uppercase tracking-wider">
          Theo môn học
        </RouterLink>
      </div>
    </div>

    <!-- Filter toolbar matching ClassView styling -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 flex flex-col sm:flex-row gap-4">
      <!-- Search bar -->
      <div class="relative flex-1">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input 
          v-model="search" 
          @input="debounceFetch" 
          type="text" 
          placeholder="Tìm kiếm bài học..."
          class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/20 focus:border-[#d63015] bg-white transition-all shadow-sm text-slate-700"
        />
      </div>
      
      <!-- Subject select dropdown -->
      <div class="w-full sm:w-64">
        <select 
          v-model="filterSubject" 
          @change="fetchData(1)" 
          class="w-full px-4 py-2.5 text-sm rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/20 focus:border-[#d63015] bg-white transition-all shadow-sm text-slate-750 font-bold"
        >
          <option value="">Tất cả môn học</option>
          <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
    </div>

    <!-- Skeletons loader -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm animate-pulse space-y-4">
        <div class="h-4 bg-slate-100 rounded w-1/4"/>
        <div class="space-y-2">
          <div class="h-6 bg-slate-100 rounded w-5/6"/>
          <div class="h-4 bg-slate-50 rounded w-2/3"/>
        </div>
        <div class="h-[1px] bg-slate-100 rounded"/>
        <div class="flex justify-between items-center pt-2">
          <div class="h-3 bg-slate-100 rounded w-1/3"/>
          <div class="h-8 bg-slate-200 rounded w-1/4"/>
        </div>
      </div>
    </div>

    <!-- Empty view -->
    <div v-else-if="!lessons.length" class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-3xl border border-slate-100 shadow-sm px-6 max-w-2xl mx-auto">
      <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-3xl mb-4">📚</div>
      <h3 class="font-bold text-slate-800 text-lg">Chưa có bài học nào</h3>
      <p class="text-sm text-slate-500 mt-1 max-w-sm leading-relaxed">Bài học từ giáo viên sẽ được cập nhật hiển thị tại đây khi xuất bản.</p>
    </div>

    <!-- Lesson Cards Grid aligned with ClassView layout -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="l in lessons" 
        :key="l.id"
        class="group bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl hover:border-red-500/10 hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between"
      >
        <div class="p-6">
          <div class="flex items-center justify-between mb-3.5 flex-wrap gap-2">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider"
                :style="{ backgroundColor: (l.subject?.color || '#d63015') + '15', color: l.subject?.color || '#d63015' }">
                {{ l.subject?.name || 'Môn học' }}
              </span>
              
              <!-- Real study progress loaded from backend -->
              <span v-if="l.progress?.is_completed" 
                class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">
                ✓ Đã học
              </span>
              <span v-else 
                class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-slate-50 text-slate-400 border border-slate-150">
                Chưa học
              </span>
            </div>
            <span class="text-[10px] text-slate-450 font-medium">Lượt xem: {{ l.view_count ?? 0 }}</span>
          </div>

          <!-- Title -->
          <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-[#c02a10] transition-colors mb-1.5">
            {{ l.title }}
          </h3>
          
          <!-- Description -->
          <p v-if="l.description" class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
            {{ l.description }}
          </p>
        </div>

        <!-- Meta info & Action button row -->
        <div class="flex items-center justify-between px-6 pb-6 pt-3 border-t border-slate-50">
          <span class="text-[10px] text-slate-400 font-semibold">{{ l.classroom?.name }}</span>
          <RouterLink :to="`/student/lessons/${l.id}`" class="px-3.5 py-2 bg-[#d63015]/10 hover:bg-[#d63015] text-[#d63015] hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
            <span>Học ngay</span>
            <span>📖</span>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Pagination -->
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
const loading = ref(true)
const search = ref('')
const filterSubject = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })

let debounceTimer = null
function debounceFetch() { 
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchData(1), 400) 
}

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/student/lessons', { 
      params: { 
        search: search.value || undefined, 
        subject_id: filterSubject.value || undefined, 
        page 
      } 
    })
    lessons.value = data.data?.data ?? data.data ?? []
    meta.value = data.meta ?? data.data ?? { total: 0, current_page: 1, last_page: 1 }
  } finally { 
    loading.value = false 
  }
}

onMounted(async () => {
  await fetchData(1)
  
  // Load list of all subjects (extracting from full non-paginated fetch or lookup)
  try {
    const allRes = await api.get('/student/lessons', { params: { per_page: 200 } })
    const all = allRes?.data?.data?.data ?? allRes?.data?.data ?? []
    const subjectSet = new Map()
    all.forEach(l => { 
      if (l.subject) subjectSet.set(l.subject.id, l.subject) 
    })
    subjects.value = [...subjectSet.values()]
  } catch { /* ignore */ }
})
</script>
