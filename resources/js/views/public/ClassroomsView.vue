<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Lớp học</h1>
      <p class="text-gray-500">Danh sách tất cả lớp học đang hoạt động trong hệ thống.</p>
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
            placeholder="Tìm kiếm tên lớp học..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] focus:border-transparent text-sm"
            @input="debouncedFetch"
          />
        </div>

        <select v-model="filters.grade_id" @change="fetchData"
          class="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm bg-white min-w-36">
          <option value="">Tất cả khối</option>
          <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
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
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-2xl bg-gray-200" />
          <div class="flex-1">
            <div class="h-5 bg-gray-200 rounded w-2/3 mb-2" />
            <div class="h-4 bg-gray-200 rounded w-1/2" />
          </div>
        </div>
        <div class="h-4 bg-gray-200 rounded w-full mb-2" />
        <div class="h-4 bg-gray-200 rounded w-3/4" />
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!classrooms.length" class="text-center py-20">
      <div class="text-5xl mb-4">🏫</div>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy lớp học</h3>
      <p class="text-gray-400">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
    </div>

    <!-- Grid -->
    <div v-else>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="(classroom, index) in classrooms" :key="classroom.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-red-100 transition-all p-6 animate__animated animate__fadeInUp card-animate"
          :style="{ animationDelay: `${index * 75}ms` }">
          <!-- Icon + name -->
          <div class="flex items-start gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
              :class="gradeColorClass(classroom.grade?.level)">
              <span class="text-lg font-bold">{{ classroom.grade?.level ?? '?' }}</span>
            </div>
            <div class="min-w-0">
              <h3 class="text-base font-bold text-gray-900 group-hover:text-[#c02a10] transition-colors truncate">
                {{ classroom.name }}
              </h3>
              <p class="text-sm text-gray-500 mt-0.5">{{ classroom.grade?.name ?? '' }}</p>
            </div>
          </div>

          <!-- Info list -->
          <div class="space-y-2.5">
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>Năm học: <span class="font-medium text-gray-800">{{ classroom.school_year?.name ?? '—' }}</span></span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>GVCN: <span class="font-medium text-gray-800">{{ classroom.homeroom_teacher?.name ?? '—' }}</span></span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>Học sinh: <span class="font-medium text-gray-800">{{ classroom.students_count ?? 0 }} / {{ classroom.max_students ?? '—' }}</span></span>
            </div>
          </div>

          <!-- Progress bar -->
          <div v-if="classroom.max_students" class="mt-4">
            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full rounded-full bg-red-500 transition-all"
                :style="{ width: Math.min(100, ((classroom.students_count ?? 0) / classroom.max_students) * 100) + '%' }" />
            </div>
          </div>
        </div>
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
              ? 'bg-[#d63015] border-[#d63015] text-white'
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
        Hiển thị {{ classrooms.length }} / {{ meta.total ?? 0 }} lớp học
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import publicApi from '@api/public'

const classrooms = ref([])
const grades = ref([])
const loading = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })

const filters = reactive({ search: '', grade_id: '' })

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchData(), 400)
}

const hasFilters = computed(() => filters.search || filters.grade_id)

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

const gradeColors = [
  'bg-red-100 text-red-700',
  'bg-orange-100 text-orange-700',
  'bg-amber-100 text-amber-700',
  'bg-yellow-100 text-yellow-700',
  'bg-lime-100 text-lime-700',
  'bg-green-100 text-green-700',
  'bg-teal-100 text-teal-700',
  'bg-cyan-100 text-cyan-700',
  'bg-sky-100 text-sky-700',
  'bg-blue-100 text-blue-700',
  'bg-red-100 text-[#c02a10]',
  'bg-purple-100 text-purple-700',
]

function gradeColorClass(level) {
  return gradeColors[((level ?? 1) - 1) % gradeColors.length]
}

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await publicApi.classrooms({ ...filters, page })
    classrooms.value = data.data
    meta.value = data.meta ?? { current_page: data.current_page, last_page: data.last_page, total: data.total }
  } catch {
    classrooms.value = []
  } finally {
    loading.value = false
  }
}

async function loadGrades() {
  const { data } = await publicApi.grades()
  grades.value = data.data
}

function resetFilters() {
  filters.search = ''
  filters.grade_id = ''
  fetchData()
}

function goPage(page) {
  if (page < 1 || page > meta.value.last_page) return
  fetchData(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  loadGrades()
  fetchData()
})
</script>
