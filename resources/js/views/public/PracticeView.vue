<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Bài tập</h1>
      <p class="text-gray-500">Hàng trăm bài tập thực hành giúp củng cố kiến thức theo từng môn và khối lớp.</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-8">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Tìm kiếm tên bài tập..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
            @input="debouncedFetch"
          />
        </div>

        <select v-model="filters.grade_id" @change="fetchData"
          class="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm bg-white min-w-36">
          <option value="">Tất cả lớp</option>
          <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
        </select>

        <select v-model="filters.subject_id" @change="fetchData"
          class="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm bg-white min-w-40">
          <option value="">Tất cả môn</option>
          <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <button v-if="hasFilters" @click="resetFilters"
          class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors shrink-0">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Xóa lọc
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-1/3 mb-4" />
        <div class="h-6 bg-gray-200 rounded w-3/4 mb-3" />
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-6" />
        <div class="h-4 bg-gray-200 rounded w-2/3" />
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!assignments.length" class="text-center py-20">
      <div class="text-5xl mb-4">✏️</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy bài tập</h3>
      <p class="text-gray-400">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
    </div>

    <!-- Grid -->
    <div v-else>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <RouterLink v-for="assignment in assignments" :key="assignment.id"
          :to="'/practice/' + assignment.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-amber-100 transition-all overflow-hidden block no-underline text-inherit">
          <div class="h-1.5 w-full" :style="{ backgroundColor: assignment.subject?.color || '#f59e0b' }" />
          <div class="p-6">
            <div class="flex items-center gap-2 mb-3 flex-wrap">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                :style="{ backgroundColor: (assignment.subject?.color || '#f59e0b') + '18', color: assignment.subject?.color || '#f59e0b' }">
                {{ assignment.subject?.name ?? 'Môn học' }}
              </span>
              <span v-if="assignment.classroom?.grade?.name"
                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
                {{ assignment.classroom.grade.name }}
              </span>
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                :class="typeClass(assignment.type)">
                {{ typeLabel(assignment.type) }}
              </span>
            </div>

            <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-amber-600 transition-colors">
              {{ assignment.title }}
            </h3>

            <p v-if="assignment.description" class="text-sm text-gray-400 line-clamp-2 mb-4">
              {{ assignment.description }}
            </p>

            <div class="flex items-center justify-between text-xs text-gray-400 pt-4 border-t border-gray-50">
              <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Hạn: {{ formatDate(assignment.deadline) }}
              </div>
              <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                {{ assignment.max_score }} điểm
              </div>
            </div>
          </div>
        </RouterLink>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-10">
        <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page === 1"
          class="p-2.5 rounded-xl border border-gray-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <template v-for="page in pageNumbers" :key="page">
          <span v-if="page === '...'" class="px-2 text-gray-400 text-sm">...</span>
          <button v-else @click="goPage(page)"
            class="w-10 h-10 rounded-xl border text-sm font-medium transition-colors"
            :class="page === meta.current_page
              ? 'bg-indigo-600 border-indigo-600 text-white'
              : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
            {{ page }}
          </button>
        </template>
        <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
          class="p-2.5 rounded-xl border border-gray-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <p class="text-center text-sm text-gray-400 mt-4">
        Hiển thị {{ assignments.length }} / {{ meta.total ?? 0 }} bài tập
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import publicApi from '@api/public'

const assignments = ref([])
const grades = ref([])
const subjects = ref([])
const loading = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })

const filters = reactive({ search: '', grade_id: '', subject_id: '' })

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchData(), 400)
}

const hasFilters = computed(() => filters.search || filters.grade_id || filters.subject_id)

const pageNumbers = computed(() => {
  const total = meta.value.last_page
  const current = meta.value.current_page
  const pages = []
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
    if (current < total - 2) pages.push('...')
    pages.push(total)
  }
  return pages
})

function typeLabel(type) {
  const map = { homework: 'Bài tập', project: 'Dự án', essay: 'Bài luận', quiz: 'Kiểm tra nhanh' }
  return map[type] ?? type ?? 'Bài tập'
}

function typeClass(type) {
  const map = {
    homework: 'bg-blue-50 text-blue-700',
    project: 'bg-purple-50 text-purple-700',
    essay: 'bg-green-50 text-green-700',
    quiz: 'bg-orange-50 text-orange-700',
  }
  return map[type] ?? 'bg-gray-100 text-gray-600'
}

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await publicApi.assignments({ ...filters, page })
    assignments.value = data.data
    meta.value = data.meta ?? { current_page: data.current_page, last_page: data.last_page, total: data.total }
  } catch {
    assignments.value = []
  } finally {
    loading.value = false
  }
}

async function loadFilters() {
  const [gradesRes, subjectsRes] = await Promise.all([publicApi.grades(), publicApi.subjects()])
  grades.value = gradesRes.data.data
  subjects.value = subjectsRes.data.data
}

function resetFilters() {
  filters.search = ''
  filters.grade_id = ''
  filters.subject_id = ''
  fetchData()
}

function goPage(page) {
  if (page < 1 || page > meta.value.last_page) return
  fetchData(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  loadFilters()
  fetchData()
})
</script>
